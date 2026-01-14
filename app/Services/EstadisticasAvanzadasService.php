<?php

namespace App\Services;

use Google\Cloud\BigQuery\BigQueryClient;

class EstadisticasAvanzadasService
{
    protected BigQueryClient $bigQuery;
    protected string $tablaUsuarios;
    protected string $tablaCompras;
    protected string $tablaEncuestas;
    protected string $tablaEncuestasDetalle;
    protected string $catalogoCiudades;
    protected string $vtaUsuariosNormalizados;
    protected string $vtaCiudadesNormalizadas;

    public function __construct()
    {
        $this->bigQuery = new BigQueryClient([
            'projectId' => env('GOOGLE_PROJECT_ID'),
            'keyFilePath' => storage_path('app/google/bigquery.json')
        ]);

        $this->tablaUsuarios = "`admanagerapiaccess-382213.UsuariosOPSA.UsuariosEvolok`";
        $this->tablaCompras = "`admanagerapiaccess-382213.UsuariosOPSA.Compras`";
        $this->tablaEncuestas = "`admanagerapiaccess-382213.UsuariosOPSA.EncuestasTypeform`";
        $this->tablaEncuestasDetalle = "`admanagerapiaccess-382213.UsuariosOPSA.EncuestasTypeformDetalle`";
        $this->catalogoCiudades = "`admanagerapiaccess-382213.UsuariosOPSA.catalogo_ciudadesNormalizacion`";
        $this->vtaUsuariosNormalizados = "`admanagerapiaccess-382213.UsuariosOPSA.normalizado_UsuariosEvolok`";
        $this->vtaCiudadesNormalizadas = "`admanagerapiaccess-382213.UsuariosOPSA.vta_ciudadesNormalizadas`";
        
    }

    /**
     * Construye dinámicamente el WHERE a partir de filtros demográficos
     */
    private function buildWhere(array $filtros): string
    {
        $where = [];

        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $where[] = "u.fechaCreacion >= TIMESTAMP('{$filtros['fecha_inicio']}')
                    AND u.fechaCreacion < TIMESTAMP('{$filtros['fecha_fin']}') + INTERVAL 1 DAY";
        }

        foreach (['marca','genero','estadoCivil','nivelEducativo','profesion','pais','ciudad','canal'] as $campo) {
            if (!empty($filtros[$campo])) {
                $valor = $this->esc($filtros[$campo]);
                $where[] = "u.$campo = '$valor'";
            }
        }

        if (!empty($filtros['edad_min'])) {
            $refDate = !empty($filtros['fecha_fin'])
                ? "DATE('{$filtros['fecha_fin']}')"
                : "CURRENT_DATE()";

            if (!empty($filtros['edad_max'])) {
                $where[] = "
                    DATE_DIFF($refDate, DATE(u.fechaNacimiento), YEAR)
                    BETWEEN {$filtros['edad_min']} AND {$filtros['edad_max']}
                ";
            } else {
                $where[] = "
                    DATE_DIFF($refDate, DATE(u.fechaNacimiento), YEAR)
                    >= {$filtros['edad_min']}
                ";
            }
        }

        return count($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    }

    private function buildAndWhere(array $filtros): string
    {
        $where = $this->buildWhere($filtros);

        return $where
            ? str_replace('WHERE', 'AND', $where)
            : '';
    }


    private function esc($value): string
    {
        return addslashes(trim($value));
    }
    
    /**
     * Cantidad de usuarios registrados que han contestado encuestas
     */
    public function usuariosQueRespondieronEncuestas(array $filtros = []): int
    {
        $where = $this->buildWhere($filtros);

        $query = "
            SELECT
                COUNT(DISTINCT u.userid) AS total
            FROM {$this->tablaUsuarios} u
            JOIN {$this->tablaEncuestasDetalle} d
                ON u.userid = d.userid
            $where
        ";

        $row = $this->runQuerySingleRow($query);

        return (int) ($row['total'] ?? 0);
    }


    /**
     * Usuarios que tienen compras y/o encuestas
     */
    public function usuariosConCompraYEncuesta(array $filtros = []): array
    {
        $where = $this->buildWhere($filtros);

        $query = "
            SELECT
                COUNT(DISTINCT u.userid) AS total_usuarios,
                COUNT(DISTINCT c.idCompra) AS total_compras,
                COUNT(DISTINCT d.userid) AS total_encuestas
            FROM {$this->tablaUsuarios} u
            LEFT JOIN {$this->tablaCompras} c ON u.userid = c.idUsuario
            LEFT JOIN {$this->tablaEncuestasDetalle} d ON u.userid = d.userid
            $where
        ";

        return $this->runQuerySingleRow($query);
    }

    /**
     * Conversión de usuarios que respondieron encuesta y compraron
     */
    public function conversionEncuestaCompra(array $filtros = []): array
    {
        $where = $this->buildWhere($filtros);

        $query = "
            SELECT
                COUNT(DISTINCT d.userid) AS respondieron_encuesta,
                COUNT(DISTINCT c.idUsuario) AS compraron,
                SAFE_DIVIDE(COUNT(DISTINCT c.idUsuario), COUNT(DISTINCT d.userid)) * 100 AS conversion_pct
            FROM {$this->tablaEncuestasDetalle} d
            LEFT JOIN {$this->tablaCompras} c ON d.userid = c.idUsuario
            JOIN {$this->tablaUsuarios} u ON u.userid = d.userid
            $where
        ";

        return $this->runQuerySingleRow($query);
    }

    /**
     * Compras agrupadas por respuesta a encuestas (top N)
     */
    public function comprasPorRespuesta(array $filtros = [], int $topN = 20): array
    {
        $where = $this->buildWhere($filtros);

        $query = "
            SELECT
                d.pregunta,
                d.respuesta,
                COUNT(DISTINCT c.idCompra) AS total_compras
            FROM {$this->tablaUsuarios} u
            JOIN {$this->tablaCompras} c ON u.userid = c.idUsuario
            JOIN {$this->tablaEncuestasDetalle} d ON u.userid = d.userid
            $where
            GROUP BY d.pregunta, d.respuesta
            ORDER BY total_compras DESC
            LIMIT $topN
        ";

        return $this->runQueryMultipleRows($query);
    }

    /**
     * Ejecuta una query que devuelve una fila
     */
    private function runQuerySingleRow(string $query): array
    {
        $queryJob = $this->bigQuery->query($query);
        $results = $this->bigQuery->runQuery($queryJob);

        foreach ($results->rows() as $row) {
            $arrayRow = [];
            foreach ($row as $key => $value) {
                $arrayRow[$key] = is_object($value) && method_exists($value, 'format') 
                    ? $value->format('Y-m-d H:i:s') 
                    : $value;
            }
            return $arrayRow;
        }

        return [];
    }

    /**
     * Ejecuta una query que devuelve múltiples filas
     */
    private function runQueryMultipleRows(string $query): array
    {
        $queryJob = $this->bigQuery->query($query);
        $results = $this->bigQuery->runQuery($queryJob);

        $data = [];
        foreach ($results->rows() as $row) {
            $arrayRow = [];
            foreach ($row as $key => $value) {
                $arrayRow[$key] = is_object($value) && method_exists($value, 'format') 
                    ? $value->format('Y-m-d H:i:s') 
                    : $value;
            }
            $data[] = $arrayRow;
        }
        return $data;
    }

    /**
     * Devuelve valores distintos de una columna para filtros
     */
    public function valoresDistintos(string $columna, array $filtros = []): array
    {
        $where = $this->buildWhere($filtros);

        $query = "
            SELECT DISTINCT $columna
            FROM {$this->tablaUsuarios} u
            $where
            ORDER BY $columna
        ";

        $result = $this->runQueryMultipleRows($query);

        return array_map(fn($r) => $r[$columna] ?? null, $result);
    }

    public function suscripcionesCompradas(array $filtros): array
    {
        $where = $this->buildAndWhere($filtros);

        $sql = "
            SELECT
                COUNT(DISTINCT c.idCompra) AS total_suscripciones,

                SUM(
                    CASE WHEN c.idMoneda = 'USD'
                    THEN c.precioFinal ELSE 0 END
                ) AS monto_usd,

                SUM(
                    CASE WHEN c.idMoneda = 'HNL'
                    THEN c.precioFinal ELSE 0 END
                ) AS monto_hnl

            FROM {$this->tablaCompras} c
            INNER JOIN {$this->tablaUsuarios} u
                ON u.userid = c.idUsuario

            WHERE 
                c.estado = 'ACTIVE'
                {$where}
        ";

        return $this->runQuerySingleRow($sql);
    }

    public function topPaisesPerfil(array $filtros): array
    {
        $where = $this->buildAndWhere($filtros);

        $sql = "
            SELECT
                u.paisPerfil AS pais,
                COUNT(*) AS total
            FROM {$this->tablaUsuarios} u
            WHERE u.paisPerfil IS NOT NULL
                AND u.paisPerfil != ''
                {$where}
            GROUP BY u.paisPerfil
            ORDER BY total DESC
            LIMIT 10
        ";

        return $this->runQueryMultipleRows($sql);
    }

    public function topPaisesIP(array $filtros): array
    {
        $where = $this->buildAndWhere($filtros);

        $sql = "
            SELECT
                u.pais AS pais,
                COUNT(*) AS total
            FROM {$this->tablaUsuarios} u
            WHERE u.pais IS NOT NULL
                AND u.pais != ''
                {$where}
            GROUP BY u.pais
            ORDER BY total DESC
            LIMIT 10
        ";

        return $this->runQueryMultipleRows($sql);
    }

    public function topCiudades(array $filtros): array
    {
        $where = $this->buildAndWhere($filtros);

        $sql = "
            SELECT
                COALESCE(
                    c.ciudad_canonica,
                    COALESCE(NULLIF(TRIM(u.ciudad), ''), 'Sin datos')
                ) AS ciudad,
                COUNT(1) AS total
            FROM {$this->tablaUsuarios} u
            LEFT JOIN {$this->vtaCiudadesNormalizadas} c
                ON REGEXP_REPLACE(
                    REGEXP_REPLACE(
                        NORMALIZE(UPPER(u.ciudad), NFD),
                        r'\p{M}',
                        ''
                    ),
                    r'[^A-Z0-9]',
                    ''
                ) = c.alias_norm
            WHERE c.ciudad_canonica IS NOT NULL
            GROUP BY ciudad
            ORDER BY total DESC
            LIMIT 10
        ";

        return $this->runQueryMultipleRows($sql);
    }

    public function topProfesiones(array $filtros): array
    {
        $where = $this->buildAndWhere($filtros);

        $sql = "
            SELECT
                u.profesion AS profesion,
                COUNT(*) AS total
            FROM {$this->tablaUsuarios} u
            WHERE u.profesion IS NOT NULL
                AND u.profesion != ''
                {$where}
            GROUP BY u.profesion
            ORDER BY total DESC
            LIMIT 10
        ";

        return $this->runQueryMultipleRows($sql);
    }

    public function topNivelesEducativos(array $filtros): array
    {
        $where = $this->buildAndWhere($filtros);

        $sql = "
            SELECT
                u.nivelEducativo AS nivelEducativo,
                COUNT(*) AS total
            FROM {$this->tablaUsuarios} u
            WHERE u.nivelEducativo IS NOT NULL
                AND u.nivelEducativo != ''
                {$where}
            GROUP BY u.nivelEducativo
            ORDER BY total DESC
            LIMIT 10
        ";

        return $this->runQueryMultipleRows($sql);
    }

}
