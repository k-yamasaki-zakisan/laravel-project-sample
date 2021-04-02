<!DOCTYPE html>
<html lang="ja">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="">
  <meta name="keywords" content="">

  <title>
    資格編集画面|運SOUL</title>

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
              <a itemprop="item" href="{{ route('unsoul.mypage.edit_self') }}">
                <span itemprop="name">ホーム</span>
              </a>
              <meta itemprop="position" content="1" />
            </li>

            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
              <a itemprop="item"
                href="{{ route('unsoul.licenses.edit', ['employee_link_key' => $employee_link_key, 'person_license_link_key' => $person_license_link_key]) }}">
                <span itemprop="name">資格編集画面</span>
              </a>
              <meta itemprop="position" content="2" />
            </li>

          </ol>
        </div><!-- breadcrumb end -->
        <!-- ▲ パンくずリスト -->

        <!-- ▼ 登録画面 -->
        <section class="requirement_edit_area">
          <h3>資格編集画面</h3>
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
            @foreach( $licenses as $license )
            <h4>{{ $license['name'] }} (資格名称)</h4>

            <form
              action="{{ route('unsoul.licenses.update', ['employee_link_key' => $employee_link_key, 'person_license_link_key' => $license['link_key']]) }}"
              class="action_btn" method="post">
              @csrf
              @method('PUT')
              <table class="no_border_table">
                <tr>
                  <th>取得日</th>
                  {{--<td><input name="acquisition" type="date" value="{{ old('acquisition', $license['acquisition'] ?? null) }}">
                  </td>--}}
                  <td>
                    年：<input type="text" name="acquisition_year"
                      value="{{ old('acquisition_year', $license['acquisition_year'] ?? null) }}" list="year">
                    <datalist id="year">
                      @foreach( $license['year_range'] as $year)
                      <option value="{{ $year }}"></option>
                      @endforeach
                    </datalist>
                  </td>
                  <td>
                    月：<input type="text" name="acquisition_month"
                      value="{{ old('acquisition_month', $license['acquisition_month'] ?? null) }}" list="month">
                    <datalist id="month">
                      @foreach( $license['month_range'] as $month)
                      <option value="{{ $month }}"></option>
                      @endforeach
                    </datalist>
                  </td>
                  <td>
                    日：<input type="text" name="acquisition_date"
                      value="{{ old('acquisition_date', $license['acquisition_date'] ?? null) }}" list="date">
                    <datalist id="date">
                      @foreach( $license['date_range'] as $date)
                      <option value="{{ $date }}"></option>
                      @endforeach
                    </datalist>
                  </td>
                  <th>有効期限</th>
                  <td><input name="expired_at" type="date"
                      value="{{ old('expired_at', $license['expired_at'] ?? null) }}"></td>
                </tr>
              </table>

              <table class="table_heading">
                <tr>
                  <th>備考</th>
                </tr>
                <tr>
                  <td><textarea name="note">{{ old('note', $license['note']) ?? null }}</textarea></td>
                </tr>
              </table>

              <div class="register_btn_wrap">
                <button type="submit">更新</button>
                <button type="reset" class="reset_btn">キャンセル</button>
              </div>
            </form>
            @endforeach
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
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
  $('.reset_btn').on('click', function(){
  location.href = "{{ route('unsoul.person_licenses.index', $employee_link_key) }}";
});
<!-- js -->
</script>

</html>