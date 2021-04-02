<div class="form-group">
    @isset ( $label )
    <label>{{ $label }}</label>
    @endisset

    <input type="datetime-local" class="form-control" @isset ( $name ) name="{{ $name }}" @endisset @isset ( $value )
        value="{{ $value }}" @endisset />

    @if ( $errors->has($error_key ?? $name ?? null) )
    <span class="text-danger">{{ $errors->first($error_key ?? $name ?? null) }}</span>
    @endif
</div>