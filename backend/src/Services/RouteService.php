<?php

namespace App\Services;

class RouteService
{
    public function __construct(
        private DataLoader $dataLoader,
        private PathFinder $pathFinder,
        private \App\Repositories\StatsRepository $statsRepository
    ) {}

    public function findRoute(int $fromStationId, int $toStationId, string $analyticCode): ?array
    {
        $data = $this->dataLoader->load();
        $result = $this->pathFinder->findShortestPath($fromStationId, $toStationId, $data['distances']);
        if (!$result) {
            return null;
        }

        $routeId = 'route-' . uniqid();
        $this->statsRepository->save(
            $routeId,
            (string)$fromStationId,
            (string)$toStationId,
            $analyticCode,
            $result['totalDistance']
        );

        return [
            'id' => $routeId,
            'fromStationId' => $fromStationId,
            'toStationId' => $toStationId,
            'analyticCode' => $analyticCode,
            'distanceKm' => $result['totalDistance'],
            'path' => $result['path'],
            'createdAt' => date('c')
        ];
    }
}
