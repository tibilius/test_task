<?php


namespace App\Tests\Taxes\Mocks;


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
            new Response(200, ['Content-Type'=> 'application/json; charset=utf-8'], '{"number":{"length":16,"luhn":true},"scheme":"visa","type":"debit","brand":"Visa/Dankort","prepaid":false,"country":{"numeric":"208","alpha2":"DK","name":"Denmark","emoji":"🇩🇰","currency":"DKK","latitude":56,"longitude":10},"bank":{"name":"Jyske Bank","url":"www.jyskebank.dk","phone":"+4589893300","city":"Hjørring"}}
'),
        ]);
        $handlerStack = HandlerStack::create($mock);

        parent::__construct(['handler' => $handlerStack] + $config);
    }

}