<?php

namespace App\Exceptions;

use JetBrains\PhpStorm\Pure;
use Throwable;

class InvalidSizeException extends \Exception
{
    #[Pure]
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}