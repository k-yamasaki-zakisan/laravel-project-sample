@extends('adminlte::page')

@section('title', 'お問い合わせ一覧')

@section('content_header')
<h1>お問い合わせ一覧</h1>
@stop

@section('content')
<!-- Main content -->
<section class="content">
    <div class="row">
        <!-- 2021/01/25 サイドバー削除(メインコンテントのcolを6→12に変更) -->
        {{--<div class="col-md-3">
          <a href="compose.html" class="btn btn-primary btn-block mb-3">Compose</a>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Folders</h3>

              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body p-0">
              <ul class="nav nav-pills flex-column">
                <li class="nav-item active">
                  <a href="#" class="nav-link">
                    <i class="fas fa-inbox"></i> Inbox
                    <span class="badge bg-primary float-right">12</span>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="far fa-envelope"></i> Sent
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="far fa-file-alt"></i> Drafts
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="fas fa-filter"></i> Junk
                    <span class="badge bg-warning float-right">65</span>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="far fa-trash-alt"></i> Trash
                  </a>
                </li>
              </ul>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Labels</h3>

              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="card-body p-0">
              <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="far fa-circle text-danger"></i>
                    Important
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="far fa-circle text-warning"></i> Promotions
                  </a>
                </li>
                <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="far fa-circle text-primary"></i>
                    Social
                  </a>
                </li>
              </ul>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->
        </div>--}}
        <!-- /.col -->
        {{--<div class="col-md-9">--}}
        <div class="col-md-12">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">お問い合わせの管理</h3>

                    <div class="card-tools">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" placeholder="Search Mail">
                            <div class="input-group-append">
                                <div class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body p-0">
                    <div class="mailbox-controls">
                        <!-- Check all button -->
                        {{--<button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="far fa-square"></i>
                </button>
                <div class="btn-group">
                  <button type="button" class="btn btn-default btn-sm">
                    <i class="far fa-trash-alt"></i>
                  </button>
                  <button type="button" class="btn btn-default btn-sm">
                    <i class="fas fa-reply"></i>
                  </button>
                  <button type="button" class="btn btn-default btn-sm">
                    <i class="fas fa-share"></i>
                  </button>
                </div>
                <!-- /.btn-group -->
                <button type="button" class="btn btn-default btn-sm">
                  <i class="fas fa-sync-alt"></i>
                </button>--}}
                        <button type="button" class="btn btn-default btn-sm">
                            <i class="fa fa-cloud-download-alt"></i>
                        </button>
                        <div class="float-right">
                            1-50/200
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-sm">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-default btn-sm">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                            <!-- /.btn-group -->
                        </div>
                        <!-- /.float-right -->
                    </div>
                    <div class="table-responsive mailbox-messages">
                        <table class="table table-hover table-striped">
                            <tbody>
                                <tr
                                    onclick="obj=document.getElementById('open1').style; obj.display=(obj.display=='none')?'table-row':'none';">
                                    <td>
                                        <div class="icheck-primary">
                                            <input type="checkbox" value="" id="check2">
                                            <label for="check2"></label>
                                        </div>
                                    </td>
                                    <td class="mailbox-star"><a href="#"><i class="fas fa-star-o text-warning"></i></a>
                                    </td>
                                    <td class="mailbox-name">山田太郎</td>
                                    <td class="mailbox-subject"><b>イベント参加方法について</b> いつもお世話になっており...
                                    </td>
                                    <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                    <td class="mailbox-date">28 mins ago</td>
                                </tr>
                                <tr id="open1" style="display: none; clear: both;">
                                    <td colspan=6>
                                        <b>件名：イベント参加方法について</b><br>
                                        いつもお世話になっております<br>
                                        ODEX2021に申し込み方法がわからずに困っております<br>
                                        ご対応よろしくお願いします
                                    </td>
                                </tr>
                                <tr
                                    onclick="obj=document.getElementById('open2').style; obj.display=(obj.display=='none')?'table-row':'none';">
                                    <td>
                                        <div class="icheck-primary">
                                            <input type="checkbox" value="" id="check2">
                                            <label for="check2"></label>
                                        </div>
                                    </td>
                                    <td class="mailbox-star"><a href="#"><i class="fas fa-star-o text-warning"></i></a>
                                    </td>
                                    <td class="mailbox-name">市川太郎</td>
                                    <td class="mailbox-subject"><b>イベント参加方法について</b> いつもお世話になっており...
                                    </td>
                                    <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                    <td class="mailbox-date">11 hours ago</td>
                                </tr>
                                <tr id="open2" style="display: none; clear: both;">
                                    <td colspan=6>
                                        <b>件名：イベント参加方法について</b><br>
                                        いつもお世話になっております<br>
                                        ODEX2021に申し込み方法がわからずに困っております<br>
                                        ご対応よろしくお願いします
                                    </td>
                                </tr>
                                <tr
                                    onclick="obj=document.getElementById('open3').style; obj.display=(obj.display=='none')?'table-row':'none';">
                                    <td>
                                        <div class="icheck-primary">
                                            <input type="checkbox" value="" id="check2">
                                            <label for="check2"></label>
                                        </div>
                                    </td>
                                    <td class="mailbox-star"><a href="#"><i class="fas fa-star-o text-warning"></i></a>
                                    </td>
                                    <td class="mailbox-name">市川太郎</td>
                                    <td class="mailbox-subject"><b>イベント金額について</b> いつもお世話になっており...
                                    </td>
                                    <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                    <td class="mailbox-date">15 hours ago</td>
                                </tr>
                                <tr id="open3" style="display: none; clear: both;">
                                    <td colspan=6>
                                        <b>件名：イベント参加方法について</b><br>
                                        いつもお世話になっております<br>
                                        ODEX2021に申し込み方法がわからずに困っております<br>
                                        ご対応よろしくお願いします
                                    </td>
                                </tr>
                                <tr
                                    onclick="obj=document.getElementById('open3').style; obj.display=(obj.display=='none')?'table-row':'none';">
                                    <td>
                                        <div class="icheck-primary">
                                            <input type="checkbox" value="" id="check2">
                                            <label for="check2"></label>
                                        </div>
                                    </td>
                                    <td class="mailbox-star"><a href="#"><i class="fas fa-star-o text-warning"></i></a>
                                    </td>
                                    <td class="mailbox-name">土井太郎</td>
                                    <td class="mailbox-subject"><b>イベント日時について</b> いつもお世話になっており...
                                    </td>
                                    <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                    <td class="mailbox-date">Yesterday</td>
                                </tr>
                                <tr id="open3" style="display: none; clear: both;">
                                    <td colspan=6>
                                        <b>件名：イベント参加方法について</b><br>
                                        いつもお世話になっております<br>
                                        ODEX2021に申し込み方法がわからずに困っております<br>
                                        ご対応よろしくお願いします
                                    </td>
                                </tr>
                                <tr
                                    onclick="obj=document.getElementById('open4').style; obj.display=(obj.display=='none')?'table-row':'none';">
                                    <td>
                                        <div class="icheck-primary">
                                            <input type="checkbox" value="" id="check2">
                                            <label for="check2"></label>
                                        </div>
                                    </td>
                                    <td class="mailbox-star"><a href="#"><i class="fas fa-star-o text-warning"></i></a>
                                    </td>
                                    <td class="mailbox-name">真下太郎</td>
                                    <td class="mailbox-subject"><b>イベント場所について</b> いつもお世話になっており...
                                    </td>
                                    <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                    <td class="mailbox-date">2 days ago</td>
                                </tr>
                                <tr id="open4" style="display: none; clear: both;">
                                    <td colspan=6>
                                        <b>件名：イベント参加方法について</b><br>
                                        いつもお世話になっております<br>
                                        ODEX2021に申し込み方法がわからずに困っております<br>
                                        ご対応よろしくお願いします
                                    </td>
                                </tr>
                                <tr
                                    onclick="obj=document.getElementById('open5').style; obj.display=(obj.display=='none')?'table-row':'none';">
                                    <td>
                                        <div class="icheck-primary">
                                            <input type="checkbox" value="" id="check2">
                                            <label for="check2"></label>
                                        </div>
                                    </td>
                                    <td class="mailbox-star"><a href="#"><i class="fas fa-star-o text-warning"></i></a>
                                    </td>
                                    <td class="mailbox-name">今野太郎</td>
                                    <td class="mailbox-subject"><b>イベント料金について</b> いつもお世話になっており...
                                    </td>
                                    <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                    <td class="mailbox-date">2 days ago</td>
                                </tr>
                                <tr id="open5" style="display: none; clear: both;">
                                    <td colspan=6>
                                        <b>件名：イベント参加方法について</b><br>
                                        いつもお世話になっております<br>
                                        ODEX2021に申し込み方法がわからずに困っております<br>
                                        ご対応よろしくお願いします
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- /.table -->
                    </div>
                    <!-- /.mail-box-messages -->
                </div>
                <!-- /.card-body -->
                <div class="card-footer p-0">
                    <div class="mailbox-controls">
                        <!-- Check all button -->
                        {{--<button type="button" class="btn btn-default btn-sm checkbox-toggle">
                  <i class="far fa-square"></i>
                </button>
                <div class="btn-group">
                  <button type="button" class="btn btn-default btn-sm">
                    <i class="far fa-trash-alt"></i>
                  </button>
                  <button type="button" class="btn btn-default btn-sm">
                    <i class="fas fa-reply"></i>
                  </button>
                  <button type="button" class="btn btn-default btn-sm">
                    <i class="fas fa-share"></i>
                  </button>
                </div>
                <!-- /.btn-group -->
                <button type="button" class="btn btn-default btn-sm">
                  <i class="fas fa-sync-alt"></i>
                </button>--}}
                        <div class="float-right">
                            1-50/200
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-sm">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-default btn-sm">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                            <!-- /.btn-group -->
                        </div>
                        <!-- /.float-right -->
                    </div>
                </div>
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</section>
@stop

@section('js')
@append