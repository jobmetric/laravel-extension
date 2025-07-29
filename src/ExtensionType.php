<?php

namespace JobMetric\Extension;

use Illuminate\Support\Traits\Macroable;
use JobMetric\Typeify\BaseType;
use JobMetric\Typeify\Traits\HasDriverNamespaceType;
use JobMetric\Typeify\Traits\List\ShowDescriptionInListType;

class ExtensionType extends BaseType
{
    use Macroable,
        ShowDescriptionInListType,
        HasDriverNamespaceType;

    protected function typeName(): string
    {
        return 'extension-type';
    }

    protected function namespaceDriver(): string
    {
        return 'extensions';
    }
}
