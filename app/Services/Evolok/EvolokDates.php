<?php

namespace App\Services\Evolok;

use DateTime;
use DateTimeZone;
use Exception;

class EvolokDates
{
    /**
     * Ventana del día operativo anterior (06:00 a 05:59:59.999) en UTC,
     * formato esperado por las consultas Mongo de Evolok.
     *
     * @return array{0: string, 1: string} [$fechaInicio, $fechaFin]
     */
    public static function ventanaDiaAnterior(): array
    {
        $fechaInicio = (new DateTime('yesterday 06:00:00'))
            ->setTimezone(new DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s.000\Z');

        $fechaFin = (new DateTime('today 05:59:59'))
            ->setTimezone(new DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s.999\Z');

        return [$fechaInicio, $fechaFin];
    }

    /**
     * Misma ventana operativa (06:00 a 05:59:59.999 del día siguiente), pero
     * anclada a una fecha arbitraria en vez de "ayer". Usada para reprocesar
     * (backfill) días concretos.
     *
     * @return array{0: string, 1: string} [$fechaInicio, $fechaFin]
     */
    public static function ventanaParaFecha(string $fecha): array
    {
        $fechaInicio = (new DateTime($fecha . ' 06:00:00'))
            ->setTimezone(new DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s.000\Z');

        $fechaFin = (new DateTime($fecha . ' +1 day 05:59:59'))
            ->setTimezone(new DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s.999\Z');

        return [$fechaInicio, $fechaFin];
    }

    public static function transform($string, int $type = 1): string
    {
        if ($string === null) {
            return $type === 1 ? '0001-01-01 00:00:00' : '0001-01-01';
        }

        try {
            if (is_numeric($string)) {
                $timestamp = (int) $string;
                if ($timestamp > 9999999999) {
                    $timestamp = (int) ($timestamp / 1000);
                }
                $date = (new DateTime())->setTimestamp($timestamp);
            } else {
                $date = new DateTime($string);
            }

            return $type === 1 ? $date->format('Y-m-d H:i:s') : $date->format('Y-m-d');
        } catch (Exception $e) {
            return $type === 1 ? '0001-01-01 00:00:00' : '0001-01-01';
        }
    }
}
