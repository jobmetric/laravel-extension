<?php

namespace JobMetric\Extension\Tests\Kernel;

use Illuminate\Console\Command;
use JobMetric\Extension\Exceptions\ExtensionCoreBasePathNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreBasePathRequiredException;
use JobMetric\Extension\Exceptions\ExtensionCoreClassNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreCommandClassNotFoundException;
use JobMetric\Extension\Exceptions\ExtensionCoreNameRequiredException;
use JobMetric\Extension\Exceptions\ExtensionCoreRegisterClassTypeNotFoundException;
use JobMetric\Extension\Kernel\ExtensionCore;
use JobMetric\Extension\Tests\TestCase;
use stdClass;

/**
 * Tests for ExtensionCore.
 */
class ExtensionCoreTest extends TestCase
{
    /**
     * name sets name and returns static.
     *
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_name_sets_name_and_returns_static(): void
    {
        $core = new ExtensionCore;
        $result = $core->name('Module_Banner');

        $this->assertSame($core, $result);
        $this->assertSame('Module_Banner', $core->name);
    }

    /**
     * name trims value.
     *
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_name_trims_value(): void
    {
        $core = new ExtensionCore;
        $core->name('  Module_Banner  ');

        $this->assertSame('Module_Banner', $core->name);
    }

    /**
     * name with empty string throws ExtensionCoreNameRequiredException.
     */
    public function test_name_throws_when_empty(): void
    {
        $this->expectException(ExtensionCoreNameRequiredException::class);

        $core = new ExtensionCore;
        $core->name('');
    }

    /**
     * setBasePath sets path and returns static.
     *
     * @throws ExtensionCoreBasePathNotFoundException
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_setBasePath_sets_path(): void
    {
        $core = new ExtensionCore;
        $core->name('Test');
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ext-core-test-' . uniqid();

        mkdir($path, 0755, true);
        try {
            $result = $core->setBasePath($path);
            $this->assertSame($core, $result);
            $this->assertSame($path, $core->getBasePath());
        } finally {
            rmdir($path);
        }
    }

    /**
     * setBasePath with empty path throws.
     *
     * @throws ExtensionCoreBasePathNotFoundException
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_setBasePath_throws_when_empty(): void
    {
        $this->expectException(ExtensionCoreBasePathRequiredException::class);

        $core = new ExtensionCore;
        $core->name('Test');
        $core->setBasePath('');
    }

    /**
     * setBasePath with non-existent path throws ExtensionCoreBasePathNotFoundException.
     *
     * @throws ExtensionCoreBasePathNotFoundException
     * @throws ExtensionCoreBasePathRequiredException
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_setBasePath_throws_when_not_dir(): void
    {
        $this->expectException(ExtensionCoreBasePathNotFoundException::class);

        $core = new ExtensionCore;
        $core->name('Test');
        $core->setBasePath('/non/existent/path/' . uniqid());
    }

    /**
     * setExtensionTypeAndName sets option and returns static.
     */
    public function test_setExtensionTypeAndName_sets_option(): void
    {
        $core = new ExtensionCore;
        $result = $core->setExtensionTypeAndName('Module', 'Banner');

        $this->assertSame($core, $result);
        $this->assertSame('Module', $core->option['extensionType']);
        $this->assertSame('Banner', $core->option['extensionName']);
    }

    /**
     * getBasePath throws when basePath not set.
     *
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_getBasePath_throws_when_not_set(): void
    {
        $this->expectException(ExtensionCoreBasePathRequiredException::class);

        $core = new ExtensionCore;
        $core->name('Test');
        $core->getBasePath();
    }

    /**
     * shortName replaces backslash and dash with underscore.
     *
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_shortName_replaces_special_chars(): void
    {
        $core = new ExtensionCore;
        $core->name('Module\\Banner-Alt');

        $this->assertSame('Module_Banner_Alt', $core->shortName());
    }

    /**
     * shortName throws when name not set.
     */
    public function test_shortName_throws_when_name_not_set(): void
    {
        $this->expectException(ExtensionCoreNameRequiredException::class);

        $core = new ExtensionCore;
        $core->shortName();
    }

    /**
     * getConfigKey returns extension_type_name when extensionType and extensionName set.
     *
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_getConfigKey_uses_type_and_name_when_set(): void
    {
        $core = new ExtensionCore;
        $core->name('Module_Banner')->setExtensionTypeAndName('Module', 'Banner');

        $this->assertSame('extension_module_banner', $core->getConfigKey());
    }

    /**
     * getConfigKey returns extension_shortName when type/name not set.
     *
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_getConfigKey_uses_shortName_otherwise(): void
    {
        $core = new ExtensionCore;
        $core->name('Module_Banner');

        $key = $core->getConfigKey();
        $this->assertStringStartsWith('extension_', $key);
        $this->assertStringContainsString('module', $key);
        $this->assertStringContainsString('banner', $key);
    }

    /**
     * registerCommand adds class when class exists.
     *
     * @throws ExtensionCoreCommandClassNotFoundException
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_registerCommand_adds_existing_class(): void
    {
        $core = new ExtensionCore;
        $core->name('Test');
        $result = $core->registerCommand(Command::class);

        $this->assertSame($core, $result);
        $this->assertContains(Command::class, $core->option['commands']);
    }

    /**
     * registerCommand throws when class does not exist.
     *
     * @throws ExtensionCoreNameRequiredException
     */
    public function test_registerCommand_throws_when_class_not_exists(): void
    {
        $this->expectException(ExtensionCoreCommandClassNotFoundException::class);

        $core = new ExtensionCore;
        $core->name('Test');
        $core->registerCommand('NonExistent\\CommandClass');
    }

    /**
     * registerClass with valid type adds to classes.
     *
     * @throws ExtensionCoreClassNotFoundException
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCoreRegisterClassTypeNotFoundException
     */
    public function test_registerClass_adds_when_class_exists(): void
    {
        $core = new ExtensionCore;
        $core->name('Test');
        $result = $core->registerClass('SomeKey', stdClass::class, 'bind');

        $this->assertSame($core, $result);
        $this->assertArrayHasKey('SomeKey', $core->option['classes']);
        $this->assertSame(stdClass::class, $core->option['classes']['SomeKey']['class']);
        $this->assertSame('bind', $core->option['classes']['SomeKey']['type']);
    }

    /**
     * registerClass with invalid type throws.
     *
     * @throws ExtensionCoreClassNotFoundException
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCoreRegisterClassTypeNotFoundException
     */
    public function test_registerClass_throws_when_invalid_type(): void
    {
        $this->expectException(ExtensionCoreRegisterClassTypeNotFoundException::class);

        $core = new ExtensionCore;
        $core->name('Test');
        $core->registerClass('Key', stdClass::class, 'invalid');
    }

    /**
     * registerClass with non-existent class string throws.
     *
     * @throws ExtensionCoreClassNotFoundException
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCoreRegisterClassTypeNotFoundException
     */
    public function test_registerClass_throws_when_class_not_exists(): void
    {
        $this->expectException(ExtensionCoreClassNotFoundException::class);

        $core = new ExtensionCore;
        $core->name('Test');
        $core->registerClass('Key', 'Fake\\NonExistent', 'bind');
    }

    /**
     * registerClass with callable does not require class_exists.
     *
     * @throws ExtensionCoreClassNotFoundException
     * @throws ExtensionCoreNameRequiredException
     * @throws ExtensionCoreRegisterClassTypeNotFoundException
     */
    public function test_registerClass_accepts_callable(): void
    {
        $core = new ExtensionCore;
        $core->name('Test');
        $factory = fn () => new stdClass;
        $core->registerClass('Key', $factory, 'singleton');

        $this->assertSame($factory, $core->option['classes']['Key']['class']);
    }
}
