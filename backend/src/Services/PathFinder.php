<?php

namespace App\Services;


use SplPriorityQueue;

class PathFinder
{
    /** 
     * @return array{path: int[], totalDistance: float} | null
     */
    public function findShortestPath(int $startId, int $endId, array $distances): ?array
    {
        // Dijkstra's algorithm
        $dist = [$startId => 0];
        $prev = [];
        $visited = [];
        $queue = new SplPriorityQueue();
        $queue->insert($startId, 0);

        while (!$queue->isEmpty()) {
            $current = $queue->extract();

            if (isset($visited[$current])) {
                continue;
            }

            $visited[$current] = true;

            if ($current === $endId) {
                break;
            }

            if (!isset($distances[$current])) {
                continue;
            }

            foreach ($distances[$current] as $_ => $value) {
                $neighbor = (int)$value[0] ;
                $cost = (float)$value[1] ;

                $distanceSoFar = ($dist[$current] ?? PHP_INT_MAX) + $cost;

                if (!isset($dist[$neighbor]) || $distanceSoFar < $dist[$neighbor]) {
                    $dist[$neighbor] = $distanceSoFar;
                    $prev[$neighbor] = $current;
                    $queue->insert($neighbor, -$distanceSoFar);
                }
            }
        }
        if (!isset($dist[$endId])) {
            return null;
        }

        $path = [];
        $current = $endId;
        while (isset($prev[$current])) {
            array_unshift($path, $current);
            $current = $prev[$current];
        }
        array_unshift($path, $startId);

        return [
            'path' => $path,
            'totalDistance' => $dist[$endId]
        ];
    }
}
