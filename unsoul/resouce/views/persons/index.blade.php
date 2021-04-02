<!DOCTYPE html>
<html lang="ja">
<head>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="">
<meta name="keywords" content="">

<title>人 管理画面|運SOUL</title>

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
            <a itemprop="item" href="{{ route('unsoul.persons.index') }}">
              <span itemprop="name">人 管理画面</span>
            </a>
            <meta itemprop="position" content="2" />
          </li>
        </ol>
      </div><!-- breadcrumb end -->
      <!-- ▲ パンくずリスト -->
      
      <!-- ▼ ①検索エリア -->
      <section class="search_area">
        <h3>検索エリア</h3>
        <div class="area_inner">
          <form action={{ route('unsoul.persons.index') }}>  
            <table>
              <tbody>
               <tr>
                 <th>氏名</th>
                 <td>
                   <input type="text" name="search[person][full_name]"
                     value="{{ old('search.person.full_name', $search['person']['full_name'] ?? null) }}"
                   >
                 </td>
                 <th>法人名</th>
                 <td>
                   <input type="text" name="search[corporation][name]"
                     value="{{ old('search.corporation.name', $search['corporation']['name'] ?? null) }}"
                   >
                 </td>
               </tr>
             </table>

            <button type="submit">検索</button>
          </form>
        </div>
      </section>
      <!-- ▲ ①検索エリア -->

      <!-- ▼ ②人登録ボタン エリア -->
      <section class="corporation_register_area">
        <h3>人の新規登録</h3>
        <div class="area_inner">
          <div class="regist_btn_wrap">
            <a href="{{ route('unsoul.persons.register') }}" tyle="button" class="regist_btn">新規登録</a>
          </div>
        </div>
      </section>
      <!-- ▲ ②人登録ボタン エリア -->

      <!-- ▼ ③人一覧エリア -->
      <section class="list_area corporation_list_area">
        <h3>人一覧のエリア</h3>
        <div class="area_inner">

          <div class="table_wrap">
            <table>
              <tr>
                <th>ID</th>
                <th>氏名</th>
                <th>所属の法人</th>
                <th>生年月日</th>
              </tr>

              <!-- TODO: ここからループ -->
              @foreach( $persons as $person )
                <tr>
                  <td>{{ $person['person_id'] }}</td>
                  <td>{{ $person['full_name'] ?? null }}</td>
                  <td>{{ $person['corporation_name'] ?? null }}</td>
                  @if($person['birthday'] == null)
                    <td>{{  null }}</td>
                  @else
                    <td>{{ \Carbon\Carbon::parse($person['birthday'])->format('Y年m月d日') }}</td>
                  @endif
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>

          <div class="pager_area">
            {{ $persons->links() }}
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
<!-- js -->
</script>
</html>
