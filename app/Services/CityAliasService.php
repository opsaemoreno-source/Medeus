<?php

namespace App\Services;

use App\Repositories\CityAliasRepository;

class CityAliasService
{
    public function __construct(
        private CityAliasRepository $repo
    ) {}

    public function normalize(string $value): string
    {
        /*$value = trim($value);
        $value = mb_strtoupper($value, 'UTF-8');

        $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
        $value = preg_replace('/[^A-Z0-9\s]/', '', $value);
        $value = preg_replace('/\s+/', ' ', $value);*/

        return $value;
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

        $data['ciudad_alias'] = $this->normalize($data['ciudad_alias']);

        $data['estado'] = (bool) $data['estado'];

        return $this->repo->insert($data);
    }

    public function update(string $originalAlias, array $data)
    {
        /*if ($this->existsDuplicate($data['ciudad_alias'], $originalAlias)) {
            throw new \Exception("Alias duplicado");
        }*/

        $data['ciudad_alias'] = $this->normalize($data['ciudad_alias']);

        $data['estado'] = (bool) $data['estado'];

        return $this->repo->updateAlias($originalAlias, $data);
    }

}