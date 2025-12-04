<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function ($app) {
    $app->group('/api/v1', function ($group) use ($app) {
        $group->post('/routes', function (Request $request, Response $response) use ($app) {
            $data = $request->getParsedBody();

            if (!is_array($data)) {
                $data = [];
            }

            $fromStationId = $data['fromStationId'] ?? null;
            $toStationId = $data['toStationId'] ?? null;
            $analyticCode = $data['analyticCode'] ?? null;

            if ($fromStationId === null || $toStationId === null || $analyticCode === null) {
                $missing = [];
                if ($fromStationId === null)
                    $missing[] = 'fromStationId';
                if ($toStationId === null)
                    $missing[] = 'toStationId';
                if ($analyticCode === null)
                    $missing[] = 'analyticCode';

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

            $routeService = $app->getContainer()->get(\App\Services\RouteService::class);
            $route = $routeService->findRoute((int) $fromStationId, (int) $toStationId, $analyticCode);

            if (!$route) {
                $error = [
                    'code' => 'ROUTE_NOT_FOUND',
                    'message' => 'No route found between the specified stations. Itinerary examples: BEMM -> CASE; WBA -> LBAD; STLV -> IO'
                ];
                $response->getBody()->write(json_encode($error));
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
            }

            $response->getBody()->write(json_encode($route));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        });

        $group->get('/stations', function (Request $request, Response $response) use ($app) {
            $dataLoader = $app->getContainer()->get(\App\Services\DataLoader::class);
            $stationsDict = $dataLoader->load()['stations'];

            $stations = [];
            foreach ($stationsDict as $id => [$shortName, $longName]) {
                $stations[] = [
                    'id' => $id,
                    'shortName' => $shortName,
                    'longName' => $longName
                ];
            }

            $response->getBody()->write(json_encode($stations));
            return $response->withHeader('Content-Type', 'application/json');
        });

        $group->get('/status', function (Request $request, Response $response) {
            $data = ['status' => 'ok', 'timestamp' => time()];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        });

        $group->get('/stats/distances', function (Request $request, Response $response) use ($app) {
            $from = $request->getQueryParams()['from'] ?? null;
            $to = $request->getQueryParams()['to'] ?? null;
            $groupBy = $request->getQueryParams()['groupBy'] ?? 'none';

            $error = null;
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

            $statsRepository = $app->getContainer()->get(\App\Repositories\StatsRepository::class);
            $items = $statsRepository->getAggregatedDistances($from, $to, $groupBy);

            foreach ($items as &$item) {
                $item['totalDistanceKm'] = (float) $item['totalDistanceKm'];
            }

            $payload = [
                'from' => $from,
                'to' => $to,
                'groupBy' => $groupBy,
                'items' => $items
            ];

            $response->getBody()->write(json_encode($payload));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        });
    });
};
