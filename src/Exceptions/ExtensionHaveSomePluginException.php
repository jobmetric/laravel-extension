<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionHaveSomePluginException extends Exception
{
    public function __construct(string $extension, string $name, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('extension::base.exceptions.extension_have_some_plugin', [
            'extension' => $extension,
            'name' => $name
        ]), $code, $previous);
    }
}
