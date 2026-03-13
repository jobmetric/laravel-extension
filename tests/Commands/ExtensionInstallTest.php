<?php

namespace JobMetric\Extension\Tests\Commands;

use JobMetric\Extension\Commands\ExtensionInstall;
use JobMetric\Extension\Exceptions\ExtensionAlreadyInstalledException;
use JobMetric\Extension\Facades\Extension;
use JobMetric\Extension\Tests\TestCase;
use JobMetric\PackageCore\Output\Response;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Tests for extension:install Artisan command.
 *
 * Mocks Extension facade for install flow to avoid filesystem/base path dependency.
 */
class ExtensionInstallTest extends TestCase
{
    private const SLIDER_TYPE = 'Module';
    private const SLIDER_NAME = 'Slider';
    private const SLIDER_NAMESPACE = 'App\\Extensions\\Module\\Slider\\Slider';

    /**
     * Run extension:install with given arguments; returns exit code.
     *
     * @param array<string, mixed> $input
     *
     * @return int
     */
    private function runExtensionInstall(array $input): int
    {
        $command = new ExtensionInstall;
        $command->setLaravel($this->app);

        $inputObj = new ArrayInput($input);
        $inputObj->setInteractive(false);

        return $command->run($inputObj, new NullOutput);
    }

    /**
     * extension:install exits with success when Extension::install returns ok.
     */
    public function test_extension_install_command_succeeds(): void
    {
        Extension::shouldReceive('namespaceFor')
            ->once()
            ->with(self::SLIDER_TYPE, self::SLIDER_NAME)
            ->andReturn(self::SLIDER_NAMESPACE);
        Extension::shouldReceive('install')
            ->once()
            ->with(self::SLIDER_NAMESPACE)
            ->andReturn(Response::make(true, 'Extension installed.', null));

        $exitCode = $this->runExtensionInstall([
            'extension' => self::SLIDER_TYPE,
            'name'      => self::SLIDER_NAME,
        ]);

        $this->assertSame(0, $exitCode);
    }

    /**
     * extension:install calls Extension::install with namespace from Extension::namespaceFor.
     */
    public function test_extension_install_calls_facade_with_correct_namespace(): void
    {
        $namespace = 'App\\Extensions\\Module\\SomeExt\\SomeExt';
        Extension::shouldReceive('namespaceFor')->once()->with('Module', 'SomeExt')->andReturn($namespace);
        Extension::shouldReceive('install')->once()->with($namespace)->andReturn(Response::make(true, 'OK', null));

        $exitCode = $this->runExtensionInstall(['extension' => 'Module', 'name' => 'SomeExt']);

        $this->assertSame(0, $exitCode);
    }

    /**
     * extension:install returns exit code 1 when Extension::install throws (e.g. already installed).
     */
    public function test_extension_install_returns_1_when_already_installed(): void
    {
        Extension::shouldReceive('namespaceFor')
            ->once()
            ->with(self::SLIDER_TYPE, self::SLIDER_NAME)
            ->andReturn(self::SLIDER_NAMESPACE);
        Extension::shouldReceive('install')
            ->once()
            ->with(self::SLIDER_NAMESPACE)
            ->andThrow(new ExtensionAlreadyInstalledException(self::SLIDER_NAME));

        $exitCode = $this->runExtensionInstall([
            'extension' => self::SLIDER_TYPE,
            'name'      => self::SLIDER_NAME,
        ]);

        $this->assertSame(1, $exitCode);
    }

    /**
     * When only extension type is given (no name), command returns 0 when nothing selected (non-interactive).
     */
    public function test_extension_install_returns_0_when_name_missing_and_nothing_selected(): void
    {
        $exitCode = $this->runExtensionInstall([
            'extension' => self::SLIDER_TYPE,
        ]);

        $this->assertSame(0, $exitCode);
    }

    /**
     * extension:install returns exit code 1 when extension does not exist (install fails).
     */
    public function test_extension_install_returns_1_when_extension_not_found(): void
    {
        Extension::shouldReceive('namespaceFor')
            ->once()
            ->with('Module', 'NonExistentExtension')
            ->andReturn('App\\Extensions\\Module\\NonExistentExtension\\NonExistentExtension');
        Extension::shouldReceive('install')->once()->andThrow(new RuntimeException('Extension folder not found.'));

        $exitCode = $this->runExtensionInstall([
            'extension' => 'Module',
            'name'      => 'NonExistentExtension',
        ]);

        $this->assertSame(1, $exitCode);
    }

    /**
     * extension:install normalizes extension and name to StudlyCase before calling facade.
     */
    public function test_extension_install_normalizes_arguments_to_studly(): void
    {
        Extension::shouldReceive('namespaceFor')
            ->once()
            ->with(self::SLIDER_TYPE, self::SLIDER_NAME)
            ->andReturn(self::SLIDER_NAMESPACE);
        Extension::shouldReceive('install')
            ->once()
            ->with(self::SLIDER_NAMESPACE)
            ->andReturn(Response::make(true, 'OK', null));

        $exitCode = $this->runExtensionInstall([
            'extension' => 'module',
            'name'      => 'slider',
        ]);

        $this->assertSame(0, $exitCode);
    }

    /**
     * extension:install returns exit code 1 when Extension::install returns ok false.
     */
    public function test_extension_install_returns_1_when_install_returns_failure(): void
    {
        Extension::shouldReceive('namespaceFor')
            ->once()
            ->with(self::SLIDER_TYPE, self::SLIDER_NAME)
            ->andReturn(self::SLIDER_NAMESPACE);
        Extension::shouldReceive('install')
            ->once()
            ->with(self::SLIDER_NAMESPACE)
            ->andReturn(Response::make(false, 'Install failed.', null));

        $exitCode = $this->runExtensionInstall([
            'extension' => self::SLIDER_TYPE,
            'name'      => self::SLIDER_NAME,
        ]);

        $this->assertSame(1, $exitCode);
    }
}
