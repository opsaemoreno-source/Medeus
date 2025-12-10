<?php

namespace App\Services;

use Google\Cloud\BigQuery\BigQueryClient;

class BigQueryService
{
    protected $bigQuery;

    public function __construct()
    {
        $this->bigQuery = new BigQueryClient([
            'projectId' => env('GOOGLE_PROJECT_ID'),
            'keyFilePath' => storage_path('app/google/bigquery.json')
        ]);
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
            FROM `admanagerapiaccess-382213.UsuariosOPSA.EncuestasTypeform`
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
            FROM `admanagerapiaccess-382213.UsuariosOPSA.UsuariosEvolok`
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
            FROM `admanagerapiaccess-382213.UsuariosOPSA.UsuariosEvolok`
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

        // Depuración temporal
        //dd($suscriptores);

        return $suscriptores;
    }

    public function contarSuscriptores($search = '')
    {
        $filter = $search ? "WHERE LOWER(CONCAT(nombre,' ',apellido)) LIKE LOWER('%$search%')" : "";

        $query = "
            SELECT COUNT(*) AS total
            FROM `admanagerapiaccess-382213.UsuariosOPSA.UsuariosEvolok`
            $filter
        ";

        $queryJobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($queryJobConfig);

        foreach ($queryResults->rows() as $row) {
            return intval($row['total']);
        }

        return 0;
    }
}