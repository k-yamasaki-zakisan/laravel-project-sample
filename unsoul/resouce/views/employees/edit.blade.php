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
              <a itemprop="item" href="{{ route('unsoul.employees.edit', $link_key) }}">
                <span itemprop="name">従業員編集画面</span>
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
            <form method="post" action={{ route('unsoul.employees.update', $link_key) }}>
              @method('put')
              @csrf
              <table>
                <tbody>
                  <tr>
                    <th colspan="2">従業員コード</th>
                    <td colspan="3">
                      <input type="text" pattern="^[0-9A-Za-z_-]+$" name="code" value="{{ old('code') ?? $code }}">
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">氏名</th>
                    <td colspan="3">
                      <span>姓<input type="text" size="10" name="last_name"
                          value="{{ old('last_name', null) ?? $last_name }}"></span>
                      <span>名<input type="text" size="10" name="first_name"
                          value="{{ old('first_name', null) ?? $first_name }}"></span>
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">カナ</th>
                    <td colspan="3">
                      <span>セイ<input type="text" size="10" name="last_name_kana"
                          value="{{ old('last_name_kana') ?? $last_name_kana }}"></span>
                      <span>メイ<input type="text" size="10" name="first_name_kana"
                          value="{{ old('first_name_kana') ?? $first_name_kana }}"></span>
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">生年月日</th>
                    <td colspan="3">
                      <input type="date" name="birthday" value="{{ old('birthday') ?? $birthday }}">
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">性別</th>
                    <td colspan="3">
                      @foreach($genders as $gender_id => $gender_name)
                      <input type="radio" name="gender_id" id="gender_id_{{ $gender_id }}" value="{{ $gender_id }}" @if
                        ( !empty( old('gender_id') ) ) @if ( $gender_id==old('gender_id') ) checked @endif @else @if (
                        $gender_id==$now_gender_id ) checked @endif @endif />
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
                        <option value="{{ $employment_status_id }}" @if( !empty( old('employment_status_id') ) ) @if(
                          $employment_status_id==old('employment_status_id') ) selected @endif @else @if(
                          $employment_status_id==$now_employment_status_id ) selected @endif @endif>
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
<input type="text" pattern="\d{3}" size="3" name="zip_code1" value="{{ old('zip_code1') ?? $zip_code1 }}"
                      maxlength='3'>-
                      <input type="text" pattern="\d{4}" size="4" name="zip_code2"
                        value="{{ old('zip_code2') ?? $zip_code2 }}" maxlength='4'>
                      --}}
                      @component('components.input.numberInput', ['pattern' => 3, 'size' => 3, 'name' => "zip_code1",
                      'value' => $zip_code1, 'maxlength' => 3])
                      @endcomponent
                      -
                      @component('components.input.numberInput', ['pattern' => 4, 'size' => 4, 'name' => "zip_code2",
                      'value' => $zip_code2, 'maxlength' => 4])
                      @endcomponent
                    </td>
                  </tr>

                  <tr>
                    <th>都道府県</th>
                    <td colspan="3">
                      <select name="prefecture_id">
                        <option></option>
                        @foreach($prefectures as $prefecture_id => $prefecture_name)
                        <option value="{{ $prefecture_id }}" @if( !empty( old('prefecture_id') ) ) @if(
                          $prefecture_id==old('prefecture_id') ) selected @endif @else @if(
                          $prefecture_id==$now_prefecture_id ) selected @endif @endif>{{ $prefecture_name }}</option>
                        @endforeach
                      </select>
                    </td>
                  </tr>

                  <tr>
                    <th>市区</th>
                    <td>
                      <input type="text" name="city" value="{{ old('city') ?? $city }}">
                    </td>
                    <th>
                      町村
                    </th>
                    <td>
                      <input type="text" name="town" value="{{ old('town') ?? $town }}">
                    </td>
                  </tr>

                  <tr>
                    <th>番地</th>
                    <td colspan="3">
                      <input type="text" name="street" value="{{ old('street') ?? $street }}">
                    </td>
                  </tr>

                  <tr>
                    <th>建物名</th>
                    <td colspan="3">
                      <input type="text" name="building" value="{{ old('building') ?? $building }}">
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">住所フリガナ</th>
                    <td colspan="3">
                      <input type="text" size="60" name="address_kana"
                        value="{{ old('address_kana') ?? $address_kana }}">
                    </td>
                  </tr>

                  <tr>
                    <th rowspan="2">連絡先</th>
                    <th>携帯</th>
                    <td colspan="3">
                      {{--
                   <span class="js-addSpace-mobile">
@foreach($mobiles as $mobile)
<span class="js-mobile-Item">
<input type="text" name="mobiles[{{ $mobile['link_key'] }}]"
                      value="{{ old('mobiles'.$mobile['link_key']) ?? $mobile['value'] }}">
                      </span>
                      @endforeach
                      </span>
                      <button class="js-add-mobile" type="button">追加</button>
                      <button class="js-remove-mobile" type="button">削除</button>
                      --}}
                      <div class="js-mobileItemList">
                        @foreach(old('mobiles', $mobiles) as $key => $value)
                        <div class="js-mobileItem">
                          <input type="text" name="mobiles[{{ $key }}]" value="{{ $value }}" />
                          <button class="js-remove-mobile" type="button">削除</button>
                        </div>
                        @endforeach
                        <button class="js-add-mobile" type="button">追加</button>
                      </div>
                    </td>
                  </tr>

                  <tr>
                    <th>メールアドレス</th>
                    <td colspan="3">
                      {{--
                   <span class="js-addSpace-email">
@foreach($emails as $email)
<span class="js-email-Item">
<input type="text" name="emails[{{ $email['link_key'] }}]"
                      value="{{ old('emails'.$email['link_key']) ?? $email['value'] }}">
                      </span>
                      @endforeach
                      </span>
                      <button class="js-add-email" type="button">追加</button>
                      <button class="js-remove-email" type="button">削除</button>
                      --}}
                      <div class="js-emailItemList">
                        @foreach(old('emails', $emails) as $key => $value)
                        <div class="js-emailItem">
                          <input type="text" name="emails[{{ $key }}]" value="{{ $value }}" />
                          <button class="js-remove-email" type="button">削除</button>
                        </div>
                        @endforeach
                        <button class="js-add-email" type="button">追加</button>
                      </div>
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">入社年月日</th>
                    <td colspan="3">
                      <input type="date" name="hire_date" value="{{ old('hire_date') ?? $hire_date }}">
                    </td>
                  </tr>

                  <tr>
                    <th colspan="2">退職年月日</th>
                    <td colspan="3">
                      <input type="date" name="retirement_date"
                        value="{{ old('retirement_date') ?? $retirement_date }}">
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
                      {{--
<input type="text" pattern="\d{4}" size="4" name="basic_pension_number_1" value="{{ old('basic_pension_number_1') ?? $basic_pension_number_1 }}"
                      maxlength='4'>-
                      <input type="text" pattern="\d{6}" size="6" name="basic_pension_number_2"
                        value="{{ old('basic_pension_number_2') ?? $basic_pension_number_2 }}" maxlength='6'>

                      @for( $i = 1; $i <= 10; $i++) <input type="text" name="basic_pension_number[{{ $i }}]"
                        value="{{ old('basic_pension_number.{$i}') ?? ${'basic_pension_number'.$i} }}" maxlength='1'>
                        @if ( $i === 4 ) - @endif
                        @endfor
                        --}}
                        @component('components.input.numberInput', ['pattern' => 4, 'size' => 4, 'name' =>
                        "basic_pension_number_1", 'value' => $basic_pension_number_1, 'maxlength' => 4])
                        @endcomponent
                        -
                        @component('components.input.numberInput', ['pattern' => 6, 'size' => 6, 'name' =>
                        "basic_pension_number_2", 'value' => $basic_pension_number_2, 'maxlength' => 6])
                        @endcomponent
                    </td>
                  </tr>
              </table>
              <div class="register_btn_wrap">
                <button type="submit">更新</button>
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
  {{-- ダミーemailItem --}}
  <div id="js-dummyEmailItem" class="js-emailItem" style="display: none;">
    <input type="text" name="emails[]" />
    <button class="js-remove-email" type="button">削除</button>
  </div>
  {{-- ダミーmobileItem --}}
  <div id="js-dummyMobileItem" class="js-mobileItem" style="display: none;">
    <input type="text" name="mobiles[]" />
    <button class="js-remove-mobile" type="button">削除</button>
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
  //
  ページ遷移のイベント
  $('button[type=reset].reset_btn').click(function()
  {
  window.location.href
  ="{{ route('unsoul.employees.index') }}";
  });
  //
  emailItem用ダミー取得
  var
  $orgDummyEmailItem
  =
  $('#js-dummyEmailItem');
  var
  $dummyEmailItem
  =
  $orgDummyEmailItem.clone().attr('style',
  null).attr('id',
  null);;
  $orgDummyEmailItem.remove();
  //
  emailItem削除用関数
  var
  removeEmailItem
  =
  function(event)
  {
  $(this).closest('.js-emailItem').remove();
  };
  //
  emailItem追加
  $('.js-add-email').click(function()
  {
  var
  $tmpEmailItem
  =
  $dummyEmailItem.clone();
  $tmpEmailItem.find('.js-remove-email').click(removeEmailItem);
  $(this).before($tmpEmailItem);
  });
  //
  emailItem削除（ロード時用）
  $('.js-remove-email').click(removeEmailItem);
  //
  mobileItem用ダミー取得
  var
  $orgDummyMobileItem
  =
  $('#js-dummyMobileItem');
  var
  $dummyMobileItem
  =
  $orgDummyMobileItem.clone().attr('style',
  null).attr('id',
  null);;
  $orgDummyMobileItem.remove();
  //
  mobileItem削除用関数
  var
  removeMobileItem
  =
  function(event)
  {
  $(this).closest('.js-mobileItem').remove();
  };
  //
  mobileItem追加
  $('.js-add-mobile').click(function()
  {
  var
  $tmpMobileItem
  =
  $dummyMobileItem.clone();
  $tmpMobileItem.find('.js-remove-mobile').click(removeMobileItem);
  $(this).before($tmpMobileItem);
  });
  //
  mobileItem削除（ロード時用）
  $('.js-remove-mobile').click(removeMobileItem);
  //
  mobile追加イベント
  /*
  $('.js-add-mobile').click(function()
  {
  var
  addform
  =
  `
<span class="js-mobile-Item">
  <input type="text" name="mobiles[]">
</span>
`;

$('.js-addSpace-mobile').append(addform);
});

// mobile削除イベント
$('.js-remove-mobile').click(function() {
//$(this).closest('.js-mobile-Item').remove();
$('.js-addSpace-mobile span:last-child').remove();
});

// email追加イベント
$('.js-add-email').click(function() {
var addform = `
<span class="js-email-Item">
  <input type="text" name="emails[]">
</span>
`;

$('.js-addSpace-email').append(addform);
});

// email削除イベント
$('.js-remove-email').click(function() {
$('.js-addSpace-email span:last-child').remove();
});
*/
});
</script>

</html>