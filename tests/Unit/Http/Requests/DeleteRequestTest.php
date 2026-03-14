<?php

namespace JobMetric\Extension\Tests\Unit\Http\Requests;

use JobMetric\Extension\Http\Requests\DeleteRequest;
use JobMetric\Extension\Tests\TestCase;

/**
 * Unit tests for DeleteRequest (authorize).
 */
class DeleteRequestTest extends TestCase
{
    /**
     * authorize returns true.
     */
    public function test_authorize_returns_true(): void
    {
        $request = new DeleteRequest;
        $this->assertTrue($request->authorize());
    }
}
