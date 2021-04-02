<!DOCTYPE html>
<html lang="ja">
<head>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="">
<meta name="keywords" content="">

<title>従業員登録画面|運SOUL</title>

<!-- # CSS area -->
<link rel="stylesheet" href="{{ asset('/css/style.css') }}">
<!-- # CSS area -->
</head>
<body class="employee_register">
<div class="body_inner">

  <header>
    <div class="mypage_btn_wrap">
      <a class="mypage_btn" href="{{ route('unsoul.mypage.index') }}">マイページ</a>
    </div>
  </header>

  <div class="container colom_flex"><!-- カラムの為のflexbox -->
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
            <a itemprop="item" href="{{ route('unsoul.labors.index') }}">
              <span itemprop="name">従業員一覧画面</span>
            </a>
            <meta itemprop="position" content="2" />
          </li>

          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="{{ route('unsoul.labors.register') }}">
              <span itemprop="name">従業員登録画面</span>
            </a>
            <meta itemprop="position" content="3" />
          </li>
        </ol>
      </div><!-- breadcrumb end -->
      <!-- ▲ パンくずリスト -->

      
      <!-- ▼ 登録画面 -->
      <section class="register_area">
        <h3>従業員登録画面</h3>
        <div class="area_inner">
          <form>
            <table>
              <tbody>
               <tr>
                 <th colspan="2">従業員番号</th>
                 <td colspan="3"><input type="text"></td>
               </tr>
               
               <tr>
                 <th colspan="2">氏名</th>
                 <td colspan="3"><span>姓<input type="text" size="5"></span> <span>名<input type="text" size="5"></span></td>
               </tr>
               
               <tr>
                 <th colspan="2">カナ</th>
                 <td colspan="3"><span>姓<input type="text" size="5"></span> <span>名<input type="text" size="5"></span></td>
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
                 <th colspan="2">雇用形態</th>
                 <td colspan="3">
                   <select>
                     <option>****</option>
                     <option>****</option>
                   </select>
                 </td>
               </tr>
               
               <tr>
                 <th rowspan="5">住所</th>
                 <th>郵便番号</th>
                 <td colspan="3">
                   <input type="text" size="3">-<input type="text" size="4">
                 </td>
               </tr>
               
               <tr>
                 <th>都道府県</th>
                 <td colspan="3">
                   <input type="text">
                 </td>
               </tr>
               
               <tr>
                 <th>市区</th>
                 <td>
                   <input type="text">
                 </td>
                 <th>
                   町村
                 </th>
                 <td>
                   <input type="text">
                 </td>
               </tr>
               
               <tr>
                 <th>番地</th>
                 <td colspan="3">
                   <input type="text">
                 </td>
               </tr>
               
               <tr>
                 <th>建物名</th>
                 <td colspan="3">
                   <input type="text">
                 </td>
               </tr>
               
               <tr>
                 <th colspan="2">住所フリガナ</th>
                 <td colspan="3">
                   <input type="text">
                 </td>
               </tr>

               <tr>
                 <th rowspan="2">連絡先</th>
                 <th>携帯</th>
                 <td colspan="3">
                   <input type="text">
                 </td>
               </tr>
               
               <tr>
                 <th>メールアドレス</th>
                 <td colspan="3">
                   <input type="text">
                 </td>
               </tr>
               
               <tr>
                 <th colspan="2">入社年月日</th>
                 <td colspan="3">
                   <input type="text">
                 </td>
               </tr>
               
               <tr>
                 <th colspan="2">退職年月日</th>
                 <td colspan="3">
                   <input type="text" readonly>
                 </td>
               </tr>
               
               <tr>
                 <th colspan="2">休職年月日</th>
                 <td colspan="3">
                   <input type="text" readonly>
                 </td>
               </tr>

               <tr>
                 <th colspan="2">復職年月日</th>
                 <td colspan="3">
                   <input type="text" readonly>
                 </td>
               </tr>

               <tr>
                 <th colspan="2">基礎年金番号</th>
                 <td colspan="3">
                   <div class="flex">
                   <input type="text"><input type="text"><input type="text"><input type="text">-<input type="text"><input type="text"><input type="text"><input type="text"><input type="text"><input type="text">
                   </div>
                 </td>
               </tr>
             </table>

            <div class="register_btn_wrap">
              <button type="submit">登録</button>
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
