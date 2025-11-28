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
     * Load all stations and distances into memory
     * @return array{stations: array, distances: array}
     */
    public function load(): array
    {
        $stations = [];
        foreach ($this->stationRepository->findAll() as $station) {
            $stations[$station->id] = [$station->shortName, $station->longName];
        }

        $distances = $this->distanceRepository->findAll();

        return [
            'stations' => $stations,
            'distances' => $distances
        ];
    }
}
