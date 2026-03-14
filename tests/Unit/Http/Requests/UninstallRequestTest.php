<?php

namespace JobMetric\Extension\Tests\Unit\Http\Requests;

use JobMetric\Extension\Http\Requests\UninstallRequest;
use JobMetric\Extension\Tests\TestCase;

/**
 * Unit tests for UninstallRequest (authorize).
 */
class UninstallRequestTest extends TestCase
{
    /**
     * authorize returns true.
     */
    public function test_authorize_returns_true(): void
    {
        $request = new UninstallRequest;
        $this->assertTrue($request->authorize());
    }
}
