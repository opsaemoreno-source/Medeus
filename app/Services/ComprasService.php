<?php

namespace App\Services;

use Google\Cloud\BigQuery\BigQueryClient;
use Exception; 

class ComprasService
{
    protected $bigQuery;
    protected string $datasetId = 'UsuariosOPSA';
    protected string $tableId   = 'UsuariosEvolok';

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
            FROM `admanagerapiaccess-382213.UsuariosOPSA.Compras`
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

        if (!empty($filtros['estado'])) {
            $conditions[] = "estado = '{$filtros['estado']}'";
        }

        if (!empty($filtros['marca'])) {
            $conditions[] = "marca = '{$filtros['marca']}'";
        }

        if (!empty($filtros['canal'])) {
            $conditions[] = "canal = '{$filtros['canal']}'";
        }

        if (!empty($filtros['tipoPago'])) {
            $conditions[] = "tipoPago = '{$filtros['tipoPago']}'";
        }

        if (!empty($filtros['search'])) {
            $search = addslashes($filtros['search']);
            $conditions[] = "(CAST(idUsuario AS STRING) LIKE '%$search%' 
                            OR CAST(idCompra AS STRING) LIKE '%$search%')";
        }
        
        if (!empty($filtros['fechaInicio'])) {
            $conditions[] = "fechaInicioSuscripcion >= '{$filtros['fechaInicio']} 00:00:00'";
        }

        if (!empty($filtros['fechaFin'])) {
            $conditions[] = "fechaInicioSuscripcion <= '{$filtros['fechaFin']} 23:59:59'";
        }

        if (!empty($filtros['producto'])) {
            $conditions[] = "nombreProductoDisplay = '{$filtros['producto']}'";
        }

        if (!empty($filtros['plan'])) {
            $conditions[] = "nombrePlanPagoDisplay = '{$filtros['plan']}'";
        }

        return count($conditions)
            ? 'WHERE ' . implode(' AND ', $conditions)
            : '';
    }
}
