<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionClassNameNotMatchException extends Exception
{
    public function __construct(string $name, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('extension::base.exceptions.extension_class_name_not_match', [
            'name' => $name
        ]), $code, $previous);
    }
}
