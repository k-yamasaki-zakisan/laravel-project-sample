<div class="row">
    <div id="attechment_file_zone" class="col-md-12">
        <div class="card card-info">
            <div class="card-body">

                <ul class="todo-list attachment-file-list files" data-widget="attachment-files-list"
                    id="previews_attechment_file">
                    <!-- アップロードソート順変更エリア -->
                    <li style="display:none;">
                        <div id="template_attechment_file">
                            <!-- drag handle -->
                            <span class="handle handle-image">
                                <i class="fas fa-ellipsis-v"></i>
                                <i class="fas fa-ellipsis-v"></i>
                            </span>
                            <!-- checkbox -->
                            {{--<div class="icheck-primary d-inline ml-2">
                    <input type="checkbox" value="" name="todo1" id="todoCheck1">
                    <label for="todoCheck1"></label>
                  </div>--}}
                            <!-- image -->
                            {{--<img src="data:," width="50%" alt="" data-dz-thumbnail />--}}
                            <span class="lead" data-dz-name></span>
                            <!-- General tools such as edit or delete-->
                            <i class="fa fa-trash" style="float:right;" data-dz-remove></i>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div id="actions_attechment_file" class="row">
                    <div class="col-lg-6">
                        <div class="btn-group w-100">
                            <span class="btn btn-success col attachment-fileinput-button">
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
<script src={{ asset("vendor/adminlte/plugins/jquery-ui/jquery-ui.min.js") }}></script>
<script src={{ asset("vendor/jquery_plugins/dropzone-5.7.0/dist/min/dropzone.min.js") }}></script>
<script type="text/javascript">
    $(function () {
  // jQuery UI sortable for the todo list
  $('.attachment-file-list').sortable({
  placeholder: 'sort-highlight',
  handle: '.handle-image',
  forcePlaceholderSize: true,
  zIndex: 999999
  })
  
  
  // DropzoneJS Demo Code Start
  //Dropzone.autoDiscover = false;
  
  // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
  var previewNodeAttechmentFile = document.querySelector("#template_attechment_file");
  previewNodeAttechmentFile.id = "";
  var previewTemplateAttechmentFile = previewNodeAttechmentFile.parentNode.innerHTML;
  previewNodeAttechmentFile.parentNode.removeChild(previewNodeAttechmentFile);
  
  var myDropzoneAttechmentFile = new Dropzone("div#attechment_file_zone", { // Make the whole body a dropzone
  url: "/target-url", // Set the url
  thumbnailWidth: 80,
  thumbnailHeight: 80,
  parallelUploads: 20,
  previewTemplate: previewTemplateAttechmentFile,
  autoQueue: false, // Make sure the files aren't queued until manually added
  previewsContainer: "#previews_attechment_file", // Define the container to display the previews
  clickable: ".attachment-fileinput-button", // Define the element that should be used as click trigger to select files.
  acceptedFiles: ".pdf",
  "error": function(file, message, xhr) {
    if (xhr == null) this.removeFile(file);
    alert('ファイルの種類はpdfのみ有効です');
  }
  });
  
  
  //myDropzoneAttechmentFile.on("addedfile", function(file) {
  // Hookup the start button
  //file.previewElement.querySelector(".start").onclick = function() { myDropzoneAttechmentFile.enqueueFile(file); };
  //});
  
  // Update the total progress bar
  //myDropzoneAttechmentFile.on("totaluploadprogress", function(progress) {
  //document.querySelector("#total-progress_attechment_file .progress-bar").style.width = progress + "%";
  //});
  
  myDropzoneAttechmentFile.on("sending", function(file) {
  // Show the total progress bar when upload starts
  document.querySelector("#total-progress_attechment_file").style.opacity = "1";
  // And disable the start button
  file.previewElement.querySelector(".start").setAttribute("disabled", "disabled");
  });
  
  // Hide the total progress bar when nothing's uploading anymore
  //myDropzoneAttechmentFile.on("queuecomplete", function(progress) {
  //document.querySelector("#total-progress_attechment_file").style.opacity = "0";
  //});
  
  // Setup the buttons for all transfers
  // The "add files" button doesn't need to be setup because the config
  // `clickable` has already been specified.
  document.querySelector("#actions_attechment_file .start").onclick = function() {
  myDropzoneAttechmentFile.enqueueFiles(myDropzoneAttechmentFile.getFilesWithStatus(Dropzone.ADDED));
  };
  document.querySelector("#actions_attechment_file .cancel").onclick = function() {
  myDropzoneAttechmentFile.removeAllFiles(true);
  };
  // DropzoneJS Demo Code End
  })
</script>
@append