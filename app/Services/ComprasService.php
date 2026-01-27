<?php

namespace App\Services;

use Google\Cloud\BigQuery\BigQueryClient;
use Exception; 

class ComprasService
{
    protected $bigQuery;
    protected string $datasetId = 'admanagerapiaccess-382213.UsuariosOPSA';
    protected string $tableCompras   = 'vta_Compras';
    protected string $viewComprasActual   = 'vta_Compras';
    protected string $viewComprasHistorico = 'vta_ComprasHistorico';

    public function __construct()
    {
        $this->bigQuery = new BigQueryClient([
            'projectId' => env('GOOGLE_PROJECT_ID'),
            'keyFilePath' => storage_path('app/google/bigquery.json')
        ]);
    }

    public function obtenerCompras(array $filtros): array
    {
        $where = $this->buildWhere($filtros);

        $sql = "
            SELECT
                idUsuario,
                idCompra,
                nombreProductoDisplay,
                nombrePlanPagoDisplay,
                precioFinal,
                idMoneda,
                cantidad,
                estado,
                marca,
                canal,
                tipoPago,
                fechaInicioSuscripcion,
                fechaFinalSuscripcion,
                ultimaFechaPago,
                proximaFechaPago
            FROM `{$this->datasetId}.{$this->tableCompras}`
            $where
            ORDER BY fechaInicioSuscripcion DESC
        ";

        $query = $this->bigQuery->query($sql);
        $rows  = iterator_to_array($this->bigQuery->runQuery($query));

        return [
            'data' => $this->mapCompras($rows),
            'total_ingresos' => $this->calcularIngresos($rows),
            'total_registros' => count($rows),
        ];
    }

    protected function calcularIngresos(array $rows): array
    {
        $totales = [];

        foreach ($rows as $row) {
            if ($row['estado'] === 'ACTIVE' && $row['precioFinal'] !== null) {
                $moneda = $row['idMoneda'] ?? 'UNKNOWN';
                $totales[$moneda] = ($totales[$moneda] ?? 0) + ((float)$row['precioFinal'] * (int)$row['cantidad']);
            }
        }

        // Redondear los totales
        foreach ($totales as $moneda => $total) {
            $totales[$moneda] = round($total, 2);
        }

        return $totales;
    }

    protected function mapCompras(array $rows): array
    {
        return array_map(function ($row) {
            return [
                'idUsuario' => $row['idUsuario'],
                'idCompra'  => $row['idCompra'],
                'producto'  => $row['nombreProductoDisplay'],
                'plan'      => $row['nombrePlanPagoDisplay'],
                'precio'    => $row['precioFinal'],
                'moneda'    => $row['idMoneda'],
                'cantidad'  => $row['cantidad'],
                'estado'    => $row['estado'],
                'marca'     => $row['marca'],
                'canal'     => $row['canal'],
                'tipoPago'  => $row['tipoPago'],
                'inicio'    => $this->formatDate($row['fechaInicioSuscripcion']),
                'fin'       => $this->formatDate($row['fechaFinalSuscripcion']),
                'ultimoPago'=> $this->formatDate($row['ultimaFechaPago']),
                'proximoPago'=> $this->formatDate($row['proximaFechaPago']),
            ];
        }, $rows);
    }

    protected function formatDate($value)
    {
        if ($value === null || $value === '0001-01-01T00:00:00') {
            return null;
        }

        // BigQuery puede devolver un objeto Timestamp o string
        if (is_object($value) && method_exists($value, 'format')) {
            return $value->format('Y-m-d H:i:s'); // formato legible
        }

        // Si ya es string
        return $value;
    }

    protected function buildWhere(array $filtros): string
    {
        $conditions = [];

        // =========================
        // ESTADO (uno o varios)
        // =========================
        if (!empty($filtros['estado'])) {

            if (is_array($filtros['estado'])) {
                $estados = array_filter($filtros['estado']);

                if (count($estados)) {
                    $estadosSQL = implode(
                        ', ',
                        array_map(fn($e) => "'" . addslashes($e) . "'", $estados)
                    );
                    $conditions[] = "estado IN ($estadosSQL)";
                }

            } else {
                $conditions[] = "estado = '" . addslashes($filtros['estado']) . "'";
            }
        }

        // =========================
        // RESTO DE FILTROS (sin cambios)
        // =========================
        if (!empty($filtros['marca'])) {
            $conditions[] = "marca = '{$filtros['marca']}'";
        }

        if (!empty($filtros['canal'])) {
            $conditions[] = "canal = '{$filtros['canal']}'";
        }

        if (!empty($filtros['tipoPago'])) {
            $conditions[] = "tipoPago = '{$filtros['tipoPago']}'";
        }

        if (!empty($filtros['producto'])) {
            $conditions[] = "nombreProductoDisplay = '{$filtros['producto']}'";
        }

        if (!empty($filtros['plan'])) {
            $conditions[] = "nombrePlanPagoDisplay = '{$filtros['plan']}'";
        }

        if (!empty($filtros['search'])) {
            $search = addslashes($filtros['search']);
            $conditions[] = "(
                CAST(idUsuario AS STRING) LIKE '%$search%' 
                OR CAST(idCompra AS STRING) LIKE '%$search%'
            )";
        }

        if (!empty($filtros['fechaInicio'])) {
            $conditions[] = "fechaInicioSuscripcion >= '{$filtros['fechaInicio']} 00:00:00'";
        }

        if (!empty($filtros['fechaFin'])) {
            $conditions[] = "fechaInicioSuscripcion <= '{$filtros['fechaFin']} 23:59:59'";
        }

        return count($conditions)
            ? 'WHERE ' . implode(' AND ', $conditions)
            : '';
    }

    public function obtenerEstadisticasCompras(array $filtros): array
    {
        $where = $this->buildWhere($filtros);

        $sql = "
            SELECT
                idMoneda,
                DATE(fechaInicioSuscripcion) AS dia,
                nombreProductoDisplay AS producto,
                estado,
                marca,
                canal,
                idFrecuencia,

                COUNT(*) AS cantidad,
                SUM(IFNULL(precioFinal, 0)) AS valor

            FROM `{$this->datasetId}.{$this->tableCompras}`
            $where
            GROUP BY idMoneda, dia, producto, estado, marca, canal, idFrecuencia
            ORDER BY dia ASC
        ";

        $query = $this->bigQuery->query($sql);
        $rows  = iterator_to_array($this->bigQuery->runQuery($query));

        return $this->mapEstadisticasCompras($rows);
    }

    public function obtenerEstadisticasComprasActivas(array $filtros): array
    {
        // FORZAR solo ACTIVE
        $filtros['estado'] = 'ACTIVE';
        $where = $this->buildWhere($filtros);

        $sql = "
            SELECT
                idMoneda,
                DATE(fechaInicioSuscripcion) AS dia,
                nombreProductoDisplay AS producto,
                estado,
                marca,
                canal,
                idFrecuencia,

                COUNT(*) AS cantidad,
                SUM(IFNULL(precioFinal, 0)) AS valor

            FROM `{$this->datasetId}.{$this->tableCompras}`
            $where
            GROUP BY idMoneda, dia, producto, estado, marca, canal, idFrecuencia
            ORDER BY dia ASC
        ";

        $query = $this->bigQuery->query($sql);
        $rows  = iterator_to_array($this->bigQuery->runQuery($query));

        return $this->mapEstadisticasCompras($rows);
    }

    protected function mapEstadisticasCompras(array $rows): array
    {
        $resultado = [];

        foreach ($rows as $row) {
            $moneda = $row['idMoneda'] ?? 'UNKNOWN';

            if (!isset($resultado[$moneda])) {
                $resultado[$moneda] = [
                    'cantidad' => [
                        'porDia'      => [],
                        'porProducto' => [],
                        'porEstado'   => [],
                        'porMarca'    => [],
                        'porCanal'    => [],
                        'porFrecuencia' => [],
                    ],
                    'valor' => [
                        'porDia'      => [],
                        'porProducto' => [],
                        'porEstado'   => [],
                        'porMarca'    => [],
                        'porCanal'    => [],
                        'porFrecuencia' => [],
                    ],
                ];
            }

            // =========================
            // CANTIDAD
            // =========================
            $this->sumar($resultado[$moneda]['cantidad']['porDia'],      $row['dia'],      $row['cantidad']);
            $this->sumar($resultado[$moneda]['cantidad']['porProducto'], $row['producto'], $row['cantidad']);
            $this->sumar($resultado[$moneda]['cantidad']['porEstado'],   $row['estado'],   $row['cantidad']);
            $this->sumar($resultado[$moneda]['cantidad']['porMarca'],    $row['marca'],    $row['cantidad']);
            $this->sumar($resultado[$moneda]['cantidad']['porCanal'],    $row['canal'],    $row['cantidad']);
            $this->sumar($resultado[$moneda]['cantidad']['porFrecuencia'], $row['idFrecuencia'], $row['cantidad']);


            // =========================
            // VALOR
            // =========================
            $valor = is_numeric($row['valor']) ? (float)$row['valor'] : 0.0;

            $this->sumar($resultado[$moneda]['valor']['porDia'],      $row['dia'],      $valor);
            $this->sumar($resultado[$moneda]['valor']['porProducto'], $row['producto'], $valor);
            $this->sumar($resultado[$moneda]['valor']['porEstado'],   $row['estado'],   $valor);
            $this->sumar($resultado[$moneda]['valor']['porMarca'],    $row['marca'],    $valor);
            $this->sumar($resultado[$moneda]['valor']['porCanal'],    $row['canal'],    $valor);
            $this->sumar($resultado[$moneda]['valor']['porFrecuencia'],$row['idFrecuencia'],$valor);
        }

        return $resultado;
    }

    protected function sumar(array &$arr, $categoria, $valor): void
    {
        if (is_object($categoria) && method_exists($categoria, 'format')) {
            $categoria = $categoria->format('Y-m-d');
        }

        $categoria = trim((string)$categoria);
        if ($categoria === '') {
            $categoria = 'Sin datos';
        }

        $valor = is_numeric($valor) ? $valor : 0;

        $arr[$categoria] = ($arr[$categoria] ?? 0) + $valor;
    }

    public function obtenerIngresosHistoricos(): array
    {
        $sql = "
            SELECT
                idMoneda,
                SUM(
                    CAST(precioFinal AS FLOAT64) * 
                    CAST(cantidad AS FLOAT64)
                ) AS total
            FROM `{$this->datasetId}.{$this->viewComprasHistorico}`
            WHERE estado = 'ACTIVE'
            GROUP BY idMoneda;
        ";

        $query = $this->bigQuery->query($sql);
        $rows  = iterator_to_array($this->bigQuery->runQuery($query));

        $totales = [];
        foreach ($rows as $row) {
            $totales[$row['idMoneda']] = round((float)$row['total'], 2);
        }

        return $totales;
    }


}
