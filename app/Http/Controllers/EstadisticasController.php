<?php

namespace App\Http\Controllers;
use App\Services\SuscriptoresService;
use App\Services\ComprasService;
use App\Services\EstadisticasAvanzadasService;
use App\Services\EncuestasService;

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
    public function encuestas(Request $request)
    {
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin    = $request->query('fecha_fin');

        $service = new EncuestasService();

        $data = [
            'kpis' => $service->kpis($fechaInicio, $fechaFin),
            'genero' => $service->demografia('genero'),
            'pais' => $service->demografia('pais'),
            'ciudad' => $service->demografia('ciudad'),
            'nivelEducativo' => $service->demografia('nivelEducativo'),
        ];

        return response()->json([
            'html' => view('estadisticas.partials.encuestas', compact('data'))->render(),
            'data' => $data
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
    public function avanzado(Request $request)
    {
        $filtros = $request->all();

        $service = new EstadisticasAvanzadasService();

        $marcas = $service->valoresDistintos('marca', $filtros);
        $canales = $service->valoresDistintos('canal', $filtros);
        $topCiudades = $service->topCiudades($filtros);
        $topProfesiones = $service->topProfesiones($filtros);
        $topNivelesEducativos = $service->topNivelesEducativos($filtros);

        $data = [
            'usuariosMixtos' => $service->usuariosConCompraYEncuesta($filtros),

            'usuariosRespondieronEncuesta' => $service->usuariosQueRespondieronEncuestas($filtros),
            'suscripciones' => $service->suscripcionesCompradas($filtros),
            'topPaisesPerfil' => $service->topPaisesPerfil($filtros),
            'topPaisesIP'     => $service->topPaisesIP($filtros),
            'topCiudades'     => $topCiudades,
            'topProfesiones' => $topProfesiones,
            'topNivelesEducativos' => $topNivelesEducativos,

            'marcas' => $marcas,
            'canales' => $canales,
        ];

        return response()->json([
            'html' => view('estadisticas.partials.avanzado', compact('data'))->render(),
            'data' => $data
        ]);
    }

}
