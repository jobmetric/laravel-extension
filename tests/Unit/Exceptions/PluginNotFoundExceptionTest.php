<?php

namespace JobMetric\Extension\Tests\Unit\Exceptions;

use Exception;
use JobMetric\Extension\Exceptions\PluginNotFoundException;
use JobMetric\Extension\Tests\TestCase;

/**
 * Unit tests for PluginNotFoundException.
 */
class PluginNotFoundExceptionTest extends TestCase
{
    /**
     * Exception extends base Exception.
     */
    public function test_extends_exception(): void
    {
        $e = new PluginNotFoundException(1);
        $this->assertInstanceOf(Exception::class, $e);
    }

    /**
     * Message includes plugin_id placeholder value.
     */
    public function test_message_includes_plugin_id(): void
    {
        $e = new PluginNotFoundException(99);
        $this->assertNotEmpty($e->getMessage());
        $this->assertStringContainsString('99', $e->getMessage());
    }

    /**
     * Default code is 400.
     */
    public function test_default_code_is_400(): void
    {
        $e = new PluginNotFoundException(1);
        $this->assertSame(400, $e->getCode());
    }
}
