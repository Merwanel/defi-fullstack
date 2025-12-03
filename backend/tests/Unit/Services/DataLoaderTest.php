<?php

use PHPUnit\Framework\TestCase;
use App\Services\DataLoader;
use App\Services\CacheService;
use App\Repositories\StationRepository;
use App\Repositories\DistanceRepository;
use App\Models\Station;
use App\Models\Distance;

class DataLoaderTest extends TestCase
{
    public function testLoadSuccess()
    {
        $stationRepo = $this->createMock(StationRepository::class);
        $distanceRepo = $this->createMock(DistanceRepository::class);
        $mockCache = $this->createMock(CacheService::class);

        $mockCache->method('get')->willReturn(null);
        $mockCache->method('set')->willReturn(true);

        $stationRepo->method('findAll')->willReturn([
            new Station(1, 'ST', 'Station Name')
        ]);

        $distanceRepo->method('findAll')->willReturn([
            new Distance(1, 'line', 1, 2, 10.5)
        ]);

        $loader = new DataLoader($stationRepo, $distanceRepo, $mockCache);
        $data = $loader->load();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('stations', $data);
        $this->assertArrayHasKey('distances', $data);
        $this->assertEquals(['ST', 'Station Name'], $data['stations'][1]);
        $this->assertEquals(10.5, $data['distances'][1][0][1]);
    }
}
