<div class="form-group">
    @isset ( $label )
    <label>{{ $label }}</label>
    @endisset

    <div class="input-group date">
        <div class="input-group-addon">
            <i class="fa fa-calendar"></i>
        </div>
        <input type="text" class="form-control trcd-datepicker @isset($class){{ $class }}@endisset" @isset ( $name )
            name="{{ $name }}" @endisset @isset ( $value ) value="{{ $value }}" @endisset autocomplete="off" readonly
            style="background-color: inherit">
    </div>

    @if ( $errors->has($error_key ?? $name ?? null) )
    <span class="text-danger">{{ $errors->first($error_key ?? $name ?? null) }}</span>
    @endif
</div>