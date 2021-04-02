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
              <a itemprop="item" href="{{route('unsoul.employees.index') }}">
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
            <form action="{{ route('unsoul.employees.index') }}" method="get">
              <table>
                <tbody>
                  <tr>
                    <th>従業員番号</th>
                    <td>
                      <input type="text" name="search[employee_code]"
                        value="{{ old('search.employee_code', $search['employee_code'] ?? null) }}" />
                    </td>
                    <th>住所</th>
                    <td>
                      <input type="text" name="search[address]"
                        value="{{ old('search.address', $search['address'] ?? null) }}" />
                    </td>
                  </tr>
                  <tr>
                    <th>氏名</th>
                    <td>
                      <input type="text" name="search[full_name]"
                        value="{{ old('search.full_name', $search['full_name'] ?? null) }}" />
                    </td>
                    <th>連絡先</th>
                    <td>
                      <input type="text" name="search[contact]"
                        value="{{ old('search.contact', $search['contact'] ?? null) }}" />
                    </td>
                  </tr>
                  <tr>
                    <th>性別</th>
                    <td>
                      @foreach( $genders as $gender_id => $gender )
                      <input type="radio" id="gender_id_{{ $gender_id }}" name="search[gender_id]"
                        value="{{ $gender_id }}" @if( old('search.contact', $search['gender_id'] ?? null)==$gender_id )
                        checked @endif />
                      <label for="gender_id_{{ $gender_id }}">{{ $gender }}</label>
                      @endforeach
                      <input type="radio" id="gender_id_" name="search[gender_id]" value="0" @if( old('search.contact',
                        $search['gender_id'] ?? null)==0 ) checked @endif />
                      <label for="gender_id_">すべて</label>
                    </td>
                    <th>入社日</th>
                    <td>
                      <input type="text" name="search[hire_date]"
                        value="{{ old('search.hire_date', $search['hire_date'] ?? null) }}" />
                    </td>
                  </tr>
                  <tr>
                    <th>在職状況</th>
                    <td>
                      @foreach( $job_statuses as $key => $value )
                      <input type="radio" id="job_status_{{ $key }}" name="search[job_status]" value="{{ $key }}" @if (
                        empty(old('search.job_status', $search['job_status'] ?? null)) && $key==='WORKING' ) checked
                        @elseif ( old('search.job_status', $search['job_status'] ?? null)==$key ) checked @endif>
                      <label for="job_status_{{ $key }}">{{ $value }}</label>
                      @endforeach
                      {{--
                    <input type="radio" id="in_office" name="status" value="employment" checked="checked">
                    <label for="in_office">在職中</label>
                    <input type="radio" id="retired" name="status" value="retirement">
                    <label for="retired">退職済み</label>
                    <input type="radio" id="both" name="status" value="both_status">
                    <label for="both">在職中＆退職済み</label>
--}}
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
              <a href="{{ route('unsoul.employees.register') }}" tyle="button" class="regist_btn">新規登録</a>
            </div>
          </div>
        </section>
        <!-- ▲ ②法人登録ボタン エリア -->

        <!-- ▼ ③法人一覧エリア -->
        <section class="list_area corporation_list_area">
          <h3>従業員一覧のエリア</h3>
          <div class="area_inner">

            @if(session('flash_message'))
            <p>{{ session('flash_message') }}<p>
                @endif

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
                    @foreach( $employees as $employee )
                    <tr>
                      <td>{{ $employee['code'] }}</td>
                      <td>{{ $employee['full_name'] ?? null }}</td>
                      <td>{{ $genders[$employee['person']['gender_id']] ?? null }}</td>
                      <td>{{ $employee['birthday'] ?? null }}</td>
                      <td>{{ $employee['address'] ?? null }}</td>
                      <td>{{ $employee['contact'] ?? null }}</td>
                      <td>{{ $employee['hire_date'] ?? null }}</td>
                      <td>
                        <a href="{{ route('unsoul.employees.edit', $employee['link_key']) }}" class="action_btn">編集</a>
                        {{--
                    <a href="" class="action_btn">削除</a>
--}}
                        @isset($employee['link_key'])
                        <button type="button" class="js-deleteButton" data-title="{{ $employee['full_name'] ?? null }}"
                          data-url="{{ route('unsoul.employees.delete', $employee['link_key']) }}">削除</button>
                        @endisset
                        @isset($employee['person']['person_id'])
                        <a href="{{ route('unsoul.person_licenses.index', $employee['person']['person_id']) }}"
                          class="action_btn">資格</a>
                        @endisset
                        <a href="" class="action_btn">CSV</a>
                        @isset($employee['person']['link_key'])
                        <a href="{{ route('unsoul.persons.password', $employee['person']['link_key']) }}"
                          class="action_btn">パス</a>
                        @endisset
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
  <!-- js -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <script>
    <!-- js 
    -->
  </script>
  @component('components.modal.delete')
  @endcomponent
</body>

</html>