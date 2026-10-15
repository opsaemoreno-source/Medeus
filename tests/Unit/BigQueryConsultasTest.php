<?php

namespace Tests\Unit;

use App\Services\BigQueryService;
use App\Services\ComprasService;
use App\Services\EncuestasService;
use App\Services\EstadisticasAvanzadasService;
use App\Services\UsuariosSegmentacionService;
use App\Support\BigQuery;
use InvalidArgumentException;
use Tests\Concerns\FakeBigQuery;
use Tests\TestCase;

class BigQueryConsultasTest extends TestCase
{
    use FakeBigQuery;

    private const INYECCION = "x') OR 1=1 --";

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.bigquery.project_id' => 'proyecto-test',
            'services.bigquery.dataset' => 'dataset_test',
        ]);
    }

    public function test_las_tablas_salen_de_la_configuracion(): void
    {
        $this->assertSame('`proyecto-test.dataset_test.Compras`', BigQuery::table('Compras'));
    }

    public function test_configuracion_faltante_da_un_error_claro(): void
    {
        config(['services.bigquery.project_id' => null]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('php artisan config:clear');
        BigQuery::table('Compras');
    }

    public function test_fecha_acepta_formatos_validos_y_rechaza_el_resto(): void
    {
        $this->assertNull(BigQuery::fecha(null));
        $this->assertNull(BigQuery::fecha(''));
        $this->assertSame('2026-01-31', BigQuery::fecha('2026-01-31'));
        $this->assertSame('2026-01-31 10:00:00', BigQuery::fecha('2026-01-31 10:00:00'));

        $this->expectException(InvalidArgumentException::class);
        BigQuery::fecha("2026-01-31' OR '1'='1");
    }

    public function test_filtros_de_suscriptores_van_como_parametros(): void
    {
        $this->fakeBigQuery();

        app(BigQueryService::class)->obtenerSuscriptoresPaginadosConFiltro(
            '20', '10', self::INYECCION, '2026-01-01', '2026-01-31',
            self::INYECCION, 'HN', null, 'web', 'blank',
            '18', '', 'univ', null, null
        );

        $query = $this->lastBigQuery();

        $this->assertStringNotContainsString(self::INYECCION, $query['sql']);
        $this->assertStringContainsString('`proyecto-test.dataset_test.vta_usuariosEvolok`', $query['sql']);
        $this->assertStringContainsString('LIMIT @length OFFSET @start', $query['sql']);
        $this->assertStringContainsString('TRIM(LOWER(canal)) = LOWER(@canal)', $query['sql']);
        $this->assertStringContainsString("(TRIM(marca) = '' OR marca IS NULL)", $query['sql']);
        $this->assertSame(self::INYECCION, $query['params']['search']);
        $this->assertSame(self::INYECCION, $query['params']['genero']);
        $this->assertSame([18, 200], [$query['params']['edad_min'], $query['params']['edad_max']]);
        $this->assertSame([20, 10], [$query['params']['start'], $query['params']['length']]);
        $this->assertArrayNotHasKey('marca', $query['params']);
    }

    public function test_fecha_invalida_no_llega_a_bigquery(): void
    {
        $this->fakeBigQuery();

        try {
            app(BigQueryService::class)->obtenerEstadisticaPorDia("2026-01-01' --", '2026-01-31');
            $this->fail('Se esperaba InvalidArgumentException');
        } catch (InvalidArgumentException) {
            $this->assertEmpty($this->bigQueryQueries);
        }
    }

    public function test_segmentacion_parametriza_valores(): void
    {
        $params = [];
        $where = (new UsuariosSegmentacionService())->buildAndWhere([
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-01-31',
            'genero' => ' ' . self::INYECCION,
            'ciudad' => ' comayagüela ',
            'edad_min' => '18',
            'edad_max' => '30',
        ], $params);

        $this->assertStringStartsWith('AND ', $where);
        $this->assertStringNotContainsString(self::INYECCION, $where);
        $this->assertStringContainsString('u.genero = @seg_genero', $where);
        $this->assertStringContainsString('BETWEEN @seg_edad_min AND @seg_edad_max', $where);
        $this->assertStringContainsString("DATE('2026-01-31')", $where);
        $this->assertSame(self::INYECCION, $params['seg_genero']);
        $this->assertSame('COMAYAGÜELA', $params['seg_ciudad']);
        $this->assertSame(18, $params['seg_edad_min']);
        $this->assertSame(30, $params['seg_edad_max']);
    }

    public function test_estadisticas_avanzadas_validan_la_columna(): void
    {
        $this->fakeBigQuery();

        $this->expectException(InvalidArgumentException::class);
        app(EstadisticasAvanzadasService::class)->valoresDistintos('marca; DROP TABLE x');
    }

    public function test_filtros_de_compras_van_como_parametros(): void
    {
        $this->fakeBigQuery();

        app(ComprasService::class)->obtenerCompras([
            'estado' => ['ACTIVE', 'CANCELLED'],
            'marca' => [3 => 'la prensa', 5 => ''],
            'producto' => self::INYECCION,
            'search' => self::INYECCION,
            'fechaInicio' => '2026-01-01',
        ]);

        $query = $this->lastBigQuery();

        $this->assertStringNotContainsString(self::INYECCION, $query['sql']);
        $this->assertStringContainsString('c.estado IN UNNEST(@estado)', $query['sql']);
        $this->assertStringContainsString('TRIM(LOWER(c.marca)) IN UNNEST(@marca)', $query['sql']);
        $this->assertStringContainsString("c.fechaInicioSuscripcion >= '2026-01-01 00:00:00'", $query['sql']);
        $this->assertSame(['ACTIVE', 'CANCELLED'], $query['params']['estado']);
        $this->assertSame(['la prensa'], $query['params']['marca']);
        $this->assertSame(self::INYECCION, $query['params']['producto']);
    }

    public function test_estadisticas_de_compras_usan_el_alias_de_la_tabla(): void
    {
        $this->fakeBigQuery();

        app(ComprasService::class)->obtenerEstadisticasComprasActivas(['fechaInicio' => '2026-01-01']);

        $query = $this->lastBigQuery();

        $this->assertStringContainsString('`proyecto-test.dataset_test.vta_Compras` AS c', $query['sql']);
        $this->assertSame('ACTIVE', $query['params']['estado']);
    }

    public function test_kpis_de_encuestas_reconocen_guest_sin_importar_mayusculas(): void
    {
        $this->fakeBigQuery([[
            'usuarios_registrados' => 1,
            'usuarios_no_registrados' => 2,
            'total_usuarios_encuesta' => 3,
            'porcentaje_completacion' => 50,
        ]]);

        $kpis = app(EncuestasService::class)->kpis('2026-01-01', '2026-01-31');

        $sql = $this->lastBigQuery()['sql'];

        $this->assertStringNotContainsString("'Guest'", $sql);
        $this->assertStringContainsString("UPPER(userid) != 'GUEST'", $sql);
        $this->assertStringContainsString("UPPER(userid) = 'GUEST'", $sql);
        $this->assertSame(2, $kpis['usuarios_no_registrados']);
    }
}
