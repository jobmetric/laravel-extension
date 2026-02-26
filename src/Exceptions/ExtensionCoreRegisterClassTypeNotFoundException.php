<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionCoreRegisterClassTypeNotFoundException extends Exception
{
    public function __construct(string $type, int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct("Register class type \"{$type}\" is not valid for extension.", $code, $previous);
    }
}
