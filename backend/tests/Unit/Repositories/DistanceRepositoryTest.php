<?php

use PHPUnit\Framework\TestCase;
use App\Repositories\DistanceRepository;
use App\Models\Distance;

class DistanceRepositoryTest extends TestCase
{
    public function testFindAllSuccess()
    {
        $pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $stmt->method('fetchAll')->willReturn([
            [
                'id' => 1,
                'line_name' => 'line',
                'parent_id' => 1,
                'child_id' => 2,
                'distance' => 10.5
            ]
        ]);

        $pdo->method('query')->willReturn($stmt);

        $repo = new DistanceRepository($pdo);
        $distances = $repo->findAll();

        $this->assertCount(1, $distances);
        $this->assertInstanceOf(Distance::class, $distances[0]);
        $this->assertEquals(1, $distances[0]->id);
    }
}
