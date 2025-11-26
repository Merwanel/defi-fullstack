<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AStationSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(__DIR__ . '/../../stations.json'), true);

        $rows = array_map(fn ($station) => [
            'id' => $station['id'],
            'short_name' => $station['shortName'],
            'long_name' => $station['longName'],
        ], $data);

        $this->table('stations')->insert($rows)->save();
    }
}
