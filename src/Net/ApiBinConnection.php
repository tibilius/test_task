<?php
declare(strict_types=1);

namespace App\Net;

use App\Net\Exception\NetException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Utils;
use function GuzzleHttp\Promise\settle;

class ApiBinConnection
{
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

    /**
     * @param string $bin
     * @return array|bool|float|int|object|string|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getBinData(string $bin)
    {
        $response = $this->client->request('GET', $this->prepareBin($bin));
        switch ($response->getStatusCode()) {
            case 200:
                $data = $response->getBody()->getContents();
                break;
            case 404:
                throw new NetException('Bin code not found: ' . $bin, $response->getStatusCode());
            case 429:
                throw new NetException('Limit is reached', $response->getStatusCode());
            default:
                throw new NetException(
                    'Bin api return status code: ' . $response->getStatusCode() .
                    ' with bin :' . $bin,
                    $response->getStatusCode()
                );
        }

        return Utils::jsonDecode($data, true);
    }

    /**
     * @param array $bin
     * @return array
     */
    public function mgetBinData(array $bin): array
    {
        $result = [];
        $promises = [];
        foreach (\array_chunk($bin, 10) as $bins) {
            foreach ($bins as $bin) {
                $promises[$bin] = $this->client->requestAsync('GET', $this->prepareBin($bin));
            }
            $responses = settle($promises)->wait();
            foreach ($responses as $bin => $response) {
                if (($response['state'] ?? null) == Promise::FULFILLED) {
                    $result[$bin] = Utils::jsonDecode($response['value']->getBody()->getContents(), true);
                } else {
                    throw $response['reason'];
                }
            }
        }

        return $result;
    }

    private function prepareBin($bin):string
    {
        return \str_replace(' ', '', $bin);
    }

}


