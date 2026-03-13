<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

/**
 * Thrown when delete is attempted on an extension that is provided by a Composer package (not under App\Extensions).
 */
class ExtensionFromPackageNotDeletableException extends Exception
{
    public function __construct(string $name, int $code = 403, ?Throwable $previous = null)
    {
        parent::__construct(trans('extension::base.exceptions.extension_from_package_not_deletable', [
            'name' => $name,
        ]), $code, $previous);
    }
}
