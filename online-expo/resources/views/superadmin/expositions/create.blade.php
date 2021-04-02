@extends('adminlte::page')

@section('title', 'superadmin_expositions_create')

@section('content_header')
<h1>EXPO新規登録</h1>
@stop

@section('content')
@include('superadmin.expositions.form',[
'action' => route('superadmin.expositions.store'),
'method' => 'post',
'submit' => '登録',
])
@stop

@section('css')
<link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script>
    $(function () {
            //datetimepicker
            $('#reservationdate').datetimepicker({
                format: 'L'
            });

            //bootstrap-switch
            $("input[data-bootstrap-switch]").each(function(){
                $(this).bootstrapSwitch('state', $(this).prop('checked'));
            });
        });
</script>
@stop