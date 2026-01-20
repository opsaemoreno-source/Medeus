<?php

namespace App\Services;

use Google\Cloud\BigQuery\BigQueryClient;

class EncuestasService
{
    protected BigQueryClient $bigQuery;

    protected string $dataset = 'UsuariosOPSA';
    protected string $tablaUsuarios = 'vta_usuariosEvolok';
    protected string $tablaEncuestas = 'EncuestasTypeform';
    protected string $tablaEncuestasDetalle = 'EncuestasTypeformDetalle';

    public function __construct()
    {
        $this->bigQuery = new BigQueryClient([
            'projectId' => env('GOOGLE_PROJECT_ID'),
            'keyFilePath' => storage_path('app/google/bigquery.json'),
        ]);
    }

    /**
     * KPIs generales de encuestas
     */
    public function kpis(?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        // Filtro de fechas opcional
        $whereFecha = '';
        if ($fechaInicio && $fechaFin) {
            $whereFecha = "AND d.fechaFin BETWEEN '$fechaInicio 00:00:00' AND '$fechaFin 23:59:59'";
        }

        $query = "
            WITH respuestas_por_usuario AS (
                SELECT
                    d.userid,
                    d.idEncuesta,
                    COUNT(DISTINCT d.idPregunta) AS respuestas_usuario
                FROM `{$this->dataset}.EncuestasTypeformDetalle` d
                INNER JOIN `{$this->dataset}.{$this->tablaUsuarios}` u
                    ON u.userid = d.userid
                WHERE d.userid IS NOT NULL AND d.userid != ''
                $whereFecha
                GROUP BY d.userid, d.idEncuesta
            ),
            encuestas AS (
                SELECT id, noCampos
                FROM `{$this->dataset}.EncuestasTypeform`
            )
            SELECT
                COUNT(DISTINCT r.userid) AS usuarios_respondieron,
                ANY_VALUE(total_usuarios.total) AS total_usuarios,
                ROUND(AVG(SAFE_DIVIDE(r.respuestas_usuario, e.noCampos) * 100), 2) AS porcentaje_completacion
            FROM respuestas_por_usuario r
            INNER JOIN encuestas e
                ON e.id = r.idEncuesta
            CROSS JOIN (
                SELECT COUNT(*) AS total
                FROM `{$this->dataset}.{$this->tablaUsuarios}`
            ) total_usuarios
        ";

        $rows = $this->runQuery($query);
        $row = $rows[0] ?? [];

        return [
            'usuarios_respondieron'   => (int) $row['usuarios_respondieron'],
            'total_usuarios'          => (int) $row['total_usuarios'],
            'porcentaje_completacion' => (float) ($row['porcentaje_completacion'] ?? 0),
        ];
    }

    /**
     * Demografía de usuarios que respondieron encuestas
     */
    public function demografia(string $campo): array
    {
        $query = "
            SELECT
                COALESCE(NULLIF(TRIM(CAST(u.$campo AS STRING)), ''), 'Sin datos') AS categoria,
                COUNT(DISTINCT u.userid) AS total
            FROM `{$this->dataset}.{$this->tablaEncuestasDetalle}` d
            INNER JOIN `{$this->dataset}.{$this->tablaUsuarios}` u
                ON u.userid = d.userid
            WHERE d.userid IS NOT NULL
            AND d.userid != ''
            GROUP BY categoria
            ORDER BY total DESC
        ";

        return $this->runQuery($query);
    }

    protected function runQuery(string $query): array
    {
        $job = $this->bigQuery->query($query);
        $results = $this->bigQuery->runQuery($job);

        $data = [];
        foreach ($results->rows() as $row) {
            $data[] = $row;
        }

        return $data;
    }
}
