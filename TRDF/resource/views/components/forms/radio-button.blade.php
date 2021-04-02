<div class="form-group">
    @isset ( $label )
    <label>{{ $label }}</label>
    @endisset

    <div>
        @foreach( $list as $key => $val )
        <div class="radio">
            <label>
                <input type="radio" @isset ( $name ) name="{{ $name }}" @endisset @isset ( $key ) value="{{ $key }}"
                    @endisset @if( isset($value) && $value==$key) checked @endif />
                {{ $val }}
            </label>
        </div>
        @endforeach
    </div>

    @if ( $errors->has($error_key ?? $name ?? null) )
    <span class="text-danger">{{ $errors->first($error_key ?? $name ?? null) }}</span>
    @endif
</div>