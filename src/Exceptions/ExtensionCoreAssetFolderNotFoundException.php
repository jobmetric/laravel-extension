<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class ExtensionCoreAssetFolderNotFoundException extends Exception
{
    public function __construct(string $extensionName, int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct("Asset folder not found in extension \"{$extensionName}\".", $code, $previous);
    }
}
