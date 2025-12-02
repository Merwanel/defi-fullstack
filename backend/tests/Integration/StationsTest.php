<?php

require_once __DIR__ . '/IntegrationTestCase.php';

class StationsTest extends IntegrationTestCase
{
    public function testGetStations()
    {
        [$response, $body] = $this->makeRequest('GET', '/api/v1/stations');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(3, $body);


        // Verify second station
        $this->assertEquals(2, $body[1]['id']);
        $this->assertEquals('ST', $body[1]['shortName']);
        $this->assertEquals('Saint-T', $body[1]['longName']);
    }
}
