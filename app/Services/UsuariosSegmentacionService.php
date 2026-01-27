<?php

namespace App\Services;

use Google\Cloud\BigQuery\BigQueryClient;

class UsuariosSegmentacionService
{
    protected string $tablaUsuarios;
    protected string $tablaCompras;
    protected string $tablaEncuestas;
    protected string $tablaEncuestasDetalle;
    protected string $catalogoCiudades;
    protected string $vtaUsuariosNormalizados;
    protected string $vtaCiudadesNormalizadas;
    protected string $tablaProfesiones;
    protected string $tablaNivEducativo;
    protected string $tablaPaises;

    public function buildWhere(array $filtros): string
    {
        $where = [];

        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $where[] = "u.fechaCreacion >= DATETIME('{$filtros['fecha_inicio']}')
                    AND u.fechaCreacion < DATETIME('{$filtros['fecha_fin']}') + INTERVAL 1 DAY";
        }

        // Dentro de buildWhere()
        if (!empty($filtros['respondieronEncuesta'])) {
            // Solo usuarios que tienen registro en EncuestasTypeformDetalle
            $where[] = "u.userid IN (SELECT userid FROM {$this->tablaEncuestasDetalle})";
        }


        foreach (['marca','genero','estadoCivil','nivelEducativo','profesion','pais','canal'] as $campo)
        {
            if (!empty($filtros[$campo])) {
                $valor = $this->esc($filtros[$campo]);
                $where[] = "u.$campo = '$valor'";
            }
        }

        if (!empty($filtros['ciudad'])) {
            $valor = strtoupper(trim($filtros['ciudad']));

            $valorNorm = "
                REGEXP_REPLACE(
                    REGEXP_REPLACE(
                        NORMALIZE('$valor', NFD),
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
            $refDate = !empty($filtros['fecha_fin'])
                ? "DATE('{$filtros['fecha_fin']}')"
                : "CURRENT_DATE()";

            if (!empty($filtros['edad_max'])) {
                $where[] = "
                    DATE_DIFF($refDate, DATE(u.fechaNacimiento), YEAR)
                    BETWEEN {$filtros['edad_min']} AND {$filtros['edad_max']}
                ";
            } else {
                $where[] = "
                    DATE_DIFF($refDate, DATE(u.fechaNacimiento), YEAR)
                    >= {$filtros['edad_min']}
                ";
            }
        }

        return count($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    }

    public function buildAndWhere(array $filtros): string
    {
        $where = $this->buildWhere($filtros);

        return $where
            ? str_replace('WHERE', 'AND', $where)
            : '';
    }

    private function esc($value): string
    {
        return addslashes(trim($value));
    }
}