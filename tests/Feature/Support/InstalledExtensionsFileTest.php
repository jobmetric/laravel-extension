<?php

namespace JobMetric\Extension\Tests\Feature\Support;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\File;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use JobMetric\Extension\Support\InstalledExtensionsFile;
use JobMetric\Extension\Tests\TestCase;
use Mockery;

/**
 * Tests for InstalledExtensionsFile.
 */
class InstalledExtensionsFileTest extends TestCase
{
    private InstalledExtensionsFile $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InstalledExtensionsFile();
    }

    protected function tearDown(): void
    {
        $path = $this->service->path();
        if (File::exists($path)) {
            File::delete($path);
        }
        Mockery::close();
        parent::tearDown();
    }

    /**
     * path returns storage path to installed_extensions.json.
     */
    public function test_path_returns_storage_app_json_path(): void
    {
        $path = $this->service->path();

        $this->assertStringEndsWith('installed_extensions.json', $path);
        $this->assertSame(storage_path('app/installed_extensions.json'), $path);
    }

    /**
     * read returns empty array when file does not exist.
     *
     * @throws FileNotFoundException
     */
    public function test_read_returns_empty_when_file_missing(): void
    {
        $path = $this->service->path();
        if (File::exists($path)) {
            File::delete($path);
        }

        $this->assertSame([], $this->service->read());
    }

    /**
     * read returns empty array when file content is invalid JSON.
     *
     * @throws FileNotFoundException
     */
    public function test_read_returns_empty_when_invalid_json(): void
    {
        $path = $this->service->path();
        $this->ensureStorageAppExists();
        File::put($path, 'not valid json {');

        $this->assertSame([], $this->service->read());
    }

    /**
     * read returns empty array when decoded value is not an array.
     *
     * @throws FileNotFoundException
     */
    public function test_read_returns_empty_when_decoded_not_array(): void
    {
        $path = $this->service->path();
        $this->ensureStorageAppExists();
        File::put($path, '"string"');

        $this->assertSame([], $this->service->read());
    }

    /**
     * read returns list of namespaces from valid file.
     *
     * @throws FileNotFoundException
     */
    public function test_read_returns_namespaces_from_valid_file(): void
    {
        $path = $this->service->path();
        $this->ensureStorageAppExists();
        $data = [
            [
                'namespace' => 'App\\Extensions\\Module\\Banner\\Banner',
                'name'      => 'Banner',
            ],
            [
                'namespace' => 'App\\Extensions\\Module\\Slider\\Slider',
                'name'      => 'Slider',
            ],
        ];
        File::put($path, json_encode($data));

        $this->assertSame([
            'App\\Extensions\\Module\\Banner\\Banner',
            'App\\Extensions\\Module\\Slider\\Slider',
        ], $this->service->read());
    }

    /**
     * read skips items without namespace key.
     *
     * @throws FileNotFoundException
     */
    public function test_read_skips_items_without_namespace(): void
    {
        $path = $this->service->path();
        $this->ensureStorageAppExists();
        $data = [
            [
                'name' => 'Banner',
            ],
            [
                'namespace' => 'App\\Extensions\\Module\\Slider\\Slider',
            ],
        ];
        File::put($path, json_encode($data));

        $this->assertSame(['App\\Extensions\\Module\\Slider\\Slider'], $this->service->read());
    }

    /**
     * read skips items where namespace is not a string.
     */
    public function test_read_skips_items_when_namespace_not_string(): void
    {
        $path = $this->service->path();
        $this->ensureStorageAppExists();
        $data = [
            [
                'namespace' => 123,
            ],
            [
                'namespace' => 'App\\Extensions\\Module\\Slider\\Slider',
            ],
        ];
        File::put($path, json_encode($data));

        $this->assertSame(['App\\Extensions\\Module\\Slider\\Slider'], $this->service->read());
    }

    /**
     * syncFromDatabase does nothing when db is not bound.
     */
    public function test_syncFromDatabase_does_nothing_when_db_not_bound(): void
    {
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('bound')->with('db')->once()->andReturn(false);

        $this->service->syncFromDatabase($app);

        $this->assertFileDoesNotExist($this->service->path());
    }

    /**
     * syncFromDatabase writes file with extension data when db is bound.
     *
     * @throws FileNotFoundException
     */
    public function test_syncFromDatabase_writes_file_when_db_bound(): void
    {
        $this->ensureStorageAppExists();

        ExtensionModel::create([
            'extension' => 'Module',
            'name'      => 'Banner',
            'namespace' => 'App\\Extensions\\Module\\Banner\\Banner',
            'info'      => [
                'version' => '1.0',
                'title'   => 'Banner',
            ],
        ]);

        $this->service->syncFromDatabase($this->app);

        $this->assertFileExists($this->service->path());
        $namespaces = $this->service->read();
        $this->assertContains('App\\Extensions\\Module\\Banner\\Banner', $namespaces);
    }

    /**
     * syncFromDatabase orders by extension then name.
     *
     * @throws FileNotFoundException
     */
    public function test_syncFromDatabase_orders_by_extension_and_name(): void
    {
        $this->ensureStorageAppExists();

        ExtensionModel::create([
            'extension' => 'Module',
            'name'      => 'Slider',
            'namespace' => 'App\\Extensions\\Module\\Slider\\Slider',
            'info'      => [],
        ]);
        ExtensionModel::create([
            'extension' => 'Module',
            'name'      => 'Banner',
            'namespace' => 'App\\Extensions\\Module\\Banner\\Banner',
            'info'      => [],
        ]);

        $this->service->syncFromDatabase($this->app);

        $content = File::get($this->service->path());
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertCount(2, $data);
        $this->assertSame('Banner', $data[0]['name']);
        $this->assertSame('Slider', $data[1]['name']);
    }

    private function ensureStorageAppExists(): void
    {
        $dir = dirname($this->service->path());
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
