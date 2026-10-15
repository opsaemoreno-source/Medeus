<?php

namespace Tests\Concerns;

use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\BigQuery\QueryJobConfiguration;
use Google\Cloud\BigQuery\ValueMapper;
use Mockery;

/**
 * Reemplaza el cliente de BigQuery por uno falso que registra cada consulta
 * (SQL y parámetros) y devuelve las filas indicadas, sin salir a la red.
 */
trait FakeBigQuery
{
    /** @var array<int, array{sql: string, params: array<string, mixed>}> */
    protected array $bigQueryQueries = [];

    protected function fakeBigQuery(array $rows = []): void
    {
        $this->bigQueryQueries = [];

        $client = Mockery::mock(BigQueryClient::class);

        $client->shouldReceive('query')->andReturnUsing(
            fn (string $sql) => (new QueryJobConfiguration(new ValueMapper(false), 'test-project', [], null))->query($sql)
        );

        $client->shouldReceive('runQuery')->andReturnUsing(function (QueryJobConfiguration $job) use ($rows) {
            $query = $job->toArray()['configuration']['query'];

            $params = [];
            foreach ($query['queryParameters'] ?? [] as $param) {
                $value = $param['parameterValue'];
                $params[$param['name']] = isset($value['arrayValues'])
                    ? array_column($value['arrayValues'], 'value')
                    : $value['value'];
            }

            $this->bigQueryQueries[] = ['sql' => $query['query'], 'params' => $params];

            return new class($rows) implements \IteratorAggregate {
                public function __construct(private array $rows) {}

                public function rows(): \ArrayIterator
                {
                    return new \ArrayIterator($this->rows);
                }

                public function getIterator(): \ArrayIterator
                {
                    return $this->rows();
                }
            };
        });

        $this->app->instance(BigQueryClient::class, $client);
    }

    protected function lastBigQuery(): array
    {
        $this->assertNotEmpty($this->bigQueryQueries, 'No se ejecutó ninguna consulta de BigQuery.');

        return end($this->bigQueryQueries);
    }
}
