@php (config(['adminlte.plugins.Datatables.active'=>true]))
@extends('adminlte::page')

@section('title', '出展社一覧')

@section('content_header')
<h1>出展社一覧</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            EXHIBITORを管理します
            <a href="{{ route('superadmin.exhibitors.create') }}" class="btn btn-primary" style="color:white;">
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
                    <th class="text-center">出展社名</th>
                    <th class="text-center">出展展示会</th>
                    <th class="text-center">出展ゾーン</th>
                    <th class="text-center">住所</th>
                    <th class="text-center">電話番号</th>
                    <th class="text-center">説明文</th>
                    <th class="text-center">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach( $exhibitors as $exhibitor )
                <tr>
                    <td class="text-center">{{ $exhibitor['name'] }}</td>
                    <td class="text-center">{{ $exhibitor['exhibition']['name'] }}</td>
                    <td class="text-center">{{ $exhibitor['exhibition_zone']['name'] }}</td>

                    <td class="text-center">
                        {{ $exhibitor['prefecture']['name'].$exhibitor['address'].$exhibitor['address'].$exhibitor['building_name'] }}
                    </td>
                    <td class="text-center">{{ $exhibitor['tel'] }}</td>
                    @if(!empty($exhibitor['profile_text']))
                    <td class="text-center" style="font-weight: bold;">○</td>
                    @else
                    <td class="text-center">×</td>
                    @endif
                    </td>
                    <td class="project-actions text-center">
                        <a class="btn btn-info btn-sm"
                            href="{{ route('superadmin.exhibitors.select_exhibitor_users', $exhibitor['id']) }}">
                            <i class="fas fa-street-view"></i>企業ユーザー選択
                        </a>
                        <a class="btn btn-info btn-sm"
                            href="{{ route('superadmin.exhibitors.edit', $exhibitor['id']) }}">
                            <i class="fas fa-pencil-alt"></i>編集
                        </a>
                        <button type="button" class="js-deleteButton btn btn-danger btn-sm"
                            data-title="Expo：「{{ $exhibitor['name'] }}」"
                            data-url="{{ route('superadmin.exhibitors.destroy', $exhibitor['id']) }}">
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