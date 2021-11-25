<?php

namespace Divido\Exceptions;

use Divido\ApiExceptions\AbstractException;

class ApplicationApiException extends AbstractException
{
    final function __construct($message, $sixDigitCode, $context = null, $previous = null)
    {
        parent::__construct(
            $message,
            $sixDigitCode,
            $context,
            $previous
        );
    }
}
