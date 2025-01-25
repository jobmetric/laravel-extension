// Toggle child row on click
function listShowDetails(data) {
    const date_created_at = new Date(data.created_at)
    const local_date_created_at =
        date_created_at.getFullYear() + '-' +
        String(date_created_at.getMonth() + 1).padStart(2, '0') + '-' +
        String(date_created_at.getDate()).padStart(2, '0') + ' ' +
        String(date_created_at.getHours()).padStart(2, '0') + ':' +
        String(date_created_at.getMinutes()).padStart(2, '0') + ':' +
        String(date_created_at.getSeconds()).padStart(2, '0')

    const date_updated_at = new Date(data.updated_at)
    const local_date_updated_at =
        date_updated_at.getFullYear() + '-' +
        String(date_updated_at.getMonth() + 1).padStart(2, '0') + '-' +
        String(date_updated_at.getDate()).padStart(2, '0') + ' ' +
        String(date_updated_at.getHours()).padStart(2, '0') + ':' +
        String(date_updated_at.getMinutes()).padStart(2, '0') + ':' +
        String(date_updated_at.getSeconds()).padStart(2, '0')

    return `
            <div class="row">
                <div class="col-12 col-md-4 mt-3">
                    <div class="card card-xxl-stretch mb-xl-8 theme-dark-bg-body h-xl-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex flex-column mb-7">
                                <a href="javascript:void(0)" class="text-dark text-hover-primary fw-bold fs-3">${data.name}</a>
                            </div>
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
                                            <div class="fs-5 text-dark fw-bold lh-1" dir="ltr">${local_date_created_at}</div>
                                            <div class="fs-7 text-gray-600 fw-bold">${getLocalize('language.package_core.fields.created_at')}</div>
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
                                            <div class="fs-5 text-dark fw-bold lh-1" dir="ltr">${local_date_updated_at}</div>
                                            <div class="fs-7 text-gray-600 fw-bold">${getLocalize('language.package_core.fields.updated_at')}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`
}

loadScriptsSequentially([
    'assets/vendor/package-core/js/datatable-columns.js',
], function() {
    $(document).ready(function(){
        dt = $('#datatable').on('preXhr.dt', function (e, settings, data) {
        }).DataTable({
            responsive: false,
            autoWidth: false,
            processing: true,
            serverSide: true,
            drawCallback: function(settings) {},
            ajax: {
                url: getLocalize('plugin.route'),
                data: function (data) {
                    if (data.order && data.order.length > 0) {
                        data.sort = data.order[0].dir === 'asc' ? data.columns[data.order[0].column].name : `-${data.columns[data.order[0].column].name}`
                    }

                    // Apply any additional filters as needed
                    data.filter = {
                        name: $('#filter-name').val()
                    }
                }
            },
            columns: [
                // show details
                {
                    data: datatableColumnShowDetailsList,
                    sortable: false
                },
                // checkbox
                {
                    data: datatableColumnSingleCheckList,
                    sortable: false
                },
                // name
                {
                    name: 'name',
                    data: function(e) {
                        return `<div class="align-start text-gray-800 word-no-break">${e.name}</div>`
                    },
                    sortable: true
                },
                // status
                {
                    name: 'status',
                    data: datatableColumnStatusList,
                    sortable: true
                },
                // action
                {
                    data: function(e) {
                        return `<div class="d-flex justify-content-center align-items-center">
                                    <a href="${getLocalize('plugin.route')}/${e.id}/edit" class="btn btn-sm btn-outline btn-outline-dashed bg-light-success btn-color-gray-800">
                                        <i class="la la-edit fs-2 position-absolute"></i>
                                        <span class="ps-9">${getLocalize('language.panelio.button.edit')}</span>
                                    </a>
                               </div>`
                    },
                    sortable: false
                }
            ],
            order: [
                [3, "asc"]
            ],
            searching: false,
            lengthChange: false,
            deferRender: true,
            pageLength: getLocalize('list_view.page_limit'),
            language: getLocalize('language.datatable')
        })
    })
})
