<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="author" content="Innovent INC">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="description" content="">
    <meta property="og:image" content="" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/visitor/colorbox.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/visitor/default.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/visitor/basic.css') }}" rel="stylesheet" type="text/css">

    <!-- Scripts -->
    {{--
    <script src="{{ asset('js/app.js') }}" defer></script>
    --}}
    <script type="text/javascript" src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/jQuery-rwdImageMaps-1.6/jquery.rwdImageMaps.min.js') }}"></script>

    <script src="{{ asset('js/visitor/common.js') }}" defer></script>
    <script src="{{ asset('js/visitor/jquery.colorbox.js') }}" defer></script>
    <script src="{{ asset('js/visitor/jquery.searcher.js') }}" defer></script>
    <script src="{{ asset('js/visitor/jquery.bgswitcher.js') }}" defer></script>

</head>

<body>
    <div id="app">
        {{--
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
        {{ config('app.name', 'Laravel') }}
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">

            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Authentication Links -->
                @guest
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                </li>
                @if (Route::has('register'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                </li>
                @endif
                @else
                <li class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        {{ Auth::user()->name }} <span class="caret"></span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
                @endguest
            </ul>
        </div>
    </div>
    </nav>
    --}}
    <!--オープニングムービー-->
    <div id="bg" class="outer" style="background: #000;position: relative;">

        @if( !empty($exposition['main_visual_path']) )
        <div class="inner" style="
        position: absolute;
        z-index: 100;
        background: #000;
        width: 98%;
        text-align: center;
        padding: 10px;
        border-top: 1px solid #FFF;
        border-bottom: 1px solid #FFF;
    ">
            <img src="{{ asset('storage/' . $exposition['main_visual_path']) }}" style="width: 300px;">
        </div>
        @endif

        <div style="opacity: 0.4;">
            <div class="bg-slider">
            </div>
        </div>

    </div>
    <!--オープニングムービー-->





    <!--コンテンツ開始ー-->
    <div id="content" style="display:none;">




        <header>

            <nav>
                @if( !empty($exposition['main_visual_path']) )
                <h1 id="logo"><a href="/"><img src="{{ asset('storage/' . $exposition['main_visual_path']) }}"
                            style="height: 30px; width: auto;"></a></h1>
                @endif

                <div id="global_navi">
                    <ul>
                        <li><a href="#map_link">MAP検索</a></li>
                        <li><a href="#category_link">カテゴリ検索</a></li>
                        <li><a href="#onkensaku_link">50音検索</a></li>
                        <li class="menu_btn"><a>フリーワード検索</a></li>
                        <li><a href="#kouen_link">講演・セミナー</a></li>
                    </ul>
                    <div id="logout">
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <img src="{{ asset('img/visitor/basic_image/logout.png') }}">ログアウト
                        </a>
                        <form id="logout-form" action="{{ route('auth.logout', $exposition['slug']) }}" method="POST"
                            style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </nav>

            <div class="sp_menu">

                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <nav class="globalMenuSp">
                    <ul>
                        <li><a href="#map_link">MAP検索</a></li>
                        <li><a href="#category_link">カテゴリ検索</a></li>
                        <li><a href="#onkensaku_link">50音検索</a></li>
                        <li class="menu_btn"><a>フリーワード検索</a></li>
                        <li><a href="#kouen_link">講演・セミナー</a></li>
                        <li><a href="">ログアウト</a></li>
                    </ul>
                </nav>

            </div>

        </header>







        <!---フリーワード検索エリア-->
        <div id="menu">
            <div class="menu_close">
                close
            </div>

            <div class="search_box">

                <p>
                    下記の入力欄へお探しの企業名やキーワードを入力頂くと、絞り込んで検索ができます。
                </p>

                <input id="tablesearchinput" />


                <!--検索文字列を入れる-->
                <table id="tabledata" style="display:none">
                    <tbody>
                        @foreach( $exhibitor_classifications as $exhibitor_classification )
                        @foreach( $exhibitor_classification as $exhibitor )
                        <tr>
                            <td>
                                <a class="iframe"
                                    href="{{ route('visitor.exhibitors.show', [$exposition['slug'], $exhibitor['id']]) }}"
                                    data-modal-close-url="{{ route('visitor.modal.close.exbhibitor', [$exposition['slug'], $exhibitor['id']]) }}">{{ $exhibitor['name'] }}</a>
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>



            </div>

        </div>
        <div class="menu-background"></div>
        <!---フリーワード検索エリア-->

        <div class="first_navi">
            <div class="first_navi_box page_width">
                <div class="men_box">
                    <img src="{{ asset('img/visitor/basic_image/men.png') }}">
                </div>

                <div class="first_navi_txt">
                    <p>
                        {{ $exposition['name'] }} オンラインへご来場いただきありがとうございます！<br>
                        リアルの会場と同じMAPから各ブースを閲覧いただくことが可能です。<br>
                        オンラインならではのカテゴリ検索もご用意しておりますので是非ご活用下さい。
                    </p>
                    <p class="red bold">
                        会期：{{ $exposition['start_date'] }} ～ {{ $exposition['end_date'] }}<br>
                        {{--チャット対応日時：2020年10月26日(月)・27日(火)--}}
                    </p>
                </div>

                <div class="lady_box">
                    <img src="{{ asset('img/visitor/basic_image/lady.png') }}">
                </div>

            </div>

        </div>
        <!--end first_navi -->

        <main class="py-4">
            @yield('content')
        </main>

        <footer>
            Copyright c Innovent INC. All Rights Reserved.
        </footer>

        <div id="page_top"><a href="#">▲</a></div>

    </div>

    <script>
        // searcherの読み込みタイミングが遅いため直接記入
(function IIFE() {

    "use strict";

    function factory($) {
        var pluginName = "searcher",
            dataKey = "plugin_" + pluginName,
            defaults = {
                itemSelector: "tbody > tr",
                textSelector: "td",
                inputSelector: "",
                caseSensitive: false,
                toggle: function (item, containsText) {
                    $(item).toggle(containsText);
                }
            };

        function Searcher(element, options) {
            this.element = element;

            this.options = $.extend({}, defaults, options);

            this._create();
        }

        Searcher.prototype = {
            dispose: function () {
                this._$input.unbind("." + pluginName);
                var options = this.options,
                    toggle = options.toggle || defaults.toggle;
                this._$element.find(options.itemSelector).each(function () { toggle(this, true); });
            },
            filter: function (value) {
                this._lastValue = value;

                var options = this.options,
                    textSelector = options.textSelector,
                    toggle = options.toggle || defaults.toggle;

                var flags = "gm" + (!options.caseSensitive ? "i" : "");
                var regex = new RegExp("(" + escapeRegExp(value) + ")", flags);

                this._$element
                    .find(options.itemSelector)
                    .each(function eachItem() {
                        var $item = $(this),
                            $textElements = textSelector ? $item.find(textSelector) : $item,
                            itemContainsText = false;

                        $textElements = $textElements.each(function eachTextElement() {
                            itemContainsText = itemContainsText || !!$(this).text().match(regex);
                            return !itemContainsText;
                        });

                        toggle(this, itemContainsText);
                    });
            },
            _create: function () {
                var options = this.options;

                this._$element = $(this.element);

                this._fn = $.proxy(this._onValueChange, this);
                var eventNames = "input." + pluginName + " change." + pluginName + " keyup." + pluginName;
                this._$input = $(options.inputSelector).bind(eventNames, this._fn);

                this._lastValue = null;

                var toggle = options.toggle || defaults.toggle;
                this._$element.find(options.itemSelector).each(function () { toggle(this, true); });
            },
            _onValueChange: function () {
                var value = this._$input.val();
                if (value === this._lastValue)
                    return;

                this.filter(value);
            }
        };

        function escapeRegExp(text) {
            return text.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
        }

        $.fn[pluginName] = function pluginHandler(options) {
            var args = Array.prototype.slice.call(arguments, 1);
            return this.each(function () {
                var searcher = $.data(this, dataKey);
                var t = typeof (options);
                if (t === "string" && searcher) {
                    searcher[options].apply(searcher, args);
                    if (options === "dispose")
                        $.removeData(this, dataKey);
                }
                else if (t === "object") {
                    if (!searcher)
                        $.data(this, dataKey, new Searcher(this, options));
                    else
                        $.extend(searcher.options, options);
                }
            });
        };

    }

    if (typeof (define) === "function" && define.amd)
        define(["jquery"], factory);
    else if (typeof (exports) === "object")
        module.exports = factory;
    else
        factory(jQuery);

}).call(this);

$("#tabledata").searcher({
    inputSelector: "#tablesearchinput"
});
    </script>

    <script>
        $(function(){
  $(".iframe").colorbox({
    onClosed:function(){
      const triger = $(this);
      const url = triger.data('modal-close-url');

      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: url,
        type: 'POST',
        timeout: 8000,
      })
      .done(function(responsData) {})
      .fail(function(jqXHR, textStatus, errorThrown) {});
    }
  });
});
    </script>
    @stack('js')
    @yield('js')
</body>

</html>