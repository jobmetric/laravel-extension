<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class PluginNotMultipleException extends Exception
{
    public function __construct(string $extension, string $name, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('extension::base.exceptions.plugin_not_multiple', [
            'extension' => $extension,
            'name' => $name
        ]), $code, $previous);
    }
}
