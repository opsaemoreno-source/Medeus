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
        $genero = $request->input('genero');
        $pais   = $request->input('pais');
        $ciudad = $request->input('ciudad');
        $canal  = $request->input('canal');
        $edad_min = $request->input('edad_min');
        $edad_max = $request->input('edad_max');
        $nivelEducativo = $request->input('nivelEducativo');
        $profesion      = $request->input('profesion');
        $marca          = $request->input('marca');
        $estadoCivil    = $request->input('estadoCivil');

        $suscriptores = $this->bigQuery->obtenerSuscriptoresPaginadosConFiltro(
            $start, $length, $search, $fechaInicio, $fechaFin,
            $genero, $pais, $ciudad, $canal, $marca,
            $edad_min, $edad_max, $nivelEducativo, $profesion, $estadoCivil
        );

        $totalRecords = $this->bigQuery->contarSuscriptoresConFiltro(
            $search, $fechaInicio, $fechaFin,
            $genero, $pais, $ciudad, $canal, $marca,
            $edad_min, $edad_max, $nivelEducativo, $profesion, $estadoCivil
        );

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $suscriptores
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
        $genero = $request->input('genero') ?? null;
        $pais = $request->input('pais') ?? null;
        $ciudad = $request->input('ciudad') ?? null;
        $canal = $request->input('canal') ?? null;
        $marca = $request->input('marca') ?? null;
        $edad_min = $request->input('edad_min') ?? null;
        $edad_max = $request->input('edad_max') ?? null;
        $nivelEducativo = $request->input('nivelEducativo') ?? null;
        $profesion = $request->input('profesion') ?? null;
        $estadoCivil = $request->input('estadoCivil') ?? null;

        $data = $this->bigQuery->obtenerSuscriptoresExportar($search, $fechaInicio, $fechaFin, $genero, $pais, $ciudad, $canal, $marca, $edad_min, $edad_max, $nivelEducativo, $profesion, $estadoCivil);
        
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

    public function getCatalogos()
    {
        return $data = [ 
            'civil' => $this->bigQuery->catalogoEstadoCivil(),
            'educativo' => $this->bigQuery->catalogoNivelEducativo(),
            'paises' => $this->bigQuery->catalogoPaises(),
            'profesiones' => $this->bigQuery->catalogoProfesiones()
        ];
    }
}
