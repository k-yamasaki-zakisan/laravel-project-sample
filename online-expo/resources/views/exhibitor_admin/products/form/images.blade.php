<div class="row">
    <div id="image_zone" class="col-md-12">
        <div class="card card-info">
            <form action={{ route('exhibitor_admin.product.images.update_sort', [$slug, $product['id']]) }}
                method="post">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <ul class="todo-list image-list" data-widget="image-list" id="add-image-zone">
                        <!-- /.アップロード画像のソート順変更エリア -->
                        @foreach($product_images as $product_image)
                        <li>
                            <input type="hidden" name="sort_indexs[]" value="{{ $product_image['id'] }}">
                            <!-- drag handle -->
                            <span class="handle handle-image">
                                <i class="fas fa-ellipsis-v"></i>
                                <i class="fas fa-ellipsis-v"></i>
                            </span>
                            <!-- image -->
                            <img src="{{ asset('storage/' . $product_image['image_path']) }}" width="50%" />
                            <div class="tools">
                                <i class="fa fa-trash js-ajax-deleteButton image-{{ $product_image['id'] }}"
                                    data-target="image-{{ $product_image['id'] }}" data-url="{{ route('exhibitor_admin.product.images.destroy', [
                        'expo_slug' => $slug,
                        'product_id' => $product['id'],
                        'product_image_id' => $product_image['id']
                      ]) }}"></i>
                            </div>
                        </li>
                        @endforeach

                    </ul>
                </div>
                <div class="card-body">
                    <button type="submit" class="btn btn-primary">ソート順を確定する</button>
                </div>
            </form>

            <div class="card-body">
                <div id="actions" class="row">
                    <div class="col-lg-6">
                        <div class="btn-group w-100">
                            <span class="btn btn-success col fileinput-button">
                                <i class="fas fa-plus"></i>
                                <span>Add files</span>
                            </span>
                            <button type="submit" class="btn btn-primary col start">
                                <i class="fas fa-upload"></i>
                                <span>Start upload</span>
                            </button>
                            <button type="reset" class="btn btn-warning col cancel">
                                <i class="fas fa-times-circle"></i>
                                <span>Cancel upload</span>
                            </button>
                        </div>
                    </div>
                    <div class="col-lg-6 d-flex align-items-center">
                        <div class="fileupload-process w-100">
                            <div id="total-progress" class="progress progress-striped active" role="progressbar"
                                aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- /.暫定アップロードエリア -->
                <div class="table table-striped files" id="previews">
                    <div id="template" class="row mt-2">
                        <div class="col-auto">
                            <span class="preview"><img src="data:," alt="" data-dz-thumbnail /></span>
                        </div>
                        <div class="col d-flex align-items-center">
                            <p class="mb-0">
                                <span class="lead" data-dz-name></span>
                                (<span data-dz-size></span>)
                            </p>
                            <strong class="error text-danger" data-dz-errormessage></strong>
                        </div>
                        <div class="col-4 d-flex align-items-center">
                            <div class="progress progress-striped active w-100" role="progressbar" aria-valuemin="0"
                                aria-valuemax="100" aria-valuenow="0">
                                <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto d-flex align-items-center">
                            <div class="btn-group">
                                <button class="btn btn-primary start">
                                    <i class="fas fa-upload"></i>
                                    <span>Start</span>
                                </button>
                                <button data-dz-remove class="btn btn-warning cancel">
                                    <i class="fas fa-times-circle"></i>
                                    <span>Cancel</span>
                                </button>
                                <button data-dz-remove class="btn btn-danger delete">
                                    <i class="fas fa-trash"></i>
                                    <span>Delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
                ※）ドラッグ＆ドロップでアップロードできます。
            </div>
        </div>
    </div>
</div>


@section('css')
<link rel="stylesheet" href={{ asset('vendor/adminlte/plugins/jquery-ui/jquery-ui.min.css') }}>
<link rel="stylesheet" href={{ asset('vendor/jquery_plugins/dropzone-5.7.0/dist/min/dropzone.min.css') }}>
@append


@section('js')
{{-- dummy用 --}}
<li id="js-dummy-image-listItem" class="hidden">
    <input type="hidden" name="sort_indexs[]">
    <!-- drag handle -->
    <span class="handle handle-image">
        <i class="fas fa-ellipsis-v"></i>
        <i class="fas fa-ellipsis-v"></i>
    </span>
    <!-- image -->
    <img src="" width="50%" />
    <!-- General tools such as edit or delete-->
    <div class="tools">
        <i class="fa fa-trash js-ajax-deleteButton"></i>
    </div>
</li>
@component('components.adminlte.modal.ajaxDelete')
@endcomponent
<script src={{ asset("vendor/adminlte/plugins/jquery-ui/jquery-ui.min.js") }}></script>
<script src={{ asset("vendor/jquery_plugins/dropzone-5.7.0/dist/min/dropzone.min.js") }}></script>
<script type="text/javascript">
    $(function () {
    // dummyの読み込み
    const data = {};
    let dummyImageListItem = $('#js-dummy-image-listItem');
    data['ImageListItem']  = dummyImageListItem.clone(true).removeAttr('id').removeClass('hidden');
    dummyImageListItem.remove();
  
    // jQuery UI sortable for the todo list
    $('.image-list').sortable({
    placeholder: 'sort-highlight',
    handle: '.handle-image',
    forcePlaceholderSize: true,
    zIndex: 999999
  })
  
  
  // DropzoneJS Demo Code Start
  //Dropzone.autoDiscover = false;
  
  // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
  var previewNode = document.querySelector("#template");
  previewNode.id = "";
  var previewTemplate = previewNode.parentNode.innerHTML;
  previewNode.parentNode.removeChild(previewNode);
  
  var myDropzone = new Dropzone("div#image_zone", { // Make the whole body a dropzone
    url: "{{ route('exhibitor_admin.product.images.store', [
      'expo_slug' => $slug,
      'product_id' => $product['id']
    ]) }}", // Set the url
    method: "post",
    thumbnailWidth: 80,
    thumbnailHeight: 80,
    parallelUploads: 20,
    paramName: "product_image",
    previewTemplate: previewTemplate,
    autoQueue: false, // Make sure the files aren't queued until manually added
    previewsContainer: "#previews", // Define the container to display the previews
    clickable: ".fileinput-button", // Define the element that should be used as click trigger to select files.
    acceptedFiles: "image/jpeg,image/png",
    "error": function(file, message, xhr) {
      if (xhr == null) this.removeFile(file);
    },
    sending: function(file, xhr, formData) {
      formData.append("_token", "{{ csrf_token() }}");
    }
  });
  
  myDropzone.on("addedfile", function(file) {
  // Hookup the start button
  file.previewElement.querySelector(".start").onclick = function() { myDropzone.enqueueFile(file); };
  });
  
  // Update the total progress bar
  myDropzone.on("totaluploadprogress", function(progress) {
  document.querySelector("#total-progress .progress-bar").style.width = progress + "%";
  });
  
  myDropzone.on("sending", function(file) {
  // Show the total progress bar when upload starts
  document.querySelector("#total-progress").style.opacity = "1";
  // And disable the start button
  file.previewElement.querySelector(".start").setAttribute("disabled", "disabled");
  });
  
  // Hide the total progress bar when nothing's uploading anymore
  myDropzone.on("queuecomplete", function(progress) {
  document.querySelector("#total-progress").style.opacity = "0";
  });
  
  // Setup the buttons for all transfers
  // The "add files" button doesn't need to be setup because the config
  // `clickable` has already been specified.
  document.querySelector("#actions .start").onclick = function() {
  myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED));
  };
  document.querySelector("#actions .cancel").onclick = function() {
  myDropzone.removeAllFiles(true);
  };
  
  // サーパーレスポンス処理(正常系)
  myDropzone.on("success", function( file, result ) {
    let imageListItem = data['ImageListItem'].clone(true);
    imageListItem.find('img').attr('src', "{{ asset('storage') }}" + '/' + result['product_image']['image_path']);
    imageListItem.find('input').attr('value', result['product_image']['id']);
    // 画像削除path作成
    const deletePath = "{{ route('exhibitor_admin.product.images.store', [$slug, $product['id']]) }}/" + result['product_image']['id'];
    imageListItem.find('.js-ajax-deleteButton').attr('data-url', deletePath);
    imageListItem.find('.js-ajax-deleteButton').attr('data-target', "image-"+ result['product_image']['id']);
    imageListItem.find('.js-ajax-deleteButton').addClass("image-" + result['product_image']['id']);
  
    $('#add-image-zone').append(imageListItem);
    this.removeFile(file);
  });
  
  // サーパーレスポンス処理(異常系)
  myDropzone.on("error", function( file, errorMessage, xhr ) {
    const text = $.parseJSON(jqXHR.responseText);
    const errors = text.errors;
    for (key in errors) {
      for (index in errors[key]) {
        let errorMessage = errors[key][index];
        $('#message').append(`<p style="color:red;">${errorMessage}</p>`);
      }
    }
  });
  // DropzoneJS Demo Code End
  })
</script>
@append