<?php

namespace App\Services;

use App\Support\BigQuery;
use Google\Cloud\BigQuery\BigQueryClient;

class BigQueryService
{
    protected $bigQuery;
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

    public function client(): BigQueryClient
    {
        return $this->bigQuery;
    }

    public function obtenerEncuestas()
    {
        $query = "
            SELECT
                e.id,
                e.titulo,
                e.fechaCreacion,
                e.fechaPublicacion,
                e.noCampos,
                COUNT(DISTINCT d.token) as noRespuestas,
                CAST(autoUpdate AS BOOL) AS autoUpdate
            FROM $this->tablaEncuestas e
            LEFT JOIN $this->tablaEncuestasDetalle d
            ON d.idEncuesta = e.id
            GROUP BY
                e.id,
                e.titulo,
                e.fechaCreacion,
                e.fechaPublicacion,
                e.noCampos,
                e.autoUpdate
            ORDER BY e.fechaCreacion DESC
        ";

        $queryJob = $this->bigQuery->query($query);
        $results = $this->bigQuery->runQuery($queryJob);

        return $results->rows();
    }

    public function obtenerSuscriptores()
    {
        ini_set('memory_limit', '1G');
        $query = "
            SELECT
                userid,
                CONCAT(nombre, ' ', apellido) AS nombre_completo,
                correo,
                telefono,
                suscripcionActiva,
                estado
            FROM $this->tablaUsuarios
            ORDER BY nombre_completo
        ";

        $queryJobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($queryJobConfig);

        $suscriptores = [];
        foreach ($queryResults->rows() as $row) {
            $suscriptores[] = [
                'userid' => $row['userid'],
                'nombre_completo' => $row['nombre_completo'],
                'correo' => $row['correo'],
                'telefono' => $row['telefono'],
                'suscripcionActiva' => $row['suscripcionActiva'] ? 'Sí' : 'No',
                'estado' => $row['estado'],
            ];
        }

        return $suscriptores;
    }

    public function obtenerSuscriptoresPaginados($start = 0, $length = 10, $search = '')
    {
        $params = ['start' => (int) $start, 'length' => (int) $length];
        $where = $this->whereSuscriptores(['search' => $search], $params);

        $query = "
            SELECT
                userid,
                CONCAT(nombre, ' ', apellido) AS nombre_completo,
                correo,
                telefono,
                suscripcionActiva,
                estado
            FROM $this->tablaUsuarios
            $where
            ORDER BY nombre_completo
            LIMIT @length OFFSET @start
        ";

        $queryResults = BigQuery::run($this->bigQuery, $query, $params);

        $suscriptores = [];
        foreach ($queryResults->rows() as $row) {
            $suscriptores[] = [
                'userid' => $row['userid'] ?? 'N/A',
                'nombre_completo' => $row['nombre_completo'] ?? 'N/A',
                'correo' => $row['correo'] ?? '',
                'telefono' => $row['telefono'] ?? '',
                'suscripcionActiva' => isset($row['suscripcionActiva']) ? ($row['suscripcionActiva'] ? 'Sí' : 'No') : 'No',
                'estado' => $row['estado'] ?? ''
            ];
        }

        return $suscriptores;
    }

    public function contarSuscriptores($search = '')
    {
        $params = [];
        $where = $this->whereSuscriptores(['search' => $search], $params);

        $query = "
            SELECT COUNT(*) AS total
            FROM $this->tablaUsuarios
            $where
        ";

        $queryResults = BigQuery::run($this->bigQuery, $query, $params);

        foreach ($queryResults->rows() as $row) {
            return intval($row['total']);
        }

        return 0;
    }

    public function obtenerConteoTotalSuscriptores()
    {
        $query = "
            SELECT COUNT(*) AS total
            FROM $this->tablaUsuarios
        ";

        $results = $this->bigQuery->runQuery($this->bigQuery->query($query));

        foreach ($results->rows() as $row) {
            return intval($row['total']);
        }
        return 0;
    }

    public function obtenerEstadisticaPorDia($fechaInicio = null, $fechaFin = null)
    {
        $fechaInicio = BigQuery::fecha($fechaInicio);
        $fechaFin = BigQuery::fecha($fechaFin);

        $filtro = "";
        if ($fechaInicio && $fechaFin) {
            $filtro = "WHERE fechaCreacion BETWEEN '$fechaInicio 00:00:00' AND '$fechaFin 23:59:59'";
        }

        $query = "
            SELECT
                DATE(fechaCreacion) AS fecha,
                COUNT(*) AS total
            FROM $this->tablaUsuarios
            $filtro
            GROUP BY fecha
            ORDER BY fecha ASC
        ";

        $results = $this->bigQuery->runQuery($this->bigQuery->query($query));

        $estadistica = [];
        foreach ($results->rows() as $row) {
            $fecha = $row['fecha'];
            if (is_object($fecha) && method_exists($fecha, 'format')) {
                $fecha = $fecha->format('Y-m-d');
            } elseif (is_object($fecha)) {
                $fecha = (string) $fecha;
            }

            $estadistica[] = [
                'fecha' => $fecha,
                'total' => $row['total']
            ];
        }

        return $estadistica;
    }


    public function obtenerSuscriptoresPaginadosConFiltro($start, $length, $search, $fechaInicio, $fechaFin, $genero = null, $pais = null, $ciudad = null, $canal = null, $marca = null, $edad_min = null, $edad_max = null, $nivelEducativo = null, $profesion = null, $estadoCivil = null)
    {
        $params = ['start' => (int) $start, 'length' => (int) $length];
        $where = $this->whereSuscriptores(compact(
            'search', 'fechaInicio', 'fechaFin', 'genero', 'pais', 'ciudad', 'canal', 'marca',
            'edad_min', 'edad_max', 'nivelEducativo', 'profesion', 'estadoCivil'
        ), $params, true);

        $query = "
            SELECT
                userid,
                CONCAT(nombre, ' ', apellido) AS nombre_completo,
                correo,
                telefono,
                suscripcionActiva,
                estado
            FROM $this->tablaUsuarios
            $where
            ORDER BY nombre_completo
            LIMIT @length OFFSET @start
        ";

        $results = BigQuery::run($this->bigQuery, $query, $params);
        $suscriptores = [];

        foreach ($results->rows() as $row) {
            $suscriptores[] = [
                'userid' => $row['userid'] ?? '',
                'nombre_completo' => $row['nombre_completo'] ?? '',
                'correo' => $row['correo'] ?? '',
                'telefono' => $row['telefono'] ?? '',
                'suscripcionActiva' => isset($row['suscripcionActiva']) ? ($row['suscripcionActiva'] ? 'Sí' : 'No') : 'No',
                'estado' => $row['estado'] ?? ''
            ];
        }

        return $suscriptores;
    }


    public function contarSuscriptoresConFiltro($search, $fechaInicio, $fechaFin, $genero = null, $pais = null, $ciudad = null, $canal = null, $marca = null, $edad_min = null, $edad_max = null, $nivelEducativo = null, $profesion = null, $estadoCivil = null)
    {
        $params = [];
        $where = $this->whereSuscriptores(compact(
            'search', 'fechaInicio', 'fechaFin', 'genero', 'pais', 'ciudad', 'canal', 'marca',
            'edad_min', 'edad_max', 'nivelEducativo', 'profesion', 'estadoCivil'
        ), $params);

        $query = "
            SELECT COUNT(*) AS total
            FROM $this->tablaUsuarios
            $where
        ";

        $results = BigQuery::run($this->bigQuery, $query, $params);

        foreach ($results->rows() as $row) {
            return intval($row['total']);
        }

        return 0;
    }

    public function obtenerSuscriptoresExportar($search, $fechaInicio, $fechaFin, $genero = null, $pais = null, $ciudad = null, $canal = null, $marca = null, $edad_min = null, $edad_max = null, $nivelEducativo = null, $profesion = null, $estadoCivil = null)
    {
        $params = [];
        $where = $this->whereSuscriptores(compact(
            'search', 'fechaInicio', 'fechaFin', 'genero', 'pais', 'ciudad', 'canal', 'marca',
            'edad_min', 'edad_max', 'nivelEducativo', 'profesion', 'estadoCivil'
        ), $params);

        $query = "
            SELECT *
            FROM $this->tablaUsuarios
            $where
            ORDER BY fechaCreacion
        ";

        $results = BigQuery::run($this->bigQuery, $query, $params);

        return iterator_to_array($results->rows());
    }

    /**
     * WHERE de los filtros de suscriptores; los valores van a $params.
     * $trimCanal conserva la comparación con TRIM que usa la tabla paginada.
     */
    private function whereSuscriptores(array $f, array &$params, bool $trimCanal = false): string
    {
        $filtro = [];

        $fechaInicio = BigQuery::fecha($f['fechaInicio'] ?? null);
        $fechaFin = BigQuery::fecha($f['fechaFin'] ?? null);

        if (!empty($f['search'])) {
            $filtro[] = "LOWER(CONCAT(nombre,' ',apellido)) LIKE LOWER(CONCAT('%', @search, '%'))";
            $params['search'] = (string) $f['search'];
        }
        if ($fechaInicio && $fechaFin) {
            $filtro[] = "fechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin'";
        }
        if (!empty($f['genero'])) {
            $filtro[] = "LOWER(genero) = LOWER(@genero)";
            $params['genero'] = (string) $f['genero'];
        }
        if (!empty($f['pais'])) {
            $filtro[] = "LOWER(pais) LIKE LOWER(CONCAT('%', @pais, '%'))";
            $params['pais'] = (string) $f['pais'];
        }
        if (!empty($f['ciudad'])) {
            $filtro[] = "LOWER(ciudad) LIKE LOWER(CONCAT('%', @ciudad, '%'))";
            $params['ciudad'] = (string) $f['ciudad'];
        }
        if (!empty($f['canal'])) {
            $filtro[] = $trimCanal
                ? "TRIM(LOWER(canal)) = LOWER(@canal)"
                : "LOWER(canal) = LOWER(@canal)";
            $params['canal'] = (string) $f['canal'];
        }
        if (!empty($f['marca']))
        {
            if($f['marca'] != "blank")
            {
                $filtro[] = "TRIM(LOWER(marca)) = LOWER(@marca)";
                $params['marca'] = (string) $f['marca'];
            }
            else
            {
                $filtro[] = "(TRIM(marca) = '' OR marca IS NULL)";
            }
        }
        if (!empty($f['edad_min']) || !empty($f['edad_max'])) {
            // fechaNacimiento es DATE: TIMESTAMP_DIFF no lo acepta
            $filtro[] = "DATE_DIFF(CURRENT_DATE(), DATE(fechaNacimiento), YEAR) BETWEEN @edad_min AND @edad_max";
            $params['edad_min'] = is_numeric($f['edad_min'] ?? null) ? (int) $f['edad_min'] : 0;
            $params['edad_max'] = is_numeric($f['edad_max'] ?? null) ? (int) $f['edad_max'] : 200;
        }
        if (!empty($f['nivelEducativo'])) {
            $filtro[] = "LOWER(nivelEducativo) = LOWER(@nivelEducativo)";
            $params['nivelEducativo'] = (string) $f['nivelEducativo'];
        }
        if (!empty($f['profesion'])) {
            $filtro[] = "LOWER(profesion) = LOWER(@profesion)";
            $params['profesion'] = (string) $f['profesion'];
        }
        if (!empty($f['estadoCivil'])) {
            $filtro[] = "LOWER(estadoCivil) = LOWER(@estadoCivil)";
            $params['estadoCivil'] = (string) $f['estadoCivil'];
        }

        return count($filtro) ? "WHERE " . implode(" AND ", $filtro) : "";
    }

    public function catalogoEstadoCivil()
    {
        $sql = "
            SELECT idCivil AS id, label
            FROM {$this->tablaEstadoCivil}
            ORDER BY label
        ";
        $results = $this->bigQuery->runQuery($this->bigQuery->query($sql));
        return iterator_to_array($results->rows());
    }

    public function catalogoNivelEducativo()
    {
        $sql = "
            SELECT idNivEducativo AS id, label
            FROM {$this->tablaNivEducativo}
            ORDER BY label
        ";
        $results = $this->bigQuery->runQuery($this->bigQuery->query($sql));
        return iterator_to_array($results->rows());
    }

    public function catalogoProfesiones()
    {
        $sql = "
            SELECT idProfesion AS id, label
            FROM {$this->tablaProfesiones}
            ORDER BY label
        ";
        $results = $this->bigQuery->runQuery($this->bigQuery->query($sql));
        return iterator_to_array($results->rows());
    }

    public function catalogoPaises()
    {
        $sql = "
            SELECT DISTINCT idPaisAlter AS id, label
            FROM {$this->tablaPaises}
            WHERE idPaisAlter IS NOT NULL
            ORDER BY label
        ";
        $results = $this->bigQuery->runQuery($this->bigQuery->query($sql));
        return iterator_to_array($results->rows());
    }

    public function actualizarAutoUpdate($id, $value)
    {
        $query = "
            UPDATE {$this->tablaEncuestas}
            SET autoUpdate = @value
            WHERE id = @id
        ";

        $jobConfig = $this->bigQuery->query($query)->parameters([
            'id' => $id,
            'value' => $value
        ]);

        $this->bigQuery->runQuery($jobConfig);
    }

}
