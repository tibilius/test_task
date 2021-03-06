<?php

namespace App\Tests\Storage\KeyValueClient;

use App\Storage\Exception\KeyNotFoundException;
use App\Storage\Exception\StorageFileNotFoundException;
use App\Storage\KeyValueClient\JsonFileKeyValueStorage;
use App\Tests\ServiceTestCase;

class JsonFileKeyValueStorageTest extends ServiceTestCase
{
    protected static $filepath = '';
    /**
     * @var JsonFileKeyValueStorage|null
     */
    protected $jsonFileValueStorage = null;


    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$filepath = static::getParam('app_root') . 'var/test_storage/test.storage';
        if (\file_exists(static::$filepath)) {
            \unlink(static::$filepath);
        }
    }

    protected function setUp(): void
    {
        $this->jsonFileValueStorage = new JsonFileKeyValueStorage([
            'filename' => static::$filepath,
            'prefix'   => 'tests',
        ]);
    }

    public function testCheckStorageNotFoundException()
    {
        $this->expectException(StorageFileNotFoundException::class);
        $this->jsonFileValueStorage->get('someKey');
    }

    /**
     * @depends testCheckStorageNotFoundException
     */
    public function testMput()
    {
        $data = ['someKey' => 'someValue', 'someAnotherKey' => 'value2'];
        $this->assertTrue($this->jsonFileValueStorage->mput($data), 'mput returned false');

    }

    /**
     * @depends testMput
     */
    public function testMget()
    {
        $keys = ['someKey', 'someAnotherKey', 'notPresentedKey'];
        $data = $this->jsonFileValueStorage->mget($keys);
        $this->assertArrayHasKey('someKey', $data);
        $this->assertArrayHasKey('someAnotherKey', $data);
        $this->assertArrayNotHasKey('notPresentedKey', $data);
    }

    /**
     * @depends testMget
     */
    public function testMdelete()
    {
        $keys = ['someKey', 'notPresentedKey'];
        $this->assertTrue($this->jsonFileValueStorage->mdelete($keys), 'mdelete return wrong result');
        $data = $this->jsonFileValueStorage->mget(['someKey', 'notPresentedKey', 'someAnotherKey']);
        $this->assertArrayHasKey('someAnotherKey', $data, 'mdelete delete something wrong');
        $this->assertArrayNotHasKey('someKey', $data, 'mdelete not deleted key');

        $this->assertTrue($this->jsonFileValueStorage->mdelete(['*']), 'mdelete return wrong result');
        $data = $this->jsonFileValueStorage->mget(['someKey', 'notPresentedKey', 'someAnotherKey']);
        $this->assertEquals([], $data);
    }

    /**
     * @depends testMdelete
     */
    public function testPut()
    {
        $this->assertTrue($this->jsonFileValueStorage->put('someKey2', 'someValue2'), 'put returned false');
        $this->assertTrue($this->jsonFileValueStorage->put('someKey3', 'someValue3'), 'put returned false');

    }

    /**
     * @depends testPut
     */
    public function testGet()
    {
        $data = $this->jsonFileValueStorage->get('someKey2');
        $this->assertEquals('someValue2', $data);

        $this->expectException(KeyNotFoundException::class);
        $this->jsonFileValueStorage->get('notExistedKey');
    }

    /**
     * @depends testGet
     */
    public function testDelete()
    {
        $this->assertTrue($this->jsonFileValueStorage->delete('notExistedKey'), 'Cannot delete not existed key');
        $this->assertTrue($this->jsonFileValueStorage->delete('someKey2'), 'Cannot delete presented key');

        $this->expectException(KeyNotFoundException::class);
        $this->jsonFileValueStorage->get('someKey2');

    }

    /**
     * @depends testDelete
     */
    public function testDeleteRegex()
    {
        $this->assertEquals('someValue3', $this->jsonFileValueStorage->get('someKey3'));
        $this->assertTrue($this->jsonFileValueStorage->delete('some*'), 'Delete returns bad response');

        $this->expectException(KeyNotFoundException::class);
        $this->jsonFileValueStorage->get('someKey3');
    }

}
