<!DOCTYPE html>
<html lang="ja">
<head>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="">
<meta name="keywords" content="">

<title>
法人検索画面|運SOUL</title>

<!-- # CSS area -->
<link rel="stylesheet" href="{{ asset('/css/style.css') }}">
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

          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="{{ route('unsoul.persons.register') }}">
              <span itemprop="name">人 登録画面</span>
            </a>
            <meta itemprop="position" content="3" />
          </li>

          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="{{ route('unsoul.persons.register.search_corporation') }}">
              <span itemprop="name">法人検索画面</span>
            </a>
            <meta itemprop="position" content="4" />
          </li>

        </ol>
      </div><!-- breadcrumb end -->
      <!-- ▲ パンくずリスト -->


      <!-- ▼ 登録画面 -->
      <section class="corporate_search_area">
        <h3>法人検索画面</h3>
        <div class="area_inner">
          <form method="GET" action="{{ route('unsoul.persons.register.search_corporation') }}">
            <table>
              <tbody>
               <tr>
                 <th colspan="2">法人名</th>
                 <td colspan="3"><input type="text" name="search[corporation][name]" value="{{ old('search.corporation.name', $search['corporation']['name'] ?? null) }}"></td>
               </tr>

               <tr>
                 <th colspan="2">住所</th>
                 <td colspan="3"><input type="text" name="search[office][address]" value="{{ old('search.office.address', $search['office']['address'] ?? null) }}"></td>
               </tr>
            </table>
           <div class="register_btn_wrap">
             <button type="submit">検索</button>
           </div>
          </form>
          <form id="corporationVal">
          @csrf
            <table>

                <tr>
                 <th>No</th>
                 <th>法人名</th>
                 <th>住所</th>
               </tr>

             @foreach( $corporations as $corporation )
               <tr class="select_corporation">
                 <td>{{ $corporation['corporation_id'] }}</td>
                 <td>{{ $corporation['name'] }}</td>
                 <td>{{ $corporation['address'] ?? null }}</td>
               </tr>
             @endforeach

             </table>

            <div class="register_btn_wrap">
              <button type="submit">選択</button>
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
  var corporation_id = "";
  var corporation_name = "";
  //変数に選択情報の格納と選択行の色変え処理
  $('.select_corporation').on('click', function(){
    $('.select_corporation').css('background-color','');
    corporation_id = $(this).children('td')[0].innerText;
    corporation_name = $(this).children('td')[1].innerText;
    $(this).css('background-color','#B0E0E6');
    //alert('No: '+ corporaiton_id + ' name: '+ corporaiton_name);
  });

  //選択した企業idと企業名をpost
  $('#corporationVal').submit(function() {
    $(this).attr("action", "{{ route('unsoul.persons.register') }}");
    $(this).attr("method", "POST");
    var id = "<input type='hidden' class='id' name='corporation[id]' value=''>";
    var name = "<input type='hidden' class='name' name='corporation[name]' value=''>";
    $('#corporationVal').append(id);
    $('#corporationVal').append(name);
    $('.id').val(corporation_id);
    $('.name').val(corporation_name);
    return true;
  });

  $('.reset_btn').on('click', function(){
    location.href = "{{ route('unsoul.persons.register') }}";
  });
</script>
<!-- js -->
</html>
