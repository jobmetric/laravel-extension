<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionCoreRouteFileNotFoundException extends Exception
{
    public function __construct(string $extensionName, int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct(
            "Route file (routes/route.php) not found in extension \"{$extensionName}\".",
            $code,
            $previous
        );
    }
}
