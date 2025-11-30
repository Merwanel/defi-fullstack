<?php

namespace App\Services;

use App\Repositories\StationRepository;
use App\Repositories\DistanceRepository;

class DataLoader
{
    public function __construct(
        private StationRepository $stationRepository,
        private DistanceRepository $distanceRepository
    ) {}

    private ?array $cache = null;

    /**
     * @return array{stations: array<int, array{0: string, 1: string}>, distances: array<int, list<array{0: int, 1: float}>>}
     */
    public function load(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $stations = [];
        foreach ($this->stationRepository->findAll() as $station) {
            $stations[$station->id] = [$station->shortName, $station->longName];
        }

        $distances = [];
        foreach ($this->distanceRepository->findAll() as $edge) {
            $parentId = (int) $edge->parentId;
            $childId = (int) $edge->childId;
            if (!isset($distances[$parentId])) {
                $distances[$parentId] = [];
            }
            $distances[$parentId][] = [$childId, (float) $edge->distance];
        }
        $this->cache = [
            'stations' => $stations,
            'distances' => $distances
        ];
        return $this->cache;
    }
}
