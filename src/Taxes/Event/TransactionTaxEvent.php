<?php


namespace App\Taxes\Event;


use App\Taxes\Entity\Transaction;

class TransactionTaxEvent implements EventInterface
{
    /**
     * @var Transaction
     */
    protected $transaction;
    /**
     * @var bool
     */
    private $stopped = false;

    /**
     * @return Transaction
     */
    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    /**
     * @param Transaction $transaction
     * @return TransactionTaxEvent
     */
    public function setTransaction(Transaction $transaction): TransactionTaxEvent
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function stopPropagation(): void
    {
        $this->stopped = true;
    }

    public function isStopped(): bool
    {
        return $this->stopped;
    }

}