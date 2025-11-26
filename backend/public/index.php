<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Middleware\BodyParsingMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Add body parsing middleware
$app->add(new BodyParsingMiddleware());

$app->post('/routes', function (Request $request, Response $response, $args) {
    
    $data = $request->getParsedBody();

    // Handle null or non-array data
    if (!is_array($data)) {
        $data = [];
    }

    $fromStationId = $data['fromStationId'] ?? null;
    $toStationId = $data['toStationId'] ?? null;
    $analyticCode = $data['analyticCode'] ?? null;
    
    if ($fromStationId === null || $toStationId === null || $analyticCode === null) {
        $missing = [];
        if ($fromStationId === null) $missing[] = 'fromStationId';
        if ($toStationId === null) $missing[] = 'toStationId';
        if ($analyticCode === null) $missing[] = 'analyticCode';
        
        $error = [
            'code' => 'MISSING_FIELDS',
            'message' => 'Missing some required fields',
            'details' => $missing
        ];
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }
    
    #TODO : replace placeholder
    $payload = [
        "id" => "route-001",
        "fromStationId" => "MX",
        "toStationId" => "ZW",
        "analyticCode" => "ANA-123",
        "distanceKm" => 45.5,
        "path" => ["MX", "ST", "ZW"],
        "createdAt" => "2025-11-25T14:30:00Z"
    ];

    $response->getBody()->write(json_encode($payload));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});


$app->get('/status', function (Request $request, Response $response) {
    $data = ['status' => 'ok', 'timestamp' => time()];
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();