<?php

namespace App\Services;

use Google\Cloud\BigQuery\BigQueryClient;
use Exception; 

class SuscriptoresService
{
    protected $bigQuery;
    protected string $datasetId = 'UsuariosOPSA';
    protected string $tableId   = 'UsuariosEvolok';

    public function __construct()
    {
        $this->bigQuery = new BigQueryClient([
            'projectId' => env('GOOGLE_PROJECT_ID'),
            'keyFilePath' => storage_path('app/google/bigquery.json')
        ]);
    }

    /**
     * Estadísticas generales
     */
    public function obtenerEstadisticas(?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        try {
            return [
                'marca'           => $this->queryCountSimple('marca', $fechaInicio, $fechaFin),
                'genero'          => $this->queryCountSimple('genero', $fechaInicio, $fechaFin),
                'estadoCivil'     => $this->queryCountConCatalogo(
                    'estadoCivil',
                    'data_estadoCivil',
                    'idCivil',
                    'label',
                    $fechaInicio,
                    $fechaFin
                ),
                'nivelEducativo'  => $this->queryCountConCatalogo(
                    'nivelEducativo',
                    'data_nivelEducativo',
                    'idNivEducativo',
                    'label',
                    $fechaInicio,
                    $fechaFin
                ),
                'profesion'       => $this->queryCountConCatalogo(
                    'profesion',
                    'data_profesion',
                    'idProfesion',
                    'label',
                    $fechaInicio,
                    $fechaFin
                ),
                'pais'            => $this->queryCountSimple('pais', $fechaInicio, $fechaFin),
                'ciudad'          => $this->queryCountCiudadNormalizada($fechaInicio, $fechaFin),
                'canal'           => $this->queryCountSimple('canal', $fechaInicio, $fechaFin),
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Conteo simple por campo (sin catálogo)
     */
    protected function queryCountSimple(
        string $campo,
        ?string $fechaInicio = null,
        ?string $fechaFin = null
    ): array {

        $where = '';
        if ($fechaInicio && $fechaFin) {
            $where = "WHERE fechaCreacion BETWEEN '$fechaInicio 00:00:00' AND '$fechaFin 23:59:59'";
        }

        if ($campo === 'genero') {
            $query = "
                SELECT
                    CASE LOWER(TRIM($campo))
                        WHEN 'femenino' THEN 'Femenino'
                        WHEN 'masculino' THEN 'Masculino'
                        ELSE COALESCE(NULLIF(TRIM($campo), ''), 'Sin datos')
                    END AS categoria,
                    COUNT(*) AS total
                FROM `{$this->datasetId}.{$this->tableId}`
                $where
                GROUP BY categoria
                ORDER BY total DESC
            ";
        } else {
            $query = "
                SELECT 
                    COALESCE(NULLIF(TRIM(CAST($campo AS STRING)), ''), 'Sin datos') AS categoria,
                    COUNT(*) AS total
                FROM `{$this->datasetId}.{$this->tableId}`
                $where
                GROUP BY categoria
                ORDER BY total DESC
            ";
        }

        return $this->runQuery($query);
    }

    /**
     * Conteo por campo con tabla de referencia
     */
    protected function queryCountConCatalogo(
        string $campoUsuario,
        string $tablaCatalogo,
        string $idCatalogo,
        string $labelCatalogo,
        ?string $fechaInicio = null,
        ?string $fechaFin = null
    ): array {

        $where = '';
        if ($fechaInicio && $fechaFin) {
            $where = "WHERE u.fechaCreacion BETWEEN '$fechaInicio 00:00:00' AND '$fechaFin 23:59:59'";
        }

        $query = "
            SELECT
                COALESCE(
                    NULLIF(TRIM(cat.$labelCatalogo), ''),
                    NULLIF(TRIM(CAST(u.$campoUsuario AS STRING)), ''),
                    'Sin datos'
                ) AS categoria,
                COUNT(*) AS total
            FROM `{$this->datasetId}.{$this->tableId}` u
            LEFT JOIN `{$this->datasetId}.{$tablaCatalogo}` cat
                ON CAST(cat.$idCatalogo AS STRING)
                = CAST(u.$campoUsuario AS STRING)
            $where
            GROUP BY categoria
            ORDER BY total DESC
        ";

        return $this->runQuery($query);
    }

    /**
     * Ejecuta la consulta y normaliza salida
     */
    protected function runQuery(string $query): array
    {
        $queryJobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($queryJobConfig);

        $result = [];
        foreach ($queryResults->rows() as $row) {
            $result[] = [
                'categoria' => $row['categoria'],
                'total'     => (int) $row['total'],
            ];
        }

        return $result;
    }

    public function obtenerSuscriptoresPorCiudad(
        string $modo = 'normalizado',
        ?string $fechaInicio = null,
        ?string $fechaFin = null
    ): array
    {
        // Selección de tabla y campo según el modo
        $tabla = $modo === 'original'
            ? "`{$this->datasetId}.{$this->tableId}`"
            : "`{$this->datasetId}.vta_ciudadesNormalizadas`";

        // Condición WHERE
        $where = [];
        if ($fechaInicio && $fechaFin) {
            $where[] = $modo === 'original'
                ? "fechaCreacion BETWEEN TIMESTAMP('$fechaInicio 00:00:00') AND TIMESTAMP('$fechaFin 23:59:59')"
                : "fechaCreacion BETWEEN '$fechaInicio 00:00:00' AND '$fechaFin 23:59:59'";
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Consulta según modo
        if ($modo === 'original') {
            $query = "
                SELECT
                    COALESCE(NULLIF(TRIM(ciudad), ''), 'Sin datos') AS categoria,
                    COUNT(*) AS total
                FROM $tabla
                $whereSql
                GROUP BY categoria
                ORDER BY total DESC
            ";
        } else {
            // Normalizado: usar alias_norm y ciudad_canonica
            $query = "
                SELECT
                    COALESCE(c.ciudad_canonica, COALESCE(NULLIF(TRIM(u.ciudad), ''), 'Sin datos')) AS categoria,
                    COUNT(1) AS total
                FROM `{$this->datasetId}.{$this->tableId}` u
                LEFT JOIN $tabla c
                    ON REGEXP_REPLACE(
                        REGEXP_REPLACE(NORMALIZE(UPPER(u.ciudad), NFD), r'\\p{M}', ''),
                        r'[^A-Z0-9]', ''
                    ) = c.alias_norm
                $whereSql
                GROUP BY categoria
                ORDER BY total DESC
            ";
        }

        // Ejecutar query
        $queryJobConfig  = $this->bigQuery->query($query);
        $queryResults    = $this->bigQuery->runQuery($queryJobConfig);

        $resultado = [];
        foreach ($queryResults->rows() as $row) {  // ⚠ Usar ->rows()
            $resultado[] = [
                'categoria' => $row['categoria'],
                'total'     => (int) $row['total'],
            ];
        }

        return $resultado;
    }

    protected function queryCountCiudadNormalizada(
        ?string $fechaInicio = null,
        ?string $fechaFin = null
    ): array {

        $where = '';
        if ($fechaInicio && $fechaFin) {
            $where = "WHERE u.fechaCreacion BETWEEN '$fechaInicio 00:00:00' AND '$fechaFin 23:59:59'";
        }

        $query = "
            SELECT
                COALESCE(
                    c.ciudad_canonica,
                    COALESCE(NULLIF(TRIM(u.ciudad), ''), 'Sin datos')
                ) AS categoria,
                COUNT(1) AS total
            FROM `{$this->datasetId}.{$this->tableId}` u
            LEFT JOIN `{$this->datasetId}.vta_ciudadesNormalizadas` c
                ON REGEXP_REPLACE(
                    REGEXP_REPLACE(
                        NORMALIZE(UPPER(u.ciudad), NFD),
                        r'\p{M}',
                        ''
                    ),
                    r'[^A-Z0-9]',
                    ''
                ) = c.alias_norm
            GROUP BY categoria
            ORDER BY total DESC;
        ";

        return $this->runQuery($query);
    }


}
