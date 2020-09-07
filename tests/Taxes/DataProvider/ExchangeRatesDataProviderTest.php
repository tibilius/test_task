<?php

namespace App\Tests\Taxes\DataProvider;

use App\Net\ApiRatesConnection;
use App\Taxes\DataProvider\ExchangeRatesDataProvider;
use App\Taxes\Exception\ExchangeRatesNotFoundDataProviderException;
use App\Tests\ServiceTestCase;
use App\Tests\Taxes\DataProvider\Mocks\ApiRatesConnection as MockApiRatesConnection;

class ExchangeRatesDataProviderTest extends ServiceTestCase
{
    /**
     * @var ExchangeRatesDataProvider
     */
    private $exchangeRatesDataProvider = null;

    public static function loadMocksBeforeSetup()
    {
        $filename = static::getParam('app_root') . 'var/test_storage/rates_data.storage';
        static::getDefinition(ApiRatesConnection::class)->setClass(MockApiRatesConnection::class);
        static::getDefinition('exchange_rates_provider.key_value.storage')
            ->setArgument('$config', ['filename' => $filename, 'prefix' => 'rates']);

        if (\file_exists($filename)) {
            \unlink($filename);
        }
    }

    protected function setUp(): void
    {
        $this->exchangeRatesDataProvider = $this->getService(ExchangeRatesDataProvider::class);
    }

    public function testGetRates()
    {
        $testSymbols = ['EUR', 'USD'];
        $time = \microtime(true);
        $data = $this->exchangeRatesDataProvider->getRates($testSymbols);
        $bench1 = \microtime(true) - $time;
        foreach ($testSymbols as $bin) {
            $this->assertArrayHasKey($bin, $data, 'Symbol not presented');
        }
        $this->assertEquals(1, $data['EUR']);
        $this->assertEquals(1.1842, $data['USD']);

        $time = \microtime(true);
        $this->exchangeRatesDataProvider->getRates($testSymbols);
        $bench2 = \microtime(true) - $time;

        $this->assertTrue($bench2 <= $bench1, 'Storage not properly works');
    }

    public function testRatesNotFound()
    {
        $this->expectException(ExchangeRatesNotFoundDataProviderException ::class);
        $this->exchangeRatesDataProvider->getRates(['XXX']);
    }

}
