<?php

namespace JobMetric\Extension\Tests\Unit\Commands;

use JobMetric\Extension\Commands\ExtensionUninstall;
use JobMetric\Extension\Exceptions\ExtensionNotInstalledException;
use JobMetric\Extension\Facades\Extension;
use JobMetric\Extension\Tests\TestCase;
use JobMetric\PackageCore\Output\Response;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Tests for extension:uninstall Artisan command.
 *
 * Mocks Extension facade for uninstall flow to avoid filesystem/base path dependency.
 */
class ExtensionUninstallTest extends TestCase
{
    private const SLIDER_TYPE = 'Module';
    private const SLIDER_NAME = 'Slider';
    private const SLIDER_NAMESPACE = 'App\\Extensions\\Module\\Slider\\Slider';

    /**
     * Run extension:uninstall with given arguments and options; returns exit code.
     *
     * @param array<string, mixed> $input
     *
     * @return int
     */
    private function runExtensionUninstall(array $input): int
    {
        $command = new ExtensionUninstall;
        $command->setLaravel($this->app);

        $inputObj = new ArrayInput($input);
        $inputObj->setInteractive(false);

        return $command->run($inputObj, new NullOutput);
    }

    /**
     * extension:uninstall exits with success when Extension::uninstall returns ok.
     */
    public function test_extension_uninstall_command_succeeds(): void
    {
        Extension::shouldReceive('namespaceFor')
            ->once()
            ->with(self::SLIDER_TYPE, self::SLIDER_NAME)
            ->andReturn(self::SLIDER_NAMESPACE);
        Extension::shouldReceive('uninstall')
            ->once()
            ->with(self::SLIDER_NAMESPACE, false)
            ->andReturn(Response::make(true, 'Extension uninstalled.', null));

        $exitCode = $this->runExtensionUninstall([
            'extension' => self::SLIDER_TYPE,
            'name'      => self::SLIDER_NAME,
        ]);

        $this->assertSame(0, $exitCode);
    }

    /**
     * extension:uninstall calls Extension::uninstall with namespace from Extension::namespaceFor.
     */
    public function test_extension_uninstall_calls_facade_with_correct_namespace(): void
    {
        $namespace = 'App\\Extensions\\Module\\SomeExt\\SomeExt';
        Extension::shouldReceive('namespaceFor')->once()->with('Module', 'SomeExt')->andReturn($namespace);
        Extension::shouldReceive('uninstall')
            ->once()
            ->with($namespace, false)
            ->andReturn(Response::make(true, 'OK', null));

        $exitCode = $this->runExtensionUninstall(['extension' => 'Module', 'name' => 'SomeExt']);

        $this->assertSame(0, $exitCode);
    }

    /**
     * extension:uninstall passes --force-delete-plugin as true to Extension::uninstall.
     */
    public function test_extension_uninstall_passes_force_delete_plugin_option(): void
    {
        Extension::shouldReceive('namespaceFor')
            ->once()
            ->with(self::SLIDER_TYPE, self::SLIDER_NAME)
            ->andReturn(self::SLIDER_NAMESPACE);
        Extension::shouldReceive('uninstall')
            ->once()
            ->with(self::SLIDER_NAMESPACE, true)
            ->andReturn(Response::make(true, 'Uninstalled.', null));

        $exitCode = $this->runExtensionUninstall([
            'extension'             => self::SLIDER_TYPE,
            'name'                  => self::SLIDER_NAME,
            '--force-delete-plugin' => true,
        ]);

        $this->assertSame(0, $exitCode);
    }

    /**
     * extension:uninstall returns exit code 1 when Extension::uninstall throws.
     */
    public function test_extension_uninstall_returns_1_when_exception(): void
    {
        Extension::shouldReceive('namespaceFor')
            ->once()
            ->with(self::SLIDER_TYPE, self::SLIDER_NAME)
            ->andReturn(self::SLIDER_NAMESPACE);
        Extension::shouldReceive('uninstall')
            ->once()
            ->with(self::SLIDER_NAMESPACE, false)
            ->andThrow(new ExtensionNotInstalledException(self::SLIDER_NAME));

        $exitCode = $this->runExtensionUninstall([
            'extension' => self::SLIDER_TYPE,
            'name'      => self::SLIDER_NAME,
        ]);

        $this->assertSame(1, $exitCode);
    }

    /**
     * When only extension type is given (no name), command returns 0 when nothing selected (non-interactive).
     */
    public function test_extension_uninstall_returns_0_when_name_missing_and_nothing_selected(): void
    {
        $exitCode = $this->runExtensionUninstall([
            'extension' => self::SLIDER_TYPE,
        ]);

        $this->assertSame(0, $exitCode);
    }

    /**
     * extension:uninstall normalizes extension and name to StudlyCase before calling facade.
     */
    public function test_extension_uninstall_normalizes_arguments_to_studly(): void
    {
        Extension::shouldReceive('namespaceFor')
            ->once()
            ->with(self::SLIDER_TYPE, self::SLIDER_NAME)
            ->andReturn(self::SLIDER_NAMESPACE);
        Extension::shouldReceive('uninstall')
            ->once()
            ->with(self::SLIDER_NAMESPACE, false)
            ->andReturn(Response::make(true, 'OK', null));

        $exitCode = $this->runExtensionUninstall([
            'extension' => 'module',
            'name'      => 'slider',
        ]);

        $this->assertSame(0, $exitCode);
    }

    /**
     * extension:uninstall returns exit code 1 when Extension::uninstall returns ok false.
     */
    public function test_extension_uninstall_returns_1_when_uninstall_returns_failure(): void
    {
        Extension::shouldReceive('namespaceFor')
            ->once()
            ->with(self::SLIDER_TYPE, self::SLIDER_NAME)
            ->andReturn(self::SLIDER_NAMESPACE);
        Extension::shouldReceive('uninstall')
            ->once()
            ->with(self::SLIDER_NAMESPACE, false)
            ->andReturn(Response::make(false, 'Uninstall failed.', null));

        $exitCode = $this->runExtensionUninstall([
            'extension' => self::SLIDER_TYPE,
            'name'      => self::SLIDER_NAME,
        ]);

        $this->assertSame(1, $exitCode);
    }

    /**
     * extension:uninstall without --force-delete-plugin passes false as second argument to uninstall.
     */
    public function test_extension_uninstall_without_force_option_passes_false(): void
    {
        Extension::shouldReceive('namespaceFor')
            ->once()
            ->with('Module', 'Foo')
            ->andReturn('App\\Extensions\\Module\\Foo\\Foo');
        Extension::shouldReceive('uninstall')
            ->once()
            ->with('App\\Extensions\\Module\\Foo\\Foo', false)
            ->andReturn(Response::make(true, 'OK', null));

        $exitCode = $this->runExtensionUninstall(['extension' => 'Module', 'name' => 'Foo']);

        $this->assertSame(0, $exitCode);
    }
}
