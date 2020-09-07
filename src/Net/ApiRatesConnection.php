<?php
declare(strict_types=1);

namespace App\Net;

use App\Net\Exception\NetException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Utils;

class ApiRatesConnection
{
    /**
     * Base currency for exchange rates. Hardcoded because it will always in eur by the design
     */
    protected const BASE_CURRENCY = 'EUR';
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * ApiRatesConnection constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->client = new Client($config);
    }

    public function getLatestRates(array $symbols = []): array
    {

        if (($key = \array_search(self::BASE_CURRENCY, $symbols, true)) !== false) {
            unset($symbols[$key]);
        }
        $query = ['base' => static::BASE_CURRENCY] + ($symbols === [] ? [] : ['symbols' => implode(',', $symbols)]);
        $response = $this->client->request('GET', 'latest', compact('query'));
        switch ($response->getStatusCode()) {
            case 200:
                $data = $response->getBody()->getContents();
                break;
            default:
                throw new NetException('Exchange rates api return status code: ' . $response->getStatusCode());
        }
        $result = Utils::jsonDecode($data, true);
        $result['rates'][self::BASE_CURRENCY] = 1.0;

        return  $result;
    }
}