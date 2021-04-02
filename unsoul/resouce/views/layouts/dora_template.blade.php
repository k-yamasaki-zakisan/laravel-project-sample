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
<link rel="stylesheet" href="./css/style.css">
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
