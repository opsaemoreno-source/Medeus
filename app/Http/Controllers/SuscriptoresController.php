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
        $totalSuscriptores = $this->bigQuery->obtenerConteoTotalSuscriptores();
        return view('suscriptores.index', compact('totalSuscriptores'));
    }

    // Datos para DataTables
    public function data(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value', '');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $suscriptores = $this->bigQuery->obtenerSuscriptoresPaginadosConFiltro(
            $start, $length, $search, $fechaInicio, $fechaFin
        );

        $totalRecords = $this->bigQuery->contarSuscriptoresConFiltro(
            $search, $fechaInicio, $fechaFin
        );

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $suscriptores,
        ]);
    }

    public function estadisticaPorDia(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $data = $this->bigQuery->obtenerEstadisticaPorDia($fechaInicio, $fechaFin);

        return response()->json($data);
    }


    public function exportar(Request $request)
    {
        $search = $request->input('search');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $data = $this->bigQuery->obtenerSuscriptoresExportar($search, $fechaInicio, $fechaFin);

        if (empty($data)) {
            return back()->with('error', 'No hay datos para exportar.');
        }

        // Normalizar datos (convertir DateTime a string)
        /** @var array<int, array<string, mixed>> $data */
        $normalizados = [];
        foreach ($data as $row) {
            $fila = [];
            foreach ($row as $key => $value) {
                if ($value instanceof \DateTimeInterface) {
                    $fila[$key] = $value->format('Y-m-d H:i:s');
                } elseif (is_object($value)) {
                    // BigQuery puede devolver objetos "Timestamp"
                    $fila[$key] = (string) $value;
                } else {
                    $fila[$key] = $value;
                }
            }
            $normalizados[] = $fila;
        }

        $filename = "suscriptores_export_" . date('Ymd_His') . ".csv";

        $handle = fopen('php://temp', 'r+');

        // Encabezados
        fputcsv($handle, array_keys($normalizados[0]));

        // Datos
        foreach ($normalizados as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);

        return response(stream_get_contents($handle), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ]);
    }
}
