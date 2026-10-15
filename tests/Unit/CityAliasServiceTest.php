<?php

namespace Tests\Unit;

use App\Repositories\CityAliasRepository;
use App\Services\CityAliasService;
use Mockery;
use Tests\TestCase;

class CityAliasServiceTest extends TestCase
{
    public function test_normaliza_igual_que_alias_norm(): void
    {
        $service = new CityAliasService(Mockery::mock(CityAliasRepository::class));

        $this->assertSame('SANPEDROSULA', $service->normalize('  San Pedro Sula '));
        $this->assertSame('SANPEDROSULA', $service->normalize('san-pedro sula.'));
        $this->assertSame('BOGOTA', $service->normalize('Bogotá'));
        $this->assertSame('PENABLANCA', $service->normalize('Peña Blanca'));
    }

    public function test_editar_sin_cambiar_el_alias_no_es_duplicado(): void
    {
        $repo = Mockery::mock(CityAliasRepository::class);
        $repo->shouldNotReceive('aliasExists');

        $service = new CityAliasService($repo);

        $this->assertFalse($service->existsDuplicate('Bogotá', 'BOGOTA'));
    }

    public function test_editar_a_un_alias_existente_lanza_error(): void
    {
        $repo = Mockery::mock(CityAliasRepository::class);
        $repo->shouldReceive('aliasExists')->with('TEGUCIGALPA')->andReturnTrue();
        $repo->shouldNotReceive('updateAlias');

        $service = new CityAliasService($repo);

        $this->expectExceptionMessage('Alias duplicado');
        $service->update('Comayagüela', [
            'ciudad_alias' => 'Tegucigalpa',
            'ciudad_canonica' => 'Tegucigalpa',
            'estado' => 1,
        ]);
    }

    public function test_el_alias_se_guarda_como_se_escribio(): void
    {
        $repo = Mockery::mock(CityAliasRepository::class);
        $repo->shouldReceive('aliasExists')->andReturnFalse();
        $repo->shouldReceive('insert')->once()->with(Mockery::on(
            fn ($data) => $data['ciudad_alias'] === 'San Pedro Sula' && $data['estado'] === true
        ))->andReturnTrue();

        (new CityAliasService($repo))->create([
            'ciudad_alias' => ' San Pedro Sula ',
            'ciudad_canonica' => 'San Pedro Sula',
            'estado' => '1',
        ]);
    }
}
