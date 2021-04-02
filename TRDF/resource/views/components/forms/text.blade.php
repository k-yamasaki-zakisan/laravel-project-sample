<div class="form-group @isset( $div_class){{ $div_class }}@endisset">
    @isset ( $label )
    <label>{{ $label }}</label>
    @endisset

    <input type="text" class="form-control" @isset ( $id ) id="{{ $id }}" @endisset @isset ( $name ) name="{{ $name }}"
        @endisset @isset ( $value ) value="{{ $value }}" @endisset @if(!empty($disabled)) disabled @endif>

    @if ( $errors->has($error_key ?? $name ?? null) )
    <span class="text-danger">{{ $errors->first($error_key ?? $name ?? null) }}</span>
    @endif
</div>