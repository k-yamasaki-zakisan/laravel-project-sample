{{--
	GETの場合は$methodは不要
--}}
<form @isset( $method ) method="POST" @endisset @isset( $action ) action="{{ $action }}" @endisset>
    @isset( $method )
    @method($method)
    @csrf
    @endisset
    {{ $slot }}
</form>