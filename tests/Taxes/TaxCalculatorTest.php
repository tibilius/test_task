<?php

namespace App\Tests\Taxes;

use App\Application;
use App\Net\ApiBinConnection;
use App\Net\ApiRatesConnection;
use App\Taxes\Entity\Transaction;
use App\Taxes\TaxCalculator;
use App\Tests\ServiceTestCase;
use App\Tests\Taxes\Mocks\ApiBinConnection as MockApiBinConnection;
use App\Tests\Taxes\Mocks\ApiRatesConnection as MockApiRatesConnection;

class TaxCalculatorTest extends ServiceTestCase
{
    /**
     * @var TaxCalculator
     */
    protected $taxCalculator;

    public static function loadMocksBeforeSetup()
    {
        Application::getInstance()->getContainer()->getDefinition(ApiBinConnection::class)
            ->setClass(MockApiBinConnection::class);
        Application::getInstance()->getContainer()->getDefinition(ApiRatesConnection::class)
            ->setClass(MockApiRatesConnection::class);
        Application::getInstance()->getContainer()->getDefinition('bin_provider.key_value.storage')
            ->setArgument('$config', ['filename' => '%app_root%var/test_storage/bin.storage', 'prefix' => 'bin_data']);
        Application::getInstance()->getContainer()->getDefinition('exchange_rates_provider.key_value.storage')
            ->setArgument('$config', ['filename' => '%app_root%var/test_storage/exr.storage', 'prefix' => 'rates']);

    }

    protected function setUp(): void
    {
        $this->taxCalculator = $this->getService(TaxCalculator::class);
    }


    public function testCalculate()
    {
        $testTransaction = new Transaction();
        $testTransaction->setAmount(50.0)->setBin('45717360')->setCurrency('USD');
        /**
         * Mock exchange rates near 1k
         */
        $this->assertEquals(0.0005004082330365111, $this->taxCalculator->calculate($testTransaction));
    }
}
