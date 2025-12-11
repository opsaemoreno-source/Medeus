<?php

namespace App\Services;

use Google\Cloud\BigQuery\BigQueryClient;
use Exception;

class SuscriptoresService
{
    protected $bigQuery;

    public function __construct()
    {
        $this->bigQuery = new BigQueryClient([
            'projectId' => env('GOOGLE_PROJECT_ID'),
            'keyFilePath' => storage_path('app/google/bigquery.json')
        ]);
    }

    /**
     * Retorna todas las estadísticas necesarias para los gráficos
     */
    public function obtenerEstadisticas(): array
    {
        try {
            $datasetId = 'UsuariosOPSA';
            $tableId = 'UsuariosEvolok';

            // Suscriptores por Marca
            $marca = $this->queryCount("marca");

            // Suscriptores por Género
            $genero = $this->queryCount("genero");

            // Estado Civil
            $estadoCivil = $this->queryCount("estadoCivil");

            // Nivel Educativo
            $nivelEducativo = $this->queryCount("nivelEducativo");

            // Profesión
            $profesion = $this->queryCount("profesion");

            // País
            $pais = $this->queryCount("pais");

            // Canal
            $canal = $this->queryCount("canal");

            return [
                'marca' => $marca,
                'genero' => $genero,
                'estadoCivil' => $estadoCivil,
                'nivelEducativo' => $nivelEducativo,
                'profesion' => $profesion,
                'pais' => $pais,
                'canal' => $canal,
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Consulta genérica: cuenta por campo
     */
    protected function queryCount(string $campo): array
    {
        $datasetId = 'UsuariosOPSA';
        $tableId = 'UsuariosEvolok';

        $query = "
            SELECT 
                COALESCE($campo, 'Sin datos') as categoria,
                COUNT(*) as total
            FROM `$datasetId.$tableId`
            GROUP BY categoria
            ORDER BY total DESC
        ";

        $queryJobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($queryJobConfig);

        $result = [];
        foreach ($queryResults->rows() as $row) {
            $result[] = [
                'categoria' => $row['categoria'],
                'total' => $row['total']
            ];
        }

        return $result;
    }
}
