<?php

namespace JobMetric\Extension\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use JobMetric\Extension\Facades\ExtensionType;
use JobMetric\Extension\Facades\Plugin;
use JobMetric\Extension\Http\Requests\PluginRequest;
use JobMetric\Extension\Http\Resources\PluginResource;
use JobMetric\Extension\Models\Extension;
use JobMetric\Language\Facades\Language;
use JobMetric\Panelio\Facades\Breadcrumb;
use JobMetric\Panelio\Facades\Button;
use JobMetric\Panelio\Facades\Datatable;
use JobMetric\Panelio\Http\Controllers\Controller;
use JobMetric\Panelio\Http\Requests\ExportActionListRequest;
use JobMetric\Panelio\Http\Requests\ImportActionListRequest;
use JobMetric\Taxonomy\Facades\Taxonomy;
use JobMetric\Taxonomy\Facades\TaxonomyType;
use JobMetric\Taxonomy\Http\Requests\SetTranslationRequest;
use JobMetric\Taxonomy\Http\Requests\StoreTaxonomyRequest;
use JobMetric\Taxonomy\Http\Requests\UpdateTaxonomyRequest;
use JobMetric\Taxonomy\Models\Taxonomy as TaxonomyModel;
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
            $query = Plugin::query([
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

        // add button
        Button::save();
        Button::saveNew();
        Button::saveClose();
        Button::cancel($this->route['index']);

        DomiScript('assets/vendor/extension/js/plugin/form.js');

        $data['type'] = $type;
        $data['action'] = $this->route['store'];

        $data['fields'] = $extension->info['fields'] ?? [];

        return view('extension::plugin.form', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PluginRequest $request
     * @param string $panel
     * @param string $section
     * @param string $type
     * @param Extension $extension
     *
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(PluginRequest $request, string $panel, string $section, string $type, Extension $extension): RedirectResponse
    {
        $form_data = $request->all();

        $plugin = Plugin::store($extension, $request->validated());

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
     * @param TaxonomyModel $taxonomy
     *
     * @return View
     */
    public function edit(string $panel, string $section, string $type, TaxonomyModel $taxonomy): View
    {
        $taxonomy->load(['files', 'metas', 'translations']);

        $data['mode'] = 'edit';

        $serviceType = TaxonomyType::type($type);

        $data['label'] = $serviceType->getLabel();
        $data['description'] = $serviceType->getDescription();
        $data['translation'] = $serviceType->getTranslation();
        $data['media'] = $serviceType->getMedia();
        $data['metadata'] = $serviceType->getMetadata();
        $data['hasUrl'] = $serviceType->hasUrl();
        $data['hasHierarchical'] = $serviceType->hasHierarchical();
        $data['hasBaseMedia'] = $serviceType->hasBaseMedia();

        DomiTitle(trans('taxonomy::base.form.edit.title', [
            'type' => $data['label'],
            'name' => $taxonomy->id
        ]));

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($data['label'], $this->route['index']);
        Breadcrumb::add(trans('taxonomy::base.form.edit.title', [
            'type' => $data['label'],
            'name' => $taxonomy->id
        ]));

        // add button
        Button::save();
        Button::saveNew();
        Button::saveClose();
        Button::cancel($this->route['index']);

        DomiScript('assets/vendor/taxonomy/js/form.js');

        $data['type'] = $type;
        $data['action'] = route('taxonomy.{type}.update', [
            'panel' => $panel,
            'section' => $section,
            'type' => $type,
            'jm_taxonomy' => $taxonomy->id
        ]);

        $data['languages'] = Language::all();
        $data['taxonomies'] = Taxonomy::all($type);

        $data['taxonomy'] = $taxonomy;
        $data['slug'] = $taxonomy->urlByCollection($type, true);
        $data['translation_edit_values'] = translationResourceData($taxonomy->translations);
        $data['media_values'] = $taxonomy->getMediaDataForObject();
        $data['meta_values'] = $taxonomy->getMetaDataForObject();

        return view('taxonomy::form', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTaxonomyRequest $request
     * @param string $panel
     * @param string $section
     * @param string $type
     * @param TaxonomyModel $taxonomy
     *
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdateTaxonomyRequest $request, string $panel, string $section, string $type, TaxonomyModel $taxonomy): RedirectResponse
    {
        $form_data = $request->all();

        $taxonomy = Taxonomy::update($taxonomy->id, $request->validated());

        if ($taxonomy['ok']) {
            $this->alert($taxonomy['message']);

            if ($form_data['save'] == 'save.new') {
                return redirect()->to($this->route['create']);
            }

            if ($form_data['save'] == 'save.close') {
                return redirect()->to($this->route['index']);
            }

            // btn save
            return redirect()->route('taxonomy.{type}.edit', [
                'panel' => $panel,
                'section' => $section,
                'type' => $type,
                'jm_taxonomy' => $taxonomy['data']->id
            ]);
        }

        $this->alert($taxonomy['message'], 'danger');

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

        $serviceType = TaxonomyType::type($type);

        try {
            foreach ($ids as $id) {
                Taxonomy::delete($id);
            }

            $alert = trans_choice('taxonomy::base.messages.deleted_items', count($ids), [
                'taxonomy' => $serviceType->getLabel()
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

        $serviceType = TaxonomyType::type($type);

        try {
            foreach ($ids as $id) {
                Taxonomy::update($id, ['status' => $value]);
            }

            if ($value) {
                $alert = trans_choice('taxonomy::base.messages.status.enable', count($ids), [
                    'taxonomy' => $serviceType->getLabel()
                ]);
            } else {
                $alert = trans_choice('taxonomy::base.messages.status.disable', count($ids), [
                    'taxonomy' => $serviceType->getLabel()
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
