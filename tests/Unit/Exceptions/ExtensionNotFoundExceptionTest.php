<?php

namespace JobMetric\Extension\Tests\Unit\Exceptions;

use Exception;
use JobMetric\Extension\Exceptions\ExtensionNotFoundException;
use JobMetric\Extension\Tests\TestCase;

/**
 * Unit tests for ExtensionNotFoundException.
 */
class ExtensionNotFoundExceptionTest extends TestCase
{
    /**
     * Exception extends base Exception.
     */
    public function test_extends_exception(): void
    {
        $e = new ExtensionNotFoundException;
        $this->assertInstanceOf(Exception::class, $e);
    }

    /**
     * Default code is 400.
     */
    public function test_default_code_is_400(): void
    {
        $e = new ExtensionNotFoundException;
        $this->assertSame(400, $e->getCode());
    }

    /**
     * Custom code is used when passed.
     */
    public function test_custom_code_is_used(): void
    {
        $e = new ExtensionNotFoundException(404);
        $this->assertSame(404, $e->getCode());
    }

    /**
     * Message is translated and non-empty.
     */
    public function test_message_is_translated(): void
    {
        $e = new ExtensionNotFoundException;
        $this->assertNotEmpty($e->getMessage());
    }
}
