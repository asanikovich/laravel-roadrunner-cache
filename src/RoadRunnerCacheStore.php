<?php

declare(strict_types=1);

namespace ASanikovich\LaravelRoadRunnerCache;

use Illuminate\Contracts\Cache\Store;
use Psr\SimpleCache\InvalidArgumentException;
use Spiral\RoadRunner\KeyValue\StorageInterface;
use Throwable;

class RoadRunnerCacheStore implements Store
{
    private readonly string $prefix;

    public function __construct(private readonly StorageInterface $storage, string $prefix)
    {
        $this->prefix = ! empty($prefix) ? $prefix.':' : '';
    }

    /**
     * @param  string  $key
     * {@inheritdoc}
     */
    public function get($key)
    {
        try {
            return $this->storage->get($this->prefix.$key);
        } catch (InvalidArgumentException|Throwable) {
            return null;
        }
    }

    /**
     * @param  string[]  $keys
     * @return array<string, mixed>
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function many(array $keys): array
    {
        $keys = array_values($keys);
        $results = [];

        $keysPrefixes = array_map(fn (string $key) => $this->prefix.$key, $keys);

        $index = 0;
        foreach ($this->storage->getMultiple($keysPrefixes) as $value) {
            $results[$keys[$index++]] = $value;
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function put($key, $value, $seconds): bool
    {
        return $this->storage->set($this->prefix.$key, $value, $seconds);
    }

    /**
     * @param  array<string, mixed>  $values
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function putMany(array $values, $seconds): bool
    {
        $keys = array_keys($values);
        $keys = array_map(fn (string $key) => $this->prefix.$key, $keys);
        $values = array_combine($keys, $values);

        return $this->storage->setMultiple($values, $seconds);
    }

    /**
     * @param  int  $value
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function increment($key, $value = 1): int
    {
        $value = (int) $value;

        $record = $this->get($key);

        if ($record === null) {
            $this->storage->set($this->prefix.$key, $value);

            return $value;
        }

        $value = (int) ($record + $value);
        $this->storage->set($this->prefix.$key, $value);

        return $value;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function decrement($key, $value = 1): int
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function forever($key, $value): bool
    {
        return $this->storage->set($this->prefix.$key, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function forget($key): bool
    {
        return $this->storage->delete($this->prefix.$key);
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): bool
    {
        return $this->storage->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
