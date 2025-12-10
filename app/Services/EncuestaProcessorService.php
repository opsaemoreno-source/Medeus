<?php

namespace App\Services;

use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use DateTime;
use Exception;

class EncuestaProcessorService
{
    public function procesarEncuesta(string $formId)
    {
        $projectId = 'admanagerapiaccess-382213';
        $datasetId = "UsuariosOPSA";
        $tableId = "EncuestasTypeform";

        $bigQuery = new BigQueryClient([
            'projectId'  => $projectId,
            'keyFilePath'=> storage_path('app/google/bigquery.json')
        ]);

        $accessToken = env('TYPEFORM_TOKEN');
        if (!$accessToken) {
            throw new Exception("TYPEFORM_TOKEN no está configurado.");
        }

        $apiUrl = "https://api.typeform.com/forms/{$formId}/responses";

        $resp = Http::withToken($accessToken)->get($apiUrl, ['page_size' => 1]);
        $totalResponses = $resp->json()['total_items'] ?? 0;

        // Obtener metadatos del formulario
        $url = "https://api.typeform.com/forms/{$formId}";
        $resp = Http::withToken($accessToken)->get($url);
        if (!$resp->successful()) {
            throw new Exception("Typeform API error ({$resp->status()}): ".$resp->body());
        }
        $formData = $resp->json();
        if (empty($formData)) {
            throw new Exception("Respuesta vacía al obtener formulario {$formId}.");
        }

        // Mapear preguntas (defensivo)
        $mapaPreguntas = [];
        $fields = $formData['fields'] ?? [];
        foreach ($fields as $field) {
            if (isset($field['id'], $field['title'])) {
                $mapaPreguntas[] = ['id' => $field['id'], 'texto' => $field['title']];
            }
        }

        // Insertar registro base en BigQuery
        $dataset = $bigQuery->dataset($datasetId);
        $table = $dataset->table($tableId);

        $dataBQ = [
            "id"                => $this->safe_str_replace('"', '', $formData["id"] ?? ''),
            "titulo"            => $this->safe_str_replace('"', '', $formData['title'] ?? ''),
            "fechaCreacion"     => $this->transformDate($formData['created_at'] ?? ''),
            "fechaPublicacion"  => $this->transformDate($formData['published_at'] ?? ''),
            "noCampos"          => isset($formData['fields']) ? count($formData['fields']) : 0,
            "noRespuestas"      => $totalResponses
        ];

        $query = sprintf(
            "SELECT COUNT(*) AS total FROM `%s.%s.%s` WHERE id = @idForm",
            $projectId,
            $datasetId,
            $tableId
        );

        $jobConfig = $bigQuery->query($query)->parameters([
            'idForm' => $formData["id"] ?? ''
        ]);

        $results = $bigQuery->runQuery($jobConfig);

        if ($results->isComplete()) {
            foreach ($results as $row) {
                if (($row['total'] ?? 0) > 0) {
                    // Ya existe el formulario → detener proceso
                    throw new Exception("El formulario con ID {$formData['id']} ya fue procesado anteriormente.");
                }
            }
        } else {
            throw new Exception("No se pudo validar existencia previa en BigQuery.");
        }

        $insertResponse = $table->insertRows([['data' => $dataBQ]]);

        if (!$insertResponse->isSuccessful()) {
            Log::error('BigQuery insert base failed', ['failedRows' => $insertResponse->failedRows()]);
            throw new Exception("Error al insertar en tabla base (BigQuery).");
        }

        // Obtener todas las respuestas del formulario (paginación)
        $apiUrl = "https://api.typeform.com/forms/{$formId}/responses";
        $pageSize = 1000;
        $after = null;
        //$totalResponses = 0;
        $allResponses = [];

        do {
            $query = ['page_size' => $pageSize];
            if ($after) {
                $query['before'] = $after; // revisa si tu API necesita 'before' o 'after'
            }

            $resp = Http::withToken($accessToken)->get($apiUrl, $query);

            if (!$resp->successful()) {
                throw new Exception("Error HTTP {$resp->status()} al consultar Typeform: ".$resp->body());
            }

            $data = $resp->json();
            if (empty($data['items'])) {
                break;
            }

            $allResponses = array_merge($allResponses, $data['items']);

            $lastItem = end($data['items']);
            $after = $lastItem['token'] ?? null;

        } while ($after);

        // Normalizar respuestas y preparar inserción
        $respuestas = $this->formatearRespuestas($allResponses, $formId, $mapaPreguntas);

        $rowsToInsert = [];
        foreach ($respuestas as $row) {
            $rowsToInsert[] = ['data' => $row];
        }

        // Insertar en la tabla detalle en chunks
        $tableId2 = "EncuestasTypeformDetalle";
        $table2 = $bigQuery->dataset($datasetId)->table($tableId2);

        Log::info('Mensaje de prueba');
        Log::debug('Variable:', ['data' => $rowsToInsert]);

        if (!empty($rowsToInsert)) {
            // ajusta chunk size según pruebas (500 es razonable)
            foreach (array_chunk($rowsToInsert, 500) as $chunk) {
                $insertResponse2 = $table2->insertRows($chunk);
                if (!$insertResponse2->isSuccessful()) {
                    Log::error('BigQuery insert detalle failed', ['failedRows' => $insertResponse2->failedRows()]);
                    throw new Exception("Errores al insertar en tabla detalle (BigQuery).");
                }
            }
        }

        return true;
    }

    private function transformDate($string)
    {
        try {
            $date = new DateTime($string);
            return $date->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return '0001-01-01 00:00:00';
        }
    }

    private function safe_str_replace($search, $replace, $subject)
    {
        return str_replace($search, $replace, $subject ?? '');
    }

    private function safe_trim($value)
    {
        return trim($value ?? '');
    }

    private function formatearRespuestas($allResponses, $idEncuesta, $mapaPreguntas = [])
    {
        $rows = [];

        $preguntasMap = [];
        foreach ($mapaPreguntas as $p) {
            if (isset($p['id'], $p['texto'])) {
                $preguntasMap[$p['id']] = $p['texto'];
            }
        }

        foreach ($allResponses as $item) {
            $userid = $item['hidden']['userid'] ?? 'GUEST';
            $fechaInicio = $item['landed_at'] ?? '0001-01-01 00:00:00';
            $fechaFin = $item['submitted_at'] ?? '0001-01-01 00:00:00';
            $tokenResp = $item['token'] ?? 'GUEST';
            $userAgent = $item['metadata']['user_agent'] ?? '';
            $platform = $item['metadata']['platform'] ?? '';

            if (!isset($item['answers']) || !is_array($item['answers'])) {
                continue;
            }

            foreach ($item['answers'] as $ans) {
                $idPregunta = $ans['field']['id'] ?? '';
                $tipoPregunta = $ans['field']['type'] ?? '';
                $tipoRespuesta = $ans['type'] ?? '';
                $pregunta = $preguntasMap[$idPregunta] ?? '';

                list($respuesta, $idRespuesta) = $this->normalizarRespuestas($ans);

                $rows[] = [
                    'userid'         => $userid,
                    'idEncuesta'     => $idEncuesta,
                    'pregunta'       => $pregunta,
                    'idPregunta'     => $idPregunta,
                    'tipoPregunta'   => $tipoPregunta,
                    'respuesta'      => $respuesta,
                    'idRespuesta'    => $idRespuesta,
                    'tipoRespuesta'  => $tipoRespuesta,
                    'fechaInicio'    => $this->transformDate($fechaInicio),
                    'fechaFin'       => $this->transformDate($fechaFin),
                    'token'          => $tokenResp,
                    'user_agent'     => $userAgent,
                    'platform'       => $platform
                ];
            }
        }

        return $rows;
    }

    private function normalizarRespuestas($ans)
    {
        $tipo = $ans['type'] ?? '';

        switch ($tipo) {
            case 'boolean':
                return [($ans['boolean'] ?? false) ? 'Sí' : 'No', ''];

            case 'text':
                return [$ans['text'] ?? '', ''];

            case 'email':
                return [$ans['email'] ?? '', ''];

            case 'number':
                return [$ans['number'] ?? '', ''];

            case 'phone_number':
                return [$ans['phone_number'] ?? '', ''];

            case 'url':
                return [$ans['url'] ?? '', ''];

            case 'date':
                return [$ans['date'] ?? '', ''];

            case 'choice':
                return [$ans['choice']['label'] ?? '', $ans['choice']['id'] ?? ''];

            case 'choices':
                return [isset($ans['choices']['labels']) ? implode(', ', $ans['choices']['labels']) : '', ''];

            default:
                return ['', ''];
        }
    }
}
