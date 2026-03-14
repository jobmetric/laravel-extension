<?php

namespace JobMetric\Extension\Tests\Feature\Commands;

use Illuminate\Support\Facades\File;
use JobMetric\Extension\Commands\ExtensionMake;
use JobMetric\Extension\Commands\ExtensionMakeTools;
use JobMetric\Extension\Tests\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Tests for extension:make-tools Artisan command.
 */
class ExtensionMakeToolsTest extends TestCase
{
    /**
     * Run extension:make with given arguments; returns exit code.
     *
     * @param array<string, mixed> $input
     *
     * @return void
     */
    private function runExtensionMake(array $input): void
    {
        $command = new ExtensionMake;
        $command->setLaravel($this->app);

        $inputObj = new ArrayInput($input);
        $inputObj->setInteractive(false);

        $command->run($inputObj, new NullOutput);
    }

    /**
     * Run extension:make-tools with given arguments; returns exit code.
     *
     * @param array<string, mixed> $input
     *
     * @return int
     */
    private function runExtensionMakeTools(array $input): int
    {
        $command = new ExtensionMakeTools;
        $command->setLaravel($this->app);

        $inputObj = new ArrayInput($input);
        $inputObj->setInteractive(false);

        return $command->run($inputObj, new NullOutput);
    }

    /**
     * Create an extension Module/ToolsTestExt for use in make-tools tests.
     *
     * @return string base path of the extension
     */
    private function createTestExtension(): string
    {
        $this->runExtensionMake([
            'extension' => 'Module',
            'name'      => 'ToolsTestExt',
        ]);
        $base = $this->app->basePath();

        return $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'ToolsTestExt';
    }

    /**
     * extension:make-tools exits with success when extension exists and tool/target given.
     */
    public function test_extension_make_tools_command_succeeds(): void
    {
        $this->createTestExtension();

        $exitCode = $this->runExtensionMakeTools([
            'extension' => 'Module',
            'name'      => 'ToolsTestExt',
            'tool'      => 'model',
            'target'    => 'Post',
        ]);

        $this->assertSame(0, $exitCode);
    }

    /**
     * extension:make-tools creates model file in Core/Models when tool is model.
     */
    public function test_extension_make_tools_model_creates_model_file(): void
    {
        $extPath = $this->createTestExtension();

        $this->runExtensionMakeTools([
            'extension' => 'Module',
            'name'      => 'ToolsTestExt',
            'tool'      => 'model',
            'target'    => 'Item',
        ]);

        $this->assertFileExists($extPath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Item.php');
    }

    /**
     * extension:make-tools returns exit code 2 when extension does not exist.
     */
    public function test_extension_make_tools_returns_2_when_extension_does_not_exist(): void
    {
        $exitCode = $this->runExtensionMakeTools([
            'extension' => 'Module',
            'name'      => 'NonExistentExt',
            'tool'      => 'model',
            'target'    => 'Post',
        ]);

        $this->assertSame(2, $exitCode);
    }

    /**
     * extension:make-tools returns exit code 3 when tool is unknown.
     */
    public function test_extension_make_tools_returns_3_when_tool_unknown(): void
    {
        $this->createTestExtension();

        $exitCode = $this->runExtensionMakeTools([
            'extension' => 'Module',
            'name'      => 'ToolsTestExt',
            'tool'      => 'unknown_tool',
            'target'    => 'Something',
        ]);

        $this->assertSame(3, $exitCode);
    }

    /**
     * extension:make-tools returns exit code 1 when tool or target is missing (non-interactive).
     */
    public function test_extension_make_tools_returns_1_when_tool_or_target_missing(): void
    {
        $this->createTestExtension();

        $exitCode = $this->runExtensionMakeTools([
            'extension' => 'Module',
            'name'      => 'ToolsTestExt',
            'tool'      => 'model',
        ]);

        $this->assertSame(1, $exitCode);
    }

    /**
     * extension:make-tools creates controller in Core/Http/Controllers when tool is controller.
     */
    public function test_extension_make_tools_controller_creates_controller_file(): void
    {
        $extPath = $this->createTestExtension();

        $this->runExtensionMakeTools([
            'extension' => 'Module',
            'name'      => 'ToolsTestExt',
            'tool'      => 'controller',
            'target'    => 'PageController',
        ]);

        $this->assertFileExists($extPath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'PageController.php');
    }

    /**
     * extension:make-tools creates migration file when tool is migration.
     */
    public function test_extension_make_tools_migration_creates_migration_file(): void
    {
        $extPath = $this->createTestExtension();
        $migrationsPath = $extPath . DIRECTORY_SEPARATOR . 'migrations';

        $this->runExtensionMakeTools([
            'extension' => 'Module',
            'name'      => 'ToolsTestExt',
            'tool'      => 'migration',
            'target'    => 'create_products_table',
        ]);

        $this->assertDirectoryExists($migrationsPath);
        $files = File::glob($migrationsPath . DIRECTORY_SEPARATOR . '*create_products_table*.php');
        $this->assertNotEmpty($files);
    }

    /**
     * extension:make-tools creates command in Core/Console/Commands when tool is command.
     */
    public function test_extension_make_tools_command_creates_command_file(): void
    {
        $extPath = $this->createTestExtension();

        $this->runExtensionMakeTools([
            'extension' => 'Module',
            'name'      => 'ToolsTestExt',
            'tool'      => 'command',
            'target'    => 'SyncData',
        ]);

        $this->assertFileExists($extPath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Console' . DIRECTORY_SEPARATOR . 'Commands' . DIRECTORY_SEPARATOR . 'SyncData.php');
    }

    /**
     * extension:make-tools creates exception in Core/Exceptions when tool is exception.
     */
    public function test_extension_make_tools_exception_creates_exception_file(): void
    {
        $extPath = $this->createTestExtension();

        $this->runExtensionMakeTools([
            'extension' => 'Module',
            'name'      => 'ToolsTestExt',
            'tool'      => 'exception',
            'target'    => 'InvalidState',
        ]);

        $this->assertFileExists($extPath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Exceptions' . DIRECTORY_SEPARATOR . 'InvalidStateException.php');
    }

    /**
     * extension:make-tools creates request in Core/Http/Requests when tool is request.
     */
    public function test_extension_make_tools_request_creates_request_file(): void
    {
        $extPath = $this->createTestExtension();

        $this->runExtensionMakeTools([
            'extension' => 'Module',
            'name'      => 'ToolsTestExt',
            'tool'      => 'request',
            'target'    => 'StoreItem',
        ]);

        $this->assertFileExists($extPath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Requests' . DIRECTORY_SEPARATOR . 'StoreItemRequest.php');
    }

    /**
     * extension:make-tools normalizes extension and name to StudlyCase.
     */
    public function test_extension_make_tools_normalizes_extension_and_name_to_studly(): void
    {
        $this->runExtensionMake([
            'extension' => 'Module',
            'name'      => 'StudlyExt',
        ]);

        $exitCode = $this->runExtensionMakeTools([
            'extension' => 'module',
            'name'      => 'studly_ext',
            'tool'      => 'model',
            'target'    => 'Tag',
        ]);

        $this->assertSame(0, $exitCode);
        $base = $this->app->basePath();
        $path = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'StudlyExt';
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Tag.php');
    }

    /**
     * extension:make-tools model file contains correct namespace.
     */
    public function test_extension_make_tools_model_contains_correct_namespace(): void
    {
        $extPath = $this->createTestExtension();
        $modelPath = $extPath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'Order.php';

        $this->runExtensionMakeTools([
            'extension' => 'Module',
            'name'      => 'ToolsTestExt',
            'tool'      => 'model',
            'target'    => 'Order',
        ]);

        $content = File::get($modelPath);
        $this->assertStringContainsString('namespace App\\Extensions\\Module\\ToolsTestExt\\Core\\Models;', $content);
        $this->assertStringContainsString('class Order', $content);
    }

    /**
     * extension:make-tools returns exit code 1 when target is empty (non-interactive).
     */
    public function test_extension_make_tools_returns_1_when_target_empty(): void
    {
        $this->createTestExtension();

        $exitCode = $this->runExtensionMakeTools([
            'extension' => 'Module',
            'name'      => 'ToolsTestExt',
            'tool'      => 'model',
            'target'    => '   ',
        ]);

        $this->assertSame(1, $exitCode);
    }

    /**
     * extension:make-tools creates event in Core/Events when tool is event.
     */
    public function test_extension_make_tools_event_creates_event_file(): void
    {
        $extPath = $this->createTestExtension();

        $this->runExtensionMakeTools([
            'extension' => 'Module',
            'name'      => 'ToolsTestExt',
            'tool'      => 'event',
            'target'    => 'OrderShipped',
        ]);

        $this->assertFileExists($extPath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Events' . DIRECTORY_SEPARATOR . 'OrderShipped.php');
    }

    /**
     * extension:make-tools creates factory in Core/Factories when tool is factory.
     */
    public function test_extension_make_tools_factory_creates_factory_file(): void
    {
        $extPath = $this->createTestExtension();

        $this->runExtensionMakeTools([
            'extension' => 'Module',
            'name'      => 'ToolsTestExt',
            'tool'      => 'factory',
            'target'    => 'Product',
        ]);

        $this->assertFileExists($extPath . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Factories' . DIRECTORY_SEPARATOR . 'ProductFactory.php');
    }

    protected function tearDown(): void
    {
        if (isset($this->app) && $this->app !== null) {
            $base = $this->app->basePath();
            if (is_string($base) && str_contains($base, 'extension-pkg-test-')) {
                File::deleteDirectory($base);
            }
        }
        parent::tearDown();
    }
}
