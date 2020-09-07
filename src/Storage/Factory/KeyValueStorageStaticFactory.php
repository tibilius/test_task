<?php


namespace App\Storage\Factory;


use App\Storage\KeyValueInterface;

class KeyValueStorageStaticFactory
{
    public static function create($className, $config)
    {
        if (!in_array(KeyValueInterface::class, class_implements($className))) {
            throw new \RuntimeException('Class ' . $className . ' have to implement ' . KeyValueInterface::class);
        }

        return new $className($config);
    }
}