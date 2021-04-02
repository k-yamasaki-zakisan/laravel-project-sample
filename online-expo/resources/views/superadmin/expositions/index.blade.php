@php (config(['adminlte.plugins.Datatables.active'=>true]))
@extends('adminlte::page')

@section('title', 'superadmin_expositions_index')

@section('content_header')
<h1>EXPO一覧</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            EXPOを管理します
            <a href="{{ route('superadmin.expositions.create') }}" class="btn btn-primary" style="color:white;">
                <i class="fas fa-plus"></i> 新規登録
            </a>
        </h3>
        {{--<div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                </button>
            </div>--}}
    </div>
    <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">EXPO名</th>
                    <th class="text-center">開催日</th>
                    <th class="text-center">開催期間</th>
                    <th class="text-center">URLスラッグ</th>
                    <th class="text-center">有効化</th>
                    <th class="text-center">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach( $expositions as $exposition )
                <tr>
                    <td class="text-center">{{ $exposition['name'] }}</td>
                    <td class="text-center">{{ $exposition['start_date'] }}</td>
                    <td class="text-center">{{ $exposition['exposition_days'] }}日間</td>
                    <td class="text-center">{{ $exposition['slug'] }}</td>
                    @if( $exposition['active_flag'])
                    <td class="text-center" style="font-weight: bold;">○</td>
                    @else
                    <td class="text-center">×</td>
                    @endif
                    <td class="project-actions text-center">
                        @if( !empty($exposition['map_path']) )
                        <a class="btn btn-primary btn-sm"
                            href="{{ route('superadmin.expositions.map.show', $exposition['id']) }}">
                            <i class="fas fa-map"></i>MAP
                        </a>
                        @endif
                        <a class="btn btn-info btn-sm"
                            href="{{ route('superadmin.expositions.edit', $exposition['id']) }}">
                            <i class="fas fa-pencil-alt"></i>編集
                        </a>
                        <button type="button" class="js-deleteButton btn btn-danger btn-sm"
                            data-title="Expo：「{{ $exposition['name'] }}」"
                            data-url="{{ route('superadmin.expositions.destroy', $exposition['id']) }}">
                            <i class="fas fa-trash"></i>削除
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<!-- table  -->
@stop

@section('css')

@stop

@section('js')
@component('components.adminlte.modal.delete')
@endcomponent
@component('components.adminlte.form.advanced_modal_message')
@endcomponent
<!-- dataTables.buttons -->
<script src="{{ asset('/vendor/adminlte/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script>
    $(function () {
        $("#example1").DataTable({
          "responsive": true, "lengthChange": false, "autoWidth": false, "order": []
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
      });
</script>
@stop