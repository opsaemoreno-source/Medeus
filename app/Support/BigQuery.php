<?php

namespace App\Support;

use Google\Cloud\BigQuery\BigQueryClient;
use InvalidArgumentException;
use RuntimeException;

/**
 * Punto único de configuración de BigQuery (cliente, proyecto y dataset) y
 * utilidades para armar consultas sin interpolar datos de la petición.
 */
class BigQuery
{
    public static function client(): BigQueryClient
    {
        return app(BigQueryClient::class);
    }

    public static function projectId(): string
    {
        return self::requerido('services.bigquery.project_id', 'GOOGLE_PROJECT_ID');
    }

    public static function dataset(): string
    {
        return self::requerido('services.bigquery.dataset', 'BIGQUERY_DATASET');
    }

    private static function requerido(string $key, string $env): string
    {
        $value = config($key);

        if (!is_string($value) || $value === '') {
            throw new RuntimeException(
                "Falta la configuración {$key} ({$env}). Si se usa config:cache, "
                . "ejecuta `php artisan config:clear` para regenerarla."
            );
        }

        return $value;
    }

    /**
     * Referencia completa `proyecto.dataset.tabla` lista para usar en SQL.
     */
    public static function table(string $name): string
    {
        return '`' . self::projectId() . '.' . self::dataset() . '.' . $name . '`';
    }

    /**
     * Ejecuta una consulta con parámetros nombrados (@nombre).
     */
    public static function run(BigQueryClient $client, string $sql, array $params = [])
    {
        $job = $client->query($sql);

        if (!empty($params)) {
            $job = $job->parameters($params);
        }

        return $client->runQuery($job);
    }

    /**
     * Valida una fecha recibida de la petición (Y-m-d, con hora opcional) antes
     * de usarla como literal en SQL. Las columnas de fecha mezclan TIMESTAMP y
     * DATETIME, por eso se conserva el literal en lugar de un parámetro tipado.
     */
    public static function fecha($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}([ T]\d{2}:\d{2}(:\d{2})?)?$/', $value)) {
            throw new InvalidArgumentException('Fecha inválida: ' . (is_string($value) ? $value : gettype($value)));
        }

        return $value;
    }

    /**
     * Valida un nombre de columna antes de interpolarlo en SQL.
     */
    public static function columna(string $name): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) {
            throw new InvalidArgumentException("Columna inválida: {$name}");
        }

        return $name;
    }
}
