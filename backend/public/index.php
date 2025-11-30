<?php
use Slim\Factory\AppFactory;
use DI\ContainerBuilder;

require __DIR__ . '/../vendor/autoload.php';

$builder = new ContainerBuilder();
$builder->addDefinitions([
    PDO::class => function () {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s', $_ENV['DB_HOST'], $_ENV['DB_NAME']
        );
        $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    },
    App\Repositories\StationRepository::class => \DI\autowire(),
    App\Repositories\DistanceRepository::class => \DI\autowire(),
    App\Repositories\StatsRepository::class => \DI\autowire(),
    App\Services\PathFinder::class => \DI\autowire(),
    App\Services\DataLoader::class => \DI\autowire(),
    App\Services\RouteService::class => \DI\autowire(),
]);
$container = $builder->build();
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$registerRoutes = require __DIR__ . '/../src/routes.php';
$registerRoutes($app);

$app->run();