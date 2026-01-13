<?php

namespace App\Services;

use Google\Cloud\BigQuery\BigQueryClient;

class CiudadesNormalizacionService
{
    protected BigQueryClient $bigQuery;
    protected string $tabla;

    public function __construct()
    {
        $this->bigQuery = new BigQueryClient([
            'projectId' => env('GOOGLE_PROJECT_ID'),
            'keyFilePath' => storage_path('app/google/bigquery.json'),
        ]);

        $this->tabla = "`admanagerapiaccess-382213.UsuariosOPSA.catalogo_ciudadesNormalizacion`";
    }

    /* ======================
     * CIUDADES CANÓNICAS
     * ====================== */

    public function obtenerCiudadesCanonicas()
    {
        $query = "
            SELECT DISTINCT ciudad_canonica, pais
            FROM {$this->tabla}
            WHERE estado = true
            ORDER BY ciudad_canonica
        ";

        return iterator_to_array(
            $this->bigQuery->runQuery($this->bigQuery->query($query))->rows()
        );
    }

    /* ======================
     * ALIAS POR CANÓNICA
     * ====================== */

    public function obtenerAliasPorCanonica(string $ciudadCanonica)
    {
        $query = "
            SELECT ciudad_alias, prioridad, estado
            FROM {$this->tabla}
            WHERE ciudad_canonica = @canon
            ORDER BY prioridad ASC, ciudad_alias
        ";

        $job = $this->bigQuery->query($query)
            ->parameters([
                'canon' => $ciudadCanonica
            ]);

        return iterator_to_array(
            $this->bigQuery->runQuery($job)->rows()
        );
    }

    /* ======================
     * CREAR / ACTUALIZAR
     * ====================== */

    public function guardarAlias(array $data)
    {
        $queryPais = "
            SELECT pais
            FROM {$this->tabla}
            WHERE ciudad_canonica = @canon
            LIMIT 1
        ";

        $paisResult = iterator_to_array(
            $this->bigQuery->runQuery(
                $this->bigQuery->query($queryPais)->parameters([
                    'canon' => $data['ciudad_canonica']
                ])
            )->rows()
        );

        $pais = $paisResult[0]['pais'];

        $query = "
            INSERT INTO {$this->tabla}
            (ciudad_alias, ciudad_canonica, pais, prioridad, estado)
            VALUES (@alias, @canon, @pais, @prioridad, true)
        ";

        $this->bigQuery->runQuery(
            $this->bigQuery->query($query)->parameters([
                'alias'     => $data['ciudad_alias'],
                'canon'     => $data['ciudad_canonica'],
                'pais'      => $pais,
                'prioridad' => (int) ($data['prioridad'] ?? 1)
            ])
        );
    }

    public function actualizarAlias(string $aliasOriginal, array $data)
    {
        $query = "
            UPDATE {$this->tabla}
            SET ciudad_alias = @alias,
                prioridad = @prioridad
            WHERE ciudad_alias = @aliasOriginal
        ";

        $job = $this->bigQuery->query($query)->parameters([
            'alias' => $data['ciudad_alias'],
            'prioridad' => (int) ($data['prioridad'] ?? 0),
            'aliasOriginal' => $aliasOriginal,
        ]);

        $this->bigQuery->runQuery($job);
    }

    /* ======================
     * ELIMINACIÓN LÓGICA
     * ====================== */

    public function desactivarAlias(string $alias)
    {
        $query = "
            UPDATE {$this->tabla}
            SET estado = false
            WHERE ciudad_alias = @alias
        ";

        $this->bigQuery->runQuery(
            $this->bigQuery->query($query)->parameters(['alias' => $alias])
        );
    }

    /* ======================
    * CREAR CIUDAD CANÓNICA
    * ====================== */
    public function guardarCiudadCanonica(
        string $ciudadCanonica,
        string $ciudadAlias,
        string $pais,
        int $prioridad = 1
    ) {
        $queryCheck = "
            SELECT COUNT(*) total
            FROM {$this->tabla}
            WHERE ciudad_canonica = @canon
        ";

        $check = iterator_to_array(
            $this->bigQuery->runQuery(
                $this->bigQuery->query($queryCheck)->parameters([
                    'canon' => $ciudadCanonica
                ])
            )->rows()
        );

        if ($check[0]['total'] > 0) {
            return;
        }

        $queryInsert = "
            INSERT INTO {$this->tabla}
            (ciudad_alias, ciudad_canonica, pais, prioridad, estado)
            VALUES (@alias, @canon, @pais, @prioridad, true)
        ";

        $this->bigQuery->runQuery(
            $this->bigQuery->query($queryInsert)->parameters([
                'alias'     => $ciudadAlias,
                'canon'     => $ciudadCanonica,
                'pais'      => $pais,
                'prioridad' => (int) $prioridad
            ])
        );
    }


    /* ======================
    * ACTUALIZAR CIUDAD CANÓNICA
    * ====================== */
    public function actualizarCiudadCanonica(string $ciudadCanonica, string $nuevoPais)
    {
        $query = "
            UPDATE {$this->tabla}
            SET pais = @pais
            WHERE ciudad_canonica = @canon
        ";
        $job = $this->bigQuery->query($query)->parameters([
            'pais' => $nuevoPais,
            'canon' => $ciudadCanonica
        ]);

        $this->bigQuery->runQuery($job);
    }
}
