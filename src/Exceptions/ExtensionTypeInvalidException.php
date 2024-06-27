<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionTypeInvalidException extends Exception
{
    public function __construct(string $extension, string $name, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('extension::base.exceptions.extension_type_invalid', [
            'extension' => $extension,
            'name' => $name
        ]), $code, $previous);
    }
}
