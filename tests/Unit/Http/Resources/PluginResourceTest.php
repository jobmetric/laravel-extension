<?php

namespace JobMetric\Extension\Tests\Unit\Http\Resources;

use ArrayObject;
use Illuminate\Http\Request;
use JobMetric\Extension\Http\Resources\PluginResource;
use JobMetric\Extension\Models\Extension;
use JobMetric\Extension\Models\Plugin;
use JobMetric\Extension\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Unit tests for PluginResource toArray.
 */
class PluginResourceTest extends TestCase
{
    /**
     * toArray returns id, extension_id, name, fields, status, timestamps.
     */
    public function test_toArray_returns_expected_structure(): void
    {
        $ext = Extension::create([
            'extension' => 'Module',
            'name'      => 'Banner',
            'namespace' => 'App\\Extensions\\Module\\Banner\\Banner',
            'info'      => null,
        ]);
        $plugin = Plugin::create([
            'extension_id' => $ext->id,
            'name'         => 'extension::plugin.main',
            'fields'       => ['key' => 'value'],
            'status'       => true,
        ]);

        $request = Request::createFromBase(SymfonyRequest::create('/'));
        $resource = new PluginResource($plugin);
        $array = $resource->toArray($request);

        $this->assertSame($plugin->id, $array['id']);
        $this->assertSame($plugin->extension_id, $array['extension_id']);
        $fields = $array['fields'];
        $arr = $fields instanceof ArrayObject ? $fields->getArrayCopy() : $fields;
        $this->assertSame(['key' => 'value'], $arr);
        $this->assertTrue($array['status']);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
    }
}
