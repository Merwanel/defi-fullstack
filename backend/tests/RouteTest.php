<?php

use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Psr7\Factory\ServerRequestFactory;

class RouteTest extends TestCase
{
    private $app;

    protected function setUp(): void
    {
        require __DIR__ . '/../vendor/autoload.php';

        $this->app = AppFactory::create();
        $this->app->add(new BodyParsingMiddleware());

        $registerRoutes = require __DIR__ . '/../src/routes.php';
        $registerRoutes($this->app);
    }

    private function getResponseForRoutesRequest($fromStationId, $toStationId, $analyticCode) : mixed 
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('POST', '/routes');
        $request = $request->withParsedBody([
            'fromStationId' => $fromStationId,
            'toStationId' => $toStationId,
            'analyticCode' => $analyticCode
        ]);
        
        $response = $this->app->handle($request);
        $body = json_decode((string)$response->getBody(), true);

        return [$response, $body];
    }

    
    public function testStatus()
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('GET', '/status');
        
        $response = $this->app->handle($request);
        $body = json_decode((string)$response->getBody(), true);

        $this->assertEquals('ok', $body['status']);
    }

    public function testPostRoutesSuccess()
    {
        [$response, $body] = $this->getResponseForRoutesRequest('MZ', 'ZW', 'ANA-123');

        $expected = [
            'id' => 'route-001',
            'fromStationId' => 'MX',
            'toStationId' => 'ZW',
            'analyticCode' => 'ANA-123',
            'distanceKm' => 45.5,
            'path' => ['MX', 'ST', 'ZW'],
            'createdAt' => '2025-11-25T14:30:00Z'
        ];

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals($expected, $body);
    }

    public function testPostRoutesMissingFields()
    {
        [$response, $body] = $this->getResponseForRoutesRequest('MX', null, null);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('MISSING_FIELDS', $body['code']);
        $this->assertContains('toStationId', $body['details']);
        $this->assertContains('analyticCode', $body['details']);
    }
}
