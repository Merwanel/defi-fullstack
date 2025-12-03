<?php

namespace App\Services;

use App\Repositories\StationRepository;
use App\Repositories\DistanceRepository;

class DataLoader
{
    private const CACHE_KEY = 'graph_data';
    private const CACHE_TTL = null;

    public function __construct(
        private StationRepository $stationRepository,
        private DistanceRepository $distanceRepository,
        private CacheService $cache
    ) {
    }

    private ?array $memoryCache = null;

    /**
     * @return array{stations: array<int, array{0: string, 1: string}>, distances: array<int, list<array{0: int, 1: float}>>}
     */
    public function load(): array
    {
        if ($this->memoryCache !== null) {
            return $this->memoryCache;
        }

        $cached = $this->cache->get(self::CACHE_KEY);
        if ($cached !== null && is_array($cached)) {
            $this->memoryCache = $cached;
            return $this->memoryCache;
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

        $this->memoryCache = [
            'stations' => $stations,
            'distances' => $distances
        ];

        $this->cache->set(self::CACHE_KEY, $this->memoryCache, self::CACHE_TTL);

        return $this->memoryCache;
    }
}
