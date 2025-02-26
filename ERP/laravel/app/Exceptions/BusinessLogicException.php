<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception that indicates an error in business logic
 */
class BusinessLogicException extends Exception {
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}