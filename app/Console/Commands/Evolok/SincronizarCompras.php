<?php

namespace App\Console\Commands\Evolok;

use App\Services\Evolok\EvolokClient;
use App\Services\Evolok\EvolokComprasMapper;
use App\Services\Evolok\EvolokDates;
use Illuminate\Support\Facades\Log;
use Throwable;

class SincronizarCompras extends EvolokSyncCommand
{
    protected $signature = 'evolok:compras {fecha? : Fecha operativa a reprocesar (Y-m-d). Por defecto, ayer}';
    protected $description = 'Importa a BigQuery las compras de Evolok creadas en el día operativo anterior (o en la fecha indicada)';

    public function handle(EvolokClient $client): int
    {
        $fecha = $this->argument('fecha');
        [$fechaInicio, $fechaFin] = $fecha
            ? EvolokDates::ventanaParaFecha($fecha)
            : EvolokDates::ventanaDiaAnterior();

        $query = [
            '$or' => [
                [
                    '$and' => [
                        [
                            'pm.purchases' => [
                                '$elemMatch' => [
                                    'createdDate' => ['$gte' => ['$date' => $fechaInicio], '$lt' => ['$date' => $fechaFin]],
                                ],
                            ],
                        ],
                        ['ic' => ['$exists' => true]],
                        ['ic.realm' => ['$ne' => 'admin_realm']],
                    ],
                ],
            ],
            '@columns' => EvolokComprasMapper::COLUMNS,
        ];

        try {
            $resultados = $client->fetchReport($query);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            Log::error('evolok:compras falló', ['error' => $e->getMessage()]);
            return self::FAILURE;
        }

        $compras = [];
        foreach ($resultados as $item) {
            $compras = array_merge($compras, EvolokComprasMapper::map($item));
        }

        $this->insertRows('Compras', $compras);

        return self::SUCCESS;
    }
}
