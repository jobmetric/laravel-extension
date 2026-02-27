<?php

namespace JobMetric\Extension\Commands\Tools;

use JobMetric\Extension\Commands\Tools\Generators\ControllerGenerator;
use JobMetric\Extension\Commands\Tools\Generators\GenericToolGenerator;
use JobMetric\Extension\Commands\Tools\Generators\MigrationGenerator;
use JobMetric\Extension\Commands\Tools\Generators\ModelGenerator;
use JobMetric\Extension\Commands\Tools\Generators\ViewGenerator;

/**
 * Registry of extension make-tools with Laravel-like options and generators.
 * stubMap: optional map of option key => stub name for variant stubs (e.g. inbound => cast.inbound.stub).
 */
final class ToolRegistry
{
    /**
     * Keyed by tool name (e.g. 'model', 'controller'), each entry contains:
     *
     * @var array<string, array{label: string, options: array, generator: class-string, stub?: string, subfolder?:
     *      string, stubMap?: array<string, string>}>
     */
    private static array $tools = [];

    /**
     * Indicates whether the registry has been booted (tools loaded). This allows for lazy loading and prevents
     *
     * @var bool
     */
    private static bool $booted = false;

    /**
     * Common "force" option for tools that simply create a class/file and can be safely overwritten.
     *
     * @return array[]
     */
    private static function forceOption(): array
    {
        return [
            [
                'key'      => 'force',
                'question' => 'Create the class even if it already exists?',
                'default'  => false,
                'type'     => 'bool',
            ],
        ];
    }

    /**
     * Boot the registry by loading the default set of tools. This method is idempotent and will only load the tools
     * once.
     *
     * @return void
     */
    private static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        self::$tools = [
            'model'        => [
                'label'     => 'Eloquent model class',
                'generator' => ModelGenerator::class,
                'options'   => [
                    [
                        'key'      => 'all',
                        'question' => 'Generate migration, seeder, factory, policy, resource controller, and form request classes?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'migration',
                        'question' => 'Create a new migration file for the model?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'factory',
                        'question' => 'Create a new factory for the model?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'seed',
                        'question' => 'Create a new seeder for the model?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'controller',
                        'question' => 'Create a new controller for the model?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'policy',
                        'question' => 'Create a new policy for the model?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'resource',
                        'question' => 'Generate a resource controller?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'api',
                        'question' => 'Generate an API resource controller?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'requests',
                        'question' => 'Create new form request classes and use them in the resource controller?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'pivot',
                        'question' => 'Custom intermediate table model (pivot)?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'morph-pivot',
                        'question' => 'Custom polymorphic intermediate table model?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'test',
                        'question' => 'Generate an accompanying PHPUnit test?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'force',
                        'question' => 'Create the class even if it already exists?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                ],
            ],
            'cast'         => [
                'label'     => 'Custom Eloquent cast class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'cast.stub',
                'subfolder' => 'Casts',
                'stubMap'   => ['inbound' => 'cast.inbound.stub'],
                'options'   => [
                    [
                        'key'      => 'inbound',
                        'question' => 'Generate an inbound-only cast class?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'force',
                        'question' => 'Create the class even if it already exists?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                ],
            ],
            'channel'      => [
                'label'     => 'Broadcast channel class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'channel.stub',
                'subfolder' => 'Broadcasting',
                'options'   => self::forceOption(),
            ],
            'command'      => [
                'label'     => 'Artisan command',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'command.stub',
                'subfolder' => 'Console\\Commands',
                'options'   => self::forceOption(),
            ],
            'component'    => [
                'label'     => 'View component class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'component.stub',
                'subfolder' => 'View\\Components',
                'options'   => [
                    [
                        'key'      => 'view',
                        'question' => 'Create an anonymous component (no class)?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'force',
                        'question' => 'Create the class even if it already exists?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                ],
            ],
            'controller'   => [
                'label'     => 'Controller class',
                'generator' => ControllerGenerator::class,
                'options'   => [
                    [
                        'key'      => 'resource',
                        'question' => 'Generate a resource controller?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'api',
                        'question' => 'Exclude create and edit methods (API controller)?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'invokable',
                        'question' => 'Generate a single invokable controller?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'singleton',
                        'question' => 'Generate a singleton resource controller?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'creatable',
                        'question' => 'Singleton resource should be creatable?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'requests',
                        'question' => 'Generate FormRequest classes for store and update?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'test',
                        'question' => 'Generate an accompanying test?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'force',
                        'question' => 'Create the class even if it already exists?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                ],
            ],
            'event'        => [
                'label'     => 'Event class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'event.stub',
                'subfolder' => 'Events',
                'options'   => self::forceOption(),
            ],
            'exception'    => [
                'label'     => 'Custom exception class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'exception.stub',
                'subfolder' => 'Exceptions',
                'stubMap'   => [
                    'render'        => 'exception.render.stub',
                    'report'        => 'exception.report.stub',
                    'render,report' => 'exception.render.report.stub',
                ],
                'options'   => [
                    [
                        'key'      => 'render',
                        'question' => 'Create the exception with a render method?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'report',
                        'question' => 'Create the exception with a report method?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'force',
                        'question' => 'Create the class even if it already exists?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                ],
            ],
            'factory'      => [
                'label'     => 'Model factory',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'factory.stub',
                'subfolder' => 'Factories',
                'options'   => self::forceOption(),
            ],
            'job'          => [
                'label'     => 'Job class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'job.stub',
                'subfolder' => 'Jobs',
                'stubMap'   => ['sync' => 'job.sync.stub'],
                'options'   => [
                    [
                        'key'      => 'sync',
                        'question' => 'Create a synchronous job (runs in foreground)?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'test',
                        'question' => 'Generate an accompanying test?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'force',
                        'question' => 'Create the class even if it already exists?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                ],
            ],
            'listener'     => [
                'label'     => 'Event listener class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'listener.plain.stub',
                'subfolder' => 'Listeners',
                'stubMap'   => ['queued' => 'listener.stub'],
                'options'   => [
                    [
                        'key'      => 'event',
                        'question' => 'Event class to listen to (optional)',
                        'default'  => null,
                        'type'     => 'string',
                    ],
                    [
                        'key'      => 'queued',
                        'question' => 'Make the listener queued?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'force',
                        'question' => 'Create the class even if it already exists?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                ],
            ],
            'mail'         => [
                'label'     => 'Mailable class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'mail.stub',
                'subfolder' => 'Mail',
                'options'   => [
                    [
                        'key'      => 'markdown',
                        'question' => 'Create a Markdown template for the mailable?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'force',
                        'question' => 'Create the class even if it already exists?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                ],
            ],
            'middleware'   => [
                'label'     => 'Middleware class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'middleware.stub',
                'subfolder' => 'Http\\Middleware',
                'options'   => self::forceOption(),
            ],
            'migration'    => [
                'label'     => 'Migration file',
                'generator' => MigrationGenerator::class,
                'options'   => self::forceOption(),
            ],
            'notification' => [
                'label'     => 'Notification class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'notification.stub',
                'subfolder' => 'Notifications',
                'options'   => [
                    [
                        'key'      => 'markdown',
                        'question' => 'Create a Markdown template for the notification?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'test',
                        'question' => 'Generate an accompanying test?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'force',
                        'question' => 'Create the class even if it already exists?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                ],
            ],
            'observer'     => [
                'label'     => 'Observer class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'observer.stub',
                'subfolder' => 'Observers',
                'options'   => [
                    [
                        'key'      => 'model',
                        'question' => 'Model class to observe (optional)',
                        'default'  => null,
                        'type'     => 'string',
                    ],
                    [
                        'key'      => 'force',
                        'question' => 'Create the class even if it already exists?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                ],
            ],
            'policy'       => [
                'label'     => 'Policy class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'policy.stub',
                'subfolder' => 'Policies',
                'options'   => [
                    [
                        'key'      => 'model',
                        'question' => 'Model class to apply policy to (optional)',
                        'default'  => null,
                        'type'     => 'string',
                    ],
                    [
                        'key'      => 'force',
                        'question' => 'Create the class even if it already exists?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                ],
            ],
            'provider'     => [
                'label'     => 'Service provider class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'provider.stub',
                'subfolder' => 'Providers',
                'options'   => self::forceOption(),
            ],
            'request'      => [
                'label'     => 'Form request class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'request.stub',
                'subfolder' => 'Http\\Requests',
                'options'   => self::forceOption(),
            ],
            'resource'     => [
                'label'     => 'API resource class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'resource.stub',
                'subfolder' => 'Http\\Resources',
                'stubMap'   => ['collection' => 'resource.collection.stub'],
                'options'   => [
                    [
                        'key'      => 'collection',
                        'question' => 'Create a resource collection?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'force',
                        'question' => 'Create the class even if it already exists?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                ],
            ],
            'rule'         => [
                'label'     => 'Validation rule class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'rule.stub',
                'subfolder' => 'Rules',
                'stubMap'   => ['implicit' => 'rule.implicit.stub'],
                'options'   => [
                    [
                        'key'      => 'implicit',
                        'question' => 'Generate an implicit (invokable) rule?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                    [
                        'key'      => 'force',
                        'question' => 'Create the class even if it already exists?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                ],
            ],
            'scope'        => [
                'label'     => 'Eloquent scope class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'scope.stub',
                'subfolder' => 'Models\\Scopes',
                'options'   => self::forceOption(),
            ],
            'seeder'       => [
                'label'     => 'Seeder class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'seeder.stub',
                'subfolder' => 'Seeders',
                'options'   => self::forceOption(),
            ],
            'test'         => [
                'label'     => 'Test class',
                'generator' => GenericToolGenerator::class,
                'stub'      => 'test.stub',
                'subfolder' => 'Tests',
                'stubMap'   => ['pest' => 'test.pest.stub'],
                'options'   => [
                    ['key' => 'unit', 'question' => 'Create a unit test?', 'default' => false, 'type' => 'bool'],
                    ['key' => 'pest', 'question' => 'Create a Pest test?', 'default' => false, 'type' => 'bool'],
                    [
                        'key'      => 'force',
                        'question' => 'Create the class even if it already exists?',
                        'default'  => false,
                        'type'     => 'bool',
                    ],
                ],
            ],
            'view'         => [
                'label'     => 'Blade view',
                'generator' => ViewGenerator::class,
                'options'   => self::forceOption(),
            ],
        ];
        self::$booted = true;
    }

    /**
     * Get the full list of registered tools with their configurations.
     *
     * @return array<string, array{label: string, options: array, generator: class-string, stub?: string, subfolder?:
     *                       string, stubMap?: array}>
     */
    public static function all(): array
    {
        self::boot();

        return self::$tools;
    }

    /**
     * Get a simple list of tool choices for display in prompts, keyed by tool name and valued by label.
     *
     * @return array<string, string>
     */
    public static function choices(): array
    {
        self::boot();

        return array_map(function ($config) {
            return $config['label'];
        }, self::$tools);
    }

    /**
     * Get the configuration for a specific tool by its key (e.g. 'model', 'controller'). Returns null if the tool is
     * not registered.
     *
     * @param string $key
     *
     * @return array|null
     */
    public static function get(string $key): ?array
    {
        self::boot();

        return self::$tools[$key] ?? null;
    }

    /**
     * Register additional tools or override existing ones. The input array should be keyed by tool name and contain
     * the same structure as the default tools.
     *
     * @param array<string, array> $tools
     *
     * @return void
     */
    public static function register(array $tools): void
    {
        self::boot();
        foreach ($tools as $k => $v) {
            self::$tools[$k] = $v;
        }
    }
}
