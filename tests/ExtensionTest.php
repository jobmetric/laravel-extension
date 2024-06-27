<?php

namespace JobMetric\Extension\Tests;

use Illuminate\Pagination\LengthAwarePaginator;
use JobMetric\Extension\Exceptions\ExtensionAlreadyInstalledException;
use JobMetric\Extension\Facades\Extension;
use JobMetric\Extension\Http\Resources\ExtensionResource;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use Tests\BaseDatabaseTestCase as BaseTestCase;
use Throwable;

class ExtensionTest extends BaseTestCase
{
    /**
     * @throws Throwable
     */
    public function testInstall(): void
    {
        $extension = Extension::install('Module', 'Banner');

        $this->assertIsArray($extension);
        $this->assertArrayHasKey('message', $extension);
        $this->assertArrayHasKey('data', $extension);
        $this->assertArrayHasKey('status', $extension);
        $this->assertInstanceOf(ExtensionResource::class, $extension['data']);
        $this->assertEquals(200, $extension['status']);

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Module',
            'name' => 'Banner',
        ]);

        // check if the extension is already installed
        try {
            Extension::install('Module', 'Banner');
        } catch (Throwable $e) {
            $this->assertInstanceOf(ExtensionAlreadyInstalledException::class, $e);
        }
    }

    /**
     * @throws Throwable
     */
    public function testUninstall(): void
    {
        Extension::install('Module', 'Banner');

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Module',
            'name' => 'Banner',
        ]);

        Extension::uninstall('Module', 'Banner', true);

        $this->assertDatabaseMissing('extensions', [
            'extension' => 'Module',
            'name' => 'Banner',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testGetInfo(): void
    {
        Extension::install('Module', 'Banner');

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Module',
            'name' => 'Banner',
        ]);

        $extension = Extension::getInfo('Module', 'Banner');

        $this->assertInstanceOf(ExtensionModel::class, $extension);
        $this->assertNotInstanceOf(ExtensionResource::class, $extension);

        $extension = Extension::getInfo('Module', 'Banner', true);

        $this->assertInstanceOf(ExtensionResource::class, $extension);
        $this->assertNotInstanceOf(ExtensionModel::class, $extension);
    }

    /**
     * @throws Throwable
     */
    public function testAll(): void
    {
        Extension::install('Module', 'Banner');

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Module',
            'name' => 'Banner',
        ]);

        $extensions = Extension::all();

        $this->assertCount(1, $extensions);

        $extensions->each(function ($extension) {
            $this->assertInstanceOf(ExtensionResource::class, $extension);
        });
    }

    /**
     * @throws Throwable
     */
    public function testPaginate(): void
    {
        Extension::install('Module', 'Banner');

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Module',
            'name' => 'Banner',
        ]);

        $extensions = Extension::paginate();

        $this->assertInstanceOf(LengthAwarePaginator::class, $extensions);
        $this->assertIsInt($extensions->total());
        $this->assertIsInt($extensions->perPage());
        $this->assertIsInt($extensions->currentPage());
        $this->assertIsInt($extensions->lastPage());
        $this->assertIsArray($extensions->items());
    }
}
