@if($errors->any() || session('flash_message'))
@section('css')
<link rel="stylesheet" href={{ asset('vendor/adminlte/plugins/toastr/toastr.min.css') }}>
@append

@section('js')
<script src={{ asset("vendor/adminlte/plugins/toastr/toastr.min.js") }}></script>
<script type="text/javascript">
	$(function () {

		$(window).on('load', function(){
@if(session('flash_message'))
			// 成功メッセージ
			toastr.options = {
				"closeButton": true,
				"debug": false,
				"newestOnTop": false,
				"progressBar": false,
				"positionClass": "toast-top-right",
				"preventDuplicates": false,
				"onclick": null,
				"showDuration": "300",
				"hideDuration": "1000",
				"timeOut": "10000",
				"extendedTimeOut": "1000",
				"showEasing": "swing",
				"hideEasing": "linear",
				"showMethod": "fadeIn",
				"hideMethod": "fadeOut"
			};
			toastr.success('{{ session('flash_message') }}');
@endif

@if($errors->any() || session('flash_message'))
			// エラーメッセージ
			toastr.options = {
				"closeButton": true,
				"debug": false,
				"newestOnTop": false,
				"progressBar": false,
				"positionClass": "toast-top-right",
				"preventDuplicates": false,
				"onclick": null,
				"showDuration": "300",
				"hideDuration": "1000",
				"timeOut": "0",
				"extendedTimeOut": "0",
				"showEasing": "swing",
				"hideEasing": "linear",
				"showMethod": "fadeIn",
				"hideMethod": "fadeOut"
			};
@foreach ($errors->all() as $error)
			toastr.error("{{$error}}");
@endforeach
@endif
		});

	})
</script>
@append

@endif