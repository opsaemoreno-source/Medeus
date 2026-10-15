<?php

namespace App\Services;

use App\Support\BigQuery;
use Google\Cloud\BigQuery\BigQueryClient;

class EstadisticasAvanzadasService
{
    protected BigQueryClient $bigQuery;
    protected UsuariosSegmentacionService $segmentador;
    protected string $tablaUsuarios;
    protected string $tablaCompras;
    protected string $tablaEncuestas;
    protected string $tablaEncuestasDetalle;
    protected string $catalogoCiudades;
    protected string $vtaUsuariosNormalizados;
    protected string $vtaCiudadesNormalizadas;
    protected string $tablaProfesiones;
    protected string $tablaNivEducativo;
    protected string $tablaPaises;
    protected string $tablaEstadoCivil;

    public function __construct()
    {
        $this->bigQuery = BigQuery::client();

        $this->segmentador = new UsuariosSegmentacionService();

        $this->tablaUsuarios = BigQuery::table('vta_usuariosEvolok');
        $this->tablaCompras = BigQuery::table('Compras');
        $this->tablaEncuestas = BigQuery::table('EncuestasTypeform');
        $this->tablaProfesiones = BigQuery::table('data_profesion');
        $this->tablaNivEducativo = BigQuery::table('data_nivelEducativo');
        $this->tablaPaises = BigQuery::table('data_paises');
        $this->tablaEstadoCivil = BigQuery::table('data_estadoCivil');
        $this->tablaEncuestasDetalle = BigQuery::table('EncuestasTypeformDetalle');
        $this->catalogoCiudades = BigQuery::table('catalogo_ciudadesNormalizacion');
        $this->vtaCiudadesNormalizadas = BigQuery::table('vta_ciudadesNormalizadas');
    }

    /**
     * Cantidad de usuarios registrados que han contestado encuestas
     */
    public function usuariosQueRespondieronEncuestas(array $filtros = []): int
    {
        $params = [];
        $where = $this->segmentador->buildWhere($filtros, $params);

        $query = "
            SELECT
                COUNT(DISTINCT u.userid) AS total
            FROM {$this->tablaUsuarios} u
            JOIN {$this->tablaEncuestasDetalle} d
                ON u.userid = d.userid
            $where
        ";

        $row = $this->runQuerySingleRow($query, $params);

        return (int) ($row['total'] ?? 0);
    }


    /**
     * Usuarios que tienen compras y/o encuestas
     */
    public function usuariosConCompraYEncuesta(array $filtros = []): array
    {
        $params = [];
        $where = $this->segmentador->buildWhere($filtros, $params);

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

        return $this->runQuerySingleRow($query, $params);
    }

    /**
     * Conversión de usuarios que respondieron encuesta y compraron
     */
    public function conversionEncuestaCompra(array $filtros = []): array
    {
        $params = [];
        $where = $this->segmentador->buildWhere($filtros, $params);

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

        return $this->runQuerySingleRow($query, $params);
    }

    /**
     * Compras agrupadas por respuesta a encuestas (top N)
     */
    public function comprasPorRespuesta(array $filtros = [], int $topN = 20): array
    {
        $params = [];
        $where = $this->segmentador->buildWhere($filtros, $params);

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

        return $this->runQueryMultipleRows($query, $params);
    }

    /**
     * Ejecuta una query que devuelve una fila
     */
    private function runQuerySingleRow(string $query, array $params = []): array
    {
        $results = BigQuery::run($this->bigQuery, $query, $params);

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
    private function runQueryMultipleRows(string $query, array $params = []): array
    {
        $results = BigQuery::run($this->bigQuery, $query, $params);

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
        $columna = BigQuery::columna($columna);
        $params = [];
        $where = $this->segmentador->buildWhere($filtros, $params);

        $query = "
            SELECT DISTINCT $columna
            FROM {$this->tablaUsuarios} u
            $where
            ORDER BY $columna
        ";

        $result = $this->runQueryMultipleRows($query, $params);

        return array_map(fn($r) => $r[$columna] ?? null, $result);
    }

    public function suscripcionesCompradas(array $filtros): array
    {
        $params = [];
        $where = $this->segmentador->buildAndWhere($filtros, $params);

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

        return $this->runQuerySingleRow($sql, $params);
    }

    public function topPaisesPerfil(array $filtros): array
    {
        $params = [];
        $where = $this->segmentador->buildAndWhere($filtros, $params);

        $sql = "
            SELECT
                COALESCE(dp.label, u.paisPerfil) AS pais,
                COUNT(*) AS total
            FROM {$this->tablaUsuarios} u
            LEFT JOIN {$this->tablaPaises} dp
                ON dp.idPais = u.paisPerfil
            WHERE u.paisPerfil IS NOT NULL
                AND u.paisPerfil != ''
                {$where}
            GROUP BY pais
            ORDER BY total DESC
            LIMIT 10
        ";

        return $this->runQueryMultipleRows($sql, $params);
    }

    public function topPaisesIP(array $filtros): array
    {
        $params = [];
        $where = $this->segmentador->buildAndWhere($filtros, $params);

        $sql = "
            SELECT
                COALESCE(dp.label, u.pais) AS pais,
                COUNT(*) AS total
            FROM {$this->tablaUsuarios} u
            LEFT JOIN {$this->tablaPaises} dp
                ON dp.idPaisAlter = u.pais
            WHERE u.pais IS NOT NULL
                AND u.pais != ''
                {$where}
            GROUP BY pais
            ORDER BY total DESC
            LIMIT 10
        ";

        return $this->runQueryMultipleRows($sql, $params);
    }

    public function topCiudades(array $filtros): array
    {
        $params = [];
        $where = $this->segmentador->buildAndWhere($filtros, $params);

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
            {$where}
            GROUP BY ciudad
            ORDER BY total DESC
            LIMIT 10
        ";

        return $this->runQueryMultipleRows($sql, $params);
    }

    public function topProfesiones(array $filtros): array
    {
        $params = [];
        $where = $this->segmentador->buildAndWhere($filtros, $params);

        $sql = "
            SELECT
                COALESCE(dp.label, u.profesion) AS profesion,
                COUNT(*) AS total
            FROM {$this->tablaUsuarios} u
            LEFT JOIN {$this->tablaProfesiones} dp
                ON dp.idProfesion = u.profesion
            WHERE u.profesion IS NOT NULL
                AND u.profesion != ''
                {$where}
            GROUP BY profesion
            ORDER BY total DESC
            LIMIT 10
        ";

        return $this->runQueryMultipleRows($sql, $params);
    }

    public function topNivelesEducativos(array $filtros): array
    {
        $params = [];
        $where = $this->segmentador->buildAndWhere($filtros, $params);

        $sql = "
            SELECT
                COALESCE(dne.label, u.nivelEducativo) AS nivelEducativo,
                COUNT(*) AS total
            FROM {$this->tablaUsuarios} u
            LEFT JOIN {$this->tablaNivEducativo} dne
                ON dne.idNivEducativo = u.nivelEducativo
            WHERE u.nivelEducativo IS NOT NULL
                AND u.nivelEducativo != ''
                {$where}
            GROUP BY nivelEducativo
            ORDER BY total DESC
            LIMIT 10
        ";

        return $this->runQueryMultipleRows($sql, $params);
    }

    public function catalogoEstadoCivil(): array
    {
        $sql = "
            SELECT idCivil AS id, label
            FROM {$this->tablaEstadoCivil}
            ORDER BY label
        ";
        return $this->runQueryMultipleRows($sql);
    }

    public function catalogoNivelEducativo(): array
    {
        $sql = "
            SELECT idNivEducativo AS id, label
            FROM {$this->tablaNivEducativo}
            ORDER BY label
        ";
        return $this->runQueryMultipleRows($sql);
    }

    public function catalogoProfesiones(): array
    {
        $sql = "
            SELECT idProfesion AS id, label
            FROM {$this->tablaProfesiones}
            ORDER BY label
        ";
        return $this->runQueryMultipleRows($sql);
    }

    public function catalogoPaises(): array
    {
        $sql = "
            SELECT DISTINCT idPaisAlter AS id, label
            FROM {$this->tablaPaises}
            WHERE idPaisAlter IS NOT NULL
            ORDER BY label
        ";
        return $this->runQueryMultipleRows($sql);
    }

}
