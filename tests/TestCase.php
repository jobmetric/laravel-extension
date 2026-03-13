<?php

namespace JobMetric\Extension\Tests;

use Illuminate\Foundation\Application;
use JobMetric\Extension\ExtensionServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * Base test case for Extension package (self-contained like Flow).
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @param Application $app
     *
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ExtensionServiceProvider::class,
        ];
    }

    /**
     * @param Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('extension.types', [
            'Module' => [
                'label'       => 'extension::base.types.module.label',
                'description' => 'extension::base.types.module.description',
            ],
        ]);

        $base = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'extension-pkg-test-' . uniqid('', true);
        if (! is_dir($base)) {
            mkdir($base, 0755, true);
        }
        if (! is_dir($base . DIRECTORY_SEPARATOR . 'app')) {
            mkdir($base . DIRECTORY_SEPARATOR . 'app', 0755, true);
        }
        $app->setBasePath($base);

        $app->booting(function () use ($app): void {
            $migrationsPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
            if (is_dir($migrationsPath)) {
                loadMigrationPath($migrationsPath);
            }
        });
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
    }
}
