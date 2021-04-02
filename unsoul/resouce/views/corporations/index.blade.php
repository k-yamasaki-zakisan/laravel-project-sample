<!DOCTYPE html>
<html lang="ja">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="">
  <meta name="keywords" content="">

  <title>法人 管理画面|UNSOUL</title>

  <!-- # CSS area -->
  <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
  <!-- # CSS area -->
</head>

<body class="corporation_list">
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
            <li><a href="{{ route('unsoul.labors.index') }}">労務システム</a></li>
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
              <a itemprop="item" href="{{ route('unsoul.corporations.index') }}">
                <span itemprop="name">法人 管理画面</span>
              </a>
              <meta itemprop="position" content="2" />
            </li>
          </ol>
        </div><!-- breadcrumb end -->
        <!-- ▲ パンくずリスト -->

        <!-- ▼ ①検索エリア -->
        <section class="search_area">
          <h2>検索エリア</h2>
          <div class="area_inner">
            <form action={{ route('unsoul.corporations.index') }}>
              <table>
                <tbody>
                  <tr>
                    <th>法人ID</th>
                    <td>
                      <x-errors.message key="search.corporation_id" />
                      <input type="text" name="search[corporation_id]"
                        value="{{ old('search.corporation_id', $search['corporation_id'] ?? null) }}" />
                    </td>
                    <th>法人名</th>
                    <td>
                      <x-errors.message key="search.name" />
                      <input type="text" name="search[name]"
                        value="{{ old('search.name', $search['name'] ?? null) }}" />
                    </td>
                  </tr>
              </table>

              <button type="submit">検索</button>
          </div>
        </section>
        <!-- ▲ ①検索エリア -->

        <!-- ▼ ②法人登録ボタン エリア -->
        <section class="corporation_register_area">
          <h2>法人の新規登録</h2>
          <div class="area_inner">
            <div class="regist_btn_wrap">
              <a href="{{ route('unsoul.corporations.register') }}" tyle="button" class="regist_btn">新規登録</a>
            </div>
          </div>
        </section>
        <!-- ▲ ②法人登録ボタン エリア -->

        <!-- ▼ ③法人一覧エリア -->
        <section class="list_area corporation_list_area">
          <h2>法人一覧のエリア</h2>
          <div class="area_inner">

            <div class="table_wrap">
              <table>
                <tr>
                  <th>ID</th>
                  <th>法人名</th>
                  <th>本社住所</th>
                  <th>電話番号</th>
                </tr>

                @foreach( $corporations as $corporation )
                <tr>
                  <td>{{ $corporation['corporation_id'] }}</td>
                  <td>{{ $corporation['name'] ?? null }}</td>
                  <td>{{ $corporation['head_office']['address'] ?? null }}</td>
                  <td>{{ $corporation['head_office']['tel'] ?? null }}</td>
                </tr>
                @endforeach
                </tbody>
              </table>
            </div>

            <div class="pager_area">
              {{ $corporations->links() }}
              <!-- TODO: ここにページャー -->
              <!--<nav class="pager">
              <ol>
                <li><a href=""><span class="prev">prev</span></a></li>
                <li><a href="">1</a></li>
                <li><a href="">2</a></li>
                <li><a href="">3</a></li>
                <li><a href=""><span class="next">next</span></a></li>
              </ol>
            </nav>-->
              <!-- TODO: ここまでページャー -->
            </div>

          </div><!-- area_inner end -->
        </section>
        <!-- ▲ ③法人一覧エリア -->

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
</script>

</html>