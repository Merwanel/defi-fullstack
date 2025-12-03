<?php

use PHPUnit\Framework\TestCase;
use App\Services\CacheService;

class FailingRedisDouble
{
    public function __call($name, $arguments)
    {
        var_dump($name, $arguments);
        throw new \Exception('Redis connection failed');
    }
}

class CacheServiceTest extends TestCase
{
    private CacheService $cache;

    protected function setUp(): void
    {
        $this->cache = new CacheService('localhost', 6379);
    }

    public function testEnsureConnectionWhenRedisNotAvailable()
    {
        $this->assertFalse($this->cache->ensureConnection());
    }

    public function testGetWhenRedisNotAvailable()
    {
        $this->assertNull($this->cache->get('test-key'));
    }

    public function testSetWhenRedisNotAvailable()
    {
        $this->assertFalse($this->cache->set('test-key', 'test-value'));
    }

    public function testTryConnectWhenRedisNotAvailable()
    {
        $this->assertFalse($this->cache->ensureConnection());
    }
}
