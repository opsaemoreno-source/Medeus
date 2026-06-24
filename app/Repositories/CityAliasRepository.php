<?php

namespace App\Repositories;

use App\Services\BigQueryService;

class CityAliasRepository
{
    private $bq;

    private string $catalogoCiudades;
    private string $projectId;
    private string $dataset;

    public function __construct(BigQueryService $bq)
    {
        $this->bq = $bq->client();

        $this->catalogoCiudades = 'catalogo_ciudadesNormalizacion';
        $this->projectId = env('GOOGLE_PROJECT_ID');
        $this->dataset = 'UsuariosOPSA';

    }

    public function getAll(?string $canonica = null, ?bool $estado = null, int $limit = 25, int $offset = 0)
    {
        $query = "
            SELECT *
            FROM {$this->tableRef()}
            WHERE 1=1
        ";

        $params = [];

        if (!empty($canonica)) {
            $query .= "
                AND UPPER(TRIM(ciudad_canonica))
                    LIKE CONCAT('%', UPPER(TRIM(@canonica)), '%')
            ";

            $params['canonica'] = $canonica;
        }

        if (!is_null($estado)) {
            $query .= "
                AND estado = @estado
            ";

            $params['estado'] = $estado;
        }

        $query .= "
            ORDER BY ciudad_canonica
            LIMIT @limit
            OFFSET @offset
        ";

        $params['limit'] = $limit;
        $params['offset'] = $offset;

        $jobConfig = $this->bq->query($query)
            ->parameters($params);

        $results = $this->bq->runQuery($jobConfig);

        $rows = [];

        foreach ($results as $row) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function countRecords(?string $canonica = null,?bool $estado = null)
    {
        $query = "
            SELECT COUNT(*) total
            FROM {$this->tableRef()}
            WHERE 1=1
        ";

        $params = [];

        if (!empty($canonica)) {

            $query .= "
                AND UPPER(TRIM(ciudad_canonica))
                    LIKE CONCAT('%', UPPER(TRIM(@canonica)), '%')
            ";

            $params['canonica'] = $canonica;
        }

        if (!is_null($estado)) {
            $query .= " AND estado = @estado";
            $params['estado'] = $estado;
        }

        $results = $this->bq->runQuery(
            $this->bq->query($query)->parameters($params)
        );

        foreach ($results as $row) {
            return (int) $row['total'];
        }

        return 0;
    }

    private function tableRef(): string
    {
        return "`{$this->projectId}.{$this->dataset}.{$this->catalogoCiudades}`";
    }

    public function insert(array $data)
    {
        $table = $this->bq
            ->dataset("UsuariosOPSA")
            ->table($this->catalogoCiudades);

        $result = $table->insertRows([
            [
                'data' => $data
            ]
        ]);

        if (!$result->isSuccessful()) {

            \Log::error('BigQuery insert failed', [
                'failedRows' => $result->failedRows()
            ]);

            throw new \Exception(
                json_encode($result->failedRows())
            );
        }

        return true;
    }

    public function updateAlias(string $originalAlias, array $data)
    {
        $query = "
            UPDATE {$this->tableRef()}
            SET
                ciudad_alias = @alias,
                ciudad_canonica = @canonica,
                pais = @pais,
                estado = @estado
            WHERE ciudad_alias = @original
        ";

        $jobConfig = $this->bq->query($query)
            ->parameters([
                'alias' => $data['ciudad_alias'],
                'canonica' => $data['ciudad_canonica'],
                'pais' => $data['pais'] ?? null,
                'estado' => $data['estado'],
                'original' => $originalAlias
            ]);

        return $this->bq->runQuery($jobConfig);
    }

    public function searchCanonicas(string $q)
    {
        $q = strtoupper(trim($q));

        $query = "
            SELECT DISTINCT ciudad_canonica
            FROM {$this->tableRef()}
            WHERE estado = TRUE
        ";

        $params = [];

        if (!empty($q)) {
            $query .= "
                AND STARTS_WITH(
                    UPPER(TRIM(ciudad_canonica)),
                    @q
                )
            ";

            $params['q'] = $q;
        }

        $query .= "
            ORDER BY ciudad_canonica
            LIMIT 10
        ";

        $jobConfig = $this->bq->query($query)
            ->parameters($params);

        $results = $this->bq->runQuery($jobConfig);

        $data = [];

        foreach ($results as $row) {
            $data[] = $row['ciudad_canonica'];
        }

        return $data;
    }

    public function aliasExists(string $normalizedAlias): bool
    {
        $query = "
            SELECT 1
            FROM {$this->tableRef()}
            WHERE UPPER(TRIM(ciudad_alias)) = @alias
            LIMIT 1
        ";

        $results = $this->bq->runQuery(
            $this->bq->query($query)
                ->parameters([
                    'alias' => $normalizedAlias
                ])
        );

        foreach ($results as $row) {
            return true;
        }

        return false;
    }
}