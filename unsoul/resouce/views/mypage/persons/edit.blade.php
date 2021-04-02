<!DOCTYPE html>
<html lang="ja">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="">
  <meta name="keywords" content="">

  <title>マイページ TOP|運SOUL</title>

  <!-- # CSS area -->
  <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
  <!-- # CSS area -->
</head>

<body class="corporation_list">
  <div class="body_inner">

    <header>
      <div class="mypage_btn_wrap">
        <a class="mypage_btn">マイページ</a>
      </div>
    </header>

    <div class="container colom_flex">
      <!-- カラムの為のflexbox -->
      <!-- ▼ サイドバー -->
      <div class="sideber">
        <nav class="menu">
          <ul>
            <li><a href="{{ route('unsoul.labors.index') }}">労務システム</a></li>
            <li><a href="{{ route('unsoul.corporations.index') }}">法人管理</a></li>
            <li><a href="{{ route('unsoul.persons.index') }}">人 管理</a></li>
          </ul>

          <hr>

          <h3>マイページ</h3>
          <ul>
            <li><a href="{{ route('unsoul.mypage.edit_self') }}" class="active">本人情報</a></li>
          </ul>

          <hr>

          <h4>事業所</h4>
          <!-- TODO:事業所の登録から引っ張る -->
          <ul>
            <li><a href="{{ route('unsoul.mypage.corporations.edit') }}">本社</a></li>
            <!-- 事務所リスト表示 -->
            @foreach( $offices as $office_id => $office_name)
            <li><a
                href="{{ route('unsoul.mypage.offices.edit', ['office_id' => $office_id]) }}">{{ $office_name }}事業所</a>
            </li>
            @endforeach

          </ul>

          <hr class="dotted">

          <div class="regist_btn_wrap">
            <a href="{{ route('unsoul.mypage.offices.register') }}" class="regist_btn">事業所追加</a>
          </div>

        </nav>
      </div>
      <!-- ▲ サイドバー -->

      <!-- ▼ メインコンテンツ -->
      <main>
        <div class="breadcrumb">
          <!-- ▼ パンくずリスト -->
          <ol itemscope itemtype="https://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
              <a itemprop="item" href="{{ route('unsoul.home') }}">
                <span itemprop="name">ホーム</span>
              </a>
              <meta itemprop="position" content="1" />
            </li>

            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
              <a itemprop="item" href="{{ route('unsoul.mypage.edit_self') }}">
                <span itemprop="name">マイページ 本人情報編集画面</span>
              </a>
              <meta itemprop="position" content="2" />
            </li>
          </ol>
        </div><!-- breadcrumb end -->
        <!-- ▲ パンくずリスト -->

        <!-- ▼ 登録画面 -->
        <section class="register_area">
          <h3>本人情報編集画面</h3>
          {{--エラー表示用（暫定）--}}
          @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
              @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          @endif
          <div class="area_inner">
            <form method="POST" action="{{ route('unsoul.mypage.update_self') }}">
              @method('PUT')
              @csrf
              <table class="no_border_table">
                <tbody>

                  <tr>
                    <th colspan="2">氏名</th>
                    <td colspan="3">
                      <span>姓<input type="text" size="5" name="last_name"
                          value="{{ old('last_name') ?? $last_name }}"></span>
                      <span>名<input type="text" size="5" name="first_name"
                          value="{{ old('first_name') ?? $first_name }}"></span>
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">カナ</th>
                    <td colspan="3">
                      <span>姓<input type="text" size="5" name="last_name_kana"
                          value="{{ old('last_name_kana') ?? $last_name_kana }}"></span>
                      <span>名<input type="text" size="5" name="first_name_kana"
                          value="{{ old('first_name_kana') ?? $first_name_kana }}"></span>
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">生年月日</th>
                    <td colspan="3"><input type="text" name="birthday" value="{{ old('birthday') ?? $birthday }}"></td>
                  </tr>

                  <tr>
                    <th colspan="2">性別</th>
                    <td colspan="3">
                      @foreach( $genders as $gender_id => $gender_name )
                      <label for="{{ $gender_id }}">{{ $gender_name }}</label>
                      <input type="radio" name="gender_id" id="{{ $gender_id }}" value="{{ $gender_id }}" @if (
                        $gender_id==$now_gender_id ) checked @endif>
                      @endforeach
                    </td>
                  </tr>

              </table>

              <div class="register_btn_wrap">
                <button type="submit">確認</button>
                <button type="reset" class="reset_btn">キャンセル</button>
              </div>
            </form>
          </div>
        </section>
        <!-- ▲  登録画面 -->

      </main>
      <!-- ▲ メインコンテンツ -->
    </div><!-- colom_flex(カラムの為のflex)end -->

  </div>
</body>
<!-- js -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>
  $('.reset_btn').on('click', function(){
	location.href = "{{ route('unsoul.home') }}";
});
<!-- js -->
</script>

</html>


${'basic_pension_number.$i}