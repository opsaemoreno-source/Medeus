<?php

namespace App\Http\Controllers;
use App\Services\SuscriptoresService;
use App\Services\ComprasService;

use Illuminate\Http\Request;

class EstadisticasController extends Controller
{
    /** 
     * Vista principal del HUB de estadísticas
     */
    public function index()
    {
        return view('estadisticas.index');
    }

    /**
     * Cargar parcial de encuestas
     */
    public function encuestas()
    {
        return response()->json([
            'html' => view('estadisticas.partials.encuestas')->render()
        ]);
    }

    /**
     * Cargar parcial de compras
     */
    public function compras(Request $request)
    {
        $filtros = [
            'fechaInicio' => $request->query('fecha_inicio'),
            'fechaFin'    => $request->query('fecha_fin'),
        ];

        $service = new ComprasService();
        $estadisticas = $service->obtenerEstadisticasCompras($filtros);
        $estadisticasActivas = $service->obtenerEstadisticasComprasActivas($filtros);

        return response()->json([
            'html' => view('estadisticas.partials.compras')->render(),
            'data' => $estadisticas,
            'activas' => $estadisticasActivas
        ]);
    }

    /**
     * Cargar parcial de suscriptores
     */
    public function suscriptores(Request $request)
    {
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin    = $request->query('fecha_fin');
        $modoCiudad  = $request->query('modo_ciudad', 'normalizado');

        $service = new SuscriptoresService();
        $estadisticas = $service->obtenerEstadisticas(
            $fechaInicio,
            $fechaFin
        );
        $estadisticas['ciudad'] = $service->obtenerSuscriptoresPorCiudad(
            $modoCiudad,
            $fechaInicio,
            $fechaFin
        );

        return response()->json([
            'html' => view('estadisticas.partials.suscriptores')
                ->with('estadisticas', $estadisticas)
                ->with('modoCiudad', $modoCiudad)
                ->render(),
            'data' => $estadisticas
        ]);
    }

    /**
     * Cargar parcial de opciones avanzadas
     */
    public function avanzado()
    {
        return response()->json([
            'html' => view('estadisticas.partials.avanzado')->render()
        ]);
    }
}
