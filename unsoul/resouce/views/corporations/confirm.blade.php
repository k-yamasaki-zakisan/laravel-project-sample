<!DOCTYPE html>
<html lang="ja">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="">
  <meta name="keywords" content="">

  <title>法人登録確認画面|運SOUL</title>

  <!-- # CSS area -->
  <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
  <!-- # CSS area -->
</head>

<body class="corporation_register_confirm">
  <div class="body_inner">

    <header>
      <div class="mypage_btn_wrap">
        <a class="mypage_btn" href="{{ route('unsoul.mypage.index') }}">マイページ</a>
      </div>
    </header>

    <div class="container colom_flex">
      <!-- カラムの為のflexbox -->
      <!-- ▼ サイドバー -->
      <div class="sideber">
        <nav class="menu">
          <ul>
            <li><a href="./sub_menu.php">労務システム</a></li>
            <li><a href="{{ route('unsoul.corporations.index') }}" class="active">法人管理</a></li>
            <li><a href="{{ route('unsoul.persons.index') }}">人 管理</a></li>
          </ul>
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
              <a itemprop="item" href="{{ route('unsoul.corporations.register') }}">
                <span itemprop="name">法人登録画面</span>
              </a>
              <meta itemprop="position" content="2" />
            </li>

            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
              <a itemprop="item">
                <span itemprop="name">法人登録確認画面</span>
              </a>
              <meta itemprop="position" content="3" />
            </li>
          </ol>
        </div><!-- breadcrumb end -->
        <!-- ▲ パンくずリスト -->


        <!-- ▼ 登録画面 -->
        <section class="register_area">
          <h3>法人登録確認画面</h3>
          <div class="area_inner">
            <form method="post" action="{{ route('unsoul.corporations.store') }}">
              @csrf
              <table>
                <tbody>
                  <tr>
                    <th colspan="2">法人ID</th>
                    <td colspan="3">{{ $corporation['corporation_id'] ?? null }}</td>
                  </tr>

                  <tr>
                    <th colspan="2">法人名</th>
                    <td colspan="3">{{ $corporation['name'] ?? null }}</td>
                  </tr>

                  <tr>
                    <th colspan="2">フリガナ</th>
                    <td colspan="3">{{ $corporation['phonetic'] }}</td>
                  </tr>

                  <tr>
                    <th colspan="2">資本金</th>
                    <td colspan="3">@isset($corporation['capital']) {{ number_format($corporation['capital']) }}万円
                      @endisset</td>
                  </tr>

                  <tr>
                    <th colspan="2">設立年月</th>
                    <td colspan="3">
                      <span>@isset($corporation['established_year']) {{ $corporation['established_year'] }}年
                        @endisset</span>
                      <span>@isset($corporation['established_month']) {{ $corporation['established_month'] }}月
                        @endisset</span>
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">代表者名</th>
                    <td colspan="3">{{ $corporation['representative'] ?? null }}</td>
                  </tr>

                  <tr>
                    <th rowspan="5">本社住所</th>
                    <th>郵便番号</th>
                    <td colspan="3">{{ $corporation['zip_code1'] ?? null}}-{{ $corporation['zip_code2'] ?? null}}</td>
                  </tr>

                  <tr>
                    <th>都道府県</th>
                    <td colspan="3">{{ $corporation['prefecture_name'] ?? null }}</td>
                  </tr>

                  <tr>
                    <th>市区</th>
                    <td>{{ $corporation['city'] ?? null }}</td>
                    <th>
                      町村
                    </th>
                    <td>{{ $corporation['town'] ?? null }}</td>
                  </tr>

                  <tr>
                    <th>番地</th>
                    <td colspan="3">
                      {{ $corporation['street'] ?? null }}
                    </td>
                  </tr>

                  <tr>
                    <th>建物</th>
                    <td colspan="3">
                      {{ $corporation['building'] ?? null }}
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">本社電話番号</th>
                    <td colspan="3">
                      {{ $corporation['tel'] ?? null }}
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">本社FAX番号</th>
                    <td colspan="3">
                      {{ $corporation['fax'] ?? null }}
                    </td>
                  </tr>
              </table>

              <div class="register_btn_wrap">
                <button type="submit">登録</button>
                {{--<button onclick="history.back()">戻る</button>--}}
                <button class="js-back-button">戻る</button>
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
  <!-- js 
  -->
  $(function()
  {
  $('.js-back-button').click(function(evt)
  {
  evt.preventDefault();
  history.back();
  });
  });
</script>

</html>