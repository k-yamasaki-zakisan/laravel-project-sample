@extends('adminlte::page')

@section('title', 'superadmin_expositions_edit')

@section('content_header')
<h1>EXPO編集</h1>
@stop

@section('content')
@include('superadmin.expositions.form', [
'exposition' => $exposition,
'action' => route('superadmin.expositions.update', $exposition['id']),
'method' => 'put',
'submit' => '更新',
'is_edit' => true
])
@include('superadmin.exhibitions.index', [
'exposition_id' => $exposition['id']
])
@stop

@section('css')
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