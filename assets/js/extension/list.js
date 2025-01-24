"use strict"

const extension = {
    list: function(data) {
        dt.clear()
        dt.rows.add(data)
        dt.draw()
    },
    install: function(element, e) {
        e.preventDefault()

        let namespace = $(element).data('namespace')

        $.ajax({
            url: getLocalize('extension.routes.install'),
            method: 'POST',
            dataType: 'json',
            data: {
                namespace: namespace
            },
            beforeSend: function () {
                $(element).attr('disabled', true).find('span').text(getLocalize('language.panelio.button.loading'))
            },
            complete: function () {
                $(element).attr('disabled', false).find('span').text(getLocalize('extension.language.buttons.install'))
            },
            success: function(json) {
                toastr.success(json.message)

                extension.list(json.extensions)
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON.message)
            }
        })
    },
    uninstall: function(element, e) {
        e.preventDefault()

        Swal.fire({
            icon: 'warning',
            title: getLocalize('language.panelio.button.are_you_sure'),
            text: getLocalize('extension.language.confirm.uninstall'),
            showCancelButton: true,
            confirmButtonText: getLocalize('extension.language.confirm.button.are_you_sure_to_uninstall'),
            cancelButtonText: getLocalize('language.panelio.button.cancel'),
            allowOutsideClick: false,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                let namespace = $(element).data('namespace')

                $.ajax({
                    url: getLocalize('extension.routes.uninstall'),
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        namespace: namespace
                    },
                    beforeSend: function () {
                        $(element).attr('disabled', true).find('span').text(getLocalize('language.panelio.button.loading'))
                    },
                    complete: function () {
                        $(element).attr('disabled', false).find('span').text(getLocalize('extension.language.buttons.uninstall'))
                    },
                    success: function(json) {
                        toastr.success(json.message)

                        extension.list(json.extensions)
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON.message)
                    }
                })
            } else {
                Swal.fire({
                    icon: 'info',
                    title: getLocalize('language.panelio.button.it_went_well'),
                    showConfirmButton: true,
                    confirmButtonText: getLocalize('language.panelio.button.realized'),
                    allowOutsideClick: false
                })
            }
        })
    },
    delete: function(element, e) {
        e.preventDefault()

        Swal.fire({
            icon: 'warning',
            title: getLocalize('language.panelio.button.are_you_sure'),
            text: getLocalize('extension.language.confirm.delete'),
            showCancelButton: true,
            confirmButtonText: getLocalize('extension.language.confirm.button.are_you_sure_to_delete'),
            cancelButtonText: getLocalize('language.panelio.button.cancel'),
            allowOutsideClick: false,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                let namespace = $(element).data('namespace')

                $.ajax({
                    url: getLocalize('extension.routes.delete'),
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        namespace: namespace
                    },
                    beforeSend: function () {
                        $(element).attr('disabled', true).find('span').text(getLocalize('language.panelio.button.loading'))
                    },
                    complete: function () {
                        $(element).attr('disabled', false).find('span').text(getLocalize('extension.language.delete'))
                    },
                    success: function(json) {
                        toastr.success(json.message)

                        extension.list(json.extensions)
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON.message)
                    }
                })
            } else {
                Swal.fire({
                    icon: 'info',
                    title: getLocalize('language.panelio.button.it_went_well'),
                    showConfirmButton: true,
                    confirmButtonText: getLocalize('language.panelio.button.realized'),
                    allowOutsideClick: false
                })
            }
        })
    }
}

// Toggle child row on click
function listShowDetails(data) {
    let html = `<div class="row">
                    <div class="col-12">
                        <div class="card card-xxl-stretch mb-xl-8 theme-dark-bg-body h-xl-100">
                            <div class="card-body d-flex flex-column pb-0">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="fs-5 text-dark fw-bold lh-1">${data.description}</div>
                                    </div>
                                </div>
                                <div class="row mt-10">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fs-7 text-gray-600 fw-bold">${getLocalize('extension.language.namespace')}</div>
                                            <div class="fs-5 text-dark fw-bold lh-1">${data.namespace}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mt-3">
                        <div class="card card-xxl-stretch mb-xl-8 theme-dark-bg-body h-xl-100">
                            <div class="card-body d-flex flex-column pb-0">
                                <div class="row g-0">
                                    <div class="col-12">
                                        <div class="d-flex align-items-center mb-9 me-2">
                                            <div class="symbol symbol-40px me-3">
                                                <div class="symbol-label bg-light">
                                                    <i class="ki-duotone ki-calendar fs-1 text-dark">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fs-7 text-gray-600 fw-bold">${getLocalize('extension.language.creation_at')}</div>
                                                <div class="fs-5 text-dark fw-bold lh-1" dir="ltr">${data.creation_at ?? ''}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center mb-9 me-2">
                                            <div class="symbol symbol-40px me-3">
                                                <div class="symbol-label bg-light">
                                                    <i class="ki-duotone ki-calendar fs-1 text-dark">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fs-7 text-gray-600 fw-bold">${getLocalize('extension.language.installed_at')}</div>
                                                <div class="fs-5 text-dark fw-bold lh-1" dir="ltr">${data.installed_at ? data.installed_at : `<div class="badge badge-warning">${getLocalize('extension.language.not_installed')}</div>`}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex align-items-center me-2">
                                            <div class="symbol symbol-40px me-3">
                                                <div class="symbol-label bg-light">
                                                    <i class="ki-duotone ki-calendar fs-1 text-dark">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fs-7 text-gray-600 fw-bold">${getLocalize('extension.language.updated_at')}</div>
                                                <div class="fs-5 text-dark fw-bold lh-1" dir="ltr">${data.updated_at}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-8 mt-3">
                        <div class="card card-xxl-stretch mb-xl-8 theme-dark-bg-body h-xl-100">
                            <div class="card-body d-flex flex-column pb-0">
                                <div class="row g-0">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center mb-9 me-2">
                                            <div class="fs-7 text-gray-600 fw-bold">${getLocalize('extension.language.website')}</div>
                                            <div class="fs-5 text-dark fw-bold lh-1">${data.website ?? ''}</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center mb-9 me-2">
                                            <div class="fs-7 text-gray-600 fw-bold">${getLocalize('extension.language.email')}</div>
                                            <div class="fs-5 text-dark fw-bold lh-1">${data.email ?? ''}</div>
                                        </div>
                                    </div>`
                                    if (data.deletable) {
                                        html += `
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center me-2">
                                                <div class="fs-7 text-gray-600 fw-bold">${getLocalize('extension.language.delete_note')}</div>
                                                <button data-namespace="${data.namespace}" class="btn btn-sm btn-outline btn-outline-dashed bg-light-danger btn-color-gray-800" onclick="extension.delete(this, event)">
                                                    <i class="la la-trash fs-2 position-absolute"></i>
                                                    <span class="ps-9">${getLocalize('extension.language.delete')}</span>
                                                </button>
                                            </div>
                                        </div>`
                                    }
                           html += `
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <div class="card card-xxl-stretch theme-dark-bg-body h-xl-100">
                            <div class="card-body d-flex flex-column pb-10">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="fs-5 text-dark fw-bold lh-1" dir="ltr">${data.copyright}</div>
                                    </div>
                                </div>
                                <div class="row mt-10">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fs-7 text-gray-600 fw-bold">${getLocalize('extension.language.license')}</div>
                                            <div class="fs-5 text-dark fw-bold lh-1">${data.license}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`

    return html
}

loadScriptsSequentially([
    'assets/vendor/package-core/js/datatable-columns.js',
], function() {
    $(document).ready(function(){
        dt = $('#datatable').DataTable({
            responsive: false,
            autoWidth: false,
            data: getLocalize('extension.extensions'),
            columns: [
                // show details
                {
                    data: datatableColumnShowDetailsList,
                    sortable: false
                },
                // title
                {
                    name: 'title',
                    data: function(e) {
                        return `<div class="align-start text-gray-800 w-100 pe-10 d-flex justify-content-between align-items-center">
                                    <span>${e.title}</span>
                                    <div class="badge text-gray-800 ${e.multiple ? 'bg-light-success' : 'bg-light-danger'}">${e.multiple ? getLocalize('extension.language.multiple') : getLocalize('extension.language.simple')}</div>
                                </div>`
                    },
                    sortable: true
                },
                // version
                {
                    name: 'version',
                    data: function(e) {
                        return `<div class="align-center text-gray-800 word-no-break">${e.version}</div>`
                    },
                    sortable: true
                },
                // author
                {
                    name: 'author',
                    data: function(e) {
                        return `<div class="align-center text-gray-800 word-no-break">${e.author}</div>`
                    },
                    sortable: true
                },
                // action
                {
                    data: function(e) {
                        let buttons = '<div class="d-flex justify-content-end align-items-center">'
                        if (e.installed) {
                            if (e.multiple) {
                                buttons += `<div class="d-flex justify-content-center align-items-center">
                                                <a href="${e.plugin_add}" class="btn btn-sm btn-outline btn-outline-dashed bg-light-danger btn-color-gray-800 me-3">
                                                    <i class="la la-plus fs-2 position-absolute"></i>
                                                    <span class="ps-9">${getLocalize('extension.language.buttons.add_plugin')}</span>
                                                </a>
                                            </div>
                                            <div class="d-flex justify-content-center align-items-center">
                                                <a href="${e.plugins_link}" class="btn btn-sm btn-outline btn-outline-dashed bg-light-danger btn-color-gray-800 me-3">
                                                    <i class="la la-bars fs-2 position-absolute"></i>
                                                    <span class="ps-9">${getLocalize('extension.language.buttons.plugin_list')}</span>
                                                </a>
                                            </div>`
                            } else {
                                buttons += `<div class="d-flex justify-content-center align-items-center">
                                                <a href="${e.edit_link}" class="btn btn-sm btn-outline btn-outline-dashed bg-light-danger btn-color-gray-800 me-3">
                                                    <i class="la la-times fs-2 position-absolute"></i>
                                                    <span class="ps-9">${getLocalize('language.panelio.button.edit')}</span>
                                                </a>
                                            </div>`
                            }
                            buttons += `<div class="d-flex justify-content-center align-items-center">
                                            <button data-namespace="${e.namespace}" data-multiple="${e.multiple}" class="btn btn-sm btn-outline btn-outline-dashed bg-light-danger btn-color-gray-800" onclick="extension.uninstall(this, event)">
                                                <i class="la la-times fs-2 position-absolute"></i>
                                                <span class="ps-9">${getLocalize('extension.language.buttons.uninstall')}</span>
                                            </button>
                                        </div>`
                        } else {
                            buttons += `<div class="d-flex justify-content-center align-items-center">
                                            <button data-namespace="${e.namespace}" class="btn btn-sm btn-outline btn-outline-dashed bg-light-success btn-color-gray-800" onclick="extension.install(this, event)">
                                                <i class="la la-download fs-2 position-absolute"></i>
                                                <span class="ps-9">${getLocalize('extension.language.buttons.install')}</span>
                                            </button>
                                        </div>`
                        }

                        buttons += '</div>'

                        return buttons
                    },
                    sortable: false
                }
            ],
            order: [
                [1, "asc"]
            ],
            searching: false,
            lengthChange: false,
            deferRender: true,
            pageLength: localize.list_view.page_limit,
            language: localize.language.datatable
        })
    })
})
