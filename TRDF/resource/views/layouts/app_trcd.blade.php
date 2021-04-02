@extends('adminlte::page', ['configuration_file'=>'adminlte.trcd'])

@push('meta')
	<meta name=”robots” content=”noindex,nofollow”>
@endpush

@push('css')
{{-- 多階層メニュー用CSS --}}
<link rel="stylesheet" href="{{ asset('css/menu-item-top-nav-multilevel.css') }}">
<link rel="stylesheet" href="{{ asset('css/trcd_style.css') }}">
@endpush

<!-- Styles -->
<link href="{{ asset('css/csv.css?' . now()->format('Ymd')) }}" rel="stylesheet">

{{-- Debug用の記述 開発が一段落したら除去すること --}}
@if(Request::ip() === '182.171.249.171')
{{--
	<p>LoginUser:{{ auth()->user()->name }}</p>
	<p>Role:
		@foreach (auth()->user()->getRoleNames() as $role_name)
			{{ $role_name }}&nbsp;
		@endforeach
	</p>
	Permissons<br />
	@foreach (auth()->user()->getAllPermissions() as $permission)
	{{ $permission->name }}<br>
	@endforeach
--}}
@endif

@section('content_header')
  @if(Session::has('error_message'))
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h4><i class="icon fa fa-warning"></i> Error</h4>
      <p>{{ session('error_message') }}</p>
    </div>
  @endif
  @if(Session::has('success_message'))
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
      <h4><i class="icon fa fa-check"></i> Success</h4>
      <p>{{ session('success_message') }}</p>
    </div>
  @endif
  <h1>@yield('title')</h1>
@endsection

@push('js')
	<script src="{{asset('js/trcd/common.js')}}"></script>
@endpush
