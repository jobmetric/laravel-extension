<?php

namespace JobMetric\Barcode\Exceptions;

use Exception;
use Throwable;

class ExtensionAlreadyInstalledException extends Exception
{
    public function __construct(string $extension, string $name, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('extension::base.exceptions.extension_already_installed', [
            'extension' => $extension,
            'name' => $name
        ]), $code, $previous);
    }
}
