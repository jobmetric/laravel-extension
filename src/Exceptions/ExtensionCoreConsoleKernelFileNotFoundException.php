<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionCoreConsoleKernelFileNotFoundException extends Exception
{
    public function __construct(string $extensionName, int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct("ConsoleKernel.php file not found in extension \"{$extensionName}\".", $code, $previous);
    }
}
