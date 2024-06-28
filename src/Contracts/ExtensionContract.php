<?php

namespace JobMetric\Extension\Contracts;

interface ExtensionContract
{
    /**
     * Handle the extension.
     *
     * @param array $options
     *
     * @return string|null
     */
    public function handle(array $options = []): ?string;
}
