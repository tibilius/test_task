<?php

namespace App\Tests\Net;

use App\Net\ApiBinConnection;
use App\Tests\ServiceTestCase;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class ApiBinConnectionTest
 * @package App\Tests\Net
 * @group network_uses
 */
class ApiBinConnectionTest extends ServiceTestCase
{
    /**
     * @var ApiBinConnection
     */
    protected $connection;
    const TEST_CARDS = [
        '4571 7360',
        '374245455400126',
        '378282246310005',
        '4263982640269299',
        '4263982640269299',
    ];

    protected function setUp(): void
    {
        $this->connection = $this->getService(ApiBinConnection::class);
    }

    protected function checkCountryBinData($data) {
        $this->assertArrayHasKey('country', $data, 'Country not provided by api');
        $this->assertArrayHasKey('alpha2', $data['country'], 'Country not provided by api');
    }

    public function testGetBinData()
    {
        $data = $this->connection->getBinData('4571 7360');
        $this->checkCountryBinData($data);
        $this->assertEquals('DK', $data['country']['alpha2'], 'Api bin provider not worked well');

        $this->expectException(GuzzleException::class);
        $data = $this->connection->getBinData('something');

        $this->expectException(GuzzleException::class);
        $data = $this->connection->getBinData('0000000000');
    }

    public function testMultiGetBinData()
    {
        $data = $this->connection->mgetBinData(self::TEST_CARDS);
        foreach (self::TEST_CARDS as $key) {
            $this->assertArrayHasKey($key, $data, 'Bin provider not provided data for cards');
            $this->checkCountryBinData($data[$key]);
        }
        $this->expectException(GuzzleException::class);
        $data = $this->connection->mgetBinData(array_merge(self::TEST_CARDS, ['something']));
    }
}
