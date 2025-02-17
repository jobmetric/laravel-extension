<label class="form-label d-flex justify-content-between align-items-center">
    @if($required ?? false)
        <span class="required">{{ __('extension::base.component.plugin_field.title') }}</span>
    @else
        <span>{{ __('extension::base.component.plugin_field.title') }}</span>
    @endif
</label>
<select name="plugin_id" class="form-select" @if($id) id="{{ $id }}" @endif data-control="select2" data-placeholder="{{ __('extension::base.component.plugin_field.placeholder') }}" @if($parent) data-dropdown-parent="{{ $parent }}" @endif>
    <option value=""></option>
    @foreach($extensions as $item)
        <optgroup label="{{ $item['name'] }}">
            @foreach($item['plugins'] as $plugin_id => $plugin_name)
                <option value="{{ $plugin_id }}" @if($value == $plugin_id) selected @endif>{{ $plugin_name }}</option>
            @endforeach
        </optgroup>
    @endforeach

</select>
