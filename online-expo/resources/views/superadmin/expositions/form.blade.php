<!-- general form elements disabled -->
<div class="card card-success">
    <div class="card-header">
        <h3 class="card-title">入力フォーム</h3>
        {{--<div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove">
                <i class="fas fa-times"></i>
            </button>
        </div>--}}
    </div>

    <!-- /.card-header -->
    <div class="card-body">
        <form action="{{ $action }}" method="post" enctype="multipart/form-data">
            @csrf
            @if( $method === 'put') @method('PUT') @endif
            <!-- エキスポ名 -->
            <div class="form-group">
                <label>EXPO名</label>
                <input type="text" class="form-control" name="exposition_name"
                    value="{{ old('exposition_name') ?? $exposition['name'] ?? '' }}">
            </div>
            @if($errors->has('exposition_name'))
            @foreach($errors->get('exposition_name') as $message)
            <p style="color:red;">{{ $message }}</p>
            @endforeach
            @endif

            <div class="row">
                <div class="col-sm-6">
                    <!-- 開催日 -->
                    <div class="form-group">
                        <label>開催日</label>
                        <div class="input-group date" id="reservationdate" data-target-input="nearest">
                            <input type="text" class="form-control datetimepicker-input" data-target="#reservationdate"
                                kl_vkbd_parsed="true" name="exposition_start_date"
                                value="{{ old('exposition_start_date') ?? $exposition['start_date'] ?? '' }}">
                            <div class="input-group-append" data-target="#reservationdate" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar-alt"></i></div>
                            </div>
                        </div>
                    </div>
                    @if($errors->has('exposition_start_date'))
                    @foreach($errors->get('exposition_start_date') as $message)
                    <p style="color:red;">{{ $message }}</p>
                    @endforeach
                    @endif
                </div>

                <div class="col-sm-6">
                    <!-- 開催期間 -->
                    <label>開催期間</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" kl_vkbd_parsed="true" name="exposition_days"
                            value="{{ old('exposition_days') ?? $exposition['exposition_days'] ?? '' }}">
                        <div class="input-group-append">
                            <span class="input-group-text">日間</span>
                        </div>
                    </div>
                    @if($errors->has('exposition_days'))
                    @foreach($errors->get('exposition_days') as $message)
                    <p style="color:red;">{{ $message }}</p>
                    @endforeach
                    @endif
                </div>
            </div>

            <!-- urlに記入する文字列 -->
            <div class="form-group">
                <label>URLスラッグ</label>
                <input type="text" class="form-control" name="slug"
                    value="{{ old('slug') ?? $exposition['slug'] ?? '' }}">
            </div>
            @if($errors->has('slug'))
            @foreach($errors->get('slug') as $message)
            <p style="color:red;">{{ $message }}</p>
            @endforeach
            @endif

            <!-- アクティブフラグ -->
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-3">
                        <label>来場者ログインの有効化</label><br>
                        <input type="checkbox" name="exposition_active_flag" data-bootstrap-switch=""
                            kl_vkbd_parsed="true" @if( !empty(old()) ) @if( old('exposition_active_flag')==='on' ) )
                            checked="" @endif @else @if( !empty($exposition) && $exposition['active_flag'] ) checked=""
                            @endif @if( empty($exposition) ) checked="" @endif @endif>
                        @if($errors->has('exposition_active_flag'))
                        @foreach($errors->get('exposition_active_flag') as $message)
                        <p style="color:red;">{{ $message }}</p>
                        @endforeach
                        @endif
                    </div>
                    <div class="col-sm-3">
                        <label>事前登録の有効化</label><br>
                        <input type="checkbox" name="exposition_can_pre_registration_flag" data-bootstrap-switch=""
                            kl_vkbd_parsed="true" @if( !empty(old()) ) @if(
                            old('exposition_can_pre_registration_flag')==='on' ) ) checked="" @endif @else @if(
                            !empty($exposition) && $exposition['can_pre_registration_flag'] ) checked="" @endif @if(
                            empty($exposition) ) checked="" @endif @endif>
                        @if($errors->has('exposition_can_pre_registration_flag'))
                        @foreach($errors->get('exposition_can_pre_registration_flag') as $message)
                        <p style="color:red;">{{ $message }}</p>
                        @endforeach
                        @endif
                    </div>
                </div>
            </div>


            <div class="form-group">
                <label>EXPOトップ画像のアップロード</label>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="btn-group w-100" id="input_main_visual_zone">
                            <input id="main_visual" type="file" style="display:none" name="exposition_main_visual"
                                accept="image/*.jpg *.jpeg, *.png">
                            <span class="btn btn-success col fileinput-button"
                                onclick="$('input[id=main_visual]').click();">
                                <i class="fas fa-plus"></i>
                                <span>Add files&nbsp;<i class="fa fa-file"></i></span>
                            </span>
                            <button id="cancel_main_visual" type="button" class="btn btn-warning col">
                                <i class="fas fa-times-circle"></i>
                                <span>Cancel upload</span>
                            </button>
                        </div>
                        <p style="font-size:0.8em;">画像サイズは大きくなりすぎないようにご注意ください</p>
                        @if($errors->has('exposition_main_visual'))
                        @foreach($errors->get('exposition_main_visual') as $message)
                        <p style="color:red;">{{ $message }}</p>
                        @endforeach
                        @endif
                    </div>
                    <div class="col-sm-2"></div>
                    <div class="col-sm-4">
                        @if( !empty($is_edit) )
                        <button type="button" class="btn btn-danger col js-deleteButton" data-title="Expoのトップ画像の削除"
                            data-url="{{ route('superadmin.expositions.mainVisualDelete', $exposition['id']) }}">
                            <i class="fas fa-trash"></i>
                            <span>delete now upload image</span>
                        </button>
                        @endif
                    </div>
                    <div class="col-sm-12">
                        <img id="preview_main_visual" style="max-width:100%;" @if(
                            !empty($exposition['main_visual_path']) )
                            src="{{ asset('storage/' . $exposition['main_visual_path']) }}" @endif>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ $submit }}</button>
            </div>
        </form>
    </div>
</div>

@section('js')
@component('components.adminlte.modal.delete')
@endcomponent
@component('components.adminlte.form.advanced_modal_message')
@endcomponent
<!-- bootstrap-switch -->
<script src="{{ asset('/vendor/adminlte/plugins/bootstrap-switch/js/bootstrap-switch.min.js') }}"></script>
<!-- datetimepicker -->
<script src="{{ asset('/vendor/adminlte/plugins/moment/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/locale/ja.js"></script>
<script
    src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/js/tempusdominus-bootstrap-4.min.js">
</script>
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

    $(function(){
        $('#main_visual').change(function(e){
            if (e.target.files[0]) {
                var file = e.target.files[0];
                var reader = new FileReader();

                //画像でない場合は処理終了
                if(file.type.indexOf("image") < 0){
                    alert(".jpg, .png画像ファイルを指定してください。");
                    return false;
                }

                //アップロードした画像を設定する
                reader.onload = (function(file){
                    return function(e){
                        $("#preview_main_visual").attr("src", e.target.result);
                        $("#preview_main_visual").attr("title", file.name);
                    };
                })(file);
                reader.readAsDataURL(file);
            } else {
                $('#preview_main_visual').removeAttr('src title');
                @if( !empty($exposition['main_visual_path']) ) $("#preview_main_visual").attr("src", "{{ asset('storage/' . $exposition['main_visual_path']) }}"); @endif
            }
        });

        // 画像のキャンセル
        $('#cancel_main_visual').click(function(e){
            $('#preview_main_visual').removeAttr('src title');
            $('#main_visual').val(null);
            @if( !empty($exposition['main_visual_path']) ) $("#preview_main_visual").attr("src", "{{ asset('storage/' . $exposition['main_visual_path']) }}"); @endif
        });
    });
</script>
@append