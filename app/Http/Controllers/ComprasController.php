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

        $resultado = $this->service->obtenerCompras($filtros);

        return response()->json($resultado);
    }
}
