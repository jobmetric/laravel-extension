<?php

namespace JobMetric\Extension\Tests\Unit;

use JobMetric\Extension\Facades\Extension;
use JobMetric\Extension\Tests\TestCase;
use JobMetric\PackageCore\Output\Response;
use Throwable;

/**
 * Unit tests for package helpers (extension_install, extension_uninstall) with mocked Extension.
 */
class HelpersTest extends TestCase
{
    /**
     * extension_install calls namespaceFor and install and returns response.
     *
     * @throws Throwable
     */
    public function test_extension_install_calls_facade_and_returns_response(): void
    {
        $response = Response::make(true, 'Installed', null);
        Extension::shouldReceive('namespaceFor')
            ->once()
            ->with('Module', 'Banner')
            ->andReturn('App\\Extensions\\Module\\Banner\\Banner');
        Extension::shouldReceive('install')
            ->once()
            ->with('App\\Extensions\\Module\\Banner\\Banner')
            ->andReturn($response);

        $result = extension_install('Module', 'Banner');

        $this->assertSame($response, $result);
    }

    /**
     * extension_uninstall with two args calls uninstall with force_delete_plugin false.
     *
     * @throws Throwable
     */
    public function test_extension_uninstall_calls_facade_with_force_delete_plugin_false(): void
    {
        $response = Response::make(true, 'Uninstalled', null);
        Extension::shouldReceive('namespaceFor')
            ->once()
            ->with('Module', 'Slider')
            ->andReturn('App\\Extensions\\Module\\Slider\\Slider');
        Extension::shouldReceive('uninstall')
            ->once()
            ->with('App\\Extensions\\Module\\Slider\\Slider', false)
            ->andReturn($response);

        $result = extension_uninstall('Module', 'Slider');

        $this->assertSame($response, $result);
    }

    /**
     * extension_uninstall with true third arg passes force_delete_plugin true.
     *
     * @throws Throwable
     */
    public function test_extension_uninstall_passes_force_delete_plugin_true(): void
    {
        $response = Response::make(true, 'Uninstalled', null);
        Extension::shouldReceive('namespaceFor')
            ->once()
            ->with('Module', 'Banner')
            ->andReturn('App\\Extensions\\Module\\Banner\\Banner');
        Extension::shouldReceive('uninstall')
            ->once()
            ->with('App\\Extensions\\Module\\Banner\\Banner', true)
            ->andReturn($response);

        $result = extension_uninstall('Module', 'Banner', true);

        $this->assertSame($response, $result);
    }
}
