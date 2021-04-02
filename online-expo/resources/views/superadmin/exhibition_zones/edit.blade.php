@extends('adminlte::page')

@section('title', '展示会ゾーン編集')

@section('content_header')
<h1>展示会ゾーン編集</h1>
@stop


@section('content')
@include('superadmin.exhibition_zones.form', [
'exhibition_zone' => $exhibition_zone,
'action' => route('superadmin.exhibition.zones.update', [
'exhibition_id' => $exhibition_id,
'exhibition_zone_id' => $exhibition_zone['id']
]),
'method' => 'put',
'submit' => '更新',
])
@stop


@section('css')
@append


@section('js')
@append