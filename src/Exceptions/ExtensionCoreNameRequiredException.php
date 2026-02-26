<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionCoreNameRequiredException extends Exception
{
    public function __construct(int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct('ExtensionCore name is required and cannot be empty.', $code, $previous);
    }
}
