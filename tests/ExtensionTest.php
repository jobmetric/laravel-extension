<?php

namespace JobMetric\Extension\Tests;

use JobMetric\Extension\Exceptions\ExtensionAlreadyInstalledException;
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

        // check if the extension is already installed
        try {
            Extension::install('Addons', 'Banner');
        } catch (Throwable $e) {
            $this->assertInstanceOf(ExtensionAlreadyInstalledException::class, $e);
        }
    }

    /**
     * @throws Throwable
     */
    public function testUninstall(): void
    {
        Extension::install('Addons', 'Banner');

        $this->assertDatabaseHas('extensions', [
            'extension' => 'Addons',
            'name' => 'Banner',
        ]);

        Extension::uninstall('Addons', 'Banner', true);

        $this->assertDatabaseMissing('extensions', [
            'extension' => 'Addons',
            'name' => 'Banner',
        ]);
    }
}
