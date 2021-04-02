@extends('layouts.app_trcd')
@section('title', '入出金管理')

@section('content')
<div class="col-xs-12">

	<div class="box">
		<div class="box-header with-border">
			<h3 class="box-title">残高</h3>
		</div>
		<div class="box-body">
			@if(is_null($latest_trcd_terminal_change_record))
				<p><strong>直近の履歴がありません</strong></p>
			@else
				<p><strong>直近日時：</strong>{{ $latest_trcd_terminal_change_record->register_datetime }}</p>

				<dl class="currencyList">
					<div class="currency">
						<dt>一万円&nbsp;残高</dt>
						<dd>{{ number_format($latest_trcd_terminal_change_record->amount_of_balance_10k) }}&nbsp;({{$latest_trcd_terminal_change_record->amount_of_balance_10k / 10000}}枚)</dd>
					</div>
					<div class="currency">
						<dt>五千円&nbsp;残高</dt>
						<dd>{{ number_format($latest_trcd_terminal_change_record->amount_of_balance_5k) }}&nbsp;({{$latest_trcd_terminal_change_record->amount_of_balance_5k / 5000}}枚)</dd>
					</div>
					<div class="currency">
						<dt>千円&nbsp;残高</dt>
						<dd>{{ number_format($latest_trcd_terminal_change_record->amount_of_balance_1k) }}&nbsp;({{$latest_trcd_terminal_change_record->amount_of_balance_1k / 1000}}枚)</dd>
					</div>
					<div class="currency">
						<dt>五百円&nbsp;残高</dt>
						<dd>{{ number_format($latest_trcd_terminal_change_record->amount_of_balance_500) }}&nbsp;({{$latest_trcd_terminal_change_record->amount_of_balance_500 / 500}}枚)</dd>
					</div>
					<div class="currency">
						<dt>五十円&nbsp;残高</dt>
						<dd>{{ number_format($latest_trcd_terminal_change_record->amount_of_balance_50) }}&nbsp;({{$latest_trcd_terminal_change_record->amount_of_balance_50 / 50}}枚)</dd>
					</div>
					<div class="currency">
						<dt>五円&nbsp;残高</dt>
						<dd>{{ number_format($latest_trcd_terminal_change_record->amount_of_balance_5) }}&nbsp;({{$latest_trcd_terminal_change_record->amount_of_balance_5 / 5}}枚)</dd>
					</div>
					<div class="currency">
						<dt>百円&nbsp;残高</dt>
						<dd>{{ number_format($latest_trcd_terminal_change_record->amount_of_balance_100) }}&nbsp;({{$latest_trcd_terminal_change_record->amount_of_balance_100 / 100}}枚)</dd>
					</div>
					<div class="currency">
						<dt>十円&nbsp;残高</dt>
						<dd>{{ number_format($latest_trcd_terminal_change_record->amount_of_balance_10) }}&nbsp;({{$latest_trcd_terminal_change_record->amount_of_balance_10 / 10}}枚)</dd>
					</div>
					<div class="currency">
						<dt>一円&nbsp;残高</dt>
						<dd>{{ number_format($latest_trcd_terminal_change_record->amount_of_balance_1) }}&nbsp;({{$latest_trcd_terminal_change_record->amount_of_balance_1}}枚)</dd>
					</div>
				</dl>
				<style type="text/css">
					.currencyList{
						display: flex;
						justify-content: space-between;
						align-items: center;
						flex-wrap: wrap;
					}
					.currencyList > .currency{
						width: 33%;
						text-align: center;
						margin-bottom: 5px;
						border: 1px solid #ccc;
					}
					.currencyList > .currency > dt{
						padding: 2px 0;
						background-color: #e6e6e6;
						border-bottom: 1px solid #ccc;
					}
					.currencyList > .currency > dd{
						padding: 7px 0;
						font-size: 16px;
					}
				</style>
			@endif
		</div>
	</div>


	<div class="box">
		<div class="box-header with-border">
			<h3 class="box-title">入出金履歴</h3>
		</div>
		<div class="box-body">
			{!! form_start($searchForm, [
				'class' => 'form-horizontal',
				'style' => 'margin-bottom: 1rem; padding: 1rem;'
			]) !!}
				<div class="form-group">
					<label class="control-label col-sm-1">端末</label>
					<div class="col-sm-11">
						{!! form_widget($searchForm->terminal_id, [
							'attr' => [
								'class' => 'form-control',
							],
						]) !!}
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-1">選択月</label>
					<div class="col-sm-2">
						{!! form_widget($searchForm->month, [
							'attr' => [
								'class' => 'form-control monthpicker',
							],
						]) !!}
					</div>
					<label class="control-label col-sm-1">状態</label>
					<div class="col-sm-3">
						{!! form_widget($searchForm->trcd_terminal_change_type_id, [
							'attr' => [
								'class' => 'form-control',
							],
						]) !!}
					</div>
				</div>
				<div class="text-right">
						{!! form_widget($searchForm->submit) !!}
				</div>
			{!! form_end($searchForm, false) !!}

			<div class="table-responsive">
				<table id="payment_history" class="table table-bordered table-hover">
					<thead>
						<tr>
							<th>日時</th>
							<th>氏名</th>
							<th>入金</th>
							<th>出金</th>
							<th>状態</th>
{{--
							<th>残&nbsp;1万円札</th>
							<th>残&nbsp;1千円札</th>
--}}
							<th>残高</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($payment_histories as $payment_history)
							<tr>
								<td>{{ $payment_history->register_datetime }}</td>
								<td>{{ $client_employee_list[$payment_history->client_employee_id] }}</td>
								<td>
									@isset($deposit_type_list[$payment_history->trcd_terminal_change_type_id])
										{{ number_format($payment_history->amount_of_change_total) }}
									@endisset
								</td>
								<td>
									@isset($withdrawal_type_list[$payment_history->trcd_terminal_change_type_id])
										{{ number_format($payment_history->amount_of_change_total) }}
									@endisset
								</td>
								<td>
								{{-- $trcd_terminal_change_types[$payment_history->trcd_terminal_change_type_id] --}}
								{{ $trcd_terminal_change_types['LIST'][$payment_history->trcd_terminal_change_type_id] ?? 'UNDEFINED' }}
								</td>
{{--
								<td>{{ $payment_history->amount_of_balance_10k / 10000 }}枚</td>
								<td>{{ $payment_history->amount_of_balance_1k / 1000 }}枚</td>
--}}
								<td>{{ number_format($payment_history->amount_of_balance_total) }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			<div class="text-center">
				{{ $payment_histories->appends($params)->links() }}
			</div>
		</div>
	</div>

</div>
@endsection

@push('js')
	{{Html::script('js/monthPicker.js')}}
@endpush
