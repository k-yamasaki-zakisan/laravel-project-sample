<!-- start of exhibitor_admin.exhibitors.edit.videos -->
<div class="row">
    <div class="col-md-12">
        <div class="card card-info">
            {{--
            <div class="card-header">
                <h3 class="card-title">プロフィール画像登録</h3>
            </div>
--}}

            <form action={{ route('exhibitor_admin.exhibitor.videos.update_sort', [$slug]) }} method="post">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <ul class="todo-list video-list" data-widget="video-list" id="add-video-zone">

                        @foreach( $exhibitor_videos as $exhibitor_video)
                        <li>
                            <input type="hidden" name="sort_indexs[]" value="{{ $exhibitor_video['id'] }}">
                            <!-- drag handle -->
                            <span class="handle handle-video">
                                <i class="fas fa-ellipsis-v"></i>
                                <i class="fas fa-ellipsis-v"></i>
                            </span>

                            <!-- video -->
                            <iframe width="240" height="135"
                                src="https://www.youtube.com/embed/{{ $exhibitor_video['embed_code']}}" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen></iframe>
                            <!-- General tools such as edit or delete-->
                            <div class="tools">
                                <i class="fa fa-trash js-ajax-deleteButton video-{{ $exhibitor_video['id'] }}"
                                    data-target="video-{{ $exhibitor_video['id'] }}" data-url="{{ route('exhibitor_admin.exhibitor.videos.destroy', [
                                        'expo_slug' => $slug,
                                        'id' => $exhibitor_video['id']
                                    ]) }}"></i>
                            </div>
                        </li>
                        @endforeach

                        {{--
                  <li>
                    <!-- drag handle -->
                    <span class="handle handle-video">
                      <i class="fas fa-ellipsis-v"></i>
                      <i class="fas fa-ellipsis-v"></i>
                    </span>
                    <!-- checkbox -->
                    <div  class="icheck-primary d-inline ml-2">
                      <input type="checkbox" value="" name="todo1" id="todoCheck1">
                      <label for="todoCheck1"></label>
                    </div>
                    <!-- video -->
                    <iframe width="240" height="135" src="https://www.youtube.com/embed/lS0_Ah5ruYA" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    <!-- General tools such as edit or delete-->
                    <div class="tools">
                      <i class="fas fa-edit"></i>
                      <i class="fas fa-trash-o"></i>
                    </div>
                 </li>
--}}
                    </ul>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">ソート順を確定する</button>
                    </div>
                    <div id="error-message-video"></div>
                </div>
                <!-- /.card-body -->
            </form>

            <div class="card-footer clearfix">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control input-video-url" name="youtube_url"
                        value="{{ old('youtube_url')}}"
                        placeholder="https://www.youtube.com/watch?v=XXXXXXXXXXXXXXXXXX">
                    <span class="input-group-append">
                        <button type="button" id="add-video-button" class="btn btn-info btn-flat"><i
                                class="fas fa-plus"></i> 追加</button>
                    </span>
                </div>
            </div>
        </div>
        <!-- /.card -->
    </div>
</div>
<!-- /.row -->


{{-- images.blade.php で読み込んでいるので不要
@section('css')
<link rel="stylesheet" href={{ asset('vendor/adminlte/plugins/jquery-ui/jquery-ui.min.css') }}>
@append
--}}


@section('js')
{{-- dummy用 --}}
<li id="js-dummy-video-listItem" class="hidden">
    <input type="hidden" name="sort_indexs[]">
    <!-- drag handle -->
    <span class="handle handle-video">
        <i class="fas fa-ellipsis-v"></i>
        <i class="fas fa-ellipsis-v"></i>
    </span>
    <!-- video -->
    <iframe width="240" height="135" frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen></iframe>
    <!-- General tools such as edit or delete-->
    <div class="tools">
        <i class="fa fa-trash js-ajax-deleteButton"></i>
    </div>
</li>

{{-- images.blade.php で読み込んでいるので不要
<script src={{ asset("vendor/adminlte/plugins/jquery-ui/jquery-ui.min.js") }}></script>
--}}
<script type="text/javascript">
    $(function () {
        const data = {};
        let dummyVideoListItem = $('#js-dummy-video-listItem');
        data['videoListItem']  = dummyVideoListItem.clone(true).removeAttr('id').removeClass('hidden');
        dummyVideoListItem.remove();

        // jQuery UI sortable for the todo list
        $('.video-list').sortable({
            placeholder: 'sort-highlight',
            handle: '.handle-video',
            forcePlaceholderSize: true,
            zIndex: 999999
        })

        $('#add-video-button').on('click', function() {
            $('#message').empty();

            const youtube_url = $('.input-video-url').val();

            if( youtube_url.length === 0 ) return;

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('exhibitor_admin.exhibitor.videos.store', [$slug]) }}",
                type: 'POST',
                timeout: 8000, //8sec
                data: {'youtube_url': youtube_url},
            })
            .done(function(responsData) {
                // 挿入予定の要素をクローンしてappend
                let videoListItem = data['videoListItem'].clone(true);
                videoListItem.find('iframe').attr('src', "https://www.youtube.com/embed/"+responsData['exhibitor_video']['embed_code']);
                videoListItem.find('input').attr('value', responsData['exhibitor_video']['id']);

                // 動画削除path作成
                const deletePath = "{{ route('exhibitor_admin.exhibitor.videos.store', [$slug]) }}/"+responsData['exhibitor_video']['id'];
                videoListItem.find('.js-ajax-deleteButton').attr('data-url', deletePath);
                videoListItem.find('.js-ajax-deleteButton').attr('data-target', "video-"+responsData['exhibitor_video']['id']);
                videoListItem.find('.js-ajax-deleteButton').addClass("video-"+responsData['exhibitor_video']['id']);

                toastr.success("動画の登録が完了しました");
                $('#add-video-zone').append(videoListItem);
                $('.input-video-url').val('');
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                const text = $.parseJSON(jqXHR.responseText);
                const errors = text.errors;
                for (key in errors) {
                    for (index in errors[key]) {
                        let errorMessage = errors[key][index];
                        toastr.error(errorMessage);
                    }
                }
            });
        });
    })
</script>
@append

<!-- end of exhibitor_admin.exhibitors.edit.videos -->