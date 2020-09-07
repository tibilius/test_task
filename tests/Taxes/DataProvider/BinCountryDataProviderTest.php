<?php

namespace App\Tests\Taxes\DataProvider;

use App\Application;
use App\Net\ApiBinConnection;
use App\Taxes\DataProvider\BinCountryDataProvider;
use App\Taxes\Exception\BinNumberNotNumericException;
use App\Taxes\Exception\BinNumberTooShortException;
use App\Tests\ServiceTestCase;
use App\Tests\Taxes\DataProvider\Mocks\ApiBinConnection as MockApiBinConnection;

class BinCountryDataProviderTest extends ServiceTestCase
{
    /**
     * @var BinCountryDataProvider
     */
    private $binDataProvider = null;

    public static function loadMocksBeforeSetup()
    {
        $filename = static::getParam('app_root') . 'var/test_storage/bin_data.storage';
        Application::getInstance()->getContainer()->getDefinition(ApiBinConnection::class)
            ->setClass(MockApiBinConnection::class);
        Application::getInstance()->getContainer()->getDefinition('bin_provider.key_value.storage')
            ->setArgument('$config', ['filename' => $filename, 'prefix' => 'bin_data']);
        if (\file_exists($filename)) {
            \unlink($filename);
        }
    }

    protected function setUp(): void
    {
        $this->binDataProvider = $this->getService(BinCountryDataProvider::class);
    }

    public function testBinNumberTooShortException()
    {
        $this->expectException(BinNumberTooShortException::class);
        $this->binDataProvider->getCountry(['123']);
    }

    public function testBinNumberNotNumericException()
    {
        $this->expectException(BinNumberNotNumericException::class);
        $this->binDataProvider->getCountry(['123xew32']);
    }

    public function testGetCountry()
    {
        $testBins = ['45717360', '516793', '45417360', '41417360'];
        $time = microtime(true);
        $data = $this->binDataProvider->getCountry($testBins);
        $bench1 = microtime(true) - $time;
        foreach ($testBins as $bin) {
            $this->assertArrayHasKey($bin, $data, 'Bin not presented');
        }
        $this->assertEquals('DK', $data['45717360']);
        $this->assertEquals('LT', $data['516793']);
        $this->assertEquals('JP', $data['45417360']);
        $this->assertEquals('US', $data['41417360']);

        $time1 = microtime(true);
        $this->binDataProvider->getCountry($testBins);
        $bench2 = microtime(true) - $time1;

        $this->assertTrue($bench2 <= $bench1, 'Storage not properly works');
    }


}
