<?php

namespace App\Services;

use Google\Cloud\BigQuery\BigQueryClient;

class BigQueryService
{
    protected $bigQuery;
    protected $tablaUsuarios;
    protected $tablaEncuestas;

    public function __construct()
    {
        $this->bigQuery = new BigQueryClient([
            'projectId' => env('GOOGLE_PROJECT_ID'),
            'keyFilePath' => storage_path('app/google/bigquery.json')
        ]);
        $this->tablaUsuarios = "`admanagerapiaccess-382213.UsuariosOPSA.UsuariosEvolok`";
        $this->tablaEncuestas = "`admanagerapiaccess-382213.UsuariosOPSA.EncuestasTypeform`";
    }

    public function obtenerEncuestas()
    {
        $query = "
            SELECT
                id,
                titulo,
                fechaCreacion,
                fechaPublicacion,
                noCampos,
                noRespuestas
            FROM $this->tablaEncuestas
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
        $filter = $search ? "WHERE LOWER(CONCAT(nombre,' ',apellido)) LIKE LOWER('%$search%')" : "";

        $query = "
            SELECT 
                userid, 
                CONCAT(nombre, ' ', apellido) AS nombre_completo, 
                correo,
                telefono,
                suscripcionActiva, 
                estado
            FROM $this->tablaUsuarios
            $filter
            ORDER BY nombre_completo
            LIMIT $length OFFSET $start
        ";

        $queryJobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($queryJobConfig);

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
        $filter = $search ? "WHERE LOWER(CONCAT(nombre,' ',apellido)) LIKE LOWER('%$search%')" : "";

        $query = "
            SELECT COUNT(*) AS total
            FROM $this->tablaUsuarios
            $filter
        ";

        $queryJobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($queryJobConfig);

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


    public function obtenerSuscriptoresPaginadosConFiltro($start, $length, $search, $fechaInicio, $fechaFin)
    {
        $filtro = [];

        if ($search) {
            $filtro[] = "LOWER(CONCAT(nombre,' ',apellido)) LIKE LOWER('%$search%')";
        }
        if ($fechaInicio && $fechaFin) {
            $filtro[] = "fechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin'";
        }

        $where = count($filtro) ? "WHERE " . implode(" AND ", $filtro) : "";

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
            LIMIT $length OFFSET $start
        ";

        $results = $this->bigQuery->runQuery($this->bigQuery->query($query));
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


    public function contarSuscriptoresConFiltro($search, $fechaInicio, $fechaFin)
    {
        $filtro = [];

        if ($search) {
            $filtro[] = "LOWER(CONCAT(nombre,' ',apellido)) LIKE LOWER('%$search%')";
        }
        if ($fechaInicio && $fechaFin) {
            $filtro[] = "fechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin'";
        }

        $where = count($filtro) ? "WHERE " . implode(" AND ", $filtro) : "";

        $query = "
            SELECT COUNT(*) AS total
            FROM $this->tablaUsuarios
            $where
        ";

        $results = $this->bigQuery->runQuery($this->bigQuery->query($query));

        foreach ($results->rows() as $row) {
            return intval($row['total']);
        }

        return 0;
    }

    public function obtenerSuscriptoresExportar($search, $fechaInicio, $fechaFin)
    {
        $filtro = [];

        if ($search) {
            $filtro[] = "LOWER(CONCAT(nombre,' ',apellido)) LIKE LOWER('%$search%')";
        }
        if ($fechaInicio && $fechaFin) {
            $filtro[] = "fechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin'";
        }

        $where = count($filtro) ? "WHERE " . implode(" AND ", $filtro) : "";

        $query = "
            SELECT *
            FROM $this->tablaUsuarios
            $where
            ORDER BY fechaCreacion
        ";

        $results = $this->bigQuery->runQuery($this->bigQuery->query($query));

        return iterator_to_array($results->rows());
    }

}