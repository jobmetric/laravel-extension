<?php

namespace JobMetric\Extension;

use Illuminate\Support\Collection;

/**
 * Trait DriverNamespaceServiceType
 *
 * @package JobMetric\Extension
 */
trait DriverNamespaceServiceType
{
    /**
     * The driver namespace.
     *
     * @var array $driverNamespace
     */
    protected array $driverNamespace = [];

    /**
     * Set Driver Namespace
     *
     * @param array $driverNamespace
     *
     * @return static
     */
    public function driverNamespace(array $driverNamespace): static
    {
        $this->driverNamespace[$this->type] = array_merge($this->driverNamespace[$this->type] ?? [], $driverNamespace);

        $this->setTypeParam('driverNamespace', $this->driverNamespace);

        return $this;
    }

    /**
     * Get driver namespace.
     *
     * @return Collection
     */
    public function getDriverNamespace(): Collection
    {
        $driverNamespace = $this->getTypeParam('driverNamespace', []);

        return collect($driverNamespace[$this->type] ?? []);
    }
}
