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
                c.idUsuario            AS idUsuario,
                c.idCompra             AS idCompra,
                c.nombreProductoDisplay AS nombreProductoDisplay,
                c.nombrePlanPagoDisplay AS nombrePlanPagoDisplay,
                c.precioFinal          AS precioFinal,
                c.idMoneda             AS idMoneda,
                c.cantidad             AS cantidad,
                c.estado               AS estado,
                c.marca                AS marca,
                c.canal                AS canal,
                c.tipoPago             AS tipoPago,
                c.fechaInicioSuscripcion AS fechaInicioSuscripcion,
                c.fechaFinalSuscripcion  AS fechaFinalSuscripcion,
                c.ultimaFechaPago       AS ultimaFechaPago,
                c.proximaFechaPago      AS proximaFechaPago,
                c.fechaCreacion         AS compraFechaCreacion,
                u.nombre                AS usuarioNombre,
                u.apellido              AS usuarioApellido,
                u.correo                AS usuarioCorreo
            FROM `{$this->datasetId}.{$this->tableCompras}` AS c
            LEFT JOIN `{$this->datasetId}.vta_usuariosEvolok` u
                ON c.idUsuario = u.userid
            $where
            ORDER BY c.fechaInicioSuscripcion DESC
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
            if (($row['estado'] ?? null) === 'ACTIVE' && ($row['precioFinal'] ?? null) !== null) {
                $moneda = $row['idMoneda'] ?? 'UNKNOWN';

                // Normalizar precioFinal (BigQuery puede devolver objetos Numeric)
                $precioFinal = $row['precioFinal'];
                if (is_object($precioFinal)) {
                    $precioFinal = (string)$precioFinal;
                }

                $precioFloat = is_numeric($precioFinal) ? (float)$precioFinal : 0.0;
                $cantidadInt  = is_numeric($row['cantidad']) ? (int)$row['cantidad'] : 0;

                $totales[$moneda] = ($totales[$moneda] ?? 0) + ($precioFloat * $cantidadInt);
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

                // datos de usuario (join a vta_usuariosEvolok)
                'fechaCreacion' => $this->formatDate($row['compraFechaCreacion'] ?? null),
                'nombreUsuario' => ($row['usuarioNombre'].' '.$row['usuarioApellido']) ?? ($row['nombre'].' '.$row['apellido'] ?? null),
                'correo'        => $row['usuarioCorreo'] ?? ($row['correo'] ?? null),
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
                    $conditions[] = "c.estado IN ($estadosSQL)";
                }

            } else {
                $conditions[] = "c.estado = '" . addslashes($filtros['estado']) . "'";
            }
        }

        // =========================
        // RESTO DE FILTROS (sin cambios)
        // =========================
        if (!empty($filtros['marca'])) {
            $conditions[] = "TRIM(LOWER(c.marca)) = '{$filtros['marca']}'";
        }

        if (!empty($filtros['canal'])) {
            $conditions[] = "c.canal = '{$filtros['canal']}'";
        }

        if (!empty($filtros['tipoPago'])) {
            $conditions[] = "c.tipoPago = '{$filtros['tipoPago']}'";
        }

        if (!empty($filtros['producto'])) {
            $conditions[] = "c.nombreProductoDisplay = '{$filtros['producto']}'";
        }

        if (!empty($filtros['plan'])) {
            $conditions[] = "c.nombrePlanPagoDisplay = '{$filtros['plan']}'";
        }

        if (!empty($filtros['search'])) {
            $search = addslashes($filtros['search']);
            $conditions[] = "(
                CAST(c.idUsuario AS STRING) LIKE '%$search%' 
                OR CAST(c.idCompra AS STRING) LIKE '%$search%'
            )";
        }

        if (!empty($filtros['fechaInicio'])) {
            $conditions[] = "c.fechaInicioSuscripcion >= '{$filtros['fechaInicio']} 00:00:00'";
        }

        if (!empty($filtros['fechaFin'])) {
            $conditions[] = "c.fechaInicioSuscripcion <= '{$filtros['fechaFin']} 23:59:59'";
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
