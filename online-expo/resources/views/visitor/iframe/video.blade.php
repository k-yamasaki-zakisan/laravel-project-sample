<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/visitor/jquery.colorbox.js') }}"></script>
    <script src="{{ asset('js/visitor/jquery.modal.js') }}"></script>
    <script src="{{ asset('vendor/jquery/jQuery-rwdImageMaps-1.6/jquery.rwdImageMaps.min.js') }}"></script>


    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/visitor/colorbox.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/visitor/default.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/visitor/basic.css') }}" rel="stylesheet" type="text/css">

</head>

<body id="modal_body">

    <main>
        <div class="campany_movie">
            <div class="movie_box">
                <div class="youtube">
                    {{--
                    <iframe width="100%" height="100%" src="https://www.youtube.com/embed/{{ $embed_code }}"
                    frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope;
                    picture-in-picture" allowfullscreen></iframe>
                    --}}
                    <div id="seminar_player"></div>
                </div>
            </div>
        </div>

    </main>
</body>

<script>
    var tag = document.createElement('script');
  tag.src = "https://www.youtube.com/iframe_api";
  var firstScriptTag = document.getElementsByTagName('script')[0];
  firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

  var player;
  function onYouTubeIframeAPIReady() {
    player = new YT.Player('seminar_player', {
      //height: '360',
      //width: '640',
      videoId: "{{ $seminar['embed_code'] }}",
      events: {
        //'onReady': onPlayerReady,  プレーヤーが準備できた時の処理
        'onStateChange': onPlayerStateChange
      }
    });
  }
{{--
  function onPlayerReady(event) {
    event.target.playVideo();
  }
--}}
  //var done = false;
  function onPlayerStateChange(event) {
    if (event.data == YT.PlayerState.PLAYING) {
      //setTimeout(stopVideo, 6000);
      //done = true;
      videoLogStore('PLAYING')
    }
    if (event.data == YT.PlayerState.PAUSED) {
      videoLogStore('STOPING')
    }
  }

  {{-- event.data == YT.PlayerState.PLAYINGの場合setTimeout後に発動するメソッド --}}
  function stopVideo() {
    player.stopVideo();
  }

  function videoLogStore(video_status) {
    let url = null;
    if (video_status === 'PLAYING') {
      url = "{{ route('visitor.seminars.video.start', [$slug, $seminar['id']]) }}"
    }
    if (video_status === 'STOPING') {
      url = "{{ route('visitor.seminars.video.stop', [$slug, $seminar['id']]) }}"
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
    .done(function(responsData) {
      //console.log('ok')
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
      //console.log('ng')
    });
  }
</script>

</html>