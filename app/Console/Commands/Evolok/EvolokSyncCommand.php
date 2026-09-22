<?php

namespace App\Console\Commands\Evolok;

use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

abstract class EvolokSyncCommand extends Command
{
    protected function bigQuery(): BigQueryClient
    {
        return new BigQueryClient([
            'projectId' => env('GOOGLE_PROJECT_ID'),
            'keyFilePath' => config('services.bigquery.key_path'),
        ]);
    }

    protected function insertRows(string $tableId, array $rows): void
    {
        if (empty($rows)) {
            $this->info('No hay datos para insertar en BigQuery.');
            Log::info("{$this->signature}: sin datos para insertar", ['tabla' => $tableId]);
            return;
        }

        $dataset = $this->bigQuery()->dataset('UsuariosOPSA');
        $table = $dataset->table($tableId);

        $rowsToInsert = array_map(fn ($row) => ['data' => $row], $rows);

        $insertResponse = $table->insertRows($rowsToInsert);

        if ($insertResponse->isSuccessful()) {
            $this->info("Se insertaron {$this->countRows($rows)} filas en {$tableId}.");
            Log::info("{$this->signature}: insertado correctamente", ['tabla' => $tableId, 'filas' => count($rows)]);
            return;
        }

        foreach ($insertResponse->failedRows() as $row) {
            $errores = array_map(fn ($e) => "{$e['reason']}: {$e['message']}", $row['errors']);
            $this->error('Fila con error: ' . json_encode($row['rowData']));
            Log::error("{$this->signature}: fila con error", [
                'tabla' => $tableId,
                'rowData' => $row['rowData'],
                'errores' => $errores,
            ]);
        }
    }

    private function countRows(array $rows): int
    {
        return count($rows);
    }
}
