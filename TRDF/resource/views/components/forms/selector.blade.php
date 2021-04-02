<div class="form-group">
    @isset ( $label )
    <label>{{ $label }}</label>
    @endisset

    <select class="form-control @isset($class) {{ $class }} @endisset" @isset ( $id ) id="{{ $id }}" @endisset @isset (
        $name ) name="{{ $name }}" @endisset>
        <option>{{ $empty_label ?? null }}</option>
        @isset( $list )
        @foreach( $list as $key => $value )
        <option value="{{ $key }}" @if ( isset($selected) && $selected==$key ) selected @endif>{{ $value }}</option>
        @endforeach
        @endisset
    </select>

    @if ( $errors->has($error_key ?? $name ?? null) )
    <span class="text-danger">{{ $errors->first($error_key ?? $name ?? null) }}</span>
    @endif
</div>