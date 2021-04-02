<div class="form-group">
    @isset ( $label )
    <label>{{ $label }}</label>
    @endisset

    <textarea class="form-control" @isset ( $name ) name="{{ $name }}" @endisset @isset ( $rows ) rows="{{ $rows }}"
        @endisset>{{ $value ?? null }}</textarea>

    @if ( $errors->has($error_key ?? $name ?? null) )
    <span class="text-danger">{{ $errors->first($error_key ?? $name ?? null) }}</span>
    @endif
</div>