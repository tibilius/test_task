<?php


namespace App\Tests\Taxes\Interceptors\Mocks;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * Class ApiBinConnection
 *
 * Disclaimer: it will not pretty work with PromiseInterface used in multiple requests;
 *
 * @package App\Tests\Taxes\Mocks
 */
class ApiBinConnection extends \App\Net\ApiBinConnection
{
    /**
     * ApiBinConnection constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type'=> 'application/json; charset=utf-8'], '{"country":{"alpha2":"JP"}}'),
            new Response(200, ['Content-Type'=> 'application/json; charset=utf-8'], '{"country":{"alpha2":"LT"}}'),
        ]);
        $handlerStack = HandlerStack::create($mock);

        parent::__construct(['handler' => $handlerStack] + $config);
    }

}