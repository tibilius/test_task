<?php
declare(strict_types=1);

namespace App\Taxes\DataProvider;

use App\Net\ApiBinConnection;
use App\Storage\Exception\KeyValueStorageException;
use App\Storage\KeyValueInterface;
use App\Taxes\Exception\BinDataNotFoundException;
use App\Taxes\Exception\BinNumberNotNumericException;
use App\Taxes\Exception\BinNumberTooShortException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class BinCountryDataProvider
{
    const BIN_LENGTH_FOR_COUNTRY = 6;

    /**
     * @var ApiBinConnection
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
     * @param ApiBinConnection $connection
     * @param KeyValueInterface $storage
     */
    public function __construct(ApiBinConnection $connection, KeyValueInterface $storage, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->storage = $storage;
        $this->logger = $logger;
    }

    /**
     * @param array $bin
     * @return array
     * @throws BinNumberNotNumericException
     * @throws BinNumberTooShortException
     */
    public function getCountry(array $bin): array
    {
        /// create map original bin => truncated bin
        $binMap = \array_combine($bin, \array_map([$this, 'binForCountry'], $bin));
        $binForCountry = \array_unique(\array_values($binMap));
        /// function return array with original bin: [original bin => country]
        $cacheToResult = function ($data) use ($binMap) {
            $result = [];
            foreach ($binMap as $bin => $key) {
                if ($country = ($data[$key] ?? null)) {
                    $result[$bin] = $country;
                }
            }

            return $result;
        };
        $cachedBinsData = [];
        try {
            $cachedBinsData = $this->storage->mget($binForCountry);
        } catch (KeyValueStorageException $exception) {
            $this->logger->info('Bin numbers not found in cache', \compact('exception', 'binForCountry'));
        }
        $notCachedBins = \array_diff($binForCountry, \array_keys($cachedBinsData));
        if ($notCachedBins === []) {
            return $cacheToResult($cachedBinsData);
        }
        try {
            $apiBinData = $this->connection->mgetBinData($notCachedBins);
        } catch (GuzzleException $e) {
            throw new BinDataNotFoundException(
                'Bin api not provided data for bins: "' . \implode(',', $notCachedBins) . '"',
                $code = 503,
                $e
            );
        }
        $binCountry = [];
        foreach ($apiBinData as $binKey => $binDatum) {
            if (!isset($binDatum['country']['alpha2']) || $binDatum['country']['alpha2'] === '') {
                throw new BinDataNotFoundException(
                    'Bin response not contained country info, bin: "' . $binKey . '"'
                );
            }
            $binCountry[\strval($binKey)] = $binDatum['country']['alpha2'];
        }
        try {
            $this->storage->mput($binCountry);
        } catch (KeyValueStorageException $e) {
            $this->logger->info('Bin numbers cannot be written to cache');
        }

        return $cacheToResult($binCountry + $cachedBinsData);
    }

    protected function binForCountry(string $bin): string
    {
        if (\strlen($bin) < static::BIN_LENGTH_FOR_COUNTRY) {
            throw new BinNumberTooShortException($bin);
        }
        if (!\ctype_digit($bin)) {
            throw new BinNumberNotNumericException($bin);
        }

        return \substr(\str_replace(' ', '', $bin), 0, static::BIN_LENGTH_FOR_COUNTRY);
    }

}