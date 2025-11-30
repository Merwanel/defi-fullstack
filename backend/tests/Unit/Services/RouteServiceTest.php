<?php

use PHPUnit\Framework\TestCase;
use App\Services\RouteService;
use App\Services\DataLoader;
use App\Services\PathFinder;
use App\Repositories\StatsRepository;

class RouteServiceTest extends TestCase
{

    public function mockSetUp($shortestPath_result): App\Services\RouteService
    {

        $dataLoader = $this->createMock(DataLoader::class);
        $pathFinder = $this->createMock(PathFinder::class);
        $statsRepository = $this->createMock(StatsRepository::class);

        $dataLoader->method('load')->willReturn([
            'distances' => []
        ]);

        $pathFinder->method('findShortestPath')->willReturn($shortestPath_result);
        $statsRepository->method('save');

        return new RouteService($dataLoader, $pathFinder, $statsRepository);
    }
    public function testFindRouteSuccess()
    {
        $service = $this->mockSetUp([
            'totalDistance' => 15.0,
            'path' => [1, 2, 3]
        ]);
        $route = $service->findRoute(1, 3, 'ANA-123');

        $this->assertNotNull($route);
        $this->assertEquals(1, $route['fromStationId']);
        $this->assertEquals(3, $route['toStationId']);
        $this->assertEquals(15.0, $route['distanceKm']);
    }

    public function testFindRouteFailure()
    {
        $service = $this->mockSetUp(null);
        $route = $service->findRoute(1, 3, 'ANA-123');

        $this->assertNull($route);
    }
}
