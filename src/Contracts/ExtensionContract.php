<?php

namespace JobMetric\Extension\Contracts;

interface ExtensionContract
{
    /**
     * Handle the extension.
     *
     * @param array $options
     *
     * @return string
     */
    public function handle(array $options = []): string;
}
