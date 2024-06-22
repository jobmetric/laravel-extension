<?php

namespace JobMetric\Extension;

use Illuminate\Contracts\Foundation\Application;

class Plugin
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Create a new Setting instance.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Add plugin
     *
     * @param string $extension
     * @param string $name
     * @param array $options
     *
     * @return void
     */
    public function add(string $extension, string $name, array $options): void
    {
    }

    /**
     * Edit plugin
     *
     * @param int $plugin_id
     *
     * @return void
     */
    public function edit(int $plugin_id): void
    {
    }

    /**
     * Delete plugin
     *
     * @param int $plugin_id
     *
     * @return void
     */
    public function delete(int $plugin_id): void
    {
    }
}
