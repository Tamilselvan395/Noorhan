<?php

namespace App\Exceptions;

use Exception;

class ZohoApiException extends Exception
{
    public function __construct(string $message, public array $body = [])
    {
        parent::__construct($message);
    }
}