<!-- start of exhibitor_admin.exhibitors.edit.images -->
<div class="row">
    <div id="attechment_file_zone" class="col-md-12">
        <div class="card card-info">
            <form action={{ route('exhibitor_admin.product.attachment_files.update_sort', [$slug, $product['id']]) }}
                method="post">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <ul class="todo-list file-list" data-widget="file-list" id="add-file-zone">

                        @foreach($product_attachment_files as $product_attachment_file)
                        <li>
                            <input type="hidden" name="sort_indexs[]" value="{{ $product_attachment_file['id'] }}">
                            <!-- drag handle -->
                            <span class="handle handle-file">
                                <i class="fas fa-ellipsis-v"></i>
                                <i class="fas fa-ellipsis-v"></i>
                            </span>
                            <!-- image -->
                            <img src="{{ asset('storage/' . $product_attachment_file['image_path']) }}" width="50%" />
                            <div class="tools">
                                <i class="fa fa-trash js-ajax-deleteButton file-{{ $product_attachment_file['id'] }}"
                                    data-target="file-{{ $product_attachment_file['id'] }}" data-url="{{ route('exhibitor_admin.product.attachment_files.destroy', [
                      'expo_slug' => $slug,
                      'product_id' => $product['id'],
                      'product_attachment_file_id' => $product_attachment_file['id']
                    ]) }}"></i>
                            </div>
                        </li>
                        @endforeach

                    </ul>
                </div>

                <!-- /.card-body -->
                <div class="card-body">
                    <button type="submit" class="btn btn-primary">ソート順を確定する</button>
                </div>
            </form>

            <div class="card-body">
                <div id="file-actions" class="row">
                    <div class="col-lg-6">
                        <div class="btn-group w-100">
                            <span class="btn btn-success col attachment-file-input-button">
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
                            <div id="file-total-progress" class="progress progress-striped active" role="progressbar"
                                aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="table table-striped files" id="preview-files">
                    <div id="template-file" class="row mt-2">
                        <div class="col-auto">
                            <span class="preview-files"><img src="data:," alt="" data-dz-thumbnail /></span>
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
        <!-- /.card -->
    </div>
</div>
<!-- /.row -->


@section('css')
<link rel="stylesheet" href={{ asset('vendor/adminlte/plugins/jquery-ui/jquery-ui.min.css') }}>
<link rel="stylesheet" href={{ asset('vendor/jquery_plugins/dropzone-5.7.0/dist/min/dropzone.min.css') }}>
@append
@section('js')
@component('components.adminlte.modal.ajaxDelete')
@endcomponent
{{-- dummy用 --}}
<li id="js-dummy-file-listItem" class="hidden">
    <input type="hidden" name="sort_indexs[]">
    <!-- drag handle -->
    <span class="handle handle-file">
        <i class="fas fa-ellipsis-v"></i>
        <i class="fas fa-ellipsis-v"></i>
    </span>
    <!-- image -->
    <iframe src="" width="50%" />
    <!-- General tools such as edit or delete-->
    <div class="tools">
        <i class="fa fa-trash js-ajax-deleteButton"></i>
    </div>
</li>

<script src={{ asset("vendor/adminlte/plugins/jquery-ui/jquery-ui.min.js") }}></script>
<script src={{ asset("vendor/jquery_plugins/dropzone-5.7.0/dist/min/dropzone.min.js") }}></script>
<script type="text/javascript">
    $(function () {
    const data = {};
    let dummyFileListItem = $('#js-dummy-file-listItem');
    data['FileListItem']  = dummyFileListItem.clone(true).removeAttr('id').removeClass('hidden');
    dummyFileListItem.remove();
  
    // jQuery UI sortable for the todo list
    $('.file-list').sortable({
      placeholder: 'sort-highlight',
      handle: '.handle-file',
      forcePlaceholderSize: true,
      zIndex: 999999
  })
  
  
  // DropzoneJS Demo Code Start
  Dropzone.autoDiscover = false;
  
  // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
  var previewNodeAttechmentFile = document.querySelector("#template-file");
  previewNodeAttechmentFile.id = "";
  var previewTemplateAttechmentFile = previewNodeAttechmentFile.parentNode.innerHTML;
  previewNodeAttechmentFile.parentNode.removeChild(previewNodeAttechmentFile);
  
  var myDropzoneAttechmentFile = new Dropzone("div#attechment_file_zone", { // Make the whole body a dropzone
    url: "{{ route('exhibitor_admin.product.attachment_files.store', [$slug, $product['id']]) }}", // Set the url
    method: "post",
    thumbnailWidth: 80,
    thumbnailHeight: 80,
    parallelUploads: 20,
    paramName: "product-attachment-files",
    previewTemplate: previewTemplateAttechmentFile,
    autoQueue: false, // Make sure the files aren't queued until manually added
    previewsContainer: "#preview-files", // Define the container to display the previews
    clickable: ".attachment-file-input-button", // Define the element that should be used as click trigger to select files.
    acceptedFiles: ".pdf",
    "error": function(file, message, xhr) {
      if (xhr == null) this.removeFile(file);
    },
    sending: function(file, xhr, formData) {
      formData.append("_token", "{{ csrf_token() }}");
    }
  });
  
  myDropzoneAttechmentFile.on("addedfile", function(file) {
    // Hookup the start button
    file.previewElement.querySelector(".start").onclick = function() { myDropzoneAttechmentFile.enqueueFile(file); };
  });
  
  // Update the total progress bar
  myDropzoneAttechmentFile.on("totaluploadprogress", function(progress) {
    document.querySelector("#file-total-progress .progress-bar").style.width = progress + "%";
  });
  
  myDropzoneAttechmentFile.on("sending", function(file) {
    $('#message').empty();
    // Show the total progress bar when upload starts
    document.querySelector("#file-total-progress").style.opacity = "1";
    // And disable the start button
    file.previewElement.querySelector(".start").setAttribute("disabled", "disabled");
  });
  
  // Hide the total progress bar when nothing's uploading anymore
  myDropzoneAttechmentFile.on("queuecomplete", function(progress) {
    document.querySelector("#file-total-progress").style.opacity = "0";
  });
  
  // Setup the buttons for all transfers
  // The "add files" button doesn't need to be setup because the config
  // `clickable` has already been specified.
  document.querySelector("#file-actions .start").onclick = function() {
    myDropzoneAttechmentFile.enqueueFiles(myDropzoneAttechmentFile.getFilesWithStatus(Dropzone.ADDED));
  };
  document.querySelector("#file-actions .cancel").onclick = function() {
    myDropzoneAttechmentFile.removeAllFiles(true);
  };
  
  // サーパーレスポンス処理(正常系)
  myDropzoneAttechmentFile.on("success", function( file, result ) {
    let fileListItem = data['FileListItem'].clone(true);
    fileListItem.find('img').attr('src', "{{ asset('storage') }}" + '/' + result['product_attachment_file']['file_path']);
    fileListItem.find('input').attr('value', result['product_attachment_file']['id']);
    // 動画削除path作成
    const deletePath = "{{ route('exhibitor_admin.product.attachment_files.store', [$slug, $product['id']]) }}/" + result['product_attachment_file']['id'];
    fileListItem.find('.js-ajax-deleteButton').attr('data-url', deletePath);
    fileListItem.find('.js-ajax-deleteButton').attr('data-target', "file-"+ result['product_attachment_file']['id']);
    fileListItem.find('.js-ajax-deleteButton').addClass("file-" + result['product_attachment_file']['id']);
  
    $('#add-file-zone').append(fileListItem);
    this.removeFile(file);
  });
  
  // サーパーレスポンス処理(異常系)
  myDropzoneAttechmentFile.on("error", function( file, errorMessage, xhr ) {
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

<!-- end of exhibitor_admin.exhibitors.edit.images -->