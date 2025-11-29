<?php

use PHPUnit\Framework\TestCase;
use App\Repositories\StationRepository;
use App\Models\Station;

class StationRepositoryTest extends TestCase
{
    public function testFindAllSuccess()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $stmt->method('fetchAll')->willReturn([
            [
                'id' => 1,
                'short_name' => 'ST',
                'long_name' => 'Station Name'
            ]
        ]);

        $pdo->method('query')->willReturn($stmt);

        $repo = new StationRepository($pdo);
        $stations = $repo->findAll();

        $this->assertCount(1, $stations);
        $this->assertInstanceOf(Station::class, $stations[0]);
        $this->assertEquals(1, $stations[0]->id);
    }
}
