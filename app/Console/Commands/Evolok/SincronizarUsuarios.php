<?php

namespace App\Console\Commands\Evolok;

use App\Services\Evolok\EvolokClient;
use App\Services\Evolok\EvolokDates;
use App\Services\Evolok\EvolokUsuariosMapper;
use Illuminate\Support\Facades\Log;
use Throwable;

class SincronizarUsuarios extends EvolokSyncCommand
{
    protected $signature = 'evolok:usuarios {fecha? : Fecha operativa a reprocesar (Y-m-d). Por defecto, ayer}';
    protected $description = 'Importa a BigQuery los usuarios de Evolok creados en el día operativo anterior (o en la fecha indicada)';

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
                        ['ic.created' => ['$gte' => ['$date' => $fechaInicio], '$lt' => ['$date' => $fechaFin]]],
                        ['ic' => ['$exists' => true]],
                        ['ic.realm' => ['$ne' => 'admin_realm']],
                    ],
                ],
            ],
            '@columns' => EvolokUsuariosMapper::COLUMNS,
        ];

        try {
            $resultados = $client->fetchReport($query);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            Log::error('evolok:usuarios falló', ['error' => $e->getMessage()]);
            return self::FAILURE;
        }

        $usuarios = EvolokUsuariosMapper::map($resultados);

        $this->insertRows('UsuariosEvolok', $usuarios);

        return self::SUCCESS;
    }
}
