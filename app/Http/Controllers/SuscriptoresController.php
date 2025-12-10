<?php

namespace App\Http\Controllers;

use App\Services\BigQueryService;
use Illuminate\Http\Request;

class SuscriptoresController extends Controller
{
    protected $bigQuery;

    public function __construct(BigQueryService $bigQuery)
    {
        $this->bigQuery = $bigQuery;
    }

    // Vista de la tabla
    public function index()
    {
        return view('suscriptores.index');
    }

    // Datos para DataTables
    public function data(Request $request)
    {
        $start = $request->input('start', 0);  // offset
        $length = $request->input('length', 10); // cantidad por página
        $search = $request->input('search.value', '');

        $suscriptores = $this->bigQuery->obtenerSuscriptoresPaginados($start, $length, $search);
        $totalRecords = $this->bigQuery->contarSuscriptores($search);

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $suscriptores,
        ]);
    }
}
