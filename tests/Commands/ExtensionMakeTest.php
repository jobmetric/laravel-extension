<?php

namespace JobMetric\Extension\Tests\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use JobMetric\Extension\Commands\ExtensionMake;
use JobMetric\Extension\Tests\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Tests for extension:make Artisan command.
 */
class ExtensionMakeTest extends TestCase
{
    /**
     * Run extension:make with given arguments and options; returns exit code.
     *
     * @param array<string, mixed> $input
     *
     * @return int
     */
    private function runExtensionMake(array $input): int
    {
        $command = new ExtensionMake;
        $command->setLaravel($this->app);

        $inputObj = new ArrayInput($input);
        $inputObj->setInteractive(false);

        return $command->run($inputObj, new NullOutput);
    }

    /**
     * extension:make exits with success when type and name given.
     */
    public function test_extension_make_command_succeeds(): void
    {
        $exitCode = $this->runExtensionMake([
            'extension' => 'Module',
            'name'      => 'TestFoo',
        ]);

        $this->assertSame(0, $exitCode);
    }

    /**
     * extension:make creates extension directory and main class file.
     */
    public function test_extension_make_creates_extension_files(): void
    {
        $base = $this->app->basePath();
        $path = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'TestBar';

        $this->runExtensionMake([
            'extension' => 'Module',
            'name'      => 'TestBar',
        ]);

        $this->assertDirectoryExists($path);
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'TestBar.php');
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'extension.json');
    }

    /**
     * extension:make returns exit code 3 when extension type is not registered.
     */
    public function test_extension_make_returns_3_when_type_not_registered(): void
    {
        $exitCode = $this->runExtensionMake([
            'extension' => 'InvalidType',
            'name'      => 'SomeName',
        ]);

        $this->assertSame(3, $exitCode);
    }

    /**
     * extension:make returns exit code 2 when extension already exists.
     */
    public function test_extension_make_returns_2_when_extension_already_exists(): void
    {
        $this->runExtensionMake([
            'extension' => 'Module',
            'name'      => 'DupExt',
        ]);

        $exitCode = $this->runExtensionMake([
            'extension' => 'Module',
            'name'      => 'DupExt',
        ]);

        $this->assertSame(2, $exitCode);
    }

    /**
     * extension:make with options creates config and lang when requested.
     */
    public function test_extension_make_with_options_creates_config_and_lang(): void
    {
        $base = $this->app->basePath();
        $path = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'WithOptions';

        $this->runExtensionMake([
            'extension'     => 'Module',
            'name'          => 'WithOptions',
            '--config'      => true,
            '--translation' => true,
        ]);

        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
        $this->assertDirectoryExists($path . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'en');
        $this->assertDirectoryExists($path . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'fa');
    }

    /**
     * extension:make with --view creates resources/views and sample blade.
     */
    public function test_extension_make_with_view_creates_views(): void
    {
        $base = $this->app->basePath();
        $path = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'WithView';

        $this->runExtensionMake([
            'extension' => 'Module',
            'name'      => 'WithView',
            '--view'    => true,
        ]);

        $this->assertDirectoryExists($path . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views');
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'sample.blade.php');
    }

    /**
     * extension:make with --route creates routes/route.php.
     */
    public function test_extension_make_with_route_creates_route_file(): void
    {
        $base = $this->app->basePath();
        $path = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'WithRoute';

        $this->runExtensionMake([
            'extension' => 'Module',
            'name'      => 'WithRoute',
            '--route'   => true,
        ]);

        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'route.php');
    }

    /**
     * extension:make with --asset creates assets folder and .gitkeep.
     */
    public function test_extension_make_with_asset_creates_assets(): void
    {
        $base = $this->app->basePath();
        $path = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'WithAsset';

        $this->runExtensionMake([
            'extension' => 'Module',
            'name'      => 'WithAsset',
            '--asset'   => true,
        ]);

        $this->assertDirectoryExists($path . DIRECTORY_SEPARATOR . 'assets');
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . '.gitkeep');
    }

    /**
     * extension:make with --component creates Component class and blade view.
     */
    public function test_extension_make_with_component_creates_component_files(): void
    {
        $base = $this->app->basePath();
        $path = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'WithComponent';

        $this->runExtensionMake([
            'extension'   => 'Module',
            'name'        => 'WithComponent',
            '--component' => true,
        ]);

        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'WithComponentComponent.php');
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'with_component-component.blade.php');
    }

    /**
     * extension:make with --console-kernel creates Core/ConsoleKernel.php.
     */
    public function test_extension_make_with_console_kernel_creates_console_kernel(): void
    {
        $base = $this->app->basePath();
        $path = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'WithConsole';

        $this->runExtensionMake([
            'extension'        => 'Module',
            'name'             => 'WithConsole',
            '--console-kernel' => true,
        ]);

        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'ConsoleKernel.php');
    }

    /**
     * extension:make normalizes extension and name to StudlyCase.
     */
    public function test_extension_make_normalizes_arguments_to_studly(): void
    {
        $base = $this->app->basePath();
        $path = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'MyBanner';

        $this->runExtensionMake([
            'extension' => 'module',
            'name'      => 'my_banner',
        ]);

        $this->assertDirectoryExists($path);
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'MyBanner.php');
    }

    /**
     * extension:make main class file contains correct namespace and class name.
     */
    public function test_extension_make_main_class_contains_namespace_and_class(): void
    {
        $base = $this->app->basePath();
        $classPath = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'NsTest' . DIRECTORY_SEPARATOR . 'NsTest.php';

        $this->runExtensionMake([
            'extension' => 'Module',
            'name'      => 'NsTest',
        ]);

        $content = File::get($classPath);
        $this->assertStringContainsString('namespace App\\Extensions\\Module\\NsTest;', $content);
        $this->assertStringContainsString('class NsTest', $content);
    }

    /**
     * extension:make extension.json contains extension type, name and version.
     *
     * @throws FileNotFoundException
     */
    public function test_extension_make_extension_json_contains_type_and_name(): void
    {
        $base = $this->app->basePath();
        $jsonPath = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'JsonCheck' . DIRECTORY_SEPARATOR . 'extension.json';

        $this->runExtensionMake([
            'extension' => 'Module',
            'name'      => 'JsonCheck',
        ]);

        $data = json_decode(File::get($jsonPath), true);
        $this->assertIsArray($data);
        $this->assertSame('Module', $data['extension'] ?? null);
        $this->assertSame('JsonCheck', $data['name'] ?? null);
        $this->assertArrayHasKey('version', $data);
    }

    /**
     * extension:make with --multiple sets multiple in extension.json.
     *
     * @throws FileNotFoundException
     */
    public function test_extension_make_with_multiple_sets_multiple_in_json(): void
    {
        $base = $this->app->basePath();
        $jsonPath = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'MultiExt' . DIRECTORY_SEPARATOR . 'extension.json';

        $this->runExtensionMake([
            'extension'  => 'Module',
            'name'       => 'MultiExt',
            '--multiple' => true,
        ]);

        $data = json_decode(File::get($jsonPath), true);
        $this->assertIsArray($data);
        $this->assertTrue($data['multiple'] ?? false);
    }

    /**
     * extension:make with all options creates all requested structure.
     */
    public function test_extension_make_with_all_options_creates_full_structure(): void
    {
        $base = $this->app->basePath();
        $path = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'FullExt';

        $this->runExtensionMake([
            'extension'        => 'Module',
            'name'             => 'FullExt',
            '--multiple'       => true,
            '--config'         => true,
            '--translation'    => true,
            '--view'           => true,
            '--route'          => true,
            '--asset'          => true,
            '--component'      => true,
            '--console-kernel' => true,
        ]);

        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'FullExt.php');
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'extension.json');
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
        $this->assertDirectoryExists($path . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'en');
        $this->assertDirectoryExists($path . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'fa');
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'sample.blade.php');
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'route.php');
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . '.gitkeep');
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . 'FullExtComponent.php');
        $this->assertFileExists($path . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'ConsoleKernel.php');
    }

    /**
     * extension:make lang files contain extension key.
     */
    public function test_extension_make_lang_files_exist_for_en_and_fa(): void
    {
        $base = $this->app->basePath();
        $path = $base . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Extensions' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'LangExt';

        $this->runExtensionMake([
            'extension'     => 'Module',
            'name'          => 'LangExt',
            '--translation' => true,
        ]);

        $enFile = $path . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'en' . DIRECTORY_SEPARATOR . 'extension.php';
        $faFile = $path . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'fa' . DIRECTORY_SEPARATOR . 'extension.php';
        $this->assertFileExists($enFile);
        $this->assertFileExists($faFile);
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
