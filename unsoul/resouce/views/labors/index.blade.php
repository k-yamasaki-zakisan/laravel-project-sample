<!DOCTYPE html>
<html lang="ja">
<head>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="">
<meta name="keywords" content="">

<title>従業員一覧画面|運SOUL</title>

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
          <li><a href="{{ route('unsoul.corporations.index') }}" class="active">法人管理</a></li>
          <li><a href="">人 管理</a></li>
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
            <a itemprop="item" href="">
              <span itemprop="name">ホーム</span>
            </a>
            <meta itemprop="position" content="1" />
          </li>

          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="{{route('unsoul.labors.index') }}">
              <span itemprop="name">従業員一覧</span>
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
          <form>
            <table>
              <tbody>
               <tr>
                 <th>従業員番号</th>
                 <td>
		   <input type="text" name="search[employee_id]" value="{{ $search['employee_id'] ?? null}}" >
		</td>
                 <th>住所</th>
                 <td>
					<input type="text" name=""
						value=""
					>
		</td>
               </tr>
               <tr>
                 <th>氏名</th>
                 <td>
		  <input type="text" name="search[last_name]" value="" >
		</td>
                 <th>連絡先</th>
                 <td>
					<input type="text" name=""
						value=""
					>
		</td>
               </tr>
               <tr>
                 <th>性別</th>
                 <td>
                    <input type="radio" id="man" name="" value="" checked="checked">
                    <label for="man">男性</label>
                    <input type="radio" id="woman" name="" value="">
                    <label for="woman">女性</label>
                 </td>
                 <th>入社日</th>
                 <td>
					<input type="text" class="form-control" name=""
						value=""
					>
		</td>
               </tr>
                 <tr>
                 <th>在職状況</th>
                 <td>
                    <input type="radio" id="in_office">
                    <label for="in_office">在職中</label>
                    <input type="radio" id="retired">
                    <label for="retired">退職済み</label>
                    <input type="radio" id="both">
                    <label for="both">在職中＆退職済み</label>
                 </td>
               </tr>
              </tbody>
             </table>

            <button type="submit">検索</button>
          </form>
        </div>
      </section>
      <!-- ▲ ①検索エリア -->

      <!-- ▼ ②法人登録ボタン エリア -->
      <section class="corporation_register_area">
        <h3>従業員の新規登録</h3>
        <div class="area_inner">
          <div class="regist_btn_wrap">
            <a href="" tyle="button" class="regist_btn">新規登録</a>
          </div>
        </div>
      </section>
      <!-- ▲ ②法人登録ボタン エリア -->

      <!-- ▼ ③法人一覧エリア -->
      <section class="list_area corporation_list_area">
        <h3>従業員一覧のエリア</h3>
        <div class="area_inner">

          <div class="table_wrap">
            <table>
              <tr>
                <th>従業員番号</th>
                <th>氏名</th>
                <th>性別</th>
                <th>生年月日</th>
                <th>住所</th>
                <th>連絡先</th>
                <th>入社日</th>
                <th>Actions</th>
              </tr>

              <!-- TODO: ここからループ -->
                <tr>
                  <td>*****</td>
                  <td>テスト太郎</td>
                  <td>男</td>
                  <td>2020/08/19</td>
                  <td>埼玉県さいたま市...</td>
                  <td>0123456789</td>
                  <td>2020/08/19</td>
                  <td>◇□△</td>
                </tr>
                <!-- TODO: ここまでループ -->
		@foreach( $employees as $employee )
			<tr>
				<td>{{ $employee['employee_id'] }}</td>
				<td>{{ $employee['last_name'] ?? null }}</td>
				<td>
				</td>
				<td>{{ $employee['birthday'] ?? null }}</td>
				<td>
				</td>
				<td>
				</td>
				<td>
				</td>
				<td>
				</td>
			</tr>
		@endforeach
              </tbody>
            </table>
          </div>

          <div class="pager_area">
		{{ $employees->links() }}
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
