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

}
