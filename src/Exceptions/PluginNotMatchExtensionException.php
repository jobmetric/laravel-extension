<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class PluginNotMatchExtensionException extends Exception
{
    public function __construct(int $extension_id, int $plugin_id, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('extension::base.exceptions.plugin_not_match_extension', [
            'extension_id' => $extension_id,
            'plugin_id' => $plugin_id
        ]), $code, $previous);
    }
}
