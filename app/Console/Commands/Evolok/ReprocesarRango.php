<?php

namespace App\Console\Commands\Evolok;

use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Console\Command;

class ReprocesarRango extends Command
{
    protected $signature = 'evolok:reprocesar
        {desde : Fecha inicial (Y-m-d)}
        {hasta : Fecha final, inclusive (Y-m-d)}
        {--solo=usuarios,compras : Lista separada por comas: usuarios,compras,usuarios-actualizados,compras-actualizadas}';

    protected $description = 'Reprocesa (backfill) usuarios y/o compras de Evolok para un rango de fechas operativas';

    protected const COMANDOS = [
        'usuarios' => 'evolok:usuarios',
        'compras' => 'evolok:compras',
        'usuarios-actualizados' => 'evolok:usuarios-actualizados',
        'compras-actualizadas' => 'evolok:compras-actualizadas',
    ];

    public function handle(): int
    {
        $desde = new DateTime($this->argument('desde'));
        $hasta = new DateTime($this->argument('hasta'));

        if ($desde > $hasta) {
            $this->error('La fecha "desde" no puede ser posterior a "hasta".');
            return self::FAILURE;
        }

        $solicitados = array_map('trim', explode(',', $this->option('solo')));
        $comandos = array_intersect_key(self::COMANDOS, array_flip($solicitados));

        if (empty($comandos)) {
            $this->error('--solo no contiene ningún valor válido: ' . implode(', ', array_keys(self::COMANDOS)));
            return self::FAILURE;
        }

        $periodo = new DatePeriod($desde, new DateInterval('P1D'), (clone $hasta)->modify('+1 day'));

        foreach ($periodo as $dia) {
            $fecha = $dia->format('Y-m-d');

            foreach ($comandos as $clave => $comando) {
                $this->info("==> {$comando} {$fecha}");
                $this->call($comando, ['fecha' => $fecha]);
            }
        }

        $this->info('Reprocesamiento finalizado.');

        return self::SUCCESS;
    }
}
