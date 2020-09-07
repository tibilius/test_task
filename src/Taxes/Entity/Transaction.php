<?php


namespace App\Taxes\Entity;


class Transaction
{
    /**
     * @var string
     */
    protected $bin;
    /**
     * @var float
     */
    protected $amount;
    /**
     * @var string
     */
    protected $currency;

    /**
     * @return string
     */
    public function getBin(): string
    {
        return $this->bin;
    }

    /**
     * @param string $bin
     * @return Transaction
     */
    public function setBin(string $bin): Transaction
    {
        $this->bin = $bin;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return Transaction
     */
    public function setAmount(float $amount): Transaction
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return Transaction
     */
    public function setCurrency(string $currency): Transaction
    {
        $this->currency = $currency;

        return $this;
    }



}