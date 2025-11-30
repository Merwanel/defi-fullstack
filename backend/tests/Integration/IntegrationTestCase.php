<?php

use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface;

abstract class IntegrationTestCase extends TestCase
{
    protected $app;
    protected $pdo;

    protected function setUp(): void
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            PDO::class => function () {
                $pdo = new PDO('sqlite::memory:');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $pdo;
            },
            App\Repositories\StationRepository::class => \DI\autowire(),
            App\Repositories\DistanceRepository::class => \DI\autowire(),
            App\Repositories\StatsRepository::class => \DI\autowire(),
            App\Services\DataLoader::class => \DI\autowire(),
            App\Services\PathFinder::class => \DI\autowire(),
            App\Services\RouteService::class => \DI\autowire(),
        ]);
        $container = $builder->build();
        AppFactory::setContainer($container);

        $this->app = AppFactory::create();
        $this->app->addBodyParsingMiddleware();

        $registerRoutes = require __DIR__ . '/../../src/routes.php';
        $registerRoutes($this->app);

        $this->pdo = $container->get(PDO::class);
        $this->setupDatabase();
    }

    private function setupDatabase(): void
    {
        // Manually run migrations by instantiating the migration classes
        $adapter = new \Phinx\Db\Adapter\SQLiteAdapter(['connection' => $this->pdo]);
        $adapter->setConnection($this->pdo);

        require_once __DIR__ . '/../../db/migrations/20251125224649_create_stations_table.php';
        /** @phpstan-ignore-next-line */
        $stationsMigration = new \CreateStationsTable('20251125224649', '20251125224649');
        $stationsMigration->setAdapter($adapter);
        $stationsMigration->change();

        require_once __DIR__ . '/../../db/migrations/20251126000629_create_distances_table.php';
        /** @phpstan-ignore-next-line */
        $distancesMigration = new \CreateDistancesTable('20251126000629', '20251126000629');
        $distancesMigration->setAdapter($adapter);
        $distancesMigration->change();

        require_once __DIR__ . '/../../db/migrations/20251130100132_create_stats_table.php';
        /** @phpstan-ignore-next-line */
        $statsMigration = new \CreateStatsTable('20251130100132', '20251130100132');
        $statsMigration->setAdapter($adapter);
        $statsMigration->change();

        $this->seedTestData();
    }

    protected function seedTestData(): void
    {
        $this->pdo->exec("
            INSERT INTO stations (id, short_name, long_name) VALUES 
            (1, 'MX', 'Mont-X'),
            (2, 'ST', 'Saint-T'),
            (3, 'ZW', 'Zweil');
            
            INSERT INTO distances (line_name, parent_id, child_id, distance) VALUES 
            ('line', 1, 2, 10),
            ('line', 2, 3, 35.5),
            ('line', 1, 3, 100);
        ");
    }

    protected function makeRequest(string $method, string $uri, ?array $body = null, ?array $queryParams = null): array
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest($method, $uri);

        if ($body !== null) {
            $request = $request->withParsedBody($body);
        }

        if ($queryParams !== null) {
            $request = $request->withQueryParams($queryParams);
        }

        $response = $this->app->handle($request);
        $responseBody = json_decode((string) $response->getBody(), true);

        return [$response, $responseBody];
    }


}
