<?php

namespace App\Providers;

use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BigQueryClient::class, fn () => new BigQueryClient([
            'projectId' => config('services.bigquery.project_id'),
            'keyFilePath' => config('services.bigquery.key_path'),
        ]));
    }
    
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
