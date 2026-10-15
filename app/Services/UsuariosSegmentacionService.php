<?php

namespace App\Services;

use App\Support\BigQuery;

class UsuariosSegmentacionService
{
    protected string $tablaEncuestasDetalle;

    public function __construct()
    {
        $this->tablaEncuestasDetalle = BigQuery::table('EncuestasTypeformDetalle');
    }

    /**
     * Construye el WHERE a partir de filtros demográficos. Los valores se
     * agregan a $params para ejecutarse como parámetros de BigQuery.
     */
    public function buildWhere(array $filtros, array &$params = []): string
    {
        $where = [];

        $fechaInicio = BigQuery::fecha($filtros['fecha_inicio'] ?? null);
        $fechaFin = BigQuery::fecha($filtros['fecha_fin'] ?? null);

        if ($fechaInicio && $fechaFin) {
            $where[] = "u.fechaCreacion >= DATETIME('{$fechaInicio}')
                    AND u.fechaCreacion < DATETIME('{$fechaFin}') + INTERVAL 1 DAY";
        }

        // Dentro de buildWhere()
        if (!empty($filtros['respondieronEncuesta'])) {
            // Solo usuarios que tienen registro en EncuestasTypeformDetalle
            $where[] = "u.userid IN (SELECT userid FROM {$this->tablaEncuestasDetalle})";
        }


        foreach (['marca','genero','estadoCivil','nivelEducativo','profesion','pais','canal'] as $campo)
        {
            if (!empty($filtros[$campo]) && is_scalar($filtros[$campo])) {
                $params["seg_$campo"] = trim((string) $filtros[$campo]);
                $where[] = "u.$campo = @seg_$campo";
            }
        }

        if (!empty($filtros['ciudad']) && is_scalar($filtros['ciudad'])) {
            // mb_strtoupper para que las letras con tilde queden en mayúscula y
            // NORMALIZE + \p{M} las reduzca a A-Z, igual que UPPER(u.ciudad)
            $params['seg_ciudad'] = mb_strtoupper(trim((string) $filtros['ciudad']), 'UTF-8');

            $valorNorm = "
                REGEXP_REPLACE(
                    REGEXP_REPLACE(
                        NORMALIZE(@seg_ciudad, NFD),
                        r'\\p{M}',
                        ''
                    ),
                    r'[^A-Z0-9]',
                    ''
                )
            ";

            $where[] = "
                REGEXP_REPLACE(
                    REGEXP_REPLACE(
                        NORMALIZE(UPPER(u.ciudad), NFD),
                        r'\\p{M}',
                        ''
                    ),
                    r'[^A-Z0-9]',
                    ''
                ) LIKE CONCAT('%', $valorNorm, '%')
            ";
        }

        if (!empty($filtros['edad_min'])) {
            $refDate = $fechaFin
                ? "DATE('{$fechaFin}')"
                : "CURRENT_DATE()";

            $params['seg_edad_min'] = (int) $filtros['edad_min'];

            if (!empty($filtros['edad_max'])) {
                $params['seg_edad_max'] = (int) $filtros['edad_max'];
                $where[] = "
                    DATE_DIFF($refDate, DATE(u.fechaNacimiento), YEAR)
                    BETWEEN @seg_edad_min AND @seg_edad_max
                ";
            } else {
                $where[] = "
                    DATE_DIFF($refDate, DATE(u.fechaNacimiento), YEAR)
                    >= @seg_edad_min
                ";
            }
        }

        return count($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    }

    public function buildAndWhere(array $filtros, array &$params = []): string
    {
        $where = $this->buildWhere($filtros, $params);

        return $where
            ? preg_replace('/^WHERE /', 'AND ', $where)
            : '';
    }
}
