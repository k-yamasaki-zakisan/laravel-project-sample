<div class="form-group">
    @isset ( $label )
    <label>{{ $label }}</label>
    @endisset

    <input type="text" class="form-control @isset($class) {{ $class }} @endisset" @isset ( $id ) id="{{ $id }}"
        @endisset @isset ( $name ) name="{{ $name }}" @endisset @isset ( $list_id ) list="{{ $list_id }}" @endisset>

    <datalist id="@isset( $list_id ){{ $list_id}}@endisset">
        @foreach( $list as $key => $val )
        <option value="{{ $val }}" />
        @endforeach
    </datalist>

    @if ( $errors->has($error_key ?? $name ?? null) )
    <span class="text-danger">{{ $errors->first($error_key ?? $name ?? null) }}</span>
    @endif
</div>