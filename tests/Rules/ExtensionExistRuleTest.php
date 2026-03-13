<?php

namespace JobMetric\Extension\Tests\Rules;

use JobMetric\Extension\Facades\Extension;
use JobMetric\Extension\Rules\ExtensionExistRule;
use JobMetric\Extension\Tests\TestCase;
use JobMetric\PackageCore\Output\Response;

/**
 * Tests for ExtensionExistRule.
 */
class ExtensionExistRuleTest extends TestCase
{
    /**
     * Rule passes when value is null.
     */
    public function test_passes_when_value_null(): void
    {
        $rule = new ExtensionExistRule('Module');
        $failCalled = false;
        $fail = function () use (&$failCalled): void {
            $failCalled = true;
        };

        $rule->validate('namespace', null, $fail);

        $this->assertFalse($failCalled);
    }

    /**
     * Rule passes when value is empty string.
     */
    public function test_passes_when_value_empty_string(): void
    {
        $rule = new ExtensionExistRule('Module');
        $failCalled = false;
        $fail = function () use (&$failCalled): void {
            $failCalled = true;
        };

        $rule->validate('namespace', '', $fail);

        $this->assertFalse($failCalled);
    }

    /**
     * Rule fails when Extension::all returns non-iterable data.
     */
    public function test_fails_when_extension_all_returns_non_iterable(): void
    {
        Extension::shouldReceive('all')->once()->with([
                'extension' => 'Module',
            ])->andReturn(Response::make(true, '', null));

        $rule = new ExtensionExistRule('Module');
        $failMessage = null;
        $fail = function ($message) use (&$failMessage): void {
            $failMessage = $message;
        };

        $rule->validate('namespace', 'App\\Extensions\\Module\\Banner\\Banner', $fail);

        $this->assertNotNull($failMessage);
    }

    /**
     * Rule passes when value matches a namespace in Extension::all data.
     */
    public function test_passes_when_namespace_found(): void
    {
        $data = [
            ['namespace' => 'App\\Extensions\\Module\\Banner\\Banner', 'name' => 'Banner'],
            ['namespace' => 'App\\Extensions\\Module\\Slider\\Slider', 'name' => 'Slider'],
        ];
        Extension::shouldReceive('all')->once()->with([
            'extension' => 'Module',
        ])->andReturn(Response::make(true, '', $data));

        $rule = new ExtensionExistRule('Module');
        $failCalled = false;
        $fail = function () use (&$failCalled): void {
            $failCalled = true;
        };

        $rule->validate('namespace', 'App\\Extensions\\Module\\Banner\\Banner', $fail);

        $this->assertFalse($failCalled);
    }

    /**
     * Rule fails when value does not match any namespace.
     */
    public function test_fails_when_namespace_not_found(): void
    {
        $data = [
            ['namespace' => 'App\\Extensions\\Module\\Banner\\Banner', 'name' => 'Banner'],
        ];
        Extension::shouldReceive('all')->once()->with([
            'extension' => 'Module',
        ])->andReturn(Response::make(true, '', $data));

        $rule = new ExtensionExistRule('Module');
        $failMessage = null;
        $fail = function ($message) use (&$failMessage): void {
            $failMessage = $message;
        };

        $rule->validate('namespace', 'App\\Extensions\\Module\\Other\\Other', $fail);

        $this->assertNotNull($failMessage);
    }

    /**
     * Rule uses type from constructor when calling Extension::all.
     */
    public function test_uses_type_from_constructor(): void
    {
        Extension::shouldReceive('all')->once()->with([
            'extension' => 'ShippingMethod',
        ])->andReturn(Response::make(true, '', []));

        $rule = new ExtensionExistRule('ShippingMethod');
        $failMessage = null;
        $fail = function ($message) use (&$failMessage): void {
            $failMessage = $message;
        };

        $rule->validate('namespace', 'Vendor\\Shipping\\Flat\\Flat', $fail);

        $this->assertNotNull($failMessage);
    }

    /**
     * Rule passes when iterable has item with matching namespace (collection).
     */
    public function test_passes_with_collection_data(): void
    {
        $data = collect([
            [
                'namespace' => 'App\\Extensions\\Module\\Slider\\Slider',
            ],
        ]);
        Extension::shouldReceive('all')
            ->once()
            ->with(['extension' => 'Module'])
            ->andReturn(Response::make(true, '', $data));

        $rule = new ExtensionExistRule('Module');
        $failCalled = false;
        $fail = function () use (&$failCalled): void {
            $failCalled = true;
        };

        $rule->validate('namespace', 'App\\Extensions\\Module\\Slider\\Slider', $fail);

        $this->assertFalse($failCalled);
    }
}
