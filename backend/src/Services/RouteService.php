<?php

namespace App\Services;

class RouteService
{
    public function __construct(
        private array $distances,
        private PathFinder $pathFinder
    ) {}

    public function findRoute(int $fromStationId, int $toStationId, string $analyticCode): ?array
    {
        $result = $this->pathFinder->findShortestPath($fromStationId, $toStationId, $this->distances);
        if (!$result) {
            return null;
        }

        return [
            'id' => 'route-' . uniqid(),
            'fromStationId' => $fromStationId,
            'toStationId' => $toStationId,
            'analyticCode' => $analyticCode,
            'distanceKm' => $result['totalDistance'],
            'path' => $result['path'],
            'createdAt' => date('c')
        ];
    }
}
