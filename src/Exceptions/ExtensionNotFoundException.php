<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionNotFoundException extends Exception
{
    public function __construct(int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('extension::base.exceptions.extension_not_found'), $code, $previous);
    }
}
