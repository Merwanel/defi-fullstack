<?php

require_once __DIR__ . '/IntegrationTestCase.php';

class StatsTest extends IntegrationTestCase
{
    protected function seedTestData(): void
    {
        parent::seedTestData();

        $this->pdo->exec("
            INSERT INTO stats (id, date, type, distance) VALUES 
            ('stat-1', '2024-01-15 10:00:00', 'fret', 100.5),
            ('stat-2', '2024-01-15 14:00:00', 'passager', 50.25),
            ('stat-3', '2024-01-16 09:00:00', 'fret', 75.0),
            ('stat-4', '2024-02-10 11:00:00', 'fret', 200.0),
            ('stat-5', '2024-02-20 16:00:00', 'passager', 150.5),
            ('stat-6', '2025-03-05 12:00:00', 'maintenance', 300.0);
        ");
    }

    public function testGetStatsDistancesNoFilters()
    {
        [$response, $body] = $this->makeRequest('GET', '/stats/distances');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertArrayHasKey('items', $body);
        $this->assertEquals('none', $body['groupBy']);

        $items = $body['items'];
        $this->assertCount(3, $items);

        $fret = array_values(array_filter($items, fn($i) => $i['analyticCode'] === 'fret'))[0];
        $this->assertEquals(375.5, $fret['totalDistanceKm']);
    }

    public function testGetStatsDistancesGroupByDay()
    {
        [$response, $body] = $this->makeRequest('GET', '/stats/distances', null, [
            'groupBy' => 'day'
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('day', $body['groupBy']);

        $items = $body['items'];

        $fretJan15 = array_values(array_filter(
            $items,
            fn($i) =>
            $i['analyticCode'] === 'fret' && $i['periodStart'] === '2024-01-15'
        ))[0];
        $this->assertEquals(100.5, $fretJan15['totalDistanceKm']);

        $fretJan16 = array_values(array_filter(
            $items,
            fn($i) =>
            $i['analyticCode'] === 'fret' && $i['periodStart'] === '2024-01-16'
        ))[0];
        $this->assertEquals(75.0, $fretJan16['totalDistanceKm']);
    }

    public function testGetStatsDistancesGroupByMonth()
    {
        [$response, $body] = $this->makeRequest('GET', '/stats/distances', null, [
            'groupBy' => 'month'
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('month', $body['groupBy']);

        $items = $body['items'];

        $fretJan = array_values(array_filter(
            $items,
            fn($i) =>
            $i['analyticCode'] === 'fret' && $i['periodStart'] === '2024-01-01'
        ))[0];
        $this->assertEquals(175.5, $fretJan['totalDistanceKm']);

        $fretFeb = array_values(array_filter(
            $items,
            fn($i) =>
            $i['analyticCode'] === 'fret' && $i['periodStart'] === '2024-02-01'
        ))[0];
        $this->assertEquals(200.0, $fretFeb['totalDistanceKm']);
    }

    public function testGetStatsDistancesGroupByYear()
    {
        [$response, $body] = $this->makeRequest('GET', '/stats/distances', null, [
            'groupBy' => 'year'
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('year', $body['groupBy']);

        $items = $body['items'];

        $fret2024 = array_values(array_filter(
            $items,
            fn($i) =>
            $i['analyticCode'] === 'fret' && $i['periodStart'] === '2024-01-01'
        ))[0];
        $this->assertEquals(375.5, $fret2024['totalDistanceKm']);
    }

    public function testGetStatsDistancesWithDateRange()
    {
        [$response, $body] = $this->makeRequest('GET', '/stats/distances', null, [
            'from' => '2024-01-01',
            'to' => '2024-01-31'
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('2024-01-01', $body['from']);
        $this->assertEquals('2024-01-31', $body['to']);

        $items = $body['items'];

        $fret = array_values(array_filter($items, fn($i) => $i['analyticCode'] === 'fret'))[0];
        $this->assertEquals(175.5, $fret['totalDistanceKm']);

        $maintenance = array_filter($items, fn($i) => $i['analyticCode'] === 'maintenance');
        $this->assertEmpty($maintenance);
    }

    public function testGetStatsDistancesInvalidFromDate()
    {
        [$response, $body] = $this->makeRequest('GET', '/stats/distances', null, [
            'from' => 'invalid-date'
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('INVALID_DATE', $body['code']);
        $this->assertStringContainsString('from', $body['message']);
    }

    public function testGetStatsDistancesInvalidToDate()
    {
        [$response, $body] = $this->makeRequest('GET', '/stats/distances', null, [
            'to' => 'not-a-date'
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('INVALID_DATE', $body['code']);
        $this->assertStringContainsString('to', $body['message']);
    }

    public function testGetStatsDistancesInvalidDateRange()
    {
        [$response, $body] = $this->makeRequest('GET', '/stats/distances', null, [
            'from' => '2024-12-31',
            'to' => '2024-01-01'
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('INVALID_DATE_RANGE', $body['code']);
    }

    public function testGetStatsDistancesInvalidGroupBy()
    {
        [$response, $body] = $this->makeRequest('GET', '/stats/distances', null, [
            'groupBy' => 'invalid'
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('INVALID_GROUP_BY', $body['code']);
    }

    public function testGetStatsDistancesValidGroupByValues()
    {
        $validGroupBy = ['day', 'month', 'year', 'none'];

        foreach ($validGroupBy as $groupBy) {
            [$response, $body] = $this->makeRequest('GET', '/stats/distances', null, [
                'groupBy' => $groupBy
            ]);

            $this->assertEquals(200, $response->getStatusCode(), "Failed for groupBy: $groupBy");
            $this->assertEquals($groupBy, $body['groupBy']);
        }
    }
}
