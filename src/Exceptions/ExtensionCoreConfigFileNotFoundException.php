<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionCoreConfigFileNotFoundException extends Exception
{
    public function __construct(string $extensionName, int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct(
            "Config file (config.php) not found in extension \"{$extensionName}\".",
            $code,
            $previous
        );
    }
}
