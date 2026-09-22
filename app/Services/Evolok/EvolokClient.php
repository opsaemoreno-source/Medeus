<?php

namespace App\Services\Evolok;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EvolokClient
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl  = config('services.evolok.base_url');
        $this->username = config('services.evolok.username');
        $this->password = config('services.evolok.password');
        $this->timeout  = config('services.evolok.timeout');
    }

    /**
     * Autentica y ejecuta el reporte en una sola sesión (cookie jar en memoria).
     * Devuelve el arreglo "results" de la respuesta de Evolok.
     */
    public function fetchReport(array $query, int $queryLimit = 999999): array
    {
        if (! $this->username || ! $this->password) {
            throw new RuntimeException('Credenciales de Evolok no configuradas (EVOLOK_USERNAME / EVOLOK_PASSWORD).');
        }

        $jar = new CookieJar();

        $authResponse = Http::withOptions(['cookies' => $jar])
            ->timeout($this->timeout)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->baseUrl}/console/api/auth", [
                'username' => $this->username,
                'password' => $this->password,
            ]);

        if ($authResponse->failed()) {
            throw new RuntimeException('Falló la autenticación con Evolok: HTTP ' . $authResponse->status());
        }

        $queryResponse = Http::withOptions(['cookies' => $jar])
            ->timeout($this->timeout)
            ->withHeaders([
                'Accept' => 'application/json, text/plain, */*',
                'Content-Type' => 'application/json;charset=utf-8',
                'Origin' => $this->baseUrl,
                'Referer' => "{$this->baseUrl}/console/index.html",
            ])
            ->send('PUT', "{$this->baseUrl}/rm/rest/reports-creator/query-result", [
                'query' => ['queryLimit' => $queryLimit],
                'body' => json_encode($query, JSON_UNESCAPED_UNICODE),
            ]);

        if ($queryResponse->failed()) {
            throw new RuntimeException('Falló la consulta del reporte en Evolok: HTTP ' . $queryResponse->status());
        }

        return $queryResponse->json('results') ?? [];
    }
}
