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
}