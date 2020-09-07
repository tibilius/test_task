<?php

namespace App\Tests\Net;

use App\Net\ApiRatesConnection;
use App\Tests\ServiceTestCase;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class ApiRatesConnectionTest
 * @package App\Tests\Net
 * @group network_uses
 */
class ApiRatesConnectionTest extends ServiceTestCase
{
    /**
     * @var ApiRatesConnection
     */
    protected $connection;

    const TEST_SYMBOLS = [
        'CZK',
        'GBP',
        'USD',
        'AUD',
        'RUB',
    ];

    protected function setUp(): void
    {
        $this->connection = $this->getService(ApiRatesConnection::class);
    }

    public function testGetLatestRates()
    {
        $data = $this->connection->getLatestRates();
        $this->assertArrayHasKey('rates', $data, 'Api rates not provided rates data');
        $this->assertArrayHasKey('base', $data, 'Api rates not provided base currency');

        $data = $this->connection->getLatestRates(self::TEST_SYMBOLS);
        foreach (self::TEST_SYMBOLS as $symbol) {
            $this->assertArrayHasKey($symbol, $data['rates'], 'Requested ' . $symbol . ' not provided');
        }

        $this->expectException(GuzzleException::class);
        $data = $this->connection->getLatestRates(['notexistedrate']);
    }
}
