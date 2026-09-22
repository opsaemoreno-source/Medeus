<?php

namespace App\Services\Evolok;

class EvolokUsuariosMapper
{
    public const COLUMNS = 'GUID,State,Created,LastModified,LastLoginDate,Brand,Channel,Attributes,ProfileBrand,ProfileChannel,active_subscription,address1_city,address1_country,address1_line1,dni,dob,el_heraldo,email_address,estadocivil,first_name,gender,la_prensa,last_name,mobile_number,newsletter_amiga,newsletter_deportes,newsletter_medica,newsletter_saber,nivel_educativo,profesion,purchased,tandc,visitor_country';

    protected const ATTRIBUTE_MAP = [
        'dni'                   => 'dni',
        'dob'                   => 'fechaNacimiento',
        'email_address'         => 'correo',
        'estadocivil'           => 'estadoCivil',
        'first_name'            => 'nombre',
        'gender'                => 'genero',
        'address1_city'         => 'ciudad',
        'last_name'             => 'apellido',
        'mobile_number'         => 'telefono',
        'nivel_educativo'       => 'nivelEducativo',
        'address1_country'      => 'paisPerfil',
        'profesion'             => 'profesion',
        'purchased'             => 'compra',
        'visitor_country'       => 'pais',
        'la_prensa'             => 'la_prensa',
        'el_heraldo'            => 'el_heraldo',
        'active_subscription'   => 'suscripcionActiva',
        'newsletter_amiga'      => 'newsletter_amiga',
        'newsletter_deportes'   => 'newsletter_deportes',
        'newsletter_saber'      => 'newsletter_saber',
        'newsletter_medica'     => 'newsletter_medicina',
        'ProfileChannel'        => 'canal',
        'State'                 => 'estado',
        'LastLoginDate'         => 'ultimaSesion',
        'mauticId'              => 'mauticId',
        'address1_line1'        => 'direccion',
        'tandc'                 => 'tandc',
    ];

    public static function map(array $apiData): array
    {
        $resultado = [];

        foreach ($apiData as $item) {
            $fila = [
                'userid'                => $item['GUID'] ?? null,
                'fechaCreacion'         => EvolokDates::transform($item['Created'] ?? null),
                'fechaModificacion'     => EvolokDates::transform($item['LastModified'] ?? null),
                'marca'                 => $item['Brand'] ?? null,
                'dni'                   => null,
                'fechaNacimiento'       => '0001-01-01',
                'ciudad'                => null,
                'correo'                => null,
                'estadoCivil'           => null,
                'nombre'                => null,
                'apellido'              => null,
                'genero'                => null,
                'paisPerfil'            => null,
                'nivelEducativo'        => null,
                'telefono'              => null,
                'profesion'             => null,
                'compra'                => null,
                'la_prensa'             => null,
                'el_heraldo'            => null,
                'pais'                  => null,
                'suscripcionActiva'     => null,
                'newsletter_amiga'      => null,
                'newsletter_deportes'   => null,
                'newsletter_saber'      => null,
                'newsletter_medicina'   => null,
                'mauticId'              => null,
                'direccion'             => null,
                'tandc'                 => null,
                'canal'                 => $item['ProfileChannel'] ?? null,
                'estado'                => $item['State'] ?? null,
                'ultimaSesion'          => (is_array($item['LastLoginDate'] ?? null) && !empty($item['LastLoginDate']))
                    ? EvolokDates::transform(end($item['LastLoginDate']))
                    : '0001-01-01 00:00:00',
            ];

            if (!empty($item['Attributes'])) {
                foreach ($item['Attributes'] as $attr) {
                    $name = $attr['name'] ?? null;
                    $value = $attr['value'] ?? null;

                    if ($name && array_key_exists($name, self::ATTRIBUTE_MAP)) {
                        $columna = self::ATTRIBUTE_MAP[$name];
                        $fila[$columna] = $name === 'dob'
                            ? EvolokDates::transform($value, 2)
                            : $value;
                    }
                }
            }

            $resultado[] = $fila;
        }

        return $resultado;
    }
}
