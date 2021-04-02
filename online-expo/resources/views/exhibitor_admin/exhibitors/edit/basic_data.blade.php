<!-- start of exhibitor_admin.exhibitors.edit.basic_data -->
<!-- general form elements disabled -->
<div class="card card-info">
    <div class="card-body">
        <form action="{{ route('exhibitor_admin.exhibitors.update', [$slug]) }}" method="post">
            @csrf
            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                        <label>出展社名</label>
                        <input type="text" class="form-control" placeholder="{{ $exhibitor->name }}" disabled>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>出展展示会</label>
                        <input type="text" class="form-control" placeholder="{{ $exhibition->name }}" disabled>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                        <label>出展ゾーン</label>
                        {{ Form::select('exhibition_zone_id', $exhibition_zones, old('exhibition_zone_id', $exhibitor->exhibition_zone_id), ['class'=>'form-control select2'] ) }}
                    </div>
                </div>
            </div>

            <!-- 郵便番号 -->
            <div class="form-group">
                <label>郵便番号</label>
                <div class="row">
                    <div class="col-1">
                        <p class="float-right">〒</p>
                    </div>
                    <div class="col-2"><input type="text" class="form-control"
                            value="{{ old('zip_code1', $exhibitor->zip_code1) }}" name="zip_code1" maxlength="3"></div>
                    <div class="col-2"><input type="text" class="form-control"
                            value="{{ old('zip_code2', $exhibitor->zip_code2) }}" name="zip_code2" maxlength="4"></div>
                </div>
            </div>
            @if($errors->has('zip_code1'))
            <p style="color:red;">{{$errors->first('zip_code1')}}</p>
            @endif
            @if($errors->has('zip_code2'))
            <p style="color:red;">{{$errors->first('zip_code2')}}</p>
            @endif

            <!-- 都道府県 -->
            <div class="form-group">
                <label>都道府県</label>
                {{--
                    <input type="text" class="form-control" value="{{ old('address', $exhibitor->address) }}"
                name="address">
                --}}
                {{ Form::select('prefecture_id', $prefectures, old('prefecture_id', $exhibitor->prefecture_id), ['class'=>'form-control select2', 'style'=>'width: 100%;'] ) }}
            </div>
            @if($errors->has('address'))
            <p style="color:red;">{{$errors->first('address')}}</p>
            @endif

            <!-- 所在地 -->
            <div class="form-group">
                <label>所在地</label>
                <div class="row">
                    <input type="text" class="form-control" value="{{ old('address', $exhibitor->address) }}"
                        name="address">
                </div>
            </div>
            @if($errors->has('address'))
            <p style="color:red;">{{$errors->first('address')}}</p>
            @endif

            <!-- 建物名 -->
            <div class="form-group">
                <label>建物名</label>
                <div class="row">
                    <input type="text" class="form-control"
                        value="{{ old('building_name', $exhibitor->building_name) }}" name="building_name">
                </div>
            </div>
            @if($errors->has('building_name'))
            <p style="color:red;">{{$errors->first('building_name')}}</p>
            @endif

            <!-- TEL -->
            <div class="form-group">
                <label>TEL</label>
                <input type="text" class="form-control" value="{{old('tel',$exhibitor->tel)}}" name="tel">
            </div>
            @if($errors->has('tel'))
            <p style="color:red;">{{$errors->first('tel')}}</p>
            @endif

            <!-- URL -->
            <div class="form-group">
                <label>URL</label>
                <input type="text" class="form-control" value="{{ old('url', $exhibitor->url ) }}" name="url">
            </div>
            @if($errors->has('url'))
            <p style="color:red;">{{$errors->first('url')}}</p>
            @endif

            <!-- 企業プロフィール -->
            <div class="form-group">
                <label>企業プロフィール</label>
                <textarea id="summernote" name="profile_text">{{ $exhibitor->profile_text }}</textarea>
            </div>
            @if($errors->has('profile_text'))
            <p style="color:red;">{{$errors->first('profile_text')}}</p>
            @endif

            <div class="card-footer">
                <button type="submit" class="btn btn-info float-right">保存</button>
            </div>

        </form>
    </div>
    <!-- /.card-body -->
</div>
<!-- /.card -->

@section('css')
<link rel="stylesheet" href={{ asset('vendor/adminlte/plugins/summernote/summernote-bs4.css') }}>
<link rel="stylesheet" href={{ asset('vendor/adminlte/plugins/select2/css/select2.min.css') }}>
<link rel="stylesheet" href={{ asset('css/adminlte_fix/select2/select2_fix.css') }}>
@append
@section('js')
<script src={{ asset("vendor/adminlte/plugins/summernote/summernote-bs4.js") }}></script>
<script src={{ asset("vendor/adminlte/plugins/select2/js/select2.full.min.js") }}></script>
<script type="text/javascript">
    $(function () {
        // Summernote
        $('#summernote').summernote()

        $('.select2').select2()

{{--
        // CodeMirror
        CodeMirror.fromTextArea(document.getElementById("codeMirrorDemo"), {
            mode: "htmlmixed",
            theme: "monokai"
        });
--}}
    })
</script>
@append

<!-- end of exhibitor_admin.exhibitors.edit.basic_data -->