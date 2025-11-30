<?php

use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use DI\ContainerBuilder;

class RouteTest extends TestCase
{
    private $app;

    protected function setUp(): void
    {
        require __DIR__ . '/../vendor/autoload.php';

        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            App\Repositories\StationRepository::class => function () {
                return new class extends App\Repositories\StationRepository {
                    public function __construct() {}
                    public function findAll(): array {
                        return [
                            new App\Models\Station(1, 'MX', 'Mont-X'),
                            new App\Models\Station(2, 'ST', 'Saint-T'),
                            new App\Models\Station(3, 'ZW', 'Zweil'),
                        ];
                    }
                };
            },
            App\Repositories\DistanceRepository::class => function () {
                return new class extends App\Repositories\DistanceRepository {
                    public function __construct() {}
                    public function findAll(): array {
                        return [
                            new App\Models\Distance(1, 'line', 1, 2, 10.0),
                            new App\Models\Distance(2, 'line', 2, 3, 35.5),
                        ];
                    }
                };
            },
            App\Services\DataLoader::class => \DI\autowire(),
            App\Services\PathFinder::class => \DI\autowire(),
            App\Services\RouteService::class => \DI\autowire(),
        ]);
        $container = $builder->build();
        AppFactory::setContainer($container);

        $this->app = AppFactory::create();
        $this->app->addBodyParsingMiddleware();

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
        [$response, $body] = $this->getResponseForRoutesRequest('1', '3', 'ANA-123');

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
        [$response, $body] = $this->getResponseForRoutesRequest('MX', null, null);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('MISSING_FIELDS', $body['code']);
        $this->assertContains('toStationId', $body['details']);
        $this->assertContains('analyticCode', $body['details']);
    }
}
