@extends('layouts.app_tenko')
@section('title', '点呼記録簿（印刷画面）')



@push('css')
<style>
    .vertical {
        writing-mode: tb-rl;
        writing-mode: vertical-rl;
        -webkit-writing-mode: vertical-rl;
        /*letter-spacing: .2em;*/
        letter-spacing: .1em;
        min-height: 150px !important;
    }

    :root {
        --border-width: 1px;
        /*--border-color: #f4f4f4;*/
        /*--border-color: #d2d6de;*/
        --border-color: #333;
    }

    #table th,
    #table td {
        vertical-align: middle;
        border: 1px ridge #f4f4f4;
        border: 1px solid #f4f4f4;
    }

    .bd-l {
        border-left-color: var(--border-color) !important;
        border-left-width: var(--border-width) !important;
        border-left-style: solid !important;
    }

    .bd-t {
        border-top-color: var(--border-color) !important;
        border-top-width: var(--border-width) !important;
        border-top-style: solid !important;
    }

    .bd-r {
        border-right-color: var(--border-color) !important;
        border-right-width: var(--border-width) !important;
        border-right-style: solid !important;
    }

    .bd-b {
        border-bottom-color: var(--border-color) !important;
        border-bottom-width: var(--border-width) !important;
        border-bottom-style: solid !important;
    }

    .print-header {
        display: none;
    }

    .printable {
        display: none;
    }



    #tbody-roll-call tr td {
        padding: 0;
    }

    #tbody-roll-call .inner-wrap-td {
        padding: 8px;
    }

    #tbody-roll-call .inner-wrap-td.first-column,
    #tbody-roll-call .inner-wrap-td.op-column {
        padding: 0;
    }

    .wrap-driver-name,
    .wrap-vehicle-number,
    .wrap-btn-edit,
    .wrap-btn-del {
        padding: 8px;
    }

    .wrap-vehicle-number,
    .wrap-btn-del {
        border-top: 1px solid rgb(244, 244, 244);
    }
</style>
<link rel="stylesheet" href="{{asset('css/tenko/print.css')}}">
@endpush






@section('content')
<div class="col-xs-12">
    <div class="box box-default hidden-print">
        <div class="box-body">


            <!--search form-->
            @component('components.forms.search_form', ['action' => route('tenko.print')])
            <div class="form-row">
                <!--点呼記録日-->
                <div class="col-xs-6">
                    @component('components.forms.date_picker', [
                    'label' => '点呼記録日',
                    'name' => 'date',
                    'value' => $search_params['date'] ?? null,
                    ])
                    @endcomponent
                </div>
            </div>
            <div class="form-row">
                <div class="col-xs-12 text-center">
                    @component('components.forms.submit_button', ['label' => '検索'])
                    @endcomponent
                    <button id="btn-print" class="btn btn-success" style="margin-left: 2em;">印刷</button>
                </div>
            </div>
            @endcomponent
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-body">
            <!--新規作成-->
            <div class="text-right hidden-print" style="margin: 1em;">
                <a href="{{ route('tenko.combined_roll_calls.create') }}" class="btn btn-primary">新規作成</a>
            </div>

            <!--wrap-table-->
            <table>
                <thead>
                    <tr class="printable">
                        <td>
                            <div class="print-header-space"></div>
                        </td>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>
                            <!--actual-table-->
                            <div class="table-responsive">
                                <table id="table" class="table table-bordered print-friendly">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" nowrap class="text-center bd-l bd-t bd-r">運転者名</th>
                                            <th colspan="11" class="text-center bd-t bd-r bd-b">乗務前点呼</th>
                                            {{--<th colspan="10" class="text-center bd-t bd-r bd-b">乗務途中点呼（中間点呼）点呼</th>--}}
                                            <th colspan="8" class="text-center bd-t bd-r bd-b">乗務後点呼</th>
                                            <th rowspan="4" nowrap class="text-center bd-t bd-r bd-b hidden-print">操作
                                            </th>
                                        </tr>
                                        <tr>
                                            <!--乗務前点呼-->
                                            <th rowspan="3" class="text-center bd-b"><span class="vertical">点呼日時</span>
                                            </th>
                                            <th rowspan="3" class="text-center bd-b"><span class="vertical">点呼方法</span>
                                            </th>
                                            <th rowspan="3" class="text-center bd-b"><span
                                                    class="vertical">アルコール検知器の<br />使用の有無</span></th>
                                            <th rowspan="3" class="text-center bd-b"><span
                                                    class="vertical">酒気帯び<br />の有無</span></th>
                                            <th rowspan="3" class="text-center bd-b"><span class="vertical">疾病の状況</span>
                                            </th>
                                            <th rowspan="3" class="text-center bd-b"><span class="vertical">疲労の状況</span>
                                            </th>
                                            <th rowspan="3" class="text-center bd-b"><span
                                                    class="vertical">睡眠不足等<br />の状況</span></th>
                                            <th rowspan="3" class="text-center bd-b"><span class="vertical">日常点検</span>
                                            </th>
                                            <th rowspan="3" class="text-center bd-b"><span class="vertical">指示事項</span>
                                            </th>
                                            <th rowspan="3" class="text-center bd-b"><span class="vertical">備考</span>
                                            </th>
                                            <th rowspan="3" class="text-center bd-b bd-r"><span
                                                    class="vertical">点呼執行者</span></th>
                                            {{--
<!--中間点呼-->
	<th rowspan="3" class="text-center bd-b"><span class="vertical">点呼日時</span></th>
	<th rowspan="3" class="text-center bd-b"><span class="vertical">点呼方法</span></th>
	<th rowspan="3" class="text-center bd-b"><span class="vertical">アルコール検知器の<br>使用の有無</span></th>
	<th rowspan="3" class="text-center bd-b"><span class="vertical">酒気帯び<br />の有無</span></th>
	<th rowspan="3" class="text-center bd-b"><span class="vertical">疾病の状況</span></th>
	<th rowspan="3" class="text-center bd-b"><span class="vertical">疲労の状況</span></th>
	<th rowspan="3" class="text-center bd-b"><span class="vertical">睡眠不足等<br />の状況</span></th>
	<th rowspan="3" class="text-center bd-b"><span class="vertical">指示事項</span></th>
	<th rowspan="3" class="text-center bd-b"><span class="vertical">備考</span></th>
	<th rowspan="3" class="text-center bd-b"><span class="vertical">点呼執行者</span></th>
--}}
                                            <!--乗務後点呼-->
                                            <th rowspan="3" class="text-center bd-b"><span class="vertical">点呼日時</span>
                                            </th>
                                            <th rowspan="3" class="text-center bd-b"><span class="vertical">点呼方法</span>
                                            </th>
                                            <th rowspan="3" class="text-center bd-b"><span
                                                    class="vertical">アルコール検知器の<br>使用の有無</span></th>
                                            <th rowspan="3" class="text-center bd-b"><span
                                                    class="vertical">酒気帯び<br />の有無</span></th>
                                            <th rowspan="3" class="text-center bd-b"><span
                                                    class="vertical">自動車・道路及<br />び運行の状況</span></th>
                                            <th rowspan="3" class="text-center bd-b"><span
                                                    class="vertical">交替運転者に<br />対する連絡</span></th>
                                            <th rowspan="3" class="text-center bd-b"><span class="vertical">備考</span>
                                            </th>
                                            <th rowspan="3" class="text-center bd-b"><span class="vertical">点呼執行者</span>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th rowspan="2" nowrap class="text-center bd-l bd-b">車両番号</th>
                                        </tr>
                                        <tr>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-roll-call">
                                        @foreach( $roll_call_data['roll_call_headers'] as $roll_call_header_id =>
                                        $header )
                                        <tr class="text-center">
                                            <!--運転者名-->
                                            <td class="bd-b bd-l bd-r">
                                                <div class="inner-wrap-td first-column">
                                                    <div class="wrap-driver-name">
                                                        @foreach($header['driver_names'] as $driver_name) <div>
                                                            {{ $driver_name }}</div> @endforeach
                                                    </div>
                                                    <div class="wrap-vehicle-number">
                                                        @foreach($header['vehicle_numbers'] as $vehicle_number) <div>
                                                            {{ $vehicle_number }}</div> @endforeach
                                                    </div>
                                                </div>
                                            </td>
                                            <!--乗務前点呼-->
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    {{ $header['PRE']['roll_called_at'] ?? null }}
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    {{ $header['PRE']['roll_call_method'] ?? null }}
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    @isset($header['PRE']['use_alcohol_checker'])
                                                    {{ $header['PRE']['use_alcohol_checker'] ? '有' : '無' }}
                                                    @endisset
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    @isset($header['PRE']['is_drinking'])
                                                    {{ $header['PRE']['is_drinking'] ? '有' : '無' }}
                                                    @endisset
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    @isset($header['PRE']['is_sick'])
                                                    {!! $header['PRE']['is_sick'] ? $ng : $ok !!}
                                                    @endisset
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    @isset($header['PRE']['is_tired'])
                                                    {!! $header['PRE']['is_tired'] ? $ng : $ok !!}
                                                    @endisset
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    @isset($header['PRE']['has_other_problems'])
                                                    {!! $header['PRE']['has_other_problems'] ? $ng : $ok !!}
                                                    @endisset
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    @isset($header['PRE']['did_daily_inspection'])
                                                    {!! $header['PRE']['did_daily_inspection'] ? $ok : $ng !!}
                                                    @endisset
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    {{ $header['PRE']['instruction'] ?? null }}
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    {{ $header['PRE']['note'] ?? null }}
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b bd-r">
                                                <div class="inner-wrap-td">
                                                    {{ $header['PRE']['executor_name'] ?? null }}
                                                </div>
                                            </td>
                                            <!--乗務後点呼-->
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    {{ $header['POST']['roll_called_at'] ?? null }}
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    {{ $header['POST']['roll_call_method'] ?? null }}
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    @isset($header['POST']['use_alcohol_checker'])
                                                    {{ $header['POST']['use_alcohol_checker'] ? '有' : '無' }}
                                                    @endisset
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    @isset($header['POST']['is_drinking'])
                                                    {{ $header['POST']['is_drinking'] ? '有' : '無' }}
                                                    @endisset
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    @isset($header['POST']['has_transportation_problems'])
                                                    {!! $header['POST']['has_transportation_problems'] ? $ng : $ok !!}
                                                    @endisset
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    @isset($header['POST']['has_notified'])
                                                    {!! $header['POST']['has_notified'] ? $ok : $ng !!}
                                                    @endisset
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b">
                                                <div class="inner-wrap-td">
                                                    {{ $header['POST']['note'] ?? null }}
                                                </div>
                                            </td>
                                            <td rowspan="1" class="bd-b bd-r">
                                                <div class="inner-wrap-td">
                                                    {{ $header['POST']['executor_name'] ?? null }}
                                                </div>
                                            </td>
                                            <!--操作-->
                                            <td class="bd-b bd-r hidden-print">
                                                <div class="wrap-btn-edit">
                                                    <a href="{{ route('tenko.combined_roll_calls.edit', ['roll_call_header_id' => $roll_call_header_id]) }}"
                                                        class="btn btn-primary">編集</a>
                                                </div>
                                                <div class="wrap-btn-del">
                                                    @component('components.tenko.del-modal-btn', [
                                                    'label' => '削除',
                                                    'btn_class' => 'btn-danger',
                                                    'modal_id' => "del-modal-{$roll_call_header_id}",
                                                    'form_action' => route('tenko.combined_roll_calls.delete',
                                                    ['roll_call_header_id' => $roll_call_header_id]),
                                                    ])
                                                    <div class="text-left" style="margin-left: 1em;">
                                                        <p>点呼記録日：<strong>{{ $search_params['date'] }}</strong><br />運転者名：<strong>{{ join(',', $header['driver_names']) }}</strong>
                                                        </p>
                                                        <p>本当に削除してよろしいですか？</p>
                                                    </div>
                                                    @endcomponent
                                                </div>
                                            </td>
                                        </tr>
                                        {{--
<tr class="text-center tr-second">
<!--車両番号-->
	<td class="bd-l bd-b bd-r vehicle-number">
		<div class="inner-wrap-td">
			@foreach($header['vehicle_numbers'] as $vehicle_number) <div>{{ $vehicle_number }}
                            </div> @endforeach
        </div>
        </td>
        <td class="bd-b bd-r hidden-print">
            @component('components.tenko.del-modal-btn', [
            'label' => '削除',
            'btn_class' => 'btn-danger',
            'modal_id' => "del-modal-{$roll_call_header_id}",
            'form_action' => route('tenko.combined_roll_calls.delete', ['roll_call_header_id' => $roll_call_header_id]),
            ])
            <div class="text-left" style="margin-left: 1em;">
                <p>点呼記録日：<strong>{{ $search_params['date'] }}</strong><br />運転者名：<strong>{{ join(',', $header['driver_names']) }}</strong>
                </p>
                <p>本当に削除してよろしいですか？</p>
            </div>
            @endcomponent
        </td>
        </tr>
        --}}
        @endforeach
        </tbody>
        </table>
    </div>
    <!--actual-table end-->
    </td>
    </tr>
    </tbody>
    </table>
</div>
</div>
</div>
</div>
@endsection

@push('js')
<!--datepicker-->
<link rel="stylesheet"
    href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" />
<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.ja.min.js">
</script>
<script>
    $(function() {
	$('.trcd-datepicker').datepicker({
		language: 'ja',
		autoclose: true,
		format: 'yyyy-mm-dd',
		orientation: 'bottom',
	});
	$('#btn-print').on('click', function(event) {
		event.preventDefault();
		window.print();
	});
});
</script>
<link rel="stylesheet" href="{{asset('css/tenko/print.css')}}">
@endpush


<!--print-header-->
<div class="box box-default print-header" style="display: none;">
    <div class="box-body">
        <div class="row">
            <div class="col-xs-4">
            </div>
            <div class="col-xs-4 text-center">
                <p class="print-header-title">点呼記録簿</p>
            </div>
            <div class="col-xs-4 text-right">
                <p class="print-header-date-entry">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;年
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;月
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;日（&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;）
                    天候&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-7">
                <div class="input-group input-group-lg margin-bottom-0">
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-default">会社名</button>
                    </div>
                    <input type="text" class="form-control" kl_vkbd_parsed="true">
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-default">支店</button>
                    </div>
                    <input type="text" class="form-control" kl_vkbd_parsed="true">
                </div>
            </div>
            {{-- <div class="col-xs-1">
			</div> --}}
            <div class="col-xs-5">
                <table class="table table-bordered">
                    <tr>
                        <th class="print-header-cell"></th>
                        <th class="text-center print-header-cell">総括運行管理者</th>
                        <th class="text-center print-header-cell">運行管理者</th>
                        <th class="text-center print-header-cell">補助者</th>
                    </tr>
                    <tr>
                        <th class="print-content-cell"></th>
                        <th class="print-content-cell"></th>
                        <th class="print-content-cell"></th>
                        <th class="print-content-cell"></th>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<!--print-header end-->