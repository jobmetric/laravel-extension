<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionCoreCommandClassNotFoundException extends Exception
{
    public function __construct(string $extensionName, string $class, int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct("Command class \"{$class}\" not found for extension \"{$extensionName}\".", $code, $previous);
    }
}
