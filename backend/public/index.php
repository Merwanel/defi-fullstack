<?php
use Slim\Factory\AppFactory;
use Slim\Middleware\BodyParsingMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->add(new BodyParsingMiddleware());

$registerRoutes = require __DIR__ . '/../src/routes.php';
$registerRoutes($app);

$app->run();