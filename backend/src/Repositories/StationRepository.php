<?php

namespace App\Repositories;

use App\Models\Station;
use PDO;

class StationRepository
{
    public function __construct(private PDO $pdo) {}

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, short_name, long_name FROM stations');
        $stations = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $stations[] = new Station($row['id'], $row['short_name'], $row['long_name']);
        }

        return $stations;
    }
}
