<?php


namespace App\Tests\Taxes\Mocks;


use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class ApiRatesConnection extends \App\Net\ApiRatesConnection
{

    /**
     * ApiBinConnection constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $mock = new MockHandler([
            new Response(
                200,
                ['Content-Type'=> 'application/json; charset=utf-8'],
                '{"rates":{"USD":999.1842},"base":"EUR","date":"' . date('Y-m-d') . '"}'
            ),
        ]);
        $handlerStack = HandlerStack::create($mock);

        parent::__construct(['handler' => $handlerStack] + $config);
    }

}