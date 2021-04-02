<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="author" content="Innovent INC">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="description" content="">
    <meta property="og:image" content="" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/visitor/colorbox.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/visitor/default.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/visitor/basic.css') }}" rel="stylesheet" type="text/css">
    @if($errors->any() || session('flash_message'))
    <link rel="stylesheet" href={{ asset('vendor/adminlte/plugins/toastr/toastr.min.css') }}>
    @endif

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('js/visitor/jquery.colorbox.js') }}"></script>
    <script src="{{ asset('js/visitor/jquery.modal.js') }}"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

</head>

<body id="modal_body">
    <main>
        <div class="head">

            <!--企業画像-->
            <div class="booth_img">
                @if( !empty($exhibitor['logo_image_path']) )
                <img src="{{ asset('storage/'.$exhibitor['logo_image_path']) }}">
                @elseif( !empty($exhibitor['exhibitor_images']) )
                <img src="{{ asset('storage/'.$exhibitor['exhibitor_images'][0]['image_path']) }}">
                @else
                <img src="{{ asset('img/visitor/basic_image/no_image.png') }}">
                @endif
            </div>

            <!--ゾーン・企業名-->
            <div class="booth_title">
                <span>{{ $exhibitor['exhibition_zone']['name'] }}ゾーン</span>
                <h1>{{ $exhibitor['name'] }}</h1>
            </div>

        </div>
        <!--head-->


        <div class="group">



            <!--tab-->
            <div class="menu_list modal">
                <div class="tab is-active" id="0">出展社情報</div>
                <div class="tab" id="1">出展製品・サービス</div>
                <div class="tab" id="2">お問合せ</div>
                <div class="tab" id="3">チャット</div>
            </div>
            <!--tab-->


            <div class="panel-group">


                <!--出展社情報-->
                <div class="panel is-show 0">


                    <!--画像-->
                    <div class="campany_img">
                        <div class="first_photo">
                            @foreach( $exhibitor['exhibitor_images'] as $exhibitor_image)
                            <div>
                                <a class="single cboxElement"
                                    href="{{ asset('storage/' . $exhibitor_image['image_path']) }}">
                                    <img src="{{ asset('storage/' . $exhibitor_image['image_path']) }}" alt="">
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!--企業情報-->
                    <div class="campany_info">
                        <dl>
                            <dt>企業名</dt>
                            <dd>{{ $exhibitor['name'] }}</dd>
                            <dt>出展展示会</dt>
                            <dd>{{ $exhibitor['exhibition']['name'] }}</dd>
                            <dt>出展ゾーン</dt>
                            <dd>{{ $exhibitor['exhibition_zone']['name'] }}</dd>
                            <dt>所在地</dt>
                            <dd>{{ $exhibitor['prefecture']['name'] . $exhibitor['address'] . '  ' . $exhibitor['building_name'] }}
                            </dd>
                            <dt>TEL</dt>
                            <dd>{{ $exhibitor['tel'] }}</dd>
                            <dt>URL</dt>
                            <dd>{{ $exhibitor['url'] }}</dd>
                        </dl>
                    </div>


                    <!--企業プロフィール-->
                    <div class="campany_prof">
                        <h2>企業プロフィール</h2>
                        <p style="white-space: pre-wrap;">{!! $exhibitor['profile_text'] !!}<p>
                    </div>


                    <!--動画-->
                    <div class="campany_movie">
                        <h2>企業紹介動画</h2>

                        @foreach( $exhibitor['exhibitor_videos'] as $exhibitor_video )
                        <div class="movie_box">
                            <div class="youtube">
                                {{--
                                <iframe width="560" height="315" src="https://www.youtube.com/embed/{{ $exhibitor_video['embed_code'] }}"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope;
                                picture-in-picture"
                                allowfullscreen></iframe>
                                --}}
                                <div id="exhibitor_player{{ $exhibitor_video['id'] }}"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>



                </div>
                <!--出展社情報-->


                <!--出展製品・サービス-->
                <div class="panel 1">


                    <!--目次-->
                    <div class="product_index">
                        <div class="product_index_title">目次</div>
                        <ul>
                            <?php $product_count = 1;?>
                            @foreach( $exhibitor['products'] as $product)
                            <li><a href="#product{{ $product_count }}">{{ $product['name'] }}</a></li>
                            <?php $product_count += 1;?>
                            @endforeach
                        </ul>

                    </div>

                    <?php $product_count = 1;?>
                    @foreach( $exhibitor['products'] as $product )
                    <div class="product_warp">
                        <h2 id="product{{ $product_count }}">{{ $product['name'] }}</h2>

                        <!--製品画像-->
                        <div class="product_img">
                            <div class="first_photo">
                                @foreach( $product['product_images'] as $product_image )
                                <div>
                                    <a class="single cboxElement"
                                        href="{{ asset('storage/'.$product_image['image_path']) }}">
                                        <img src="{{ asset('storage/'.$product_image['image_path']) }}" alt="">
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!--製品説明-->
                        <div class="product_txt">
                            <p style="white-space: pre-wrap;">{{ $product['description'] }}</p>
                        </div>

                        <!--動画-->
                        @foreach( $product['product_videos'] as $product_video )
                        <div class="movie_box">
                            <div class="youtube">
                                {{--
                                <iframe width="560" height="315" src="https://www.youtube.com/embed/{{ $product_video['embed_code'] }}"
                                frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media;
                                gyroscope; picture-in-picture" allowfullscreen></iframe>
                                --}}
                                <div id="product_player{{ $product_video['id'] }}"></div>
                            </div>
                        </div>
                        @endforeach

                        <!--添付ファイル-->
                        <div class="file_box">
                            <div class="file_box_title">【参考資料のダウンロード】</div>
                            <ul>
                                <?php $file_number = 1;?>
                                @foreach( $product['product_attachment_files'] as $product_attachment_file )
                                <li>
                                    <a class="product_file_download"
                                        href="{{ asset('storage/'. $product_attachment_file['file_path']) }}"
                                        download="{{ $product['name'] }}参考資料{{ $file_number }}{{ $product_attachment_file['file_extension'] }}"
                                        data-url="{{ route('visitor.modal.product.file_download', [$slug, $product_attachment_file['id']]) }}">
                                        参考資料{{ $file_number }}
                                    </a>
                                </li>
                                <?php $file_number += 1;?>
                                @endforeach
                            </ul>
                        </div>

                    </div>
                    <?php $product_count += 1;?>
                    @endforeach

                </div>
                <!--出展製品・サービス-->


                <!--お問合せ-->
                <div class="panel 2">


                    <div class="form_body">
                        <h2>お問合せフォーム</h2>

                        <p>以下の内容をご登録頂ければ、後ほど担当者からご連絡させて頂きます。</p>

                        <form
                            action="{{ route('visitor.contact.store', $exhibitor['exhibition']['exposition']['slug']) }}"
                            method="post">
                            @csrf
                            <input type="hidden" name="exhibitor_id" value="{{ $exhibitor['id'] }}">
                            <dl>
                                <dt>ご希望 <span class="required">必須</span></dt>
                                <dd>
                                    <select name="contact_request_type_id" required="">
                                        <option value="">選択して下さい</option>
                                        @foreach( $contact_request_types as $contact_request_type)
                                        @if( $contact_request_type['name'] === 'その他')
                                        <option value="{{ $contact_request_type['id'] }}" @if(
                                            old('contact_request_type_id')==$contact_request_type['id'] ) selected
                                            @endif>{{ $contact_request_type['name'] }}（以下に記載）</option>
                                        @else
                                        <option value="{{ $contact_request_type['id'] }}" @if(
                                            old('contact_request_type_id')==$contact_request_type['id'] ) selected
                                            @endif>{{ $contact_request_type['name'] }}が欲しい</option>
                                        @endif
                                        @endforeach
                                    </select>
                                    @if($errors->has('contact_request_type_id'))
                                    @foreach($errors->get('contact_request_type_id') as $message)
                                    <p style="color:red;">{{ $message }}</p>
                                    @endforeach
                                    @endif
                                </dd>
                                <dt>ご質問・要望</dt>
                                <dd>
                                    <textarea name="body">{{ old('body') }}</textarea>
                                    @if($errors->has('body'))
                                    @foreach($errors->get('body') as $message)
                                    <p style="color:red;">{{ $message }}</p>
                                    @endforeach
                                    @endif
                                </dd>
                            </dl>

                            <div class="submit_area">
                                <input type="submit" value="送信する">
                            </div>

                        </form>


                    </div>


                </div>
                <!--お問合せ-->


                <!--チャット-->
                <div class="panel 3">

                    <div class="chat_body">
                        <h2>チャットを開始する場合は、下記の開始ボタンをクリックして下さい</h2>
                        <div class="chat_start_btn">
                            チャットを開始する
                        </div>
                    </div>


                    <div class="chat_window_body" style="display:none">
                        <h2>下記のメッセージウインドへ入力をし、送信を押してください</h2>
                        <div class="chat_window_box"
                            style="width: 90%;background: #CCC;margin: 30px auto;padding: 90px 0;text-align: center;font-size: 16px;">
                            ここにチャットの要素を配置想定
                        </div>
                    </div>


                </div>


            </div>
            <!--チャット-->

        </div>
        <!--group-->
    </main>
    @if($errors->any() || session('flash_message'))
    <script src={{ asset("vendor/adminlte/plugins/toastr/toastr.min.js") }}></script>
    <script type="text/javascript">
        $(function () {
    $(window).on('load', function(){
        @if(session('flash_message'))
            // 成功メッセージ
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "7000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            toastr.success('{{ session('flash_message') }}');
        @endif

        @if($errors->any() || session('flash_message'))
            // エラーメッセージ
            toastr.options = {
		"closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "0",
                "extendedTimeOut": "0",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            @foreach ($errors->all() as $error)
                toastr.error("{{$error}}");
            @endforeach
        @endif
    });
})
    </script>
    @endif
    <script>
        $(function () {
    let now_tab = 0;

    $('#0').click(function() {
      if ( now_tab === 0 ) return;
      tabMoveLogStore('exhibitor')
      now_tab = 0
    })

    $('#1').click(function() {
      if ( now_tab === 1 ) return;
      tabMoveLogStore('products')
      now_tab = 1
    })

    $('#2').click(function() {
      if ( now_tab === 2 ) return;
      tabMoveLogStore('contact')
      now_tab = 2
    })

    $('#3').click(function() {
      if ( now_tab === 3 ) return;
      tabMoveLogStore('chat')
      now_tab = 3
    })
});

function tabMoveLogStore(tab) {
    let url = null;
    if (tab === 'exhibitor') {
        url = "{{ route('visitor.modal.move.exhibitor', [$slug, $exhibitor['id']]) }}"
    }
    if (tab === 'products') {
        url = "{{ route('visitor.modal.move.products', [$slug, $exhibitor['id']]) }}"
    }
    if (tab === 'contact') {
        url = "{{ route('visitor.modal.move.contact', [$slug, $exhibitor['id']]) }}"
    }
    if (tab === 'chat') {
        url = "{{ route('visitor.modal.move.chat', [$slug, $exhibitor['id']]) }}"
    }

    if ( url === null) return

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

// 動画API設定
let tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
let firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

// 展示社動画ログ
let ytExhibitorPlayer = [];
let ytExhibitorData = [];
@foreach( $exhibitor['exhibitor_videos'] as $exhibitor_video )
ytExhibitorData.push({
    videoId:"{{ $exhibitor_video['embed_code']}}",
    area: "exhibitor_player{{ $exhibitor_video['id'] }}",
    requestUrl: {
        play: "{{ route('visitor.modal.exhibitor.video_play', [$slug, $exhibitor_video['id']]) }}",
        stop: "{{ route('visitor.modal.exhibitor.video_stop', [$slug, $exhibitor_video['id']]) }}"
    }
})
@endforeach

// 製品動画ログ
let ytProductPlayer = [];
let ytProductData = [];
@foreach( $exhibitor['products'] as $product )
    @foreach( $product['product_videos'] as $product_video )
    ytProductData.push({
        videoId:"{{ $product_video['embed_code']}}",
        area: "product_player{{ $product_video['id'] }}",
        requestUrl: {
            play: "{{ route('visitor.modal.product.video_play', [$slug, $product_video['id']]) }}",
            stop: "{{ route('visitor.modal.product.video_stop', [$slug, $product_video['id']]) }}"
        }
    })
    @endforeach
@endforeach


function onYouTubeIframeAPIReady() {
    for(let i = 0; i < ytExhibitorData.length; i++) {
        ytExhibitorPlayer[i] = new YT.Player(ytExhibitorData[i]['area'], {
            videoId: ytExhibitorData[i]['videoId'],
            events: {
                'onStateChange': onPlayerStateChange
            }
        });
    }

    for(let i = 0; i < ytProductData.length; i++) {
        ytProductPlayer[i] = new YT.Player(ytProductData[i]['area'], {
            videoId: ytProductData[i]['videoId'],
            events: {
                'onStateChange': onPlayerStateChange
            }
        });
    }
}

function onPlayerStateChange(event) {
    let actionUrl = null

    for (let i = 0; i < ytExhibitorData.length; i++) {
        if(event.target.getIframe().id === ytExhibitorData[i]['area']) {
            actionUrl = ytExhibitorData[i]['requestUrl']
            break;
        }
    };

    for (let i = 0; i < ytProductData.length; i++) {
        if(event.target.getIframe().id === ytProductData[i]['area']) {
            actionUrl = ytProductData[i]['requestUrl']
            break;
        }
    };

    if (actionUrl === null) return;

    if (event.data == YT.PlayerState.PLAYING) {
        videoLogStore(actionUrl['play'])
    }
    if (event.data == YT.PlayerState.PAUSED) {
        videoLogStore(actionUrl['stop'])
    }
}

function videoLogStore(url) {
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

// ファイルダウンロードログ
$(function () {
    $('.product_file_download').click(function() {
        let $trigger = $(this);
        let url = $trigger.data('url');
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
    })
})

    </script>

</body>

</html>