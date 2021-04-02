<!DOCTYPE html>
<html lang="ja">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="">
  <meta name="keywords" content="">

  <title>
    資格管理画面|運SOUL</title>

  <!-- # CSS area -->
  <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
  <!-- # CSS area -->
</head>

<header>
  <div class="mypage_btn_wrap">
    <a class="mypage_btn" href="{{ route('unsoul.mypage.edit_self') }}">マイページ</a>
  </div>
</header>

<div class="container colom_flex">
  <!-- カラムの為のflexbox -->
  <!-- ▼ サイドバー -->
  <div class="sideber">
    <nav class="menu">
      <ul>
        <li><a href="{{ route('unsoul.labors.index') }}" class="active">労務システム</a></li>
        <li><a href="{{ route('unsoul.corporations.index') }}">法人管理</a></li>
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
          <a itemprop="item" href="{{ route('unsoul.person_licenses.index', $employee_link_key) }}">
            <span itemprop="name">資格管理画面</span>
          </a>
          <meta itemprop="position" content="2" />
        </li>

      </ol>
    </div><!-- breadcrumb end -->
    <!-- ▲ パンくずリスト -->

    <h3>資格管理画面</h3>
    <div class="area_inner">
      @if ($errors->any())
      <ul class="error_list">
        @foreach( $errors->all() as $error )
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      @endif
      {{--フラッシュメッセージ表示（暫定））--}}
      @if (session('success_message'))
      <p>{{ session('success_message') }}</p>
      @endif
      @if (session('error_message'))
      <p style="color: red;">{{ session('error_message') }}</p>
      @endif
      <form id="licenseVal">
        @csrf
        <table class="category_table table_heading">
          <tr>
            <th colspan="6">資格カテゴリ</th>
          </tr>
          <?php
// 配列の要素数をカウント
$count = count($license_categories);

for($i = 0; $i < $count; $i++){

if($i % 6 == 0){

print("<tr>");
print("<td><a href='".route('unsoul.licenses.register', $employee_link_key)."?license_category_id=".$license_categories[$i]['license_category_id']."'>".$license_categories[$i]['name']."</a></td>");

}elseif($i % 6 == 5){
print("<td><a href='".route('unsoul.licenses.register', $employee_link_key)."?license_category_id=".$license_categories[$i]['license_category_id']."'>".$license_categories[$i]['name']."</a></td>");
print("</tr>");

}else{
print("<td><a href='".route('unsoul.licenses.register', $employee_link_key)."?license_category_id=".$license_categories[$i]['license_category_id']."'>".$license_categories[$i]['name']."</a></td>");
}

}

print("<td><a href='".route('unsoul.licenses.register_free_license', $employee_link_key)."'>フリー入力</a></td>");

?>
        </table>

        <table class="no_border_table">
          <tr>
            <th>取得日</th>
            {{--<td><input type="date" name="acquisition" value="{{ old('acquisition') }}"></td>--}}
            <td>
              年：<input type="text" name="acquisition_year" value="{{ old('acquisition_year') }}" list="year">
              <datalist id="year">
                @foreach( $year_range as $year)
                <option value="{{ $year }}"></option>
                @endforeach
              </datalist>
            </td>
            <td>
              月：<input type="text" name="acquisition_month" value="{{ old('acquisition_month') }}" list="month">
              <datalist id="month">
                @foreach( $month_range as $month)
                <option value="{{ $month }}"></option>
                @endforeach
              </datalist>
            </td>
            <td>
              日：<input type="text" name="acquisition_date" value="{{ old('acquisition_date') }}" list="date">
              <datalist id="date">
                @foreach( $date_range as $date)
                <option value="{{ $date }}"></option>
                @endforeach
              </datalist>
            </td>
            <th>有効期限</th>
            <td><input type="date" name="expired_at" value="{{ old('expired_at') }}"></td>
          </tr>
        </table>

        <table class="table_heading">
          <tr>
            <th>資格番号</th>
            <th>資格一覧</th>
          </tr>

          @foreach( $licenses as $license )
          <tr class="select_license">
            <td>{{ $license['license_id'] }}</td>
            <td>{{ $license['name'] }}</td>
          </tr>
          @endforeach

        </table>

        <table class="table_heading">
          <tr>
            <th>備考</th>
          </tr>
          <tr>
            <td><textarea name="note">{{ old('note') }}</textarea></td>
          </tr>
        </table>
        <table>
          <div class="register_btn_wrap">
            <button type="submit">追加</button>
            <button type="reset" class="reset_btn">キャンセル</button>
          </div>
        </table>
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
  var license_name = "";
  //変数に選択情報の格納と選択行の色変え処理
  $('.select_license').on('click', function(){
    $('.select_license').css('background-color','');
    license_id = $(this).children('td')[0].innerText;
    $(this).css('background-color','#B0E0E6');
    //alert(' name: '+ license_name);
  });

  //選択した資格名をpost
  $('#licenseVal').submit(function() {
    $(this).attr("action", "{{ route('unsoul.licenses.store', $employee_link_key) }}");
    $(this).attr("method", "POST");
    var id = "<input type='hidden' class='id' name='license_id' value=''>";
    $('#licenseVal').append(id);
    $('.id').val(license_id);
    return true;
  });

  $('.reset_btn').on('click', function(){
    location.href = "{{ route('unsoul.person_licenses.index', $employee_link_key) }}";
  });
<!-- js -->
</script>

</html>