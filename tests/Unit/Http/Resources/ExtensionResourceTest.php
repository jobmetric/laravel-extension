<?php

namespace JobMetric\Extension\Tests\Unit\Http\Resources;

use Illuminate\Http\Request;
use JobMetric\Extension\Http\Resources\ExtensionResource;
use JobMetric\Extension\Models\Extension;
use JobMetric\Extension\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Unit tests for ExtensionResource toArray (model resource, no route dependency).
 */
class ExtensionResourceTest extends TestCase
{
    /**
     * toArray with model returns expected keys and values.
     */
    public function test_toArray_with_model_returns_expected_keys(): void
    {
        $ext = Extension::create([
            'extension' => 'Module',
            'name'      => 'Banner',
            'namespace' => 'App\\Extensions\\Module\\Banner\\Banner',
            'info'      => ['version' => '1.0', 'title' => 'extension::banner.title'],
        ]);
        $request = Request::createFromBase(SymfonyRequest::create('/'));
        $resource = new ExtensionResource($ext);
        $array = $resource->toArray($request);

        $this->assertSame($ext->id, $array['id']);
        $this->assertSame('Module', $array['extension']);
        $this->assertSame('Banner', $array['name']);
        $this->assertSame('App\\Extensions\\Module\\Banner\\Banner', $array['namespace']);
        $this->assertSame('1.0', $array['version']);
        $this->assertFalse($array['installed']);
        $this->assertSame(0, $array['plugins_count']);
    }

    /**
     * toArray with installed false does not add plugins_link or plugin_add.
     */
    public function test_toArray_with_installed_false_does_not_add_route_links(): void
    {
        $ext = Extension::create([
            'extension' => 'Module',
            'name'      => 'Slider',
            'namespace' => 'App\\Extensions\\Module\\Slider\\Slider',
            'info'      => [],
        ]);
        $request = Request::createFromBase(SymfonyRequest::create('/'));
        $array = (new ExtensionResource($ext))->toArray($request);

        $this->assertArrayNotHasKey('plugins_link', $array);
        $this->assertArrayNotHasKey('plugin_add', $array);
    }
}
