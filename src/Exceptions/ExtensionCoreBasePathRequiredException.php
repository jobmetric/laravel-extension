<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionCoreBasePathRequiredException extends Exception
{
    public function __construct(string $extensionName = '', int $code = 500, ?Throwable $previous = null)
    {
        $message = $extensionName !== ''
            ? "ExtensionCore base path is required for extension \"{$extensionName}\"."
            : 'ExtensionCore base path is required and cannot be empty.';
        parent::__construct($message, $code, $previous);
    }
}
