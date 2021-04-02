@extends('adminlte::page')

@section('title', '展示会一覧')

@section('content_header')
<h1>展示会一覧</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">展示会を管理します</h3>
        {{--
    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse">
        <i class="fas fa-minus"></i>
      </button>
      <button type="button" class="btn btn-tool" data-card-widget="remove">
        <i class="fas fa-times"></i>
      </button>
    </div>
--}}
    </div>
    <div class="card-body">
        <form action="{{ route('superadmin.exhibitions.update_sort') }}" method="post">
            @csrf
            {{--@method('PUT')--}}
            <ul class="exhibition-list" data-widget="exhibition-list">
                @foreach( $exhibitions as $exhibition )
                <div class="card">
                    <input type="hidden" name="sort_indexs[{{ $exhibition['id']}}]" value="{{ $exhibition['id'] }}">
                    <div class="card-header handle handle-exhibition"
                        style="background-color: {{ $exhibition['background_color'] ?? '#EEEEEE' }};">
                        <h4 class="card-title"
                            style="cursor: move; color: {{ $exhibition['main_color'] ?? '#000000' }};">
                            {{ $exhibition['name'] }}</h4>
                        <div class="float-right">
                            <a href="{{ route('superadmin.exhibitions.edit', $exhibition['id']) }}">
                                <i class="fas fa-edit" style="padding-right:20px;"></i>
                            </a>
                            <i class="fas fa-trash js-deleteButton mouse-over"
                                data-title="展示会：「{{ $exhibition['name'] }}」"
                                data-url="{{ route('superadmin.exhibitions.destroy', $exhibition['id']) }}"
                                style="padding-right:20px; color: red;"></i>
                            <i class="fas fa-caret-down fa-lg mouse-over"
                                onclick="obj=document.getElementById('open{{ $exhibition["id"] }}').style; obj.display=(obj.display=='none')?'table-row':'none';"></i>
                        </div>
                    </div>
                    <div class="card-body" id="open{{ $exhibition['id'] }}" style="display: none; clear: both;">
                        <ul class="exhibition-zone-list" data-widget="exhibition-zone-list">
                            @foreach( $exhibition['exhibition_zones'] as $exhibition_zone )
                            <div class="card">
                                <input type="hidden" name="sort_indexs[{{ $exhibition['id'] }}][]"
                                    value="{{ $exhibition_zone['id'] }}">
                                <div class="card-header handle handle-exhibition-zone">
                                    <h4 class="card-title" style="cursor: move;">{{ $exhibition_zone['name'] }}</h4>
                                    <div class="float-right">
                                        <a
                                            href="{{ route('superadmin.exhibition.zones.edit', [ 'exhibition_id' => $exhibition['id'], 'exhibition_zone_id' => $exhibition_zone['id'] ]) }}">
                                            <i class="fas fa-edit" style="padding-right:20px;"></i>
                                        </a>
                                        <i class="fas fa-trash js-deleteButton mouse-over"
                                            data-title="展示会ゾーン：「{{ $exhibition_zone['name'] }}」" data-url="{{ route('superadmin.exhibition.zones.destroy', [
                        'exhibition_id' => $exhibition['id'],
                        'exhibition_zone_id' => $exhibition_zone['id']
                       ]) }}" style="padding-right:20px; color: red;"></i>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </ul>
                        <a class="btn btn-primary float-right"
                            href="{{ route('superadmin.exhibition.zones.create', $exhibition['id']) }}">
                            <i class="fas fa-plus"></i>ゾーンの追加
                        </a>
                    </div>
                </div>
                @endforeach
            </ul>
            <div class="card-footer clearfix">
                <button type="submit" class="btn btn-primary">ソート順を確定する</button>
                <a href="{{ route('superadmin.exhibitions.create') }}" class="btn btn-primary float-right">
                    <i class="fas fa-plus"></i>展示会追加
                </a>
            </div>
        </form>
    </div>
</div>
@stop


@section('css')
<link rel="stylesheet" href={{ asset('vendor/adminlte/plugins/jquery-ui/jquery-ui.min.css') }}>
<style>
    .mouse-over {
        cursor: pointer;
    }
</style>
@append


@section('js')
<script src={{ asset("vendor/adminlte/plugins/jquery-ui/jquery-ui.min.js") }}></script>
@component('components.adminlte.modal.delete')
@endcomponent
@component('components.adminlte.form.advanced_modal_message')
@endcomponent
<script type="text/javascript">
    $(function () {
  // jQuery UI sortable for the todo list
  $('.exhibition-list').sortable({
    placeholder: 'sort-highlight',
    handle: '.handle-exhibition',
    forcePlaceholderSize: true,
    zIndex: 999999
  });

  $('.exhibition-zone-list').sortable({
    placeholder: 'sort-highlight',
    handle: '.handle-exhibition-zone',
    forcePlaceholderSize: true,
    zIndex: 999999
  });
});
</script>
@append