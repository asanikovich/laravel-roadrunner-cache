<?php

namespace ASanikovich\LaravelRoadRunnerCache\Tests;

use ASanikovich\LaravelRoadRunnerCache\LaravelRoadRunnerCacheProvider;
use Generator;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\TestCase;

class RoadRunnerServerTest extends TestCase
{
    private Guzzle $httpClient;

    public function setUp(): void
    {
        $this->httpClient = new Guzzle(['base_uri' => 'http://127.0.0.1:8080']);

        parent::setUp();
    }

    public function testServerStart(): void
    {
        $this->checkServerHttp();
    }

    /**
     * @dataProvider cacheProvider
     */
    public function testCache(string $driver, string $prefix): void
    {
        $repository = Cache::driver($driver);

        $this->assertTrue($repository->clear());

        $this->assertNull($repository->get('k'));
        $this->assertEquals(['k1' => null, 'k2' => null], $repository->getMultiple(['k1', 'k2']));
        $this->assertTrue($repository->add('k1', 'v', 3600));
        $this->assertEquals(['k1' => 'v', 'k2' => null], $repository->getMultiple(['k1', 'k2']));

        /** @phpstan-ignore-next-line  */
        $this->assertTrue($repository->put(['k3' => 'v3', 'k4' => 'v4'], null));
        $this->assertEquals(['k3' => 'v3', 'k4' => 'v4'], $repository->getMultiple(['k3', 'k4']));

        $this->assertTrue($repository->setMultiple(['k3' => 'v31', 'k4' => 'v41'], 2));
        /** @phpstan-ignore-next-line  */
        $this->assertEquals(['k3' => 'v31', 'k4' => 'v41'], $repository->get(['k3', 'k4']));
        sleep(3);
        $this->assertEquals(['k3' => 0, 'k4' => 0], $repository->getMultiple(['k3', 'k4'], 0));

        $this->assertTrue($repository->add('k5', 'v5', 3600));
        $this->assertEquals('v5', $repository->get('k5'));
        $this->assertTrue($repository->delete('k5'));
        $this->assertEquals(null, $repository->get('k5'));
        $this->assertEquals('def', $repository->get('k5', 'def'));

        $this->assertEquals(1, $repository->increment('inc'));
        $this->assertEquals(1, $repository->get('inc'));
        $this->assertEquals(3, $repository->increment('inc', 2));
        $this->assertEquals(4, $repository->increment('inc'));
        $this->assertEquals(4, $repository->get('inc'));
        $this->assertEquals(3, $repository->decrement('inc'));
        $this->assertEquals(1, $repository->decrement('inc', 2));

        /** @phpstan-ignore-next-line  */
        $this->assertNull($repository->get((object) []));

        $this->assertEquals($prefix, $repository->getStore()->getPrefix());

        $this->assertTrue($repository->clear());
    }

    public static function cacheProvider(): Generator
    {
        yield ['memory', 'laravel_cache_:'];
        yield ['boltdb', 'custom:'];
    }

    protected function checkServerHttp(): void
    {
        $response = $this->httpClient->send(new Request('GET', '/'));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Laravel', $body = (string) $response->getBody());
        $this->assertStringContainsString('https://laravel.com/', $body);
    }

    protected function getPackageProviders($app): array
    {
        $app['config']['cache.stores.memory'] = [
            'driver' => 'roadrunner',
            'connection' => 'memory',
        ];

        $app['config']['cache.stores.boltdb'] = [
            'driver' => 'roadrunner',
            'connection' => 'boltdb',
            'prefix' => 'custom',
        ];

        $app['config']['roadrunner'] = [
            'rpc' => [
                'host' => env('ROADRUNNER_RPC_HOST', '127.0.0.1'),
                'port' => env('ROADRUNNER_RPC_PORT', 6001),
            ],
        ];

        return [
            LaravelRoadRunnerCacheProvider::class,
        ];
    }
}
