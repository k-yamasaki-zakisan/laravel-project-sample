@extends('adminlte::page')

@section('title', '展示会ゾーン新規作成')

@section('content_header')
<h1>展示会ゾーン新規登録</h1>
@stop

@section('content')
@include('superadmin.exhibition_zones.form',[
'action' => route('superadmin.exhibition.zones.store', $exhition_id),
'method' => 'post',
'submit' => '登録',
])
@stop


@section('css')
@append

@section('js')
@append