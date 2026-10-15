<?php

namespace App\Services;

use App\Repositories\CityAliasRepository;

class CityAliasService
{
    public function __construct(
        private CityAliasRepository $repo
    ) {}

    /**
     * Forma normalizada de un alias, equivalente a la columna alias_norm de
     * vta_ciudadesNormalizadas: mayúsculas, sin acentos y solo A-Z/0-9.
     * Se usa para comparar duplicados; el alias se guarda tal como se escribió.
     */
    public function normalize(string $value): string
    {
        $value = mb_strtoupper(trim($value), 'UTF-8');

        if (class_exists(\Normalizer::class)) {
            $value = preg_replace('/\p{Mn}/u', '', \Normalizer::normalize($value, \Normalizer::FORM_D));
        } else {
            $value = strtr($value, [
                'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Ã' => 'A', 'Å' => 'A',
                'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
                'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
                'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Ö' => 'O', 'Õ' => 'O',
                'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
                'Ñ' => 'N', 'Ç' => 'C', 'Ý' => 'Y',
            ]);
        }

        return preg_replace('/[^A-Z0-9]/', '', $value);
    }

    public function existsDuplicate(string $alias, ?string $original = null): bool
    {
        $normalized = $this->normalize($alias);

        if ($original && $this->normalize($original) === $normalized)
        {
            return false;
        }

        return $this->repo->aliasExists($normalized);
    }

    public function create(array $data)
    {
        if ($this->existsDuplicate($data['ciudad_alias'])) {
            throw new \Exception("Alias duplicado");
        }

        $data['ciudad_alias'] = trim($data['ciudad_alias']);

        $data['estado'] = (bool) $data['estado'];

        return $this->repo->insert($data);
    }

    public function update(string $originalAlias, array $data)
    {
        if ($this->existsDuplicate($data['ciudad_alias'], $originalAlias)) {
            throw new \Exception("Alias duplicado");
        }

        $data['ciudad_alias'] = trim($data['ciudad_alias']);

        $data['estado'] = (bool) $data['estado'];

        return $this->repo->updateAlias($originalAlias, $data);
    }

}
