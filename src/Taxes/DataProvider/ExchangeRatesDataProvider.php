<?php
declare(strict_types=1);

namespace App\Taxes\DataProvider;

use App\Net\ApiRatesConnection;
use App\Storage\Exception\KeyValueStorageException;
use App\Storage\KeyValueInterface;
use App\Taxes\Exception\ExchangeRatesMissingDataProviderException;
use App\Taxes\Exception\ExchangeRatesNotFoundDataProviderException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class ExchangeRatesDataProvider
{
    /**
     * @var ApiRatesConnection
     */
    private $connection;
    /**
     * @var KeyValueInterface
     */
    private $storage;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * BinDataProvider constructor.
     * @param ApiRatesConnection $connection
     * @param KeyValueInterface $storage
     */
    public function __construct(ApiRatesConnection $connection, KeyValueInterface $storage, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->storage = $storage;
        $this->logger = $logger;
    }

    /**
     * Here some logic for rates, imo it should be renew only once per day
     * @param array $symbols
     * @return array
     */
    protected function key(array $symbols = []): array
    {
        $keys = [];
        foreach ($symbols as $symbol) {
            if (!is_string($symbol)) {
                throw new \RuntimeException('Symbol should be a string, ' . gettype($symbol) . ' given');
            }
            $keys[] = \date('Y-m-d') . '|' . $symbol;
        }

        return $keys;
    }

    protected function unwrapKey($key): string
    {
        return explode('|', $key)[1] ?? '';
    }

    public function getRates($symbols = []): array
    {
        $cachedRates = [];
        $symbolKeys = $this->key($symbols);///makes keys like Y-m-d|CURRENCY, to avoid to old cached values
        try {
            $cachedRates = $this->storage->mget($symbolKeys);
        } catch (KeyValueStorageException $exception) {
            $this->logger->info('Exchange rates not found in cache', compact('exception', 'symbolKeys'));
        }
        $cachedRates =  \array_combine(
            \array_map([$this, 'unwrapKey'], \array_keys($cachedRates)),
            \array_values($cachedRates)
        );
        $notCachedSymbols = \array_diff($symbols, \array_keys($cachedRates));
        if ($notCachedSymbols === []) {
            return $cachedRates;
        }
        try {
            $apiRatesData = $this->connection->getLatestRates($notCachedSymbols);
        } catch (GuzzleException $e) {
            throw new ExchangeRatesNotFoundDataProviderException(
                'Exchange rates api not provided data for rates: "' . join(',', $notCachedSymbols) . '"',
                $code = 503,
                $e
            );
        }
        $badRatesResponse = !isset($apiRatesData['rates'])
            || ($missedRate = \array_diff($notCachedSymbols, \array_keys($apiRatesData['rates']))) !== [];
        if ($badRatesResponse) {
            if (isset($missedRate)) {
                throw new ExchangeRatesMissingDataProviderException(
                    'Exchange rates not found in api, symbols are: ' . \implode(',', $missedRate)
                );
            }
            throw new ExchangeRatesNotFoundDataProviderException('API response does not contain rates');
        }
        $ratesData = \array_combine(
            $this->key(\array_keys($apiRatesData['rates'])),
            \array_values($apiRatesData['rates'])
        );
        try {
            $this->storage->mput($ratesData);
        } catch (KeyValueStorageException $e) {
            $this->logger->info('Exchange rates cannot be written to cache', compact('ratesData'));
        }
        finally {
            return $apiRatesData['rates'] + $cachedRates;
        }
    }

}