<?php


namespace App\Storage\Exception;


class KeyNotFoundException extends KeyValueStorageException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $message = 'Key not found: "' . $message.'"';
        parent::__construct($message, $code, $previous);
    }

}