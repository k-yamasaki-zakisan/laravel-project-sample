@php (config(['adminlte.plugins.Datatables.active'=>true]))
@extends('adminlte::page')

@section('title', 'お問い合わせ一覧')

@section('content_header')
<h1>お問い合わせ一覧</h1>
@stop

@section('content')
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-primary card-outline">
        <div class="card-body p-0">
          <div class="table-responsive mailbox-messages">
            <a class="btn btn-default btn-sm" href="{{ route('exhibitor_admin.contacts.csv_download', $slug) }}">
              <i class="fa fa-cloud-download-alt"></i>
            </a>
            <table id="contactTable" class="table table-hover table-striped">
              <thead>
                <tr>
                  <th class="text-center">差出人</th>
                  <th class="text-center">内容</th>
                  <th class="text-center">日時</th>
                  <th class="text-center">対応ステータス</th>
                </tr>
              </thead>
              <tbody>

                @foreach( $contacts as $contact )
                <tr>
                  <td class="mailbox-name"
                    onclick="obj=document.getElementById('open{{ $contact['id'] }}').style; obj.display=(obj.display=='none')?'table-row':'none';">
                    {{ $contact['user_name'] }}</td>
                  <td class="mailbox-subject"
                    onclick="obj=document.getElementById('open{{ $contact['id'] }}').style; obj.display=(obj.display=='none')?'table-row':'none';">
                    {{ mb_substr($contact['body'],0,25) }} @if(mb_strlen($contact['body']) > 25) ... @endif
                  </td>
                  <td class="text-center"
                    onclick="obj=document.getElementById('open{{ $contact['id'] }}').style; obj.display=(obj.display=='none')?'table-row':'none';">
                    {{ $contact['display_time'] }}
                  </td>
                  <td class="text-center">
                    <div class="input-group mb-3">
                      <input type="hidden" class="contact_id" name="contact_id" value="{{ $contact['id'] }}">
                      <input type="text" class="status_text" name="status_text"
                        value="{{ old('status_text') ?? $contact['status_text'] }}">
                      <button type="button" class="btn btn-info btn-flat btn-sm update-status-button"><i
                          class="fas fa-plus"></i> 追加</button>
                    </div>
                  </td>
                </tr>
                <tr id="open{{ $contact['id'] }}" style="display: none; clear: both;">
                  <td>連絡希望方法：{{ $contact['contact_request_type']['name'] }}</td>
                  <td>{!! nl2br(e($contact['body'])) !!}</td>
                  <td class="text-center">電話番号：{{ $contact['phone_number'] }}</td>
                  <td class="text-center">メール：{{ $contact['email'] }}</td>
                </tr>
                @endforeach

              </tbody>
            </table>
            <!-- /.table -->
          </div>
          <!-- /.mail-box-messages -->
        </div>
        <!-- /.card-body -->
      </div>
      <!-- /.card -->
    </div>
    <!-- /.col -->
  </div>
  <!-- /.row -->
</section>
@stop

@section('js')
@component('components.adminlte.form.advanced_modal_message')
@endcomponent
@component('components.adminlte.form.advanced_modal_message_setting')
@endcomponent
<script src="{{ asset('/vendor/adminlte/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script>
  $(function() {
  $("#contactTable").DataTable({
    "responsive": true, "lengthChange": false, "autoWidth": false, "order": [], "ordering": false
  }).buttons().container().appendTo('#contact_table_wrapper .col-md-6:eq(0)');

  $('.update-status-button').click(function() {
    let id = $(this).closest('.input-group').find('.contact_id').val();
    let status_text = $(this).closest('.input-group').find('.status_text').val();

    //if(!status_text) return;

    $.ajax({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      url: "{{ route('exhibitor_admin.contacts.update', [$slug]) }}",
      type: 'PUT',
      timeout: 8000, //8sec
      data: {'id': id, 'status_text': status_text},
    })
    .done(function(responsData) {
      toastr.success(responsData['message']);
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