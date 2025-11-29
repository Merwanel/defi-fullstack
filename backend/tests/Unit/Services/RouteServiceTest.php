<?php

use PHPUnit\Framework\TestCase;
use App\Services\RouteService;
use App\Services\DataLoader;
use App\Services\PathFinder;

class RouteServiceTest extends TestCase
{
    public function testFindRouteSuccess()
    {
        $dataLoader = $this->createMock(DataLoader::class);
        $pathFinder = $this->createMock(PathFinder::class);

        $dataLoader->method('load')->willReturn([
            'distances' => []
        ]);

        $pathFinder->method('findShortestPath')->willReturn([
            'totalDistance' => 15.0,
            'path' => [1, 2, 3]
        ]);

        $service = new RouteService($dataLoader, $pathFinder);
        $route = $service->findRoute(1, 3, 'ANA-123');

        $this->assertNotNull($route);
        $this->assertEquals(1, $route['fromStationId']);
        $this->assertEquals(3, $route['toStationId']);
        $this->assertEquals(15.0, $route['distanceKm']);
    }

    public function testFindRouteFailure()
    {
        $dataLoader = $this->createMock(DataLoader::class);
        $pathFinder = $this->createMock(PathFinder::class);

        $dataLoader->method('load')->willReturn([
            'distances' => []
        ]);

        $pathFinder->method('findShortestPath')->willReturn(null);

        $service = new RouteService($dataLoader, $pathFinder);
        $route = $service->findRoute(1, 3, 'ANA-123');

        $this->assertNull($route);
    }
}
