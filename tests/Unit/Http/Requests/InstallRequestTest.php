<?php

namespace JobMetric\Extension\Tests\Unit\Http\Requests;

use JobMetric\Extension\Http\Requests\InstallRequest;
use JobMetric\Extension\Tests\TestCase;

/**
 * Unit tests for InstallRequest (authorize).
 */
class InstallRequestTest extends TestCase
{
    /**
     * authorize returns true.
     */
    public function test_authorize_returns_true(): void
    {
        $request = new InstallRequest;
        $this->assertTrue($request->authorize());
    }
}
