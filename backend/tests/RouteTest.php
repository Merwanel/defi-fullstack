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

        // Load routes without running the server
        $registerRoutes = require __DIR__ . '/../routes.php';
        $registerRoutes($this->app);
    }

    public function testPostRoutesSuccess()
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('POST', '/routes');
        $request = $request->withParsedBody([
            'fromStationId' => 'MX',
            'toStationId' => 'ZW',
            'analyticCode' => 'ANA-123'
        ]);

        $response = $this->app->handle($request);
        $body = json_decode((string)$response->getBody(), true);

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
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('POST', '/routes');
        $request = $request->withParsedBody([
            'fromStationId' => 'MX'
        ]);

        $response = $this->app->handle($request);
        $body = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('MISSING_FIELDS', $body['code']);
        $this->assertContains('toStationId', $body['details']);
        $this->assertContains('analyticCode', $body['details']);
    }
}
