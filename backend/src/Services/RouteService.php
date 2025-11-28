<?php

namespace App\Services;

class RouteService
{
    public function __construct(
        private array $stationsDict,
        private array $distances,
        private PathFinder $pathFinder
    ) {}

    public function findRoute(int $fromStationId, int $toStationId, string $analyticCode): ?array
    {
        #TODO replace placeholder
        $result = [];
        $result['path'] = [1,2,3,4];
        $result['totalDistance'] = 36;
        if (!$result) {
            return null;
        }

        // Enrich path with station names
        $enrichedPath = [];
        foreach ($result['path'] as $stationId) {
            [$shortName, $longName] = $this->stationsDict[$stationId];
            $enrichedPath[] = [
                'id' => $stationId,
                'shortName' => $shortName,
                'longName' => $longName
            ];
        }

        return [
            'id' => 'route-' . uniqid(),
            'fromStationId' => $fromStationId,
            'toStationId' => $toStationId,
            'analyticCode' => $analyticCode,
            'distanceKm' => $result['totalDistance'],
            'path' => $enrichedPath,
            'createdAt' => date('c')
        ];
    }
}
