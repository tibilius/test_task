<?php


namespace App\Taxes;


use App\Taxes\DataProvider\ExchangeRatesDataProvider;
use App\Taxes\Entity\Transaction;
use App\Taxes\Event\TransactionTaxEvent;

class TaxCalculator
{
    /**
     * @var TaxInterceptorInterface[]|array
     */
    protected $interceptors = [];

    /**
     * @var ExchangeRatesDataProvider
     */
    protected $exchangeRatesProvider;

    /**
     * TaxCalculator constructor.
     * @param ExchangeRatesDataProvider $exchangeRatesProvider
     */
    public function __construct(ExchangeRatesDataProvider $exchangeRatesProvider)
    {
        $this->exchangeRatesProvider = $exchangeRatesProvider;
    }


    public function addInterceptor(TaxInterceptorInterface $interceptor)
    {
        $this->interceptors[] = $interceptor;
        usort($this->interceptors, function (TaxInterceptorInterface $a, TaxInterceptorInterface $b) {
            return $a->getPriority() - $b->getPriority();
        });

        return $this;
    }

    public function calculate(Transaction $transaction): float
    {
        $factor = 1.00;
        $event = new TransactionTaxEvent();
        $event->setTransaction($transaction);
        foreach ($this->interceptors as $interceptor) {
            if (!$event->isStopped()) {
                $factor *= $interceptor->calculateFactor($event);
            }
        }
        $exchangeRates = $this->exchangeRatesProvider->getRates([$transaction->getCurrency()]);
        $exchangeRate = $exchangeRates[$transaction->getCurrency()];

        return ($factor * $transaction->getAmount()) / ($exchangeRate ?? 1);
    }

}