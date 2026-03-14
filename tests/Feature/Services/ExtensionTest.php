<?php

namespace JobMetric\Extension\Tests\Feature\Services;

use JobMetric\Extension\Exceptions\ExtensionNotInstalledException;
use JobMetric\Extension\Facades\ExtensionTypeRegistry;
use JobMetric\Extension\Http\Resources\ExtensionResource;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use JobMetric\Extension\Services\Extension;
use JobMetric\Extension\Tests\TestCase;
use JobMetric\PackageCore\Output\Response;

/**
 * Tests for Extension service.
 */
class ExtensionTest extends TestCase
{
    /**
     * namespaceFor builds FQCN with app prefix and Studly type/name.
     */
    public function test_namespaceFor_returns_correct_fqcn(): void
    {
        $namespace = Extension::namespaceFor('Module', 'Banner');

        $this->assertStringEndsWith('Extensions\\Module\\Banner\\Banner', $namespace);
        $this->assertSame('App\\Extensions\\Module\\Banner\\Banner', $namespace);
    }

    /**
     * namespaceFor normalizes extension and name to StudlyCase.
     */
    public function test_namespaceFor_normalizes_to_studly(): void
    {
        $namespace = Extension::namespaceFor('module', 'some_extension');

        $this->assertSame('App\\Extensions\\Module\\SomeExtension\\SomeExtension', $namespace);
    }

    /**
     * upgrade returns success response with message.
     */
    public function test_upgrade_returns_success_response(): void
    {
        $service = $this->app->make(Extension::class);

        $response = $service->upgrade('Module', 'Banner');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->ok);
        $this->assertNotNull($response->message);
    }

    /**
     * isUpdated returns true.
     */
    public function test_isUpdated_returns_true(): void
    {
        $service = $this->app->make(Extension::class);

        $this->assertTrue($service->isUpdated('Module', 'Banner'));
    }

    /**
     * getInfo returns model when extension is installed.
     *
     * @throws ExtensionNotInstalledException
     */
    public function test_getInfo_returns_model_when_installed(): void
    {
        ExtensionModel::create([
            'extension' => 'Module',
            'name'      => 'Banner',
            'namespace' => 'App\\Extensions\\Module\\Banner\\Banner',
            'info'      => [
                'version' => '1.0',
                'title'   => 'Banner',
            ],
        ]);

        $service = $this->app->make(Extension::class);
        $result = $service->getInfo('Module', 'Banner', false);

        $this->assertInstanceOf(ExtensionModel::class, $result);
        $this->assertSame('Module', $result->extension);
        $this->assertSame('Banner', $result->name);
    }

    /**
     * getInfo returns resource when has_resource is true.
     *
     * @throws ExtensionNotInstalledException
     */
    public function test_getInfo_returns_resource_when_has_resource_true(): void
    {
        ExtensionModel::create([
            'extension' => 'Module',
            'name'      => 'Banner',
            'namespace' => 'App\\Extensions\\Module\\Banner\\Banner',
            'info'      => [],
        ]);

        $service = $this->app->make(Extension::class);
        $result = $service->getInfo('Module', 'Banner', true);

        $this->assertInstanceOf(ExtensionResource::class, $result);
    }

    /**
     * getInfo throws ExtensionNotInstalledException when extension not in DB.
     */
    public function test_getInfo_throws_when_not_installed(): void
    {
        $this->expectException(ExtensionNotInstalledException::class);

        $service = $this->app->make(Extension::class);
        $service->getInfo('Module', 'NonExistent');
    }

    /**
     * doAll with extension filter when type not in registry returns empty collection.
     */
    public function test_doAll_returns_empty_when_type_not_in_registry(): void
    {
        ExtensionTypeRegistry::register('Module');
        $service = $this->app->make(Extension::class);

        $response = $service->doAll([
            'extension' => 'UnknownType',
        ], [], null);

        $this->assertTrue($response->ok);
        $this->assertCount(0, $response->data);
    }

    /**
     * doAll with extension filter when type in registry returns response with data.
     */
    public function test_doAll_with_extension_filter_returns_collection(): void
    {
        ExtensionTypeRegistry::register('Module');
        $service = $this->app->make(Extension::class);

        $response = $service->doAll([
            'extension' => 'Module',
        ], [], null);

        $this->assertTrue($response->ok);
        $this->assertIsIterable($response->data);
    }
}
