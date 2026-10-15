<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\FakeBigQuery;
use Tests\TestCase;

class RutasProtegidasTest extends TestCase
{
    use FakeBigQuery;
    use RefreshDatabase;

    public function test_datos_de_suscriptores_requieren_sesion(): void
    {
        $this->fakeBigQuery();

        $this->getJson('/suscriptores/data')->assertUnauthorized();
        $this->get('/suscriptores/data')->assertRedirect('/login');
        $this->assertEmpty($this->bigQueryQueries);
    }

    public function test_datos_de_suscriptores_con_sesion(): void
    {
        $this->fakeBigQuery();

        $this->actingAs(User::factory()->create())
            ->getJson('/suscriptores/data?draw=3')
            ->assertOk()
            ->assertJson(['draw' => 3, 'data' => []]);
    }

    public function test_gestion_de_usuarios_solo_para_admin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get('/users')
            ->assertForbidden();
    }
}
