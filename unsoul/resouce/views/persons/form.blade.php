<!DOCTYPE html>
<html lang="ja">
<head>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="">
<meta name="keywords" content="">

<title>
人登録画面|運SOUL</title>

<!-- # CSS area -->
<link rel="stylesheet" href="{{ asset('/css/style.css') }}">
<!-- # CSS area -->
</head>
<body class="corporation_register">
<div class="body_inner">

  <header>
    <div class="mypage_btn_wrap">
      <a class="mypage_btn" href="{{ route('unsoul.mypage.edit_self') }}">マイページ</a>
    </div>
  </header>

  <div class="container colom_flex"><!-- カラムの為のflexbox -->
    <!-- ▼ サイドバー -->
    <div class="sideber">
      <nav class="menu">
        <ul>
          <li><a href="{{ route('unsoul.labors.index') }}">労務システム</a></li>
          <li><a href="{{ route('unsoul.corporations.index') }}">法人管理</a></li>
          <li><a href="{{ route('unsoul.persons.index') }}" class="active">人 管理</a></li>
        </ul>
      </nav>
    </div>
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
            <a itemprop="item" href="{{ route('unsoul.persons.index') }}">
              <span itemprop="name">人 管理画面</span>
            </a>
            <meta itemprop="position" content="2" />
          </li>

          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="{{ route('unsoul.persons.register') }}">
              <span itemprop="name">人 登録画面</span>
            </a>
            <meta itemprop="position" content="3" />
          </li>
        </ol>
      </div><!-- breadcrumb end -->
      <!-- ▲ パンくずリスト -->


      <!-- ▼ 登録画面 -->
      <section class="register_area">
        <h3>人登録画面</h3>
        <div class="area_inner">
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
{{--フラッシュメッセージ表示（暫定））--}}
@if (session('error_message'))
    <p>{{ session('error_message') }}</p>
@endif
          <form method="POST" action="@yield('form_action')" >
          @csrf
            <table>
              <tbody>
               <tr>
                 <th colspan="2">人ID</th>
                 <td colspan="3">@yield('person_id')</td>
               </tr>

               <tr>
                 <th colspan="2">法人名</th>
                 <td colspan="3">@yield('corporation_name')</td>
               </tr>

               <tr>
                 <th colspan="2">氏名</th>
                 <td colspan="3"><span>@yield('last_name')</span> <span>@yield('first_name')</span></td>
               </tr>

               <tr>
                 <th colspan="2">カナ</th>
                 <td colspan="3"><span>@yield('last_name_kana')</span> <span>@yield('first_name_kana')</span></td>
               </tr>

               <tr>
                 <th colspan="2">生年月日</th>
                 <td colspan="3">@yield('birthday')</td>
               </tr>

               <tr>
                 <th colspan="2">性別</th>
                 <td colspan="3">
                    @yield('gender_id')
                 </td>
               </tr>

               <tr>
                 <th colspan="2">ログインID</th>
                 <td colspan="3">
                   @yield('login_id')
                 </td>
               </tr>

               <tr>
                 <th colspan="2">パスワード</th>
                 <td colspan="3">
                   @yield('password')
                 </td>
               </tr>

               @yield('password_confirm')

             </table>

            <div class="register_btn_wrap">
              <button type="submit">@yield('submit')</button>
              <button type="reset" class="reset_btn">@yield('cancel')</button>
            </div>
          </form>
        </div>
      </section>
      <!-- ▲  登録画面 -->
    </div><!-- area_inner end -->

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
@yield('script')
<!-- js -->
</script>
</html>