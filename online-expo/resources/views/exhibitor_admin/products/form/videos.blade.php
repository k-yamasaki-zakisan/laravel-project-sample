<div class="row">
    <div class="col-md-12">
        <div class="card card-info">
            <div class="card-body">
                <ul class="todo-list video-list" data-widget="video-list" id="add-video-zone">
                </ul>
            </div>

            <div class="card-footer clearfix">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control input-video-url"
                        placeholder="https://www.youtube.com/watch?v=XXXXXXXXXXXXXXXXXX">
                    <span class="input-group-append">
                        <button type="button" class="btn btn-info btn-flat add-video"><i class="fas fa-plus"></i>
                            追加</button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

@section('css')
<style>
    .hidden {
        display: none;
    }
</style>
@append


@section('js')
{{-- dummy用 --}}
<li id="js-dummy-video-listItem" class="hidden js-video-listItem">
    <!-- drag handle -->
    <span class="handle handle-video">
        <i class="fas fa-ellipsis-v"></i>
        <i class="fas fa-ellipsis-v"></i>
    </span>
    <!-- checkbox -->
    <div class="icheck-primary d-inline ml-2">
        <input type="checkbox" value="" name="todo1" id="todoCheck1">
        <label for="todoCheck1"></label>
    </div>
    <!-- video -->
    <iframe width="240" height="135" frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen>
    </iframe>
    <!-- General tools such as edit or delete-->
    <div class="tools">
        <i class="fa fa-trash js-remove-video"></i>
    </div>
</li>

<script type="text/javascript">
    $(function () {
      let data = {};
  
      // jQuery UI sortable for the todo list
      $('.video-list').sortable({
        placeholder: 'sort-highlight',
        handle: '.handle-video',
        forcePlaceholderSize: true,
        zIndex: 999999
      })
  
      $('.add-video').click( function() {
        const url = $('.input-video-url').val();
        const resultUrl = checkVideoUrl(url);
        // 入力値がない場合は終了
        if (resultUrl === null) {
          alert('こちらの形式で入力ください \n https://www.youtube.com/watch?v=XXXXXXXXXXXXXXXXXX');
          $('.input-video-url').val('');
          return;
        }
        // 挿入予定の要素をクローンしてappend
        let videoListItem = data['videoListItem'].clone(true);
        videoListItem.find('iframe').attr('src', resultUrl);
        $('#add-video-zone').append(videoListItem);
        //inputの初期化
        $('.input-video-url').val('');
      })
  
      $('.js-remove-video').click( function() {
        $(this).closest('.js-video-listItem').remove();
      })
  
      let dummyVideoListItem = $('#js-dummy-video-listItem');
      data['videoListItem']  = dummyVideoListItem.clone(true).removeAttr('id').removeClass('hidden');
      dummyVideoListItem.remove();
    })
  
    function checkVideoUrl(url) {
      const embed_check = /^https:\/\/www.youtube.com\/embed\/[^ ]+$/g;
      //const watch_check = /^https:\/\/www.youtube.com\/watch\?[a-zA-Z0-9_=-]+$/g;
      const watch_check = /^https:\/\/www.youtube.com\/watch\?[^ ]+$/g;
      // embedの場合はそのままurlを返す
      if (embed_check.test(url)) return url;
      else if(watch_check.test(url)) return translateEmbedUrl(url);
      else return null;
    }
  
    function translateEmbedUrl(url) {
      return url.replace(/watch\?v=/, 'embed/');
    }
</script>
@append