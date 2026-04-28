<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Cloud\BigQuery\BigQueryClient;
use App\Services\EncuestaProcessorService;
use Illuminate\Support\Facades\Log;

class ActualizarEncuestas extends Command
{
    protected $signature = 'encuestas:actualizar';
    protected $description = 'Actualiza encuestas con autoUpdate activo';

    public function handle()
    {
        $bigQuery = new BigQueryClient([
            'projectId'  => 'admanagerapiaccess-382213',
            'keyFilePath'=> storage_path('app/google/bigquery.json')
        ]);

        $query = "
            SELECT id
            FROM `admanagerapiaccess-382213.UsuariosOPSA.EncuestasTypeform`
            WHERE autoUpdate = TRUE
            AND id IS NOT NULL
        ";

        $results = $bigQuery->runQuery($bigQuery->query($query));

        $service = app(EncuestaProcessorService::class);

        $total = 0;
        $ok = 0;
        $fail = 0;

        foreach ($results as $row) {

            $total++;
            $formId = $row['id'];

            try {
                $service->actualizarEncuesta($formId);
                $ok++;

                $this->info("✔ Actualizada: {$formId}");

            } catch (\Exception $e) {
                $fail++;

                Log::error("Error encuesta {$formId}", [
                    'error' => $e->getMessage()
                ]);

                $this->error("✖ Error: {$formId}");
            }
        }

        $this->info("Resumen → Total: {$total}, OK: {$ok}, Error: {$fail}");

        return Command::SUCCESS;
    }
}