<?php

namespace JobMetric\Extension\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use JobMetric\Extension\Facades\Plugin as PluginFacade;
use Throwable;

class Plugin extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $type,
        public bool $required = false,
        public string|null $parent = null,
        public string|null $value = null,
        public string|null $id = null,
    )
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @throws Throwable
     */
    public function render(): View|Closure|string
    {
        $data['type'] = Str::studly($this->type);

        $plugins = PluginFacade::all([
            'extension_type' => $data['type'],
            'status' => true,
        ]);

        $data['extensions'] = [];
        foreach ($plugins as $plugin) {
            $data['extensions'][$plugin->extension->name]['name'] = trans($plugin->extension->info['title']);
            $data['extensions'][$plugin->extension->name]['plugins'][$plugin->id] = $plugin->name;
        }

        DomiPlugins('select2');

        return view('extension::components.plugin_field', $data);
    }
}
