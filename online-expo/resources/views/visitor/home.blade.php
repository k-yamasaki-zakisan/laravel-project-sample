@extends('layouts.app')

@section('content')
{{--
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
</div>
@endif

You are logged in!
</div>
</div>
</div>
</div>
</div>
--}}

<!--MAP検索-->
<a class="sc_link" id="map_link"></a>

@if( !empty($exposition['map_path']) )
<section id="map">
    <h2><img src="{{ asset('img/visitor/basic_image/icon_01.png') }}">MAP検索 <span>閲覧したいブースをクリックすると、ブース詳細が表示されます</span>
    </h2>

    <div class="map_box">
        <img src="{{ asset('storage/' . $exposition['map_path'] ) }}" usemap="#ImageMap" class="map_link">

        <map name="ImageMap">
            <!--リンク設定-->
            <?php $area_count = 1;?>
            @foreach( $exhibitors as $exhibitor)
            <area shape="rect"
                coords="{{ $exhibitor['map_left'] }},{{ $exhibitor['map_top'] }},{{ $exhibitor['map_left']+$exhibitor['map_width'] }},{{ $exhibitor['map_top']+$exhibitor['map_height'] }}"
                class="area{{ $area_count }} iframe"
                href="{{ route('visitor.exhibitors.show', [$exposition['slug'], $exhibitor['id']]) }}" />
            <?php $area_count += 1;?>
            @endforeach
            {{--
            <area shape="rect" coords="280,85,432,118" class="area1 iframe" href="./modal_sample.html" />
            <area shape="rect" coords="620,52,722,117" class="area2 iframe" href="./modal_sample.html" />
            <area shape="rect" coords="1382,208,1478,243" class="area3 iframe" href="./modal_sample.html" />
--}}
            <!--リンク設定-->
        </map>

    </div>
    <?php $p_area_count = 1;?>
    <!--ツールチップウィンド 要素分を繰り返す-->
    @foreach( $exhibitors as $exhibitor)
    <div id="p_area{{ $p_area_count }}" class="p_box">{{ $exhibitor['name'] }}<span class="">{!!
            $exhibitor['profile_text'] !!}</span>
        @if( !empty($exhibitor['logo_image_path']) )
        <img src="{{ asset('storage/'.$exhibitor['logo_image_path']) }}">
        @else
        <img src="{{ asset('img/visitor/basic_image/no_image.png') }}">
        @endif
    </div>
    <?php $p_area_count += 1;?>
    @endforeach
    <!--ツールチップウィンド 要素分を繰り返す-->

</section>
<!--MAP検索終わり-->
@endif

<!--カテゴリ検索-->
<a class="sc_link" id="category_link"></a>

<section id="category">
    <h2><img src="{{ asset('img/visitor/basic_image/icon_02.png') }}">カテゴリ検索 <span>カテゴリをクリックすると、該当ブースが表示されます</span></h2>
    <div class="group">
        <div class="cate_navi">
            <div class="cate_all_btn tab is-active" id="0">全てのブースを表示する</div>
            <?php
                $exhibition_const = 0;
                $panel_count = 1;
            ?>
            @foreach( $exhibitions as $exhibition )
            <div class="cate_navi_box page_width category_color{{ $exhibition_const%2+1 }}">
                <div class="category_name">
                    {{ $exhibition['name'] }}
                </div>
                <div class="category_list">
                    <span class="tab" id="{{ $panel_count }}">全てを表示</span> <span class="line">｜</span>
                    <?php
                            $zone_const = 0;
                            $panel_count += 1;
                        ?>
                    @foreach( $exhibition['exhibition_zones'] as $exhibition_zone)
                    <span class="tab" id="{{ $panel_count }}">{{ $exhibition_zone['name'] }}</span> <span
                        class="line">｜</span>
                    <?php
                                $zone_const += 1;
                                $panel_count += 1;
                            ?>
                    @if( $zone_const%3 === 0) <br> @endif
                    @endforeach
                </div>
            </div>
            <?php $exhibition_const += 1; ?>
            @endforeach
        </div>
        <!--end cate_navi -->

        <!-- ここに切り替える内容 -->
        <div class="panel-group">
            <div class="panel is-show 0">
                <div class="main_category">全件表示</div>
                <div class="item_box">
                    @foreach( $exhibitions as $exhibition )
                    @foreach( $exhibition['exhibitors'] as $exhibitor )
                    <div class="item_list">
                        <a class="iframe"
                            href="{{ route('visitor.exhibitors.show', [$exposition['slug'], $exhibitor['id']]) }}"
                            data-modal-close-url="{{ route('visitor.modal.close.exbhibitor', [$exposition['slug'], $exhibitor['id']]) }}">
                            <span class="name">{{ $exhibitor['name'] }}</span>
                            @if( !empty($exhibitor['logo_image_path']) )
                            <img src="{{ asset('storage/'.$exhibitor['logo_image_path']) }}">
                            @elseif( !empty($exhibitor['exhibitor_images']) )
                            <img src="{{ asset('storage/'.$exhibitor['exhibitor_images'][0]['image_path']) }}">
                            @else
                            <img src="{{ asset('img/visitor/basic_image/no_image.png') }}">
                            @endif
                        </a>
                    </div>
                    @endforeach
                    @endforeach
                </div>
            </div>
            <?php $panel_count = 1;?>
            @foreach( $exhibitions as $exhibition)
            <div class="panel {{ $panel_count }}">
                <div class="main_category">{{ $exhibition['name'] }}</div>
                <div class="sub_category">全てを表示</div>
                <div class="item_box">
                    <!-- ここに企業名が入る -->
                    @foreach( $exhibition['exhibitors'] as $exhibitor )
                    <div class="item_list">
                        <a class="iframe"
                            href="{{ route('visitor.exhibitors.show', [$exposition['slug'], $exhibitor['id']]) }}"
                            data-modal-close-url="{{ route('visitor.modal.close.exbhibitor', [$exposition['slug'], $exhibitor['id']]) }}">
                            <span class="name">{{ $exhibitor['name'] }}</span>
                            @if( !empty($exhibitor['logo_image_path']) )
                            <img src="{{ asset('storage/'.$exhibitor['logo_image_path']) }}">
                            @elseif( !empty($exhibitor['exhibitor_images']) )
                            <img src="{{ asset('storage/'.$exhibitor['exhibitor_images'][0]['image_path']) }}">
                            @else
                            <img src="{{ asset('img/visitor/basic_image/no_image.png') }}">
                            @endif
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            <?php $panel_count += 1;?>
            @foreach( $exhibition['exhibition_zones'] as $exhibition_zone )
            <div class="panel {{ $panel_count }}">
                <div class="main_category">{{ $exhibition['name'] }}</div>
                <div class="sub_category">{{ $exhibition_zone['name'] }}</div>
                <div class="item_box">
                    @foreach( $exhibition_zone['exhibitors'] as $exhibitor )
                    <div class="item_list">
                        <a class="iframe"
                            href="{{ route('visitor.exhibitors.show', [$exposition['slug'], $exhibitor['id']]) }}"
                            data-modal-close-url="{{ route('visitor.modal.close.exbhibitor', [$exposition['slug'], $exhibitor['id']]) }}">
                            <span class="name">{{ $exhibitor['name'] }}</span>
                            @if( !empty($exhibitor['logo_image_path']) )
                            <img src="{{ asset('storage/'.$exhibitor['logo_image_path']) }}">
                            @elseif( !empty($exhibitor['exhibitor_images']) )
                            <img src="{{ asset('storage/'.$exhibitor['exhibitor_images'][0]['image_path']) }}">
                            @else
                            <img src="{{ asset('img/visitor/basic_image/no_image.png') }}">
                            @endif
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            <?php $panel_count += 1;?>
            @endforeach
            @endforeach
        </div>
    </div>
    <!--end group -->

</section>
<!--カテゴリ検索終わり-->

<!--50音検索 -->
<a class="sc_link" id="onkensaku_link"></a>

<section id="onkensaku">

    <h2><img src="{{ asset('img/visitor/basic_image/icon_04.png') }}">50音検索 <span>50音から検索、企業名をクリックで詳細を開きます</span></h2>

    <div class="group">

        <div class="onkensaku_list">
            <div class="tab is-active" id="0">あ行</div>
            <div class="tab" id="1">か行</div>
            <div class="tab" id="2">さ行</div>
            <div class="tab" id="3">た行</div>
            <div class="tab" id="4">な行</div>
            <div class="tab" id="5">は行</div>
            <div class="tab" id="6">ま行</div>
            <div class="tab" id="7">や行</div>
            <div class="tab" id="8">ら行</div>
            <div class="tab" id="9">わ行</div>
        </div>

        <!--ここに切り替える内容 -->
        <div class="panel-group">

            <div class="panel is-show 0">
                <div class="main_category">あ行</div>

                <div class="onkensaku_box">
                    @foreach( $exhibitor_classifications['あ'] as $exhibitor)
                    <div class="onken_item">
                        <a class="iframe"
                            href="{{ route('visitor.exhibitors.show', [$exposition['slug'], $exhibitor['id']]) }}"
                            data-modal-close-url="{{ route('visitor.modal.close.exbhibitor', [$exposition['slug'], $exhibitor['id']]) }}">{{$exhibitor['name']}}</a>
                    </div>
                    @endforeach
                </div>

            </div>

            <div class="panel 1">
                <div class="main_category">か行</div>

                <div class="onkensaku_box">
                    @foreach( $exhibitor_classifications['か'] as $exhibitor)
                    <div class="onken_item">
                        <a class="iframe"
                            href="{{ route('visitor.exhibitors.show', [$exposition['slug'], $exhibitor['id']]) }}"
                            data-modal-close-url="{{ route('visitor.modal.close.exbhibitor', [$exposition['slug'], $exhibitor['id']]) }}">{{$exhibitor['name']}}</a>
                    </div>
                    @endforeach
                </div>

            </div>

            <div class="panel 2">
                <div class="main_category">さ行</div>

                <div class="onkensaku_box">
                    @foreach( $exhibitor_classifications['さ'] as $exhibitor)
                    <div class="onken_item">
                        <a class="iframe"
                            href="{{ route('visitor.exhibitors.show', [$exposition['slug'], $exhibitor['id']]) }}"
                            data-modal-close-url="{{ route('visitor.modal.close.exbhibitor', [$exposition['slug'], $exhibitor['id']]) }}">{{$exhibitor['name']}}</a>
                    </div>
                    @endforeach
                </div>

            </div>

            <div class="panel 3">
                <div class="main_category">た行</div>

                <div class="onkensaku_box">
                    @foreach( $exhibitor_classifications['た'] as $exhibitor )
                    <div class="onken_item">
                        <a class="iframe"
                            href="{{ route('visitor.exhibitors.show', [$exposition['slug'], $exhibitor['id']]) }}"
                            data-modal-close-url="{{ route('visitor.modal.close.exbhibitor', [$exposition['slug'], $exhibitor['id']]) }}">{{$exhibitor['name']}}</a>
                    </div>
                    @endforeach
                </div>

            </div>

            <div class="panel 4">
                <div class="main_category">な行</div>

                <div class="onkensaku_box">
                    @foreach( $exhibitor_classifications['な'] as $exhibitor)
                    <div class="onken_item">
                        <a class="iframe"
                            href="{{ route('visitor.exhibitors.show', [$exposition['slug'], $exhibitor['id']]) }}"
                            data-modal-close-url="{{ route('visitor.modal.close.exbhibitor', [$exposition['slug'], $exhibitor['id']]) }}">{{$exhibitor['name']}}</a>
                    </div>
                    @endforeach
                </div>

            </div>

            <div class="panel 5">
                <div class="main_category">は行</div>

                <div class="onkensaku_box">
                    @foreach( $exhibitor_classifications['は'] as $exhibitor)
                    <div class="onken_item">
                        <a class="iframe"
                            href="{{ route('visitor.exhibitors.show', [$exposition['slug'], $exhibitor['id']]) }}"
                            data-modal-close-url="{{ route('visitor.modal.close.exbhibitor', [$exposition['slug'], $exhibitor['id']]) }}">{{$exhibitor['name']}}</a>
                    </div>
                    @endforeach
                </div>

            </div>

            <div class="panel 6">
                <div class="main_category">ま行</div>

                <div class="onkensaku_box">
                    @foreach( $exhibitor_classifications['ま'] as $exhibitor)
                    <div class="onken_item">
                        <a class="iframe"
                            href="{{ route('visitor.exhibitors.show', [$exposition['slug'], $exhibitor['id']]) }}"
                            data-modal-close-url="{{ route('visitor.modal.close.exbhibitor', [$exposition['slug'], $exhibitor['id']]) }}">{{$exhibitor['name']}}</a>
                    </div>
                    @endforeach
                </div>

            </div>

            <div class="panel 7">
                <div class="main_category">や行</div>

                <div class="onkensaku_box">
                    @foreach( $exhibitor_classifications['や'] as $exhibitor)
                    <div class="onken_item">
                        <a class="iframe"
                            href="{{ route('visitor.exhibitors.show', [$exposition['slug'], $exhibitor['id']]) }}"
                            data-modal-close-url="{{ route('visitor.modal.close.exbhibitor', [$exposition['slug'], $exhibitor['id']]) }}">{{$exhibitor['name']}}</a>
                    </div>
                    @endforeach
                </div>

            </div>

            <div class="panel 8">
                <div class="main_category">ら行</div>

                <div class="onkensaku_box">
                    @foreach( $exhibitor_classifications['ら'] as $exhibitor)
                    <div class="onken_item">
                        <a class="iframe"
                            href="{{ route('visitor.exhibitors.show', [$exposition['slug'], $exhibitor['id']]) }}"
                            data-modal-close-url="{{ route('visitor.modal.close.exbhibitor', [$exposition['slug'], $exhibitor['id']]) }}">{{$exhibitor['name']}}</a>
                    </div>
                    @endforeach
                </div>

            </div>

            <div class="panel 9">
                <div class="main_category">わ行</div>

                <div class="onkensaku_box">
                    @foreach( $exhibitor_classifications['わ'] as $exhibitor)
                    <div class="onken_item">
                        <a class="iframe"
                            href="{{ route('visitor.exhibitors.show', [$exposition['slug'], $exhibitor['id']]) }}"
                            data-modal-close-url="{{ route('visitor.modal.close.exbhibitor', [$exposition['slug'], $exhibitor['id']]) }}">{{$exhibitor['name']}}</a>
                    </div>
                    @endforeach
                </div>

            </div>

        </div>
        <!--end panel-group -->
        <!--ここに切り替える内容 -->

    </div>
    <!--end group -->
</section>
<!--50音検索終わり -->

<!--講演・セミナー-->
<a class="sc_link" id="kouen_link"></a>

<section id="semminer">

    <!--セミナータイトル-->
    <div class="seminer_navi">

        <h2><img src="{{ asset('img/visitor/basic_image/icon_04.png') }}">講演・セミナー</h2>

        <div class="first_navi_box page_width">

            <div class="men_box">
                <img src="{{ asset('img/visitor/basic_image/men.png') }}">
            </div>

            <div class="first_navi_txt">
                <p>
                    リアル展示会の会場で開催した各講演がご視聴いただけます。<br>
                    （一部、講師の都合等により配信できないセッションがございます。）<br>
                    専門セミナーについては、オンライン聴講が可能なセッションのみ表示しています。
                </p>
                <p class="red bold">
                    オンライン展示会会期中のみの公開になりますので、ぜひお見逃しなく！
                </p>
            </div>

            <div class="lady_box">
                <img src="{{ asset('img/visitor/basic_image/lady.png') }}">
            </div>

        </div>


        <div class="seminer_btn_box">
            <div class="seminer_btn"><a href="#seminer01">特別講演の詳細へ<span>▼</span></a></div>
            <div class="seminer_btn"><a href="#seminer02">専門セミナーの詳細へ<span>▼</span></a></div>
        </div>

    </div>
    <!--セミナータイトル-->

    <!--タイムテーブル-->

    <div class="timetable_title">タイムテーブル一覧</div>

    <div class="taimetable_box">
        <img src="{{ asset('img/visitor/basic_image/timetable.jpg') }}">
    </div>

    <!--タイムテーブル-->

    <a class="sc_link" id="seminer01"></a>

    @foreach($exhibitions_with_seminars as $exhibition_with_seminars)
    <!--セミナー大カテゴリ-->
    <h3 class="bg_green">{{ $exhibition_with_seminars['name'] }}</h3>

    @foreach($exhibition_with_seminars['seminar_categories'] as $seminar_categorie)
    <!--セミナー中カテゴリ全体枠-->
    <div class="seminer_warp">

        <!--セミナー中カテゴリ-->
        <h4>●{{ $seminar_categorie['name'] }}</h4>

        <!--セミナー中カテゴリ毎の枠-->
        <div class="seminer_inner">
            @foreach($seminar_categorie['seminars'] as $seminar)
            <!--セミナー個別枠-->
            <div class="seminer_box">
                <h5>
                    @if( !empty($seminar['seminar_number']) )<span
                        class="semi_num">{{ $seminar['seminar_number'] }}</span>@endif {{ $seminar['title'] }}
                    <br>
                    @if( !empty($seminar['subtitle']) ) ～ {{ $seminar['subtitle'] }} ～ @endif
                </h5>

                @if( !empty($seminar['embed_code']) )
                <a class="iframe cboxElement"
                    href="{{ route('visitor.seminars.video', [$exposition['slug'], $seminar['id']]) }}"
                    data-modal-close-url="{{ route('visitor.modal.close.seminar', [$exposition['slug'], $seminar['id']]) }}">
                    @else
                    @endif

                    <div class="seminer_middle">
                        <div class="seminer_photo">
                            @if( !empty($seminar['profile_image_path']) )
                            <img src="{{ asset('storage/' . $seminar['profile_image_path']) }}">
                            @else
                            <img src="{{ asset('img/visitor/basic_image/no_image.png') }}">
                            @endif
                        </div>
                        <div class="seminer_txt">
                            {!! $seminar['profile_text'] !!}
                        </div>
                    </div>



                    @if( !empty($seminar['embed_code']) )
                </a>
                @else
                @endif

                {{--
                            <div class="seminer_bottom">
                                <div class="seminer_view">
                                    @if( !empty($seminar['embed_code']) )
                                    <a class="iframe cboxElement"
                                        href="{{ route('visitor.seminars.video', [$exposition['slug'], $seminar['id']]) }}"
                data-modal-close-url="{{ route('visitor.modal.close.seminar', [$exposition['slug'], $seminar['id']]) }}"
                >動画を視聴する</a>
                @else
                <!-- TODO動画が登録されていない場合の挙動 -->
                <a>動画が登録されておりません</a>
                @endif
            </div>
            <div class="seminer_prof">詳細プロフィールを見る</div>
            <div class="seminer_pro_txt">{!! $seminar['content'] !!}</div>
        </div>
        --}}


    </div>
    @endforeach
    </div>
    </div>
    @endforeach
    @endforeach

    <a class="sc_link" id="seminer02"></a>
    <h3 class="bg_red">専門セミナー</h3>

    @foreach($specialized_seminar_categories as $specialized_seminar_category)
    <!--セミナー中カテゴリ全体枠-->
    <div class="seminer_warp">
        <!--セミナー中カテゴリ-->
        <h4>●{{ $specialized_seminar_category['name'] }}</h4>

        <!--セミナー中カテゴリ毎の枠-->
        <div class="seminer_inner">
            @foreach($specialized_seminar_category['seminars'] as $seminar)
            <!--セミナー個別枠-->
            <div class="seminer_box">

                <h5>
                    @if( !empty($seminar['seminar_number']) )<span
                        class="semi_num">{{ $seminar['seminar_number'] }}</span>@endif {{ $seminar['title'] }}
                    <br>
                    @if( !empty($seminar['subtitle']) ) ～ {{ $seminar['subtitle'] }} ～ @endif
                </h5>

                <div class="seminer_middle">
                    <div class="seminer_photo">
                        @if( !empty($seminar['profile_image_path']) )
                        <img src="{{ asset('storage/' . $seminar['profile_image_path']) }}">
                        @else
                        <img src="{{ asset('img/visitor/basic_image/no_image.png') }}">
                        @endif
                    </div>
                    <div class="seminer_txt">
                        {!! $seminar['profile_text'] !!}
                    </div>
                </div>


                {{--
                    <div class="seminer_bottom">
                        <div class="seminer_view">
                            @if( !empty($seminar['embed_code']) )
                            <a class="iframe cboxElement"
                                href="{{ route('visitor.seminars.video', [$exposition['slug'], $seminar['id']]) }}"
                data-modal-close-url="{{ route('visitor.modal.close.seminar', [$exposition['slug'], $seminar['id']]) }}"
                >動画を視聴する</a>
                @else
                <!-- TODO動画が登録されていない場合の挙動 -->
                <a>動画が登録されておりません</a>
                @endif
            </div>
            <div class="seminer_prof">詳細プロフィールを見る</div>
            <div class="seminer_pro_txt">{!! $seminar['content'] !!}</div>
        </div>
        --}}


    </div>
    @endforeach
    </div>
    </div>
    @endforeach
</section>
<!--講演・セミナ終わりー-->

@endsection

@section('js')
<script>
    $(function() {
        $('.bg-slider').bgSwitcher({
            images: ["{{ asset('img/visitor/basic_image/01.jpg') }}", "{{ asset('img/visitor/basic_image/02.JPG') }}", "{{ asset('img/visitor/basic_image/03.JPG') }}", "{{ asset('img/visitor/basic_image/04.JPG') }}", "{{ asset('img/visitor/basic_image/05.JPG') }}", "{{ asset('img/visitor/basic_image/06.JPG') }}", "{{ asset('img/visitor/basic_image/07.JPG') }}", "{{ asset('img/visitor/basic_image/08.JPG') }}", "{{ asset('img/visitor/basic_image/09.JPG') }}", "{{ asset('img/visitor/basic_image/10.jpg') }}", "{{ asset('img/visitor/basic_image/seminar_bg.jpg') }}"
            ], // 切り替える背景画像を指定
            interval: 850, // 背景画像を切り替える間隔を指定 3000=3秒
            duration: 500, // エフェクトの時間を指定します。
            loop: false // 切り替えを繰り返すか指定 true=繰り返す　false=繰り返さない
        })
    });

    $(function() {
        setTimeout(function () {
            $('#bg').fadeOut(500);
        }, 10000),

        setTimeout(function () {
            $('#content').fadeIn(1000);
            $(window).resize();
        }, 10000)
    });

// アニメーションスキップ
@if($is_preview === true)
    $(function() {
        setTimeout(function () {
            $('#bg').fadeOut(500);
        }, 100),

        setTimeout(function () {
            $('#content').fadeIn(100);
            $(window).resize();
        }, 100)
    });
@endif

    $('img[usemap]').rwdImageMaps();

    $(function() {
        //要素分を繰り返す
<?php $area_count = 1;?>
        @foreach( $exhibitors as $exhibitor)
        $('.area{{ $area_count }}').hover(function() {$("#p_area{{ $area_count }}").show();},function() {$("#p_area{{ $area_count }}").hide();});
<?php $area_count += 1;?>
        @endforeach
{{--
        $('.area1').hover(function() {$("#p_area1").show();},function() {$("#p_area1").hide();});
        $('.area2').hover(function() {$("#p_area2").show();},function() {$("#p_area2").hide();});
        $('.area3').hover(function() {$("#p_area3").show();},function() {$("#p_area3").hide();});
--}}
        //要素分を繰り返す　ここまで

        $('.map_link').hover(function(){
            $(window).mousemove(function(event){
                var mouse_x = event.clientX + 5;
                var mouse_y = $(window).scrollTop() + event.clientY + 5;
                if(mouse_x < window.outerWidth/2){
                    $(".p_box").css({
                        "left": mouse_x + 40,
                        "top": mouse_y - 30,
                    });
                }else{
                    $(".p_box").css({
                        "left": mouse_x - 250,
                        "top": mouse_y - 30,
                    });
                }
            });
	});
    });

// オンラインムービークリックで発火
$('#bg').click(function() {
  setTimeout(function () {
    $('#bg').fadeOut(500);
  }, 100),

  setTimeout(function () {
    $('#content').fadeIn(1000);
    $(window).resize();
  }, 100)
});

</script>
@append