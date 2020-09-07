<?php


namespace App\Taxes\Interceptors;


use App\Taxes\DataProvider\BinCountryDataProvider;
use App\Taxes\Event\TransactionTaxEvent;
use App\Taxes\TaxInterceptorInterface;

class CountryInterceptor implements TaxInterceptorInterface
{
    /**
     * Here might be in config or any storage, like db, file etc
     */
    private const ZONE_COUNTRIES = [
        'eu' => [
            'country_list'=> ['AT','BE','BG','CY','CZ','DE','DK','EE','ES','FI','FR','GR','HR','HU',
              'IE','IT','LT','LU','LV','MT','NL','PO','PT','RO','SE','SI','SK',],
            'factor' => 0.01,
        ],
    ];
    private const DEFAULT_FACTOR = 0.02;
    private const PRIORITY = -1;

    /**
     * @var BinCountryDataProvider
     */
    private $binCountryProvider;

    /**
     * CountryInterceptor constructor.
     * @param BinCountryDataProvider $binCountryProvider
     */
    public function __construct(BinCountryDataProvider $binCountryProvider)
    {
        $this->binCountryProvider = $binCountryProvider;
    }

    public function getPriority(): int
    {
        return  self::PRIORITY;
    }

    public function calculateFactor(TransactionTaxEvent $transaction): float
    {
        $countries = $this->binCountryProvider->getCountry([$transaction->getTransaction()->getBin()]);
        $country = $countries[$transaction->getTransaction()->getBin()];
        $factor = self::DEFAULT_FACTOR;
        foreach (self::ZONE_COUNTRIES as $rules) {
            if (in_array($country, $rules['country_list'])) {
                return $rules['factor'];
            }
        }

        return  $factor;
    }

}