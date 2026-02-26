<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionCorePublishablePathNotFoundException extends Exception
{
    public function __construct(string $extensionName, string $path, int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct("Publishable path \"{$path}\" not found for extension \"{$extensionName}\".", $code, $previous);
    }
}
