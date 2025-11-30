<?php

require_once __DIR__ . '/IntegrationTestCase.php';

class RouteTest extends IntegrationTestCase
{
    public function testStatus()
    {
        [$response, $body] = $this->makeRequest('GET', '/status');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('ok', $body['status']);
    }

    public function testPostRoutesSuccess()
    {
        [$response, $body] = $this->makeRequest('POST', '/routes', [
            'fromStationId' => '1',
            'toStationId' => '3',
            'analyticCode' => 'ANA-123'
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(1, $body['fromStationId']);
        $this->assertEquals(3, $body['toStationId']);
        $this->assertEquals('ANA-123', $body['analyticCode']);
        $this->assertEquals(45.5, $body['distanceKm']);
        $this->assertEquals([1, 2, 3], $body['path']);
    }

    public function testPostRoutesMissingFields()
    {
        [$response, $body] = $this->makeRequest('POST', '/routes', [
            'fromStationId' => 'MX',
            'toStationId' => null,
            'analyticCode' => null
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('MISSING_FIELDS', $body['code']);
        $this->assertContains('toStationId', $body['details']);
        $this->assertContains('analyticCode', $body['details']);
    }

    public function testPostRoutesNotFound()
    {
        [$response, $body] = $this->makeRequest('POST', '/routes', [
            'fromStationId' => '1',
            'toStationId' => '999',
            'analyticCode' => 'TEST-404'
        ]);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('ROUTE_NOT_FOUND', $body['code']);
    }
}
