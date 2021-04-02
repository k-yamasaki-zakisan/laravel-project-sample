@extends('layouts.development')
@section('title', '人登録画面')
@section('content')
@php
dump($errors) ?? 'なし';
@endphp
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="container mt-5 p-lg-5 bg-light">
    <form method="post" action="{{ $form_action }}">
	@csrf 

	<!--人ID-->
        <div class="form-group row">
            <label for="person_id" class="col-sm-2 col-form-label">人ID</label>
            <div class="col-sm-10">
                @yield('person_id')
		<div class="invalid-feedback">入力してください</div>
            </div>
        </div>
        <!--/人ID-->

	<!--法人名-->
        <div class="form-group row">
            <label for="name" class="col-sm-2 col-form-label">法人名</label>
            <div class="col-sm-10">
                @yield('corporation_name')
		<div class="invalid-feedback">入力してください</div>
            </div>
        </div>
        <!--/法人名-->

        <!--/氏名-->
	<div class="form-row mb-4">
            <div class="col-md-6">
                <label for="last_name">名字</label>
                @yield('last_name')
		<div class="invalid-feedback">
                    入力してください
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="first_name">名前</label>
                @yield('first_name')
		<div class="invalid-feedback">
                    入力してください
                </div>
            </div>
        </div>
        <!--/氏名-->

	<!--/カナ氏名-->
        <div class="form-row mb-4">
            <div class="col-md-6">
                <label for="last_name_kana">カタカナ名字</label>
                @yield('last_name_kana')
		<div class="invalid-feedback">
                    入力してください
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="first_name_kana">カタカナ名前</label>
                @yield('first_name_kana')
		<div class="invalid-feedback">
                    入力してください
                </div>
            </div>
        </div>
        <!--/カナ氏名-->

        <!--生年月日-->
        <div class="form-group row">
            <label for="birthday" class="col-sm-2 col-form-label">生年月日</label>
            <div class="col-sm-10">
                @yield('birthday')
		<div class="invalid-feedback">入力してください</div>
            </div>
        </div>
        <!--/生年月日-->

	<!--ログインID-->
        <div class="form-group row">
            <label for="login_id" class="col-sm-2 col-form-label">ログインID</label>
            <div class="col-sm-10">
                @yield('login_id')
		<div class="invalid-feedback">入力してください</div>
            </div>
        </div>
        <!--/法人名-->

        <!--パスワード-->
        <div class="form-group row mb-5">
            <label for="password" class="col-sm-2 col-form-label">パスワード</label>
            <div class="col-sm-10">
                @yield('password')
		<div class="invalid-feedback">入力してください</div>
                <small id="passwordHelpBlock" class="form-text text-muted">パスワードは、文字と数字を含めて8～20文字で、空白、特殊文字、絵文字を含むことはできません。</small>
            </div>
        </div>
        <!--/パスワード-->

	<!--パスワード確認用-->
	@yield('password_confirm')
	<!--/パスワード確認用-->

        <!--性別-->
        <div class="form-group">
        	<legend class="col-form-label col-sm-2">性別</legend>
                @yield('gender_id')
        </div>
        <!--/性別-->

	<!--ボタンブロック-->
            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary">@yield('submit')</button>
        	<a class="btn btn-warning" href="{{ $back_to }}" role="button">@yield('cancel')</a>    
	</div>
        </div>
        <!--/ボタンブロック-->

    </form>

</div><!-- /container -->

@endsection


@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
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
<link rel="stylesheet" href="./css/style.css">
<!-- # CSS area -->
</head>
<body class="corporation_register">
<div class="body_inner">

  <header>
    <div class="mypage_btn_wrap">
      <a class="mypage_btn">マイページ</a>
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
          <form>
            <table>
              <tbody>
               <tr>
                 <th colspan="2">人ID</th>
                 <td colspan="3"><input type="text" readonly></td>
               </tr>
               
               <tr>
                 <th colspan="2">法人名</th>
                 <td colspan="3"><input type="text"><span><a href="{{ route('unsoul.persons.register.search_corporation') }}">検索</a></span></td>
               </tr>
               
               <tr>
                 <th colspan="2">氏名</th>
                 <td colspan="3"><span>性<input type="text" size="5"></span> <span>名<input type="text" size="5"></span></td>
               </tr>
               
               <tr>
                 <th colspan="2">カナ</th>
                 <td colspan="3"><span>性<input type="text" size="5"></span> <span>名<input type="text" size="5"></span></td>
               </tr>
               
               <tr>
                 <th colspan="2">生年月日</th>
                 <td colspan="3"><input type="text"></td>
               </tr>
               
               <tr>
                 <th colspan="2">性別</th>
                 <td colspan="3">
                    <input type="radio" name="sex" id="man">
                    <label for="man">男性</label>
                    <input type="radio" name="sex" id="woman">
                    <label for="woman">女性</label>
                 </td>
               </tr>
               
               <tr>
                 <th colspan="2">ログインID</th>
                 <td colspan="3">
                   <input type="text">
                 </td>
               </tr>
               
               <tr>
                 <th colspan="2">パスワード</th>
                 <td colspan="3">
                   <input type="text">
                 </td>
               </tr>
               
               <tr>
                 <th colspan="2">パスワード確認</th>
                 <td colspan="3">
                   <input type="text">
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
<!-- js -->
</script>
</html>
