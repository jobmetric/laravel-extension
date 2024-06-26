<?php

namespace JobMetric\Extension\Tests;

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
        Extension::install('Addons', 'Banner');

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Addons',
            'name' => 'Banner',
        ]);

        // check if the extension is already installed
        try {
            Extension::install('Addons', 'Banner');
        } catch (Throwable $e) {
            $this->assertInstanceOf(ExtensionAlreadyInstalledException::class, $e);
        }
    }

    /**
     * @throws Throwable
     */
    public function testUninstall(): void
    {
        Extension::install('Addons', 'Banner');

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Addons',
            'name' => 'Banner',
        ]);

        Extension::uninstall('Addons', 'Banner', true);

        $this->assertDatabaseMissing('extensions', [
            'extension' => 'Addons',
            'name' => 'Banner',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function testGetInfo(): void
    {
        Extension::install('Addons', 'Banner');

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Addons',
            'name' => 'Banner',
        ]);

        $extension = Extension::getInfo('Addons', 'Banner');

        $this->assertInstanceOf(ExtensionModel::class, $extension);
        $this->assertNotInstanceOf(ExtensionResource::class, $extension);

        $extension = Extension::getInfo('Addons', 'Banner', true);

        $this->assertInstanceOf(ExtensionResource::class, $extension);
        $this->assertNotInstanceOf(ExtensionModel::class, $extension);
    }

    /**
     * @throws Throwable
     */
    public function testAll(): void
    {
        Extension::install('Addons', 'Banner');

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Addons',
            'name' => 'Banner',
        ]);

        $extensions = Extension::all();

        $this->assertCount(1, $extensions);

        $extensions->each(function ($extension) {
            $this->assertInstanceOf(\JobMetric\Extension\Http\Resources\ExtensionResource::class, $extension);
        });
    }

    /**
     * @throws Throwable
     */
    public function testPaginate(): void
    {
        Extension::install('Addons', 'Banner');

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Addons',
            'name' => 'Banner',
        ]);

        $extensions = Extension::paginate();

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $extensions);
        $this->assertIsInt($extensions->total());
        $this->assertIsInt($extensions->perPage());
        $this->assertIsInt($extensions->currentPage());
        $this->assertIsInt($extensions->lastPage());
        $this->assertIsArray($extensions->items());
    }
}
