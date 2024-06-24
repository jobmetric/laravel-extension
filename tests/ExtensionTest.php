<?php

namespace JobMetric\Extension\Tests;

use JobMetric\Extension\Facades\Extension;
use Tests\BaseDatabaseTestCase as BaseTestCase;
use Throwable;

class ExtensionTest extends BaseTestCase
{
    /**
     * @throws Throwable
     */
    public function testInstall(): void
    {
        Extension::install('Addons', 'Banner');

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Addons',
            'name' => 'Banner',
        ]);
    }
}
