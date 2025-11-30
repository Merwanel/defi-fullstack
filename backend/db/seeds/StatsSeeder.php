<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class StatsSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $data = [];
        $types = ['fret', 'passager', 'maintenance'];
        
        for ($i = 0; $i < 50; $i++) {
            $date = new DateTime('2025-10-01');
            $date->modify('+' . rand(0, 60) . ' days'); 
            $date->modify('+' . rand(0, 86400) . ' seconds');

            $data[] = [
                'id' => 'route-' . uniqid(),
                'date' => $date->format('Y-m-d H:i:s'),
                'type' => $types[array_rand($types)],
                'distance' => rand(100, 5000) / 10,
            ];
        }

        $this->table('stats')->insert($data)->saveData();
    }
}
