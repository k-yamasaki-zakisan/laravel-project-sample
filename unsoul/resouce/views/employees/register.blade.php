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
              <a itemprop="item" href="{{ route('unsoul.employees.index') }}">
                <span itemprop="name">従業員一覧画面</span>
              </a>
              <meta itemprop="position" content="2" />
            </li>

            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
              <a itemprop="item" href="{{ route('unsoul.employees.register') }}">
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
          @if ($errors->any())
          <ul>
            @foreach( $errors->all() as $message )
            <li>{{ $message }}</li>
            @endforeach
          </ul>
          @endif
          <div class="area_inner">
            <form method="post" action={{ route('unsoul.employees.register.confirm') }}>
              @csrf
              <table>
                <tbody>
                  <tr>
                    <th colspan="2">従業員コード</th>
                    <td colspan="3">
                      <input type="text" pattern="^[0-9A-Za-z_-]+$" name="employee_code"
                        value="{{ old('employee_code', null) }}">
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">氏名</th>
                    <td colspan="3">
                      <span>姓<input type="text" size="10" name="last_name" value="{{ old('last_name', null) }}"></span>
                      <span>名<input type="text" size="10" name="first_name"
                          value="{{ old('first_name', null) }}"></span>
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">カナ</th>
                    <td colspan="3">
                      <span>セイ<input type="text" size="10" name="last_name_kana"
                          value="{{ old('last_name_kana', null) }}"></span>
                      <span>メイ<input type="text" size="10" name="first_name_kana"
                          value="{{ old('first_name_kana', null) }}"></span>
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">生年月日</th>
                    <td colspan="3">
                      <input type="date" name="birthday" value="{{ old('birthday', null) }}">
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">性別</th>
                    <td colspan="3">
                      @foreach($genders as $gender_id => $gender_name)
                      <input type="radio" name="gender_id" id="gender_id_{{ $gender_id }}" value="{{ $gender_id }}" @if
                        ( $gender_id==old('gender_id', null) ) checked @endif />
                      <label for="gender_id_{{ $gender_id }}">{{ $gender_name }}</label>
                      @endforeach
                      {{--
                    <input type="radio" name="sex" id="man">
                    <label for="man">男性</label>
                    <input type="radio" name="sex" id="woman">
                    <label for="woman">女性</label>
--}}
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">雇用形態</th>
                    <td colspan="3">
                      <select name="employment_status_id">
                        <option></option>
                        @foreach($employment_statuses as $employment_status_id => $employment_status_name)
                        <option value="{{ $employment_status_id }}" @if(
                          $employment_status_id==old('employment_status_id', null) ) selected @endif>
                          {{ $employment_status_name }}</option>
                        @endforeach
                      </select>
                      {{--
                   <select>
                     <option>****</option>
                     <option>****</option>
                   </select>
--}}
                    </td>
                  </tr>

                  <tr>
                    <th rowspan="5">住所</th>
                    <th>郵便番号</th>
                    <td colspan="3">
                      {{--
<input type="text" pattern="\d{3}" size="3" name="zip_code1" value="{{ old('zip_code1', null) }}" maxlength='3'>-
                      <input type="text" pattern="\d{4}" size="4" name="zip_code2" value="{{ old('zip_code2', null) }}"
                        maxlength='4'>
                      --}}
                      @component('components.input.numberInput', ['pattern' => 3, 'size' => 3, 'name' => "zip_code1",
                      'value' => '', 'maxlength' => 3])
                      @endcomponent
                      -
                      @component('components.input.numberInput', ['pattern' => 4, 'size' => 4, 'name' => "zip_code2",
                      'value' => '', 'maxlength' => 4])
                      @endcomponent
                    </td>
                  </tr>

                  <tr>
                    <th>都道府県</th>
                    <td colspan="3">
                      <select name="prefecture_id">
                        <option></option>
                        @foreach($prefectures as $prefecture_id => $prefecture_name)
                        <option value="{{ $prefecture_id }}" @if( $prefecture_id==old('prefecture_id', null) ) selected
                          @endif>{{ $prefecture_name }}</option>
                        @endforeach
                      </select>
                    </td>
                  </tr>

                  <tr>
                    <th>市区</th>
                    <td>
                      <input type="text" name="city" value="{{ old('city', null) }}">
                    </td>
                    <th>
                      町村
                    </th>
                    <td>
                      <input type="text" name="town" value="{{ old('town', null) }}">
                    </td>
                  </tr>

                  <tr>
                    <th>番地</th>
                    <td colspan="3">
                      <input type="text" name="street" value="{{ old('street', null) }}">
                    </td>
                  </tr>

                  <tr>
                    <th>建物名</th>
                    <td colspan="3">
                      <input type="text" name="building" value="{{ old('building', null) }}">
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">住所フリガナ</th>
                    <td colspan="3">
                      <input type="text" size="62" name="address_kana" value="{{ old('address_kana', null) }}">
                    </td>
                  </tr>

                  <tr>
                    <th rowspan="2">連絡先</th>
                    <th>携帯</th>
                    <td colspan="3">
                      <input type="text" name="mobile" value="{{ old('mobile', null) }}">
                    </td>
                  </tr>

                  <tr>
                    <th>メールアドレス</th>
                    <td colspan="3">
                      <input type="text" name="email_address" value="{{ old('email_address', null) }}">
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">入社年月日</th>
                    <td colspan="3">
                      <input type="date" name="hire_date" value="{{ old('hire_date', null) }}">
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">退職年月日</th>
                    <td colspan="3">
                      <input type="text" readonly>
                    </td>
                  </tr>
                  {{--
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
--}}
                  <tr>
                    <th colspan="2">基礎年金番号</th>
                    <td colspan="3">
                      {{--
<input type="text" pattern="\d{4}" size="4" name="basic_pension_number_1" value="{{ old('basic_pension_number_1') ?? null }}"
                      maxlength='4'>-
                      <input type="text" pattern="\d{6}" size="6" name="basic_pension_number_2"
                        value="{{ old('basic_pension_number_2') ?? null }}" maxlength='6'>
                      --}}
                      @component('components.input.numberInput', ['pattern' => 4, 'size' => 4, 'name' =>
                      "basic_pension_number_1", 'value' => '', 'maxlength' => 4])
                      @endcomponent
                      -
                      @component('components.input.numberInput', ['pattern' => 6, 'size' => 6, 'name' =>
                      "basic_pension_number_2", 'value' => '', 'maxlength' => 6])
                      @endcomponent
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
  <!-- js 
  -->
  $(function()
  {
  $('button[type=reset].reset_btn').click(function()
  {
  window.location.href
  =
  '{!!
  $back_to
  !!}';
  });
  });
</script>

</html>