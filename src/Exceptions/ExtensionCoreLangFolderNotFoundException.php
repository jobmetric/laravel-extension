<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionCoreLangFolderNotFoundException extends Exception
{
    public function __construct(string $extensionName, int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct("Lang folder not found in extension \"{$extensionName}\".", $code, $previous);
    }
}
