<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionCoreViewFolderNotFoundException extends Exception
{
    public function __construct(string $extensionName, int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct("Resources view folder not found in extension \"{$extensionName}\".", $code, $previous);
    }
}
