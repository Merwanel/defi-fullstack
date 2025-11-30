<?php

namespace App\Repositories;

use PDO;

class StatsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function save(string $id, string $fromStationId, string $toStationId, string $analyticCode, float $distanceKm): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO stats (id, date, type, distance)
            VALUES (:id, CURRENT_TIMESTAMP, :type, :distance)
        ');

        $stmt->execute([
            'id' => $id,
            'type' => $analyticCode,
            'distance' => $distanceKm
        ]);
    }

    public function getAggregatedDistances(?string $from, ?string $to, ?string $groupBy): array
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $isSqlite = $driver === 'sqlite';

        $sql = "SELECT type as analyticCode, SUM(distance) as totalDistanceKm";

        // Add grouping fields based on groupBy (database-specific)
        if ($groupBy === 'day') {
            if ($isSqlite) {
                $sql .= ", DATE(date) as periodStart, DATE(date) as periodEnd";
            } else {
                $sql .= ", DATE_FORMAT(date, '%Y-%m-%d') as periodStart, DATE_FORMAT(date, '%Y-%m-%d') as periodEnd";
            }
        } elseif ($groupBy === 'month') {
            if ($isSqlite) {
                $sql .= ", DATE(date, 'start of month') as periodStart, DATE(date, 'start of month', '+1 month', '-1 day') as periodEnd";
            } else {
                $sql .= ", DATE_FORMAT(date, '%Y-%m-01') as periodStart, LAST_DAY(date) as periodEnd";
            }
        } elseif ($groupBy === 'year') {
            if ($isSqlite) {
                $sql .= ", DATE(date, 'start of year') as periodStart, DATE(date, 'start of year', '+1 year', '-1 day') as periodEnd";
            } else {
                $sql .= ", DATE_FORMAT(date, '%Y-01-01') as periodStart, DATE_FORMAT(date, '%Y-12-31') as periodEnd";
            }
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
            if ($isSqlite) {
                $sql .= ", DATE(date)";
            } else {
                $sql .= ", DATE_FORMAT(date, '%Y-%m-%d')";
            }
        } elseif ($groupBy === 'month') {
            if ($isSqlite) {
                $sql .= ", strftime('%Y-%m', date)";
            } else {
                $sql .= ", DATE_FORMAT(date, '%Y-%m')";
            }
        } elseif ($groupBy === 'year') {
            if ($isSqlite) {
                $sql .= ", strftime('%Y', date)";
            } else {
                $sql .= ", DATE_FORMAT(date, '%Y')";
            }
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
