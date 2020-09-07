<?php


namespace App\Taxes\Exception;


use Throwable;

class BinNumberTooShortException extends BinProviderException
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $message = 'Bin number is too short: "' . $message.'"';
        parent::__construct($message, $code, $previous);
    }


}