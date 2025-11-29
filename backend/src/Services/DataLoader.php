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

    /**
     * @return array{stations: array<int, array{0: string, 1: string}>, distances: array<int, array<int, float>>}
     */
    public function load(): array
    {
        $stations = [];
        foreach ($this->stationRepository->findAll() as $station) {
            $stations[$station->id] = [$station->shortName, $station->longName];
        }

        $distances = [];
        foreach ($this->distanceRepository->findAll() as $distance) {
            $parentId = (int) $distance->parentId;
            $childId = (int) $distance->childId;
            if (!isset($distances[$parentId])) {
                $distances[$parentId] = [];
            }
            $distances[$parentId][$childId] = (float) $distance->distance;
        }

        return [
            'stations' => $stations,
            'distances' => $distances
        ];
    }
}
