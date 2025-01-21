<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionDontHaveContractException extends Exception
{
    public function __construct(string $name, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('extension::base.exceptions.extension_dont_have_contract', [
            'name' => $name
        ]), $code, $previous);
    }
}
