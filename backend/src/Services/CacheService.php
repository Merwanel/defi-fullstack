<?php

namespace App\Services;

use Predis\Client;
use Throwable;

/**
 * Cache service using Redis, that fails silently if Redis is not available
 */
class CacheService
{
    private ?Client $redis = null;
    private bool $available = false;
    private string $host;
    private int $port;

    public function __construct(?string $redisHost = null, ?int $redisPort = null)
    {
        $this->host = $redisHost ?? $_ENV['REDIS_HOST'] ?? 'redis-cache';
        $this->port = $redisPort ?? (int) ($_ENV['REDIS_PORT'] ?? 6379);

        $this->tryConnect();
    }

    private function tryConnect(): bool
    {
        try {
            $this->redis = new Client([
                'scheme' => 'tcp',
                'host' => $this->host,
                'port' => $this->port,
                'timeout' => 1.0,
            ]);

            $this->redis->ping();
            $this->available = true;
            return true;
        } catch (Throwable $e) {
            $this->available = false;
            $this->redis = null;
            return false;
        }
    }

    public function get(string $key): mixed
    {
        if (!$this->ensureConnection()) {
            return null;
        }

        try {
            $value = $this->redis->get($key);
            if ($value === null) {
                return null;
            }
            return json_decode($value, true);
        } catch (Throwable $e) {
            $this->available = false;
            return null;
        }
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        if (!$this->ensureConnection()) {
            return false;
        }

        try {
            $serialized = json_encode($value);
            if ($ttl !== null) {
                $this->redis->setex($key, $ttl, $serialized);
            } else {
                $this->redis->set($key, $serialized);
            }
            return true;
        } catch (Throwable $e) {
            $this->available = false;
            return false;
        }
    }


    public function ensureConnection(): bool
    {
        if ($this->available) {
            try {
                $this->redis->ping();
                return true;
            } catch (Throwable $e) {
                $this->available = false;
            }
        }
        return $this->tryConnect();
    }
}
