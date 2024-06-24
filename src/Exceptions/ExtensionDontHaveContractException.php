<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionDontHaveContractException extends Exception
{
    public function __construct(string $extension, string $name, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('extension::base.exceptions.extension_dont_have_contract', [
            'extension' => $extension,
            'name' => $name
        ]), $code, $previous);
    }
}
