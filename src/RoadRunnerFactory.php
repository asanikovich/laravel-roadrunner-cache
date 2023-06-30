<?php

declare(strict_types=1);

namespace ASanikovich\LaravelRoadRunnerCache;

use Spiral\Goridge\RPC\RPC;
use Spiral\Goridge\RPC\RPCInterface;
use Spiral\RoadRunner\KeyValue\Factory;
use Spiral\RoadRunner\KeyValue\Serializer\DefaultSerializer;
use Spiral\RoadRunner\KeyValue\Serializer\IgbinarySerializer;
use Spiral\RoadRunner\KeyValue\Serializer\SodiumSerializer;

class RoadRunnerFactory
{
    public static function createRPC(): RPCInterface
    {
        /** @phpstan-ignore-next-line  */
        return RPC::create(sprintf('tcp://%s:%s', config('roadrunner.rpc.host'), config('roadrunner.rpc.port')));
    }

    /**
     * @param  array<string, non-empty-string>  $config
     */
    public static function createLaravelCacheStore(RPCInterface $rpc, array $config): RoadRunnerCacheStore
    {
        if (($config['serializer'] ?? null) === 'igbinary') {
            $serializer = new IgbinarySerializer();
        } else {
            $serializer = new DefaultSerializer();
        }

        $encryptionKey = $config['encryption_key'] ?? null;
        if ($encryptionKey !== null) {
            $serializer = new SodiumSerializer($serializer, $encryptionKey);
        }

        $storage = (new Factory($rpc, $serializer))->select($config['connection']);

        /** @phpstan-ignore-next-line  */
        return new RoadRunnerCacheStore($storage, $config['prefix'] ?? config('cache.prefix', ''));
    }
}
