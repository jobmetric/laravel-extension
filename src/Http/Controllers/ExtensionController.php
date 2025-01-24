<?php

namespace JobMetric\Extension\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use JobMetric\Extension\Facades\Extension;
use JobMetric\Extension\Facades\ExtensionType;
use JobMetric\Extension\Http\Requests\DeleteRequest;
use JobMetric\Extension\Http\Requests\InstallRequest;
use JobMetric\Extension\Http\Requests\UninstallRequest;
use JobMetric\Panelio\Facades\Breadcrumb;
use JobMetric\Panelio\Http\Controllers\Controller;
use Throwable;

class ExtensionController extends Controller
{
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
            'routes' => [
                'install' => route('extension.install', [
                    'panel' => $panel,
                    'section' => $section,
                    'type' => $type
                ]),
                'uninstall' => route('extension.uninstall', [
                    'panel' => $panel,
                    'section' => $section,
                    'type' => $type
                ]),
                'delete' => route('extension.delete', [
                    'panel' => $panel,
                    'section' => $section,
                    'type' => $type
                ]),
            ],
            'language' => [
                'website' => trans('extension::base.list.extension.columns.website'),
                'email' => trans('extension::base.list.extension.columns.email'),
                'namespace' => trans('extension::base.list.extension.columns.namespace'),
                'license' => trans('extension::base.list.extension.columns.license'),
                'delete_note' => trans('extension::base.list.extension.columns.delete_note'),
                'delete' => trans('extension::base.list.extension.columns.delete'),
                'creation_at' => trans('extension::base.list.extension.columns.creation_at'),
                'installed_at' => trans('extension::base.list.extension.columns.installed_at'),
                'updated_at' => trans('extension::base.list.extension.columns.updated_at'),
                'not_installed' => trans('extension::base.list.extension.columns.not_installed'),
                'simple' => trans('extension::base.list.extension.columns.simple'),
                'multiple' => trans('extension::base.list.extension.columns.multiple'),
                'buttons' => [
                    'install' => trans('extension::base.list.extension.buttons.install'),
                    'uninstall' => trans('extension::base.list.extension.buttons.uninstall'),
                    'add_plugin' => trans('extension::base.list.extension.buttons.add_plugin'),
                    'plugin_list' => trans('extension::base.list.extension.buttons.plugin_list'),
                ],
                'confirm' => [
                    'uninstall' => trans('extension::base.list.extension.message.confirm.uninstall'),
                    'delete' => trans('extension::base.list.extension.message.confirm.delete'),
                    'button' => [
                        'are_you_sure_to_uninstall' => trans('extension::base.list.extension.buttons.are_you_sure_to_uninstall'),
                        'are_you_sure_to_delete' => trans('extension::base.list.extension.buttons.are_you_sure_to_delete'),
                    ],
                ],
            ],
            'extensions' => Extension::all($type)
        ]);

        DomiScript('assets/vendor/extension/js/extension/list.js');

        $data['type'] = $type;

        return view('extension::extension.list', $data);
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
     * Uninstall the extension.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     * @param UninstallRequest $request
     *
     * @return JsonResponse
     */
    public function uninstall(string $panel, string $section, string $type, UninstallRequest $request): JsonResponse
    {
        try {
            $namespace = $request->validated()['namespace'];

            return $this->response(
                Extension::uninstall($namespace),
                additional: [
                    'extensions' => Extension::all($type)
                ]
            );
        } catch (Throwable $exception) {
            return $this->response(message: $exception->getMessage(), status: $exception->getCode());
        }
    }

    /**
     * Remove all extension files.
     *
     * @param string $panel
     * @param string $section
     * @param string $type
     * @param DeleteRequest $request
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function delete(string $panel, string $section, string $type, DeleteRequest $request): JsonResponse
    {
        try {
            $namespace = $request->validated()['namespace'];

            return $this->response(
                Extension::delete($type, $namespace),
                additional: [
                    'extensions' => Extension::all($type)
                ]
            );
        } catch (Throwable $exception) {
            return $this->response(message: $exception->getMessage(), status: $exception->getCode());
        }
    }
}
