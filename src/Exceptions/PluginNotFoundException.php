<?php

namespace JobMetric\Extension\Exceptions;

use Exception;
use Throwable;

class PluginNotFoundException extends Exception
{
    public function __construct(int $plugin_id, int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('extension::base.exceptions.plugin_not_found', [
            'plugin_id' => $plugin_id
        ]), $code, $previous);
    }
}
