<?php

namespace JobMetric\Extension\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use JobMetric\Extension\Exceptions\PluginNotMatchExtensionException;
use JobMetric\Extension\Facades\ExtensionType;
use JobMetric\Extension\Facades\Plugin as PluginFacade;
use JobMetric\Extension\Http\Requests\StorePluginRequest;
use JobMetric\Extension\Http\Requests\UpdatePluginRequest;
use JobMetric\Extension\Http\Resources\PluginResource;
use JobMetric\Extension\Models\Extension;
use JobMetric\Extension\Models\Plugin;
use JobMetric\Panelio\Facades\Breadcrumb;
use JobMetric\Panelio\Facades\Button;
use JobMetric\Panelio\Facades\Datatable;
use JobMetric\Panelio\Http\Controllers\Controller;
use JobMetric\Panelio\Http\Requests\ExportActionListRequest;
use JobMetric\Panelio\Http\Requests\ImportActionListRequest;
use JobMetric\Taxonomy\Facades\Taxonomy;
use JobMetric\Taxonomy\Http\Requests\SetTranslationRequest;
use Throwable;

class PluginController extends Controller
{
    private array $route;

    public function __construct()
    {
        if (request()->route()) {
            $parameters = request()->route()->parameters();

            $this->route = [
                'index' => route('extension.plugin.index', $parameters),
                'create' => route('extension.plugin.create', $parameters),
                'store' => route('extension.plugin.store', $parameters),
                'options' => route('extension.plugin.options', $parameters),
            ];

            array_pop($parameters);

            $this->route['extension'] = [
                'index' => route('extension.index', $parameters),
            ];
        }
    }

    /**
     * Display a listing of the extension plugins.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     * @param Extension $extension
     *
     * @return View|JsonResponse
     * @throws Throwable
     */
    public function index(string $panel, string $section, string $type, Extension $extension): View|JsonResponse
    {
        if (request()->ajax()) {
            $query = PluginFacade::query([
                'extension_id' => $extension->id
            ]);

            return Datatable::of($query, resource_class: PluginResource::class);
        }

        $serviceType = ExtensionType::type($type);

        $extensionLabel = $serviceType->getLabel();
        $data['description'] = trans('extension::base.list.plugin.description', [
            'name' => trans($extension->info['title'])
        ]);
        $data['hasShowDescriptionInList'] = $serviceType->hasShowDescriptionInList();

        $data['label'] = trans('extension::base.list.plugin.label', [
            'name' => trans($extension->info['title'])
        ]);

        DomiTitle($data['label']);

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($extensionLabel, $this->route['extension']['index']);
        Breadcrumb::add($data['label']);

        // add button
        Button::add($this->route['create']);
        Button::delete();
        Button::status();

        DomiLocalize('plugin', [
            'route' => $this->route['index'],
        ]);

        DomiScript('assets/vendor/extension/js/plugin/list.js');

        $data['route'] = $this->route['options'];

        return view('extension::plugin.list', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     * @param Extension $extension
     *
     * @return View
     */
    public function create(string $panel, string $section, string $type, Extension $extension): View
    {
        $data['mode'] = 'create';

        $serviceType = ExtensionType::type($type);

        $extensionLabel = $serviceType->getLabel();

        $data['label'] = trans('extension::base.list.plugin.label', [
            'name' => trans($extension->info['title'])
        ]);

        DomiTitle(trans('extension::base.form.plugin.create.title', [
            'name' => trans($extension->info['title'])
        ]));

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($extensionLabel, $this->route['extension']['index']);
        Breadcrumb::add($data['label'], $this->route['index']);
        Breadcrumb::add(trans('extension::base.form.plugin.create.title', [
            'name' => trans($extension->info['title'])
        ]));

        $data['multiple'] = $extension->info['multiple'] ?? false;

        // add button
        Button::save();
        Button::saveNew();
        Button::saveClose();
        Button::cancel($this->route['index']);

        $data['type'] = $type;
        $data['action'] = $this->route['store'];

        $data['fields'] = $extension->info['fields'] ?? [];

        return view('extension::plugin.form', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePluginRequest $request
     * @param string $panel
     * @param string $section
     * @param string $type
     * @param Extension $extension
     *
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(StorePluginRequest $request, string $panel, string $section, string $type, Extension $extension): RedirectResponse
    {
        $form_data = $request->all();

        $plugin = PluginFacade::store($extension->id, $request->validated());

        if ($plugin['ok']) {
            $this->alert($plugin['message']);

            if ($form_data['save'] == 'save.new') {
                return back();
            }

            if ($form_data['save'] == 'save.close') {
                return redirect()->to($this->route['index']);
            }

            // btn save
            return redirect()->route('extension.plugin.edit', [
                'panel' => $panel,
                'section' => $section,
                'type' => $type,
                'jm_extension' => $extension->id,
                'jm_plugin' => $plugin['data']->id
            ]);
        }

        $this->alert($plugin['message'], 'danger');

        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     * @param Extension $extension
     * @param Plugin $plugin
     *
     * @return View
     * @throws Throwable
     */
    public function edit(string $panel, string $section, string $type, Extension $extension, Plugin $plugin): View
    {
        if ($plugin->extension_id != $extension->id) {
            throw new PluginNotMatchExtensionException($extension->id, $plugin->id);
        }

        $plugin->load('extension');

        $data['mode'] = 'edit';

        $serviceType = ExtensionType::type($type);

        $extensionLabel = $serviceType->getLabel();

        $data['label'] = trans('extension::base.list.plugin.label', [
            'name' => trans($extension->info['title'])
        ]);

        DomiTitle(trans('extension::base.form.plugin.edit.title', [
            'name' => trans($extension->info['title']),
            'number' => $plugin->id
        ]));

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($extensionLabel, $this->route['extension']['index']);
        Breadcrumb::add($data['label'], $this->route['index']);
        Breadcrumb::add(trans('extension::base.form.plugin.edit.title', [
            'name' => trans($extension->info['title']),
            'number' => $plugin->id
        ]));

        $data['multiple'] = $extension->info['multiple'] ?? false;

        // add button
        Button::save();
        if ($data['multiple']) {
            Button::saveNew();
        }
        Button::saveClose();
        Button::cancel($this->route['index']);

        $data['type'] = $type;
        $data['action'] = route('extension.plugin.update', [
            'panel' => $panel,
            'section' => $section,
            'type' => $type,
            'jm_extension' => $extension->id,
            'jm_plugin' => $plugin->id
        ]);

        $data['fields'] = $extension->info['fields'] ?? [];

        $data['plugin'] = $plugin;

        return view('extension::plugin.form', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePluginRequest $request
     * @param string $panel
     * @param string $section
     * @param string $type
     * @param Extension $extension
     * @param Plugin $plugin
     *
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdatePluginRequest $request, string $panel, string $section, string $type, Extension $extension, Plugin $plugin): RedirectResponse
    {
        $form_data = $request->all();

        $plugin = PluginFacade::update($extension->id, $plugin->id, $request->validated());

        if ($plugin['ok']) {
            $this->alert($plugin['message']);

            if ($form_data['save'] == 'save.new') {
                return redirect()->to($this->route['create']);
            }

            if ($form_data['save'] == 'save.close') {
                $multiple = $extension->info['multiple'] ?? false;
                if ($multiple) {
                    return redirect()->to($this->route['index']);
                } else {
                    return redirect()->to($this->route['extension']['index']);
                }
            }

            // btn save
            return redirect()->route('extension.plugin.edit', [
                'panel' => $panel,
                'section' => $section,
                'type' => $type,
                'jm_extension' => $extension->id,
                'jm_plugin' => $plugin['data']->id
            ]);
        }

        $this->alert($plugin['message'], 'danger');

        return back();
    }

    /**
     * Delete the specified resource from storage.
     *
     * @param array $ids
     * @param mixed $params
     * @param string|null $alert
     * @param string|null $danger
     *
     * @return bool
     * @throws Throwable
     */
    public function deletes(array $ids, mixed $params, string &$alert = null, string &$danger = null): bool
    {
        $type = $params[2] ?? null;
        ExtensionType::type($type);

        $extension = $params[3] ?? null;

        try {
            foreach ($ids as $id) {
                PluginFacade::delete($id);
            }

            $alert = trans_choice('extension::base.messages.plugin.deleted_items', count($ids), [
                'extension' => trans($extension->info['title'])
            ]);

            return true;
        } catch (Throwable $e) {
            $danger = $e->getMessage();

            return false;
        }
    }

    /**
     * Change Status the specified resource from storage.
     *
     * @param array $ids
     * @param bool $value
     * @param mixed $params
     * @param string|null $alert
     * @param string|null $danger
     *
     * @return bool
     * @throws Throwable
     */
    public function changeStatus(array $ids, bool $value, mixed $params, string &$alert = null, string &$danger = null): bool
    {
        $type = $params[2] ?? null;
        ExtensionType::type($type);

        $extension = $params[3] ?? null;

        try {
            foreach ($ids as $id) {
                PluginFacade::update($extension->id, $id, ['status' => $value]);
            }

            if ($value) {
                $alert = trans_choice('extension::base.messages.plugin.status.enable', count($ids), [
                    'extension' => trans($extension->info['title'])
                ]);
            } else {
                $alert = trans_choice('extension::base.messages.plugin.status.disable', count($ids), [
                    'extension' => trans($extension->info['title'])
                ]);
            }

            return true;
        } catch (Throwable $e) {
            $danger = $e->getMessage();

            return false;
        }
    }

    /**
     * Import data
     */
    public function import(ImportActionListRequest $request, string $panel, string $section, string $type)
    {
        //
    }

    /**
     * Export data
     */
    public function export(ExportActionListRequest $request, string $panel, string $section, string $type)
    {
        $export_type = $request->type;

        $filePath = public_path('favicon.ico');
        $fileName = 'favicon.ico';

        return response()->download($filePath, $fileName, [
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
        ]);
    }

    /**
     * Set Translation in list
     *
     * @param SetTranslationRequest $request
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function setTranslation(SetTranslationRequest $request): JsonResponse
    {
        try {
            return $this->response(
                Taxonomy::setTranslation($request->validated())
            );
        } catch (Throwable $exception) {
            return $this->response(message: $exception->getMessage(), status: $exception->getCode());
        }
    }
}
