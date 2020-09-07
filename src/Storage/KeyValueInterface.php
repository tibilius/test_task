<?php
declare(strict_types=1);

namespace App\Storage;


use App\Storage\Exception\KeyNotFoundException;
use App\Storage\Exception\KeyValueStorageException;

interface KeyValueInterface
{
    /**
     * KeyValueInterface constructor.
     * @param array $config
     */
    public function __construct(array $config);

    /**
     * @param string $key
     * @return mixed
     * @throws KeyNotFoundException
     */
    public function get(string $key);

    /**
     * @param $key
     * @param $value
     * @return bool
     * @throws KeyValueStorageException
     */
    public function put(string $key, $value): bool;

    /**
     * @param string $key, might be like key1, or key* ('*' should be placed in the string's end)
     * @return mixed
     */
    public function delete(string $key);

    /**
     * @param string[] $keys
     * @return array with structure key => value, in case if exited in cache, if not - there are not presented in result
     */
    public function mget(array $keys): array;

    /**
     * @param array $values with structure key => value
     * @return bool
     * @throws KeyValueStorageException
     */
    public function mput(array $values): bool;

    /**
     * @param array $keys
     * @return mixed
     */
    public function mdelete(array $keys):bool;

}