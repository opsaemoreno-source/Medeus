<?php

namespace App\Http\Controllers;
use App\Services\SuscriptoresService;

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
     * Cargar parcial de suscriptores
     */
    public function suscriptores()
    {
        $service = new SuscriptoresService();
        $estadisticas = $service->obtenerEstadisticas();

        return response()->json([
            'html' => view('estadisticas.partials.suscriptores')->with('estadisticas', $estadisticas)->render(),
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
