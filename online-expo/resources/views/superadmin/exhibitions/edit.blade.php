@extends('adminlte::page')

@section('title', '展示会編集')

@section('content_header')
<h1>展示会編集</h1>
@stop

@section('content')
@include('superadmin.exhibitions.form', [
'exhibition' => $exhibition,
'action' => route('superadmin.exhibitions.update', $exhibition['id']),
'method' => 'put',
'submit' => '更新',
])
@stop

@section('css')
@append


@section('js')
@append