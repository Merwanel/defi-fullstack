<?php

namespace App\Repositories;

use PDO;

class StatsRepository
{
    public function __construct(private PDO $pdo) {}

    public function save(string $id, string $fromStationId, string $toStationId, string $analyticCode, float $distanceKm): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO stats (id, date, type, distance)
            VALUES (:id, NOW(), :type, :distance)
        ');
        
        $stmt->execute([
            'id' => $id,
            'type' => $analyticCode,
            'distance' => $distanceKm
        ]);
    }

    public function getAggregatedDistances(?string $from, ?string $to, ?string $groupBy): array
    {
        $sql = "SELECT type as analyticCode, SUM(distance) as totalDistanceKm";
        
        // Add grouping fields based on groupBy
        if ($groupBy === 'day') {
            $sql .= ", DATE_FORMAT(date, '%Y-%m-%d') as periodStart, DATE_FORMAT(date, '%Y-%m-%d') as periodEnd";
        } elseif ($groupBy === 'month') {
            $sql .= ", DATE_FORMAT(date, '%Y-%m-01') as periodStart, LAST_DAY(date) as periodEnd";
        } elseif ($groupBy === 'year') {
            $sql .= ", DATE_FORMAT(date, '%Y-01-01') as periodStart, DATE_FORMAT(date, '%Y-12-31') as periodEnd";
        } else {
             $sql .= ", MIN(date) as periodStart, MAX(date) as periodEnd";
        }

        $sql .= " FROM stats WHERE 1=1";
        $params = [];

        if ($from) {
            $sql .= " AND date >= :from";
            $params['from'] = $from;
        }
        if ($to) {
            $sql .= " AND date <= :to";
            $params['to'] = $to . ' 23:59:59'; // Include the whole end day
        }

        $sql .= " GROUP BY type";
        
        if ($groupBy === 'day') {
            $sql .= ", DATE_FORMAT(date, '%Y-%m-%d')";
        } elseif ($groupBy === 'month') {
            $sql .= ", DATE_FORMAT(date, '%Y-%m')";
        } elseif ($groupBy === 'year') {
            $sql .= ", DATE_FORMAT(date, '%Y')";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
