<?php


namespace App\Taxes;


use App\Taxes\Event\TransactionTaxEvent;

interface TaxInterceptorInterface
{
    public function getPriority(): int;

    public function calculateFactor(TransactionTaxEvent $transaction): float;
}