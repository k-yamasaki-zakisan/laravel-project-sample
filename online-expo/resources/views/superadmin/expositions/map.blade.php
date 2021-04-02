@extends('layouts.map_setting')

@section('title', 'superadmin_expositions_map')

@section('content_header')
<h1>EXPOマップzone登録</h1>
@stop

@section('content')
<h2>{{ $exposition['name'] }}：会場設定</h2>
<div class="row">
    <div class="col-sm-3">
        <div class="form-group">
            <label>企業選択</label>
            <select class="form-control" id="exhibitor_select">
                <option></option>
                @foreach($exhibitors as $exhibitor)
                <option value="{{ $exhibitor['id'] }}">{{ $exhibitor['name'] }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-sm-3"></div>
    <div class="col-sm-2">
        <form id="mapUpdateForm" method="post"
            action="{{ route('superadmin.expositions.map_zone.update', $exposition['id']) }}">
            @csrf
            @method('PUT')
            <button type="submit" id="rectSaveBtn" class="btn btn-primary">座標を確定</button>
        </form>
    </div>
    <div class="col-sm-2">
        <button type="button" id="rectDeleteBtn" class="btn btn-default">選択した図形を削除</button>
    </div>
    <div class="col-sm-2">
        <a class="btn btn-default" href="{{ route('superadmin.expositions.index') }}">Expo一覧へ戻る</a>
    </div>
</div>
<div class="map_content">
    <img src="{{ asset('storage/' . $exposition['map_path']) }}">
    <canvas id="map"></canvas>
</div>

@stop

@section('css')
<style>
    .map_content {
        position: relative;
        height: 100vh;
    }

    .map_content img {
        position: absolute;
        top: 0;
        left: 0;
        width: auto;
        height: auto;
        max-width: inherit;
        padding-bottom: 20px;
    }
</style>
@append

@section('js')
@component('components.adminlte.form.advanced_modal_message')
@endcomponent
<input type="hidden" id="dummy-input" name="" value="">
<script src="{{ asset('/vendor/jquery_plugins/fabric-4.3.1/fabric.min.js') }}"></script>
<script type="text/javascript">
    $(function(){
    var w = $('.map_content img').width();
    var h = $('.map_content img').height();
    $('#map').attr('width', w);
    $('#map').attr('height', h);

    const data = {};
    let dummyInput = $('#dummy-input');
    data['mapZoneInput'] = dummyInput.clone(true).removeAttr('id');
    dummyInput.remove();

    const boxData = {};

    const canvas = new fabric.Canvas('map');

    const select = document.querySelector('#exhibitor_select');

    let exhibitors = document.querySelectorAll("#exhibitor_select option");
{{--
    fabric.Image.fromURL("{{ asset('storage/' . $exposition['map_path']) }}", function(oImg) {
        oImg.scaleToWidth(canvas.width);
        canvas.setBackgroundImage(oImg, canvas.renderAll.bind(canvas));
    });
--}}
    @foreach($exhibitors as $exhibitor)
        @if( !empty($exhibitor['map_width']) && !empty($exhibitor['map_height']) && !empty($exhibitor['map_left']) && !empty($exhibitor['map_top']) )
            canvas.add(
                new fabric.Rect({
                    width: {{ $exhibitor['map_width'] }},
                    height: {{ $exhibitor['map_height'] }},
                    left: {{ $exhibitor['map_left'] }},
                    top: {{ $exhibitor['map_top'] }},
                    fill: 'rgba(0,0,255,0.5)',
                    lockRotation: true,
                    metaData: {
                        id: {{ $exhibitor['id'] }}
                    }
                })
             );

             boxData[{{ $exhibitor['id'] }}] = {
                 width: {{ $exhibitor['map_width'] }},
                 height: {{ $exhibitor['map_height'] }},
                 left: {{ $exhibitor['map_left'] }},
                 top: {{ $exhibitor['map_top'] }}
             }

        @endif
    @endforeach

    canvas.on('mouse:down', function(options) {
        if (options.target) {
            let id = canvas.getActiveObject().metaData?.id;
            for (var i = 0, len = exhibitors.length; i < len; i++) {
                if( exhibitors[i].value == id) select.options[i].selected = true;
            }
        }
    });

    canvas.on('mouse:up', function(options) {
        if (options.target) {
            let id = canvas.getActiveObject().metaData?.id;
            boxData[id] = {
                width: options.target.width * options.target.scaleX,
                height: options.target.height * options.target.scaleY,
                left: options.target.left,
                top: options.target.top
            }
        }
    });

    select.addEventListener('change', function(){
        let index =  this.selectedIndex;
        let id = exhibitors[ index ].value;

        // 空要素なら終了
        if( id == '' ) return;

        if(id in boxData) {
            canvas._objects.forEach(function( rect ) {
                if(rect.metaData.id == id) {
                    canvas.setActiveObject(rect);
                    canvas.renderAll();
                    return
                }
            });
        } else {
            canvas.add(
                new fabric.Rect({
                    width: 50,
                    height: 100,
                    left: 0,
                    top: 0,
                    fill: 'rgba(0,0,255,0.5)',
                    lockRotation: true,
                    metaData: {
                        id: id
                    }
                })
            );

            boxData[id] = {
                width: 100,
                height: 100,
                left: 0,
                top: 0
            }
        }
    });

    $('#rectDeleteBtn').click(function() {
        //選択されているオブジェクトを削除する。
        let activeObjects = canvas.getActiveObjects();

        if (activeObjects != null) {
            activeObjects.forEach(obj => {

                let id = obj.metaData.id;

                //対象オブジェクトを削除
                canvas.remove(obj);

                delete boxData[id]

            });
        }

        // selecterを初期化
        select.options[0].selected = true;
    });

    $('#rectSaveBtn').click(function() {
        Object.keys(boxData).forEach(exhibitor_id => {
            let input_exhibitor_id = data['mapZoneInput'].clone(true);
            input_exhibitor_id.attr('name', `exhibitors[${exhibitor_id}][exhibitor_id]`);
            input_exhibitor_id.val(exhibitor_id);
            $('#mapUpdateForm').append(input_exhibitor_id);

            let input_width = data['mapZoneInput'].clone(true);
            input_width.attr('name', `exhibitors[${exhibitor_id}][width]`);
            input_width.val(boxData[exhibitor_id]["width"]);
            $('#mapUpdateForm').append(input_width);

            let input_height = data['mapZoneInput'].clone(true);
            input_height.attr('name', `exhibitors[${exhibitor_id}][height]`);
            input_height.val(boxData[exhibitor_id]["height"]);
            $('#mapUpdateForm').append(input_height);

            let input_left = data['mapZoneInput'].clone(true);
            input_left.attr('name', `exhibitors[${exhibitor_id}][left]`);
            input_left.val(boxData[exhibitor_id]["left"]);
            $('#mapUpdateForm').append(input_left);

            let input_top = data['mapZoneInput'].clone(true);
            input_top.attr('name', `exhibitors[${exhibitor_id}][top]`);
            input_top.val(boxData[exhibitor_id]["top"]);
            $('#mapUpdateForm').append(input_top);
        });

    });
});
</script>
@append