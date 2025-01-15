<?php

namespace JobMetric\Extension;

use Illuminate\Support\Traits\Macroable;
use JobMetric\PackageCore\Services\BaseServiceType;
use JobMetric\PackageCore\Services\InformationServiceType;
use JobMetric\PackageCore\Services\ListShowDescriptionServiceType;
use JobMetric\PackageCore\Services\ServiceType;

class ExtensionType extends ServiceType
{
    use Macroable,
        BaseServiceType,
        InformationServiceType,
        ListShowDescriptionServiceType,
        DriverNamespaceServiceType;

    protected function serviceType(): string
    {
        return 'ExtensionServiceType';
    }
}
