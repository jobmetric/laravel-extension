@extends('panelio::layout.layout')

@section('body')
    <x-list-view name="{{ $label }}" action="javascript:void(0)">
        <thead>
            <tr>
                <th width="1%"></th>
                <th width="39%" class="text-gray-800 auto-width-content">{{ trans('extension::base.list.extension.columns.name') }}</th>
                <th width="10%" class="text-center text-gray-800">{{ trans('extension::base.list.extension.columns.version') }}</th>
                <th width="10%" class="text-center text-gray-800">{{ trans('extension::base.list.extension.columns.author') }}</th>
                <th width="40%" class="text-center text-gray-800">{{ trans('package-core::base.list.columns.action') }}</th>
            </tr>
        </thead>
    </x-list-view>

    @if($hasShowDescriptionInList)
        <div class="mt-10">
            <h6>{{ $description ?? '' }}</h6>
        </div>
    @endif
@endsection
