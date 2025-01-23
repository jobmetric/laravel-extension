<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionNotDeletableException extends Exception
{
    public function __construct(string $name, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('extension::base.exceptions.extension_not_deletable', [
            'name' => $name
        ]), $code, $previous);
    }
}
