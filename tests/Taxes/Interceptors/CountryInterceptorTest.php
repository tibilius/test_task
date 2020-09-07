<?php

namespace App\Tests\Taxes\Interceptors;

use App\Net\ApiBinConnection;
use App\Taxes\Entity\Transaction;
use App\Taxes\Event\TransactionTaxEvent;
use App\Taxes\Interceptors\CountryInterceptor;
use App\Tests\ServiceTestCase;
use App\Tests\Taxes\Interceptors\Mocks\ApiBinConnection as MockApiBinConnection;

class CountryInterceptorTest extends ServiceTestCase
{
    /**
     * @var CountryInterceptor
     */
    protected $interceptor;

    public static function loadMocksBeforeSetup()
    {
        $filename = static::getParam('app_root') . 'var/test_storage/interceptor.storage';
        static::getDefinition(ApiBinConnection::class)->setClass(MockApiBinConnection::class);
        static::getDefinition('bin_provider.key_value.storage')
            ->setArgument('$config', ['filename' => $filename, 'prefix' => 'bin_data']);
        if (\file_exists($filename)) {
            \unlink($filename);
        }
    }
    protected function setUp(): void
    {
        $this->interceptor = $this->getService(CountryInterceptor::class);
    }

    public function testCalculateFactor()
    {
        $testTransaction = new Transaction();
        $testTransaction->setAmount(50.0)->setBin('45717360')->setCurrency('USD');
        $transactionEvent = (new TransactionTaxEvent())->setTransaction($testTransaction);
        $this->assertEquals(0.02, $this->interceptor->calculateFactor($transactionEvent));

        $testTransaction = new Transaction();
        $testTransaction->setAmount(50.0)->setBin('516793')->setCurrency('USD');
        $transactionEvent = (new TransactionTaxEvent())->setTransaction($testTransaction);
        $this->assertEquals(0.01, $this->interceptor->calculateFactor($transactionEvent));
    }
}
