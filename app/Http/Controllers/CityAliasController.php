<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CityAliasService;
use App\Repositories\CityAliasRepository;
use App\Services\BigQueryService;

class CityAliasController extends Controller
{
    public function __construct(
        private CityAliasService $service,
        private CityAliasRepository $repo
    ) {}

    public function index(Request $request)
    {
        $canonica = $request->get('canonica');

        $estado = $request->get('estado');

        if ($estado !== null && $estado !== '') {
            $estado = filter_var($estado, FILTER_VALIDATE_BOOLEAN);
        } else {
            $estado = null;
        }

        $page = max(1, (int) $request->get('page', 1));

        $perPage = 25;

        $offset = ($page - 1) * $perPage;

        $data = $this->repo->getAll(
            $canonica,
            $estado,
            $perPage,
            $offset
        );

        $total = $this->repo->countRecords(
            $canonica,
            $estado
        );

        $totalPages = max(1, ceil($total / $perPage));

        return view('cities.index', [
            'data' => $data,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'canonica' => $canonica,
            'estado' => $estado,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ciudad_alias' => 'required',
            'ciudad_canonica' => 'required'
        ]);

        if ($this->service->existsDuplicate($request->ciudad_alias)) {
            return response()->json([
                'ok' => false,
                'message' => 'Este alias ya existe'
            ], 400);
        }

        try {
            $this->service->create($request->all());

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, string $alias)
    {
        $this->service->update($alias, $request->all());

        return response()->json(['ok' => true]);
    }

    public function canonicasSearch(Request $request)
    {
        $q = $request->get('q', '');

        $results = $this->repo->searchCanonicas($q);

        return response()->json($results);
    }

    public function checkDuplicate(Request $request)
    {
        $alias = $request->get('alias');
        $original = $request->get('original'); // opcional (para edición)

        $exists = $this->service->existsDuplicate($alias, $original);

        return response()->json([
            'duplicate' => $exists
        ]);
    }

}