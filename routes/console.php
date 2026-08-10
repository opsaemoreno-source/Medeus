<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('encuestas:actualizar')->dailyAt('06:00')
    ->withoutOverlapping(120)
    ->runInBackground();

// Limpieza de conversaciones que quedaron sin mensajes ni logs de IA.
// Corre a las 03:30 (fuera del horario de uso del chatbot y antes de que
// arranque la actualización de encuestas, para no pelear por la conexión).
Schedule::command('chatbot:purgar-conversaciones-vacias')
    ->dailyAt('03:30')
    ->withoutOverlapping(30)
    ->runInBackground();
