<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class DistancesSeeder extends AbstractSeed
{
    public function run(): void
    {
        $stationsData = json_decode(file_get_contents(__DIR__ . '/../../stations.json'), true);
        $distancesData = json_decode(file_get_contents(__DIR__ . '/../../distances.json'), true);

        $stationMap = [];
        foreach ($stationsData as $station) {
            $stationMap[$station['shortName']] = $station['id'];
        }

        $rows = [];
        foreach ($distancesData as $line) {
            $lineName = $line['name'];
            foreach ($line['distances'] as $distance) {
                $rows[] = [
                    'line_name' => $lineName,
                    'parent_id' => $stationMap[$distance['parent']],
                    'child_id' => $stationMap[$distance['child']],
                    'distance' => $distance['distance'],
                ];
            }
        }

        $this->table('distances')->insert($rows)->save();
    }
}
