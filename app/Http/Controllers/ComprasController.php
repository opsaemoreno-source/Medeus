<?php

namespace App\Http\Controllers;

use App\Services\ComprasService;
use Illuminate\Http\Request;

class ComprasController extends Controller
{
    protected ComprasService $service;

    public function __construct(ComprasService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('compras.index');
    }

    public function data(Request $request)
    {
        $filtros = [
            'estado'      => $request->input('estado'),
            'marca'       => $request->input('marca'),
            'canal'       => $request->input('canal'),
            'tipoPago'    => $request->input('tipoPago'),
            'producto'    => $request->input('producto'),
            'plan'        => $request->input('plan'),
            'fechaInicio' => $request->input('fecha_inicio'),
            'fechaFin'    => $request->input('fecha_fin'),
            'search'      => $request->input('search'),
        ];

        return response()->json([
            'data' => $this->service->obtenerCompras($filtros)['data'],
            'total_ingresos_actuales' =>
                $this->service->obtenerCompras($filtros)['total_ingresos'],
            'total_ingresos_historicos' =>
                $this->service->obtenerIngresosHistoricos(),
        ]);
    }

    public function exportarCSV(Request $request)
    {
        $filtros = [
            'estado'      => $request->input('estado'),
            'marca'       => $request->input('marca'),
            'canal'       => $request->input('canal'),
            'tipoPago'    => $request->input('tipoPago'),
            'producto'    => $request->input('producto'),
            'plan'        => $request->input('plan'),
            'fechaInicio' => $request->input('fecha_inicio'),
            'fechaFin'    => $request->input('fecha_fin'),
            'search'      => $request->input('search'),
        ];

        $compras = $this->service->obtenerCompras($filtros)['data'];

        // Headers para CSV
        $headers = [
            'Fecha Creación',
            'Nombre Usuario',
            'Correo',
            'Producto',
            'Plan',
            'Precio',
            'Moneda',
            'Cantidad',
            'Estado',
            'Marca',
            'Canal',
            'Tipo Pago',
            'Inicio Suscripción',
            'Fin Suscripción',
            'ID Usuario',
            'ID Compra',
        ];

        // Crear contenido CSV con BOM UTF-8 para Excel
        $csv = "\xEF\xBB\xBF"; // BOM UTF-8
        $csv .= implode(',', array_map(function($h) { return '"' . str_replace('"', '""', $h) . '"'; }, $headers)) . "\n";

        foreach ($compras as $row) {
            $values = [
                $row['fechaCreacion'] ?? '',
                $row['nombreUsuario'] ?? '',
                $row['correo'] ?? '',
                $row['producto'] ?? '',
                $row['plan'] ?? '',
                $row['precio'] ?? '',
                $row['moneda'] ?? '',
                $row['cantidad'] ?? '',
                $row['estado'] ?? '',
                $row['marca'] ?? '',
                $row['canal'] ?? '',
                $row['tipoPago'] ?? '',
                $row['inicio'] ?? '',
                $row['fin'] ?? '',
                $row['idUsuario'] ?? '',
                $row['idCompra'] ?? '',
            ];
            $csv .= implode(',', array_map(function($v) { return '"' . str_replace('"', '""', $v) . '"'; }, $values)) . "\n";
        }

        $filename = 'compras_' . date('Y-m-d_H-i-s') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
