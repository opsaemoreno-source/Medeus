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
        // Filtro opcional de fecha
        $whereFecha = '';
        if ($fechaInicio && $fechaFin) {
            $whereFecha = "AND d.fechaFin BETWEEN '$fechaInicio 00:00:00' AND '$fechaFin 23:59:59'";
        }

        $query = "
            WITH respuestas_por_token AS (
                SELECT
                    d.idEncuesta,
                    d.token,
                    d.userid,
                    COUNT(DISTINCT d.idPregunta) AS respuestas_token
                FROM `UsuariosOPSA.EncuestasTypeformDetalle` d
                WHERE d.token IS NOT NULL
                $whereFecha
                GROUP BY d.idEncuesta, d.token, d.userid
            ),

            encuestas AS (
                SELECT id, noCampos
                FROM `UsuariosOPSA.EncuestasTypeform`
            ),

            pct_por_token AS (
                SELECT
                    r.userid,
                    r.token,
                    SAFE_DIVIDE(r.respuestas_token, e.noCampos) * 100 AS pct_completacion_token
                FROM respuestas_por_token r
                INNER JOIN encuestas e
                    ON e.id = r.idEncuesta
            ),

            usuarios_normalizados AS (
                SELECT
                    CASE
                        WHEN userid IS NOT NULL AND userid != 'Guest'
                            THEN userid
                        ELSE token
                    END AS usuario_unico,
                    userid,
                    token,
                    pct_completacion_token
                FROM pct_por_token
            )

            SELECT
                COUNT(DISTINCT IF(userid IS NOT NULL AND userid != 'Guest', userid, NULL))
                    AS usuarios_registrados,

                COUNT(DISTINCT IF(userid = 'Guest' OR userid IS NULL, token, NULL))
                    AS usuarios_no_registrados,

                COUNT(DISTINCT usuario_unico)
                    AS total_usuarios_encuesta,

                ROUND(AVG(pct_completacion_token), 2)
                    AS porcentaje_completacion
            FROM usuarios_normalizados
        ";

        $rows = $this->runQuery($query);
        $row = $rows[0] ?? [];

        return [
            'usuarios_registrados'       => (int) $row['usuarios_registrados'],
            'usuarios_no_registrados'    => (int) $row['usuarios_no_registrados'],
            'total_usuarios_encuesta'    => (int) $row['total_usuarios_encuesta'],
            'porcentaje_completacion' => (float) ($row['porcentaje_completacion'] ?? 0),
        ];
    }

    /**
     * Demografía de usuarios que respondieron encuestas
     */
    public function demografia(string $campo, ?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $whereFecha = '';
        if ($fechaInicio && $fechaFin) {
            $whereFecha = "AND d.fechaFin BETWEEN '$fechaInicio 00:00:00' AND '$fechaFin 23:59:59'";
        }
        $setLimit = "";
        $joinExtra = '';
        if ($campo === 'genero') {
            $categoriaSql = "
                CASE
                    WHEN LOWER(TRIM(u.genero)) IN ('f', 'femenino', 'female', 'mujer')
                        THEN 'Femenino'
                    WHEN LOWER(TRIM(u.genero)) IN ('m', 'masculino', 'male', 'hombre')
                        THEN 'Masculino'
                    ELSE 'Sin datos'
                END
            ";
        } elseif ($campo === 'ciudad') {
            $categoriaSql = "
                COALESCE(
                    c.ciudad_canonica,
                    COALESCE(NULLIF(TRIM(u.ciudad), ''), 'Sin datos')
                )
            ";
            $joinExtra = "
                LEFT JOIN `{$this->dataset}.vta_ciudadesNormalizadas` c
                    ON REGEXP_REPLACE(
                        REGEXP_REPLACE(
                            NORMALIZE(UPPER(u.ciudad), NFD),
                            r'\\p{M}',
                            ''
                        ),
                        r'[^A-Z0-9]',
                        ''
                    ) = c.alias_norm
            ";
            $setLimit = "LIMIT 10";
        } elseif ($campo === 'pais') {

            $categoriaSql = "
                COALESCE(
                    dp.label,
                    NULLIF(TRIM(u.pais), ''),
                    'Sin datos'
                )
            ";

            $joinExtra = "
                LEFT JOIN `{$this->dataset}.data_paises` dp
                    ON dp.idPaisAlter = u.pais
            ";
            $setLimit = "LIMIT 10";

        } elseif($campo === 'nivelEducativo')
        {
            $categoriaSql = "
                COALESCE(dne.label, u.nivelEducativo, 'Sin datos')
            ";
            $joinExtra = "
            LEFT JOIN `{$this->dataset}.data_nivelEducativo` dne
                ON dne.idNivEducativo = u.nivelEducativo
            ";
        }else {
            $categoriaSql = "
                COALESCE(
                    NULLIF(TRIM(CAST(u.$campo AS STRING)), ''),
                    'Sin datos'
                )
            ";
        }

        $query = "
            SELECT
                $categoriaSql AS categoria,
                COUNT(DISTINCT u.userid) AS total
            FROM `{$this->dataset}.{$this->tablaEncuestasDetalle}` d
            INNER JOIN `{$this->dataset}.{$this->tablaUsuarios}` u
                ON u.userid = d.userid
            $joinExtra
            WHERE d.userid IS NOT NULL
            AND d.userid != ''
            {$whereFecha}
            GROUP BY categoria
            ORDER BY total DESC
            {$setLimit}
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
