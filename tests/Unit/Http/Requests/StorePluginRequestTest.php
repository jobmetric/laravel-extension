<?php

namespace JobMetric\Extension\Tests\Unit\Http\Requests;

use JobMetric\Extension\Http\Requests\StorePluginRequest;
use JobMetric\Extension\Tests\TestCase;

/**
 * Unit tests for StorePluginRequest (authorize, setExtensionId, setContext).
 */
class StorePluginRequestTest extends TestCase
{
    /**
     * authorize returns true.
     */
    public function test_authorize_returns_true(): void
    {
        $request = new StorePluginRequest;
        $this->assertTrue($request->authorize());
    }

    /**
     * setExtensionId sets value and returns self.
     */
    public function test_setExtensionId_sets_value_and_returns_self(): void
    {
        $request = new StorePluginRequest;
        $result = $request->setExtensionId(5);
        $this->assertSame($request, $result);
        $this->assertSame(5, $request->extension_id);
    }

    /**
     * setContext sets extension_id from context array.
     */
    public function test_setContext_sets_extension_id_from_context(): void
    {
        $request = new StorePluginRequest;
        $request->setContext(['extension_id' => 10]);
        $this->assertSame(10, $request->extension_id);
    }

    /**
     * setContext overwrites existing extension_id when context has extension_id.
     */
    public function test_setContext_merges_with_existing_extension_id(): void
    {
        $request = new StorePluginRequest;
        $request->setExtensionId(3);
        $request->setContext(['extension_id' => 7]);
        $this->assertSame(7, $request->extension_id);
    }
}
