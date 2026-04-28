<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Services\BigQueryService;

class EncuestasController extends Controller
{
    protected $bigQuery;

    public function __construct(BigQueryService $bigQuery)
    {
        $this->bigQuery = $bigQuery;
    }

    public function index()
    {
        $encuestas = $this->bigQuery->obtenerEncuestas();

        return view('encuestas.index', compact('encuestas'));
    }

    // EncuestasController.php
    public function procesar(Request $request)
    {
        $id = $request->input('id');
        try {
            app(\App\Services\EncuestaProcessorService::class)->procesarEncuesta($id);
            return back()->with('success', "Encuesta $id procesada correctamente.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function actualizar(Request $request)
    {
        $id = $request->input('id');

        try {
            app(\App\Services\EncuestaProcessorService::class)
                ->actualizarEncuesta($id);

            return back()->with('success', 
                "Encuesta $id actualizada correctamente."
            );

        } catch (\Exception $e) {
            return back()->with('error', 
                'Error: '.$e->getMessage()
            );
        }
    }

    public function syncAutoUpdate(Request $request)
    {
        $items = $request->input('items', []);

        try {
            $service = app(\App\Services\BigQueryService::class);

            foreach ($items as $item) {
                $service->actualizarAutoUpdate(
                    $item['id'],
                    (bool) $item['autoUpdate']
                );
            }

            return response()->json([
                'success' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
