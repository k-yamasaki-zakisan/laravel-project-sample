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
        <form class="h-adr" action="{{ $action }}" method="post">
            @csrf
            @if( $method === 'put') @method('PUT') @endif
            <!-- 出展社 -->
            <div class="form-group">
                <label>出展社</label>
                <input type="text" class="form-control" maxlength="90" name="name"
                    value="{{ old('name') ?? $exhibitor['name'] ?? '' }}">
                @if($errors->has('name'))
                <span style="color:red;">{{$errors->first('name')}}</span>
                @endif
            </div>

            <!-- 出展社カナ -->
            <div class="form-group">
                <label>出展社カナ</label>
                <input type="text" class="form-control" maxlength="90" name="name_kana"
                    value="{{ old('name_kana') ?? $exhibitor['name_kana'] ?? '' }}">
                @if($errors->has('name_kana'))
                <span style="color:red;">{{$errors->first('name_kana')}}</span>
                @endif
            </div>

            <!-- 出展企業名カナ（SORT） -->
            <div class="form-group">
                <label>ソート順のための出展企業名カナ</label>
                <input type="text" class="form-control" maxlength="90" name="name_kana_for_sort"
                    value="{{ old('name_kana_for_sort') ?? $exhibitor['name_kana_for_sort'] ?? '' }}">
                @if($errors->has('name_kana_for_sort'))
                <span style="color:red;">{{$errors->first('name_kana_for_sort')}}</span>
                @endif
            </div>


            <!-- 会社名 -->
            <div class="form-group">
                <label>会社名</label>
                　 <select class="custom-select" name="company_id">
                    <option value="">会社名を選んでください</option>
                    @isset($exhibitor)
                    @foreach( $companies as $val )
                    @if( $exhibitor['company_id'] == $val['id'])
                    <option value="{{ $val['id'] }}" @if(old('company_id')==$val['id']) selected @endif selected>
                        {{ $val['name'] }} </option>
                    @else
                    <option value="{{ $val['id'] }}" @if(old('company_id')==$val['id']) selected @endif>
                        {{ $val['name'] }} </option>
                    @endif
                    @endforeach
                    @else
                    @foreach( $companies as $val )
                    <option value="{{ $val['id'] }}" @if(old('company_id')==$val['id']) selected @endif>
                        {{ $val['name'] }} </option>
                    @endforeach
                    @endisset
                </select>
                @if($errors->has('company_id'))
                <span style="color:red;">{{$errors->first('company_id')}}</span>
                @endif

            </div>

            <!-- 出展展示会 -->
            <div class="form-group">
                <label>出展展示会</label>
                　 <select class="custom-select" name="exhibition_id">
                    <option value="">出展展示会を選んでください</option>
                    @isset($exhibitor)
                    @foreach( $exhibitions as $val )
                    @if( $exhibitor['exhibition_id'] == $val['id'])
                    <option value="{{ $val['id'] }}" @if(old('exhibition_id')==$val['id']) selected @endif selected>
                        {{ $exhibitor['exhibition']['name'] }} </option>
                    @else
                    <option value="{{ $val['id'] }}" @if(old('exhibition_id')==$val['id']) selected @endif>
                        {{ $val['name'] }} </option>
                    @endif
                    @endforeach
                    @else
                    @foreach( $exhibitions as $val )
                    <option value="{{ $val['id'] }}" @if(old('exhibition_id')==$val['id']) selected @endif>
                        {{ $val['name'] }} </option>
                    @endforeach
                    @endisset
                </select>
                @if($errors->has('exhibition_id'))
                <span style="color:red;">{{$errors->first('exhibition_id')}}</span>
                @endif
            </div>

            <!-- 出展ゾーン -->
            <div class="form-group">
                <label>出展ゾーン</label>
                <select id="exhibition_zone" class="custom-select" name="exhibition_zone_id">
                    <option value="">出展ゾーンを選んでください</option>
                    @foreach( $exhibition_zones as $exhibition_zone )
                    @if( old('exhibition_zone_id') )
                    @if( $exhibition_zones[old('exhibition_zone_id')]['exhibition_id'] ===
                    $exhibition_zone['exhibition_id'] )
                    <option value="{{ $exhibition_zone['id'] }}" @if( old('exhibition_zone_id')==$exhibition_zone['id']
                        ) selected @endif> {{ $exhibition_zone['name'] }} </option>
                    @endif
                    @elseif( isset($exhibitor) )
                    @if( $exhibitor['exhibition_id'] === $exhibition_zone['exhibition_id'] )
                    <option value="{{ $exhibition_zone['id'] }}" @if(
                        $exhibitor['exhibition_zone_id']==$exhibition_zone['id'] ) selected @endif>
                        {{ $exhibition_zone['name'] }} </option>
                    @endif
                    @endif
                    @endforeach
                </select>
                @if($errors->has('exhibition_zone_id'))
                <span style="color:red;">{{$errors->first('exhibition_zone_id')}}</span>
                @endif
            </div>


            <!-- 郵便番号 -->
            <div class="form-group">
                <!-- class="p-country-name" は郵便番号自動入力の為必須-->
                <span class="p-country-name" style="display:none;">Japan</span>
                <label>郵便番号</label>
                <div class="row">
                    <div class="col-0">
                        <p class="float-right">〒</p>
                    </div>
                    <div class="col-2"><input type="text" class="p-postal-code form-control"
                            value="{{ old('zip_code1') ?? $exhibitor['zip_code1'] ?? '' }}" name="zip_code1"
                            maxlength="3"></div>ー
                    <div class="col-2"><input type="text" class="p-postal-code form-control"
                            value="{{ old('zip_code2') ?? $exhibitor['zip_code2'] ?? '' }}" name="zip_code2"
                            maxlength="4"></div>
                </div>
                @if($errors->has('zip_code1'))
                <span style="color:red;">{{$errors->first('zip_code1')}}</span>
                @endif
                <br>
                @if($errors->has('zip_code2'))
                <span style="color:red;">{{$errors->first('zip_code2')}}</span>
                @endif

                <!-- 都道府県 -->
                <div class="form-group">
                    <label>都道府県</label>
                    　 <select class="p-region-id custom-select" name="prefecture_id">
                        <option value="">都道府県を選択下さい</option>
                        @isset($exhibitor)
                        @foreach($prefectures as $val)
                        @if( $exhibitor['prefecture_id'] == $val['id'])
                        <option value="{{ $val['id'] }}" @if(old('prefecture_id')==$val['id']) selected @endif selected>
                            {{ $val['name'] }} </option>
                        @else
                        <option value="{{ $val['id'] }}" @if(old('prefecture_id')==$val['id']) selected @endif>
                            {{ $val['name'] }} </option>
                        @endif
                        @endforeach
                        @else
                        @foreach($prefectures as $val)
                        <option value="{{ $val['id'] }}" @if(old('prefecture_id')==$val['id']) selected @endif>
                            {{ $val['name'] }} </option>
                        @endforeach
                        @endisset
                    </select>
                    @if($errors->has('prefecture_id'))
                    <span style="color:red;">{{$errors->first('prefecture_id')}}</span>
                    @endif
                </div>

                <!-- 所在地 -->
                <div class="form-group">
                    <label>所在地</label>
                    <div class="row">
                        <input type="text" class="p-region p-locality p-street-address form-control" maxlength="200"
                            value="{{ old('address') ?? $exhibitor['address'] ?? '' }}" name="address">
                        @if($errors->has('address'))
                        <span style="color:red;">{{$errors->first('address')}}</span>
                        @endif
                    </div>
                </div>

                <!-- 建物名 -->
                <div class="form-group">
                    <label>建物名</label>
                    <div class="row">
                        <input type="text" class="form-control" maxlength="200"
                            value="{{ old('building_name') ?? $exhibitor['building_name'] ?? '' }}"
                            name="building_name">
                        @if($errors->has('building_name'))
                        <span style="color:red;">{{$errors->first('building_name')}}</span>
                        @endif
                    </div>
                </div>

                <!-- TEL -->
                <div class="form-group">
                    <label>TEL</label>
                    <input type="text" class="form-control" maxlength="18"
                        value="{{ old('tel') ?? $exhibitor['tel'] ?? '' }}" name="tel">
                    @if($errors->has('tel'))
                    <span style="color:red;">{{$errors->first('tel')}}</span>
                    @endif
                </div>

                <!-- URL -->
                <div class="form-group">
                    <label>URL</label>
                    <input type="text" class="form-control" maxlength="250"
                        value="{{ old('url') ?? $exhibitor['url'] ?? '' }}" name="url">
                    @if($errors->has('url'))
                    <span style="color:red;">{{$errors->first('url')}}</span>
                    @endif
                </div>

                <!-- 企業プロフィール -->
                <div class="form-group">
                    <label>企業プロフィール</label>
                    @if( $action === route('superadmin.exhibitors.store'))
                    <textarea id="summernote" type="text" class="form-control" maxlength="1950"
                        name="profile_text">{{ old('profile_text') ?? '' }}</textarea>
                    @elseif( $action === route('superadmin.exhibitors.update', $exhibitor['id']))
                    <textarea id="summernote" type="text" class="form-control" maxlength="1950"
                        name="profile_text">{{ $exhibitor['profile_text'] ?? old('profile_text') ?? '' }}</textarea>
                    @else
                    @endif
                    <span>文字数:</span>
                    <span id="maxContentPost"></span>
                    <span>/ 2000</span>
                    @if($errors->has('profile_text'))
                    <span style="color:red;">{{$errors->first('profile_text')}}</span>
                    @endif
                </div>

                <!-- 出展プラン -->
                <div class="form-group">
                    <label for="plan_id">出展プラン</label>
                    　 <select id="plan_id" class="custom-select" name="plan_id">
                        <option value="">出展プランを選んでください</option>
                        @foreach( $plans as $plan )
                        <option value="{{ $plan['id'] }}" @if( old('plan_id')==$plan['id'] ) selected @elseif(
                            isset($exhibitor['plan_id']) && $exhibitor['plan_id']===$plan['id'] ) selected @endif>
                            {{ $plan['display_name'] }} </option>
                        @endforeach
                    </select>
                    @if($errors->has('plan_id'))
                    <span style="color:red;">{{$errors->first('plan_id')}}</span>
                    @endif
                </div>

                <!-- 更新ボタン -->
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">{{ $submit }}</button>
                </div>

        </form>
    </div>
</div>

@section('css')
<link rel="stylesheet" href={{ asset('vendor/adminlte/plugins/summernote/summernote-bs4.css') }}>
<link rel="stylesheet" href={{ asset('css/adminlte_fix/superadmin/list_fix.css') }}>
@append
@section('js')
@component('components.adminlte.form.advanced_modal_message')
@endcomponent
<!-- Summernote -->
<script src={{ asset("vendor/adminlte/plugins/summernote/summernote-bs4.js") }}></script>
<!-- 郵便番号自動入力CDN -->
<script src="https://yubinbango.github.io/yubinbango/yubinbango.js" charset="UTF-8"></script>
<script>
    $(document).ready(function() {

  var meuEditor = $('#summernote');
  var limite = 2000;
  meuEditor.summernote({
  toolbar: [
    // [groupName, [list of button]]
    ['style', ['bold', 'italic', 'underline', 'clear']],
    ['font', ['strikethrough', 'superscript', 'subscript']],
    ['fontsize', ['fontsize']],
    ['color', ['color']],
    ['para', ['ul', 'ol', 'paragraph']],
    ['height', ['height']]
  ],
    callbacks: {
      onInit: function() {
        var t = $('#summernote').summernote('code');
        // 関数の呼び出し(htmlタグ削除)
        var t = delete_html_tag(t);
        var t = AddOrdDeleteCss(t,limite);
        // 初期入力されている文字数を表示
          $('#maxContentPost').text(t.trim().length);
      },
      // キーを押した時のイベント
      onKeydown: function(e) {
        var t = e.currentTarget.innerText;
        // 関数の呼び出し
        var t = delete_html_tag(t);
        var t = AddOrdDeleteCss(t,limite);
      },
      // キーを離した時のイベント
      onKeyup: function(e) {
        var t = e.currentTarget.innerText;
        // 関数の呼び出し
        var t = delete_html_tag(t);
        var t = AddOrdDeleteCss(t,limite);
      },

    }

  });
});

// htmlタグの削除
function delete_html_tag(t) {
  var t = t.replace(/<("[^"]*"|'[^']*'|[^'">])*>/g,'');
  var t = t.replace(/\r?\n/g,"");
  var t = t.replace( /\s|&nbsp;/g , '');
  return t;
}

// htmlタグの削除
function AddOrdDeleteCss(t,limite) {
  if (t.trim().length >= limite) {
    $('#maxContentPost').css('color','red');
    $('#maxContentPost').text(t.trim().length);
  }else{
    $('#maxContentPost').css('color','');
    $('#maxContentPost').text(t.trim().length);
  }
  return t;
}

$(function() {
  //セレクトボックスが切り替わったら発動
  $('select[name="exhibition_id"]').change(function() {

    //選択したvalue値を変数に格納
    var exhibition_id = $(this).val();
    // 出展ゾーンのデータをJSで取得
    var exhibition_zones = @json($exhibition_zones);
    // 配列の作成
    var exhibition_zone_array = new Array();

    // 出展展示会と紐づく出展ゾーンを取得
    for (let index in exhibition_zones) {
      if( exhibition_id == exhibition_zones[index]['exhibition_id'] ){
        exhibition_zone_array.push(exhibition_zones[index]);
      }
    };
/*
    for (let i = 0; i < exhibition_zones.length; i++) {
      if( exhibition_id == exhibition_zones[i]['exhibition_id'] ){
        exhibition_zone_array.push(exhibition_zones[i]);
      }
    }
*/
    // selectタグを取得する
    let exhibition_zone_select = document.getElementById("exhibition_zone");

    exhibition_zone_select.innerHTML = '';
    let option = document.createElement('option');
    option.innerHTML = '出展ゾーンを選んでください';
    exhibition_zone_select.appendChild(option);

    // オプションタグの追加
    for (let i = 0; i < exhibition_zone_array.length; i++) {
        let exhibition_zone_option = document.createElement('option');
       // optionタグのtextに出展ゾーンを代入
       exhibition_zone_option.text = exhibition_zone_array[i]['name'];
       // optionタグのvalueに出展idを代入
       exhibition_zone_option.value = exhibition_zone_array[i]['id'];
       // selectタグの子要素にoptionタグを追加する
       exhibition_zone_select.appendChild(exhibition_zone_option);
    }

  });
});
</script>
@append