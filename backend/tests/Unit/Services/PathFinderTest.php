<?php

use PHPUnit\Framework\TestCase;
use App\Services\PathFinder;

class PathFinderTest extends TestCase
{
    public function testFindShortestPathSuccess()
    {
        $finder = new PathFinder();
        $distances = [
            1 => [2 => 10.0],
            1 => [3 => 20.0],
            2 => [3 => 5.0]
        ];

        $result = $finder->findShortestPath(1, 3, $distances);

        $this->assertNotNull($result);
        $this->assertEquals([1, 2, 3], $result['path']);
        $this->assertEquals(15.0, $result['totalDistance']);
    }

    public function testFindShortestPathFailure()
    {
        $finder = new PathFinder();
        $distances = [
            1 => [2 => 10.0],
            3 => [4 => 10.0]
        ];

        $result = $finder->findShortestPath(1, 3, $distances);

        $this->assertNull($result);
    }
}
