<?php

namespace App\Repositories;

use App\Models\Distance;
use PDO;

class DistanceRepository
{
    public function __construct(private PDO $pdo) {}

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, line_name, parent_id, child_id, distance FROM distances');
        $distances = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $distances[] = new Distance(
                $row['id'],
                $row['line_name'],
                $row['parent_id'],
                $row['child_id'],
                (float) $row['distance']
            );
        }

        return $distances;
    }
}
