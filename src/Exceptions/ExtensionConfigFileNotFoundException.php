<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionConfigFileNotFoundException extends Exception
{
    public function __construct(string $name, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('extension::base.exceptions.extension_config_file_not_found', [
            'name' => $name
        ]), $code, $previous);
    }
}
