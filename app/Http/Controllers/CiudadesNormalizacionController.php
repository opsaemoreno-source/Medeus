<?php

namespace App\Http\Controllers;

use App\Services\CiudadesNormalizacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CiudadesNormalizacionController extends Controller
{
    public function index()
    {
        return view('ciudades.index');
    }

    public function canonicas(CiudadesNormalizacionService $service)
    {
        $data = $service->obtenerCiudadesCanonicas();
        Log::info('Canonicas', $data);
        return response()->json($data);
    }

    public function alias($ciudadCanonica, CiudadesNormalizacionService $service)
    {
        return response()->json(
            $service->obtenerAliasPorCanonica($ciudadCanonica)
        );
    }

    public function store(Request $request, CiudadesNormalizacionService $service)
    {
        $request->validate([
            'ciudad_alias' => 'required',
            'ciudad_canonica' => 'required',
            'prioridad' => 'nullable|integer'
        ]);

        $service->guardarAlias($request->all());

        return response()->json(['ok' => true]);
    }

    public function update(Request $request, CiudadesNormalizacionService $service)
    {
        $service->actualizarAlias(
            $request->alias_original,
            $request->all()
        );

        return response()->json(['ok' => true]);
    }

    public function destroy($alias, CiudadesNormalizacionService $service)
    {
        $service->desactivarAlias($alias);
        return response()->json(['ok' => true]);
    }

    public function storeCanonica(Request $request, CiudadesNormalizacionService $service)
    {
        $request->validate([
            'ciudad_canonica' => 'required',
            'ciudad_alias'    => 'required',
            'pais'            => 'required',
            'prioridad'       => 'nullable|integer'
        ]);

        $service->guardarCiudadCanonica(
            $request->ciudad_canonica,
            $request->ciudad_alias,
            $request->pais,
            $request->prioridad ?? 1
        );

        return response()->json(['ok' => true]);
    }


    // PUT /ciudades/canonicas
    public function updateCanonica(Request $request, CiudadesNormalizacionService $service)
    {
        $request->validate([
            'ciudad_canonica' => 'required',
            'pais' => 'required'
        ]);

        $service->actualizarCiudadCanonica($request->ciudad_canonica, $request->pais);

        return response()->json(['ok' => true]);
    }
}
