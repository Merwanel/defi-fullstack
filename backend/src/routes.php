<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function ($app) {
    $app->post('/routes', function (Request $request, Response $response) {
        $data = $request->getParsedBody();

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

    $app->get('/stats/distances', function (Request $request, Response $response) {
        $from = $request->getQueryParams()['from'] ?? null;
        $to = $request->getQueryParams()['to'] ?? null;
        $groupBy = $request->getQueryParams()['groupBy'] ?? 'none';

        $error = null ;
        if ($from !== null) {
            try {
                new DateTime($from);
            } catch (Exception $e) {
                $error = [
                    'code' => 'INVALID_DATE',
                    'message' => 'Invalid "from" date format',
                    'details' => $from . ' ; ' . $e->getMessage()
                ];
            }
        }
        if ($to !== null) {
            try {
                new DateTime($to);
            } catch (Exception $e) {
                $error = [
                    'code' => 'INVALID_DATE',
                    'message' => 'Invalid "to" date format',
                    'details' => $to . ' ; ' . $e->getMessage()
                ];
            }
        }
        if ($from !== null && $to !== null && strtotime($from) > strtotime($to)) {
            $error = [
                'code' => 'INVALID_DATE_RANGE',
                'message' => 'from date must be before or equal to date',
                'details' => "{$from} > {$to}"
            ];
        }
        
        $validGroupBy = ['day', 'month', 'year', 'none'];
        if (!in_array($groupBy, $validGroupBy)) {
            $error = [
                'code' => 'INVALID_GROUP_BY',
                'message' => 'groupBy must be one of: day, month, year, none'
            ];
        }

        if ($error !== null) {
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
        
        // #TODO : replace placeholder
        $payload = [
            'from' => $from,
            'to' => $to,
            'groupBy' => $groupBy,
            'items' => [
                [
                    'analyticCode' => 'ANA-123',
                    'totalDistanceKm' => 125.5,
                    'periodStart' => '2025-11-01',
                    'periodEnd' => '2025-11-30'
                ],
                [
                    'analyticCode' => 'ANA-456',
                    'totalDistanceKm' => 89.3,
                    'periodStart' => '2025-11-01',
                    'periodEnd' => '2025-11-30'
                ]
            ]
        ];

        $response->getBody()->write(json_encode($payload));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    });
};
