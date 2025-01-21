<?php

namespace JobMetric\Extension\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use JobMetric\Extension\Facades\Extension;
use JobMetric\Extension\Facades\ExtensionType;
use JobMetric\Extension\Http\Requests\InstallRequest;
use JobMetric\Extension\Http\Requests\StoreExtensionRequest;
use JobMetric\Extension\Http\Requests\UpdateExtensionRequest;
use JobMetric\Extension\Models\Extension as ExtensionModel;
use JobMetric\Language\Facades\Language;
use JobMetric\Panelio\Facades\Breadcrumb;
use JobMetric\Panelio\Facades\Button;
use JobMetric\Panelio\Http\Controllers\Controller;
use Throwable;

class ExtensionController extends Controller
{
    private array $route;

    public function __construct()
    {
        if (request()->route()) {
            $parameters = request()->route()->parameters();

            $this->route = [
                'index' => route('extension.{type}.index', $parameters),
                'create' => route('extension.{type}.create', $parameters),
                'store' => route('extension.{type}.store', $parameters),
                'options' => route('extension.options', $parameters),
            ];
        }
    }

    /**
     * Display a listing of the extension.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     *
     * @return View|JsonResponse
     * @throws Throwable
     */
    public function index(string $panel, string $section, string $type): View|JsonResponse
    {
        $serviceType = ExtensionType::type($type);

        $data['label'] = $serviceType->getLabel();
        $data['description'] = $serviceType->getDescription();
        $data['hasShowDescriptionInList'] = $serviceType->hasShowDescriptionInList();

        DomiTitle($data['label']);

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($data['label']);

        DomiLocalize('extension', [
            'route' => $this->route['index'],
            'install' => route('extension.install', [
                'panel' => $panel,
                'section' => $section,
                'type' => $type
            ]),
            'language' => [
                'website' => trans('extension::base.list.columns.website'),
                'email' => trans('extension::base.list.columns.email'),
                'namespace' => trans('extension::base.list.columns.namespace'),
                'license' => trans('extension::base.list.columns.license'),
                'delete_note' => trans('extension::base.list.columns.delete_note'),
                'delete' => trans('extension::base.list.columns.delete'),
                'creation_at' => trans('extension::base.list.columns.creation_at'),
                'installed_at' => trans('extension::base.list.columns.installed_at'),
                'updated_at' => trans('extension::base.list.columns.updated_at'),
                'not_installed' => trans('extension::base.list.columns.not_installed'),
                'buttons' => [
                    'install' => trans('extension::base.list.buttons.install'),
                    'uninstall' => trans('extension::base.list.buttons.uninstall'),
                ],
            ],
            'extensions' => Extension::all($type)
        ]);

        DomiScript('assets/vendor/extension/js/list.js');

        $data['type'] = $type;

        $data['route'] = $this->route['options'];

        return view('extension::list', $data);
    }

    /**
     * Install the extension.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     * @param InstallRequest $request
     *
     * @return JsonResponse
     */
    public function install(string $panel, string $section, string $type, InstallRequest $request): JsonResponse
    {
        try {
            $namespace = $request->validated()['namespace'];

            return $this->response(
                Extension::install($namespace),
                additional: [
                    'extensions' => Extension::all($type)
                ]
            );
        } catch (Throwable $exception) {
            return $this->response(message: $exception->getMessage(), status: $exception->getCode());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     *
     * @return View
     */
    public function create(string $panel, string $section, string $type): View
    {
        $data['mode'] = 'create';

        $serviceType = ExtensionType::type($type);

        $data['label'] = $serviceType->getLabel();
        $data['description'] = $serviceType->getDescription();
        $data['translation'] = $serviceType->getTranslation();
        $data['media'] = $serviceType->getMedia();
        $data['metadata'] = $serviceType->getMetadata();
        $data['hasUrl'] = $serviceType->hasUrl();
        $data['hasHierarchical'] = $serviceType->hasHierarchical();
        $data['hasBaseMedia'] = $serviceType->hasBaseMedia();

        DomiTitle(trans('extension::base.form.create.title', [
            'type' => $data['label']
        ]));

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($data['label'], $this->route['index']);
        Breadcrumb::add(trans('extension::base.form.create.title', [
            'type' => $data['label']
        ]));

        // add button
        Button::save();
        Button::saveNew();
        Button::saveClose();
        Button::cancel($this->route['index']);

        DomiScript('assets/vendor/extension/js/form.js');

        $data['type'] = $type;
        $data['action'] = $this->route['store'];

        $data['taxonomies'] = Extension::all($type);

        return view('extension::form', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreExtensionRequest $request
     * @param string $panel
     * @param string $section
     * @param string $type
     *
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(StoreExtensionRequest $request, string $panel, string $section, string $type): RedirectResponse
    {
        $form_data = $request->all();

        $extension = Extension::store($request->validated());

        if ($extension['ok']) {
            $this->alert($extension['message']);

            if ($form_data['save'] == 'save.new') {
                return back();
            }

            if ($form_data['save'] == 'save.close') {
                return redirect()->to($this->route['index']);
            }

            // btn save
            return redirect()->route('extension.{type}.edit', [
                'panel' => $panel,
                'section' => $section,
                'type' => $type,
                'jm_extension' => $extension['data']->id
            ]);
        }

        $this->alert($extension['message'], 'danger');

        return back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     * @param ExtensionModel $extension
     *
     * @return View
     */
    public function edit(string $panel, string $section, string $type, ExtensionModel $extension): View
    {
        $extension->load(['files', 'metas', 'translations']);

        $data['mode'] = 'edit';

        $serviceType = ExtensionType::type($type);

        $data['label'] = $serviceType->getLabel();
        $data['description'] = $serviceType->getDescription();
        $data['translation'] = $serviceType->getTranslation();
        $data['media'] = $serviceType->getMedia();
        $data['metadata'] = $serviceType->getMetadata();
        $data['hasUrl'] = $serviceType->hasUrl();
        $data['hasHierarchical'] = $serviceType->hasHierarchical();
        $data['hasBaseMedia'] = $serviceType->hasBaseMedia();

        DomiTitle(trans('extension::base.form.edit.title', [
            'type' => $data['label'],
            'name' => $extension->id
        ]));

        // Add breadcrumb
        add_breadcrumb_base($panel, $section);
        Breadcrumb::add($data['label'], $this->route['index']);
        Breadcrumb::add(trans('extension::base.form.edit.title', [
            'type' => $data['label'],
            'name' => $extension->id
        ]));

        // add button
        Button::save();
        Button::saveNew();
        Button::saveClose();
        Button::cancel($this->route['index']);

        DomiScript('assets/vendor/extension/js/form.js');

        $data['type'] = $type;
        $data['action'] = route('extension.{type}.update', [
            'panel' => $panel,
            'section' => $section,
            'type' => $type,
            'jm_extension' => $extension->id
        ]);

        $data['languages'] = Language::all();
        $data['taxonomies'] = Extension::all($type);

        $data['extension'] = $extension;
        $data['slug'] = $extension->urlByCollection($type, true);
        $data['translation_edit_values'] = translationResourceData($extension->translations);
        $data['media_values'] = $extension->getMediaDataForObject();
        $data['meta_values'] = $extension->getMetaDataForObject();

        return view('extension::form', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateExtensionRequest $request
     * @param string $panel
     * @param string $section
     * @param string $type
     * @param ExtensionModel $extension
     *
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(UpdateExtensionRequest $request, string $panel, string $section, string $type, ExtensionModel $extension): RedirectResponse
    {
        $form_data = $request->all();

        $extension = Extension::update($extension->id, $request->validated());

        if ($extension['ok']) {
            $this->alert($extension['message']);

            if ($form_data['save'] == 'save.new') {
                return redirect()->to($this->route['create']);
            }

            if ($form_data['save'] == 'save.close') {
                return redirect()->to($this->route['index']);
            }

            // btn save
            return redirect()->route('extension.{type}.edit', [
                'panel' => $panel,
                'section' => $section,
                'type' => $type,
                'jm_extension' => $extension['data']->id
            ]);
        }

        $this->alert($extension['message'], 'danger');

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

        $serviceType = ExtensionType::type($type);

        try {
            foreach ($ids as $id) {
                Extension::delete($id);
            }

            $alert = trans_choice('extension::base.messages.deleted_items', count($ids), [
                'extension' => $serviceType->getLabel()
            ]);

            return true;
        } catch (Throwable $e) {
            $danger = $e->getMessage();

            return false;
        }
    }
}
