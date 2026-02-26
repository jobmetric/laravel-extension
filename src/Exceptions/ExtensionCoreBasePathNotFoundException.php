<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionCoreBasePathNotFoundException extends Exception
{
    public function __construct(string $basePath, string $extensionName = '', int $code = 500, ?Throwable $previous = null)
    {
        $message = $extensionName !== ''
            ? "ExtensionCore base path does not exist or is not a directory: \"{$basePath}\" (extension: \"{$extensionName}\")."
            : "ExtensionCore base path does not exist or is not a directory: \"{$basePath}\".";
        parent::__construct($message, $code, $previous);
    }
}
