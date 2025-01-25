@extends('panelio::layout.layout')

@section('body')
    <form method="post" action="{{ $action }}" class="form d-flex flex-column flex-lg-row" id="form">
        @csrf
        @if($mode === 'edit')
            @method('put')
        @endif
        <input type="hidden" name="type" value="{{ $type }}">
        <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
            <x-boolean-status value="{{ old('status', $plugin->status ?? true) }}" />
        </div>

        <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab_general">
                    <div class="d-flex flex-column gap-7 gap-lg-10">
                        @if($extension->info['multiple'] ?? false)
                            <!--begin::Information-->
                            <div class="card card-flush py-4">
                                <div class="card-header">
                                    <div class="card-title">
                                        <span class="fs-5 fw-bold">{{ trans('package-core::base.cards.proprietary_info') }}</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-0">
                                        <label class="form-label">{{ trans('extension::base.form.plugin.fields.name.title') }}</label>
                                        <input type="text" name="name" class="form-control mb-2" placeholder="{{ trans('extension::base.form.plugin.fields.name.placeholder') }}" value="{{ old('name', $plugin->name ?? null) }}">
                                        @error('name')
                                        <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <!--end::Information-->
                        @endif

                        @if(!empty($fields))
                            <!--begin::Fields-->
                            <div class="card card-flush py-4 mb-10">
                                <div class="card-header">
                                    <div class="card-title">
                                        <span class="fs-5 fw-bold">{{ trans('package-core::base.cards.fields') }}</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @foreach($fields as $field)
                                        {{--begin::field text--}}
                                        @if($field['type'] === 'text')
                                            <div class="mb-10">
                                                <label class="form-label d-flex justify-content-between align-items-center">
                                                    <span {{ ($field['required'] ?? false) ? 'class="required"' : '' }}>{{ $field['label'] ? trans($field['label']) : '' }}</span>
                                                    @if($field['info'] ?? false)
                                                        <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ $field['info'] ? trans($field['info']) : '' }}</div>
                                                    @endif
                                                </label>
                                                <input type="text" name="fields[{{ $field['name'] }}]" class="form-control mb-2" placeholder="{{ $field['placeholder'] ? trans($field['placeholder']) : '' }}" value="{{ old('fields.' . $field['name'], $plugin->fields[$field['name']] ?? $field['default'] ?? null) }}">
                                                @error('fields.' . $field['name'])
                                                    <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endif
                                        {{--end::field text--}}

                                        {{--begin::field number--}}
                                        @if($field['type'] == 'number')
                                            <div class="mb-10">
                                                <label class="form-label d-flex justify-content-between align-items-center">
                                                    <span {{ ($field['required'] ?? false) ? 'class="required"' : '' }}>{{ $field['label'] ? trans($field['label']) : '' }}</span>
                                                    @if($field['info'] ?? false)
                                                        <div class="text-gray-600 fs-7 d-none d-md-block d-lg-none d-xl-block">{{ $field['info'] ? trans($field['info']) : '' }}</div>
                                                    @endif
                                                </label>
                                                <input type="number" name="fields[{{ $field['name'] }}]" class="form-control mb-2" placeholder="{{ $field['placeholder'] ? trans($field['placeholder']) : '' }}" value="{{ old('fields.' . $field['name'], $plugin->fields[$field['name']] ?? $field['default'] ?? null) }}">
                                                @error('fields.' . $field['name'])
                                                    <div class="form-errors text-danger fs-7 mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endif
                                        {{--end::field number--}}
                                    @endforeach
                                </div>
                            </div>
                            <!--end::Fields-->
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
