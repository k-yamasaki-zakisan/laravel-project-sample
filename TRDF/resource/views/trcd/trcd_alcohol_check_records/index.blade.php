@extends('components.pages.index')
@section('title', 'アルコールチェック履歴一覧')

@section('box-search-content')
	@component('components.forms.search_form', [
		'action' => route('trcd.trcd_alcohol_check_records.index'),
	])

		<div class="row">
			<div class="col-xs-6">
<!--開始日付-->
				@component('components.forms.date_picker', [
					'name' => 'from',
					'value' => old('from', $search_conditions['from'] ?? null),
				])
					@slot('label') 開始日 @endslot
				@endcomponent
			</div>
			<div class="col-xs-6">
<!--終了日付-->
				@component('components.forms.date_picker', [
					'name' => 'until',
					'value' => old('until', $search_conditions['until'] ?? null),
				])
					@slot('label') 終了日 @endslot
				@endcomponent
			</div>
		</div>

		<div class="row">
			<div class="col-xs-6">
<!--勤怠所属グループ-->
				@component('components.forms.selector', [
					'list' => $client_group_list ?? [],
					'selected' => old('client_group_id', $search_conditions['client_group_id'] ?? null),
					'name' => 'client_group_id',
					'id' => 'js-clientGroupId',
				])
					@slot('label') 勤怠所属グループ @endslot
				@endcomponent
			</div>
			<div class="col-xs-6">
<!--社員-->
				@component('components.forms.selector', [
					'list' => $client_employees->pluck('name', 'id') ?? [],
					'selected' => old('client_employee_id', $search_conditions['client_employee_id'] ?? null),
					'name' => 'client_employee_id',
					'class' => 'js-select2',
					'id' => 'js-clientEmployeeId',
				])
					@slot('label') 社員 @endslot
				@endcomponent
			</div>
		</div>

<!--検索ボタン-->
		<div class="form-group text-center">
			@component('components.forms.submit_button')
				@slot('label') 検索 @endslot
			@endcomponent
		</div>
	@endcomponent
@endsection

@section('box-table-header')
	<div class="col-sm-12 text-right">
		@component('components.forms.search_form', [
			'action' => route('trcd.trcd_alcohol_check_records.download'),
		])

			@foreach( $search_conditions as $key => $value )
				<input type="hidden" name="{{ $key }}" value="{{ $value }}" />
			@endforeach

			@component('trcd.aggregates.common.components.form.submit_button')
				@slot('label') CSVダウンロード @endslot
			@endcomponent
		@endcomponent
	</div>
@endsection

@section('box-table-content')
<thead>
	<tr>
		<th>測定日時</th>
@if( $trcd_terminal_list->count() > 1 )
		<th>端末名</th>
@endif
		<th>測定者</th>
		<th class="text-center">測定値</th>
		<th class="text-center">測定結果</th>
		<th class="text-center">出勤時/退勤時</th>
		<th class="text-center">画像確認</th>
	</tr>
</thead>
<tbody>
	@foreach( $trcd_alcohol_check_records as $key => $trcd_alcohol_check_record )
		<tr>
			<td>{{ $trcd_alcohol_check_record['checked_datetime'] ?? null }}</td>
@if( $trcd_terminal_list->count() > 1 )
			<td>{{ $trcd_terminal_list[$trcd_alcohol_check_record['trcd_terminal_id']] ?? null }}</td>
@endif
			<td>{{ $client_employees[$trcd_alcohol_check_record['client_employee_id']]['name'] ?? null }}</td>
			<td class="text-center">{{ $trcd_alcohol_check_record['measured_value'] ?? null }}</td>
			<td class="text-center">
				@if( empty($trcd_alcohol_check_record['result_flag']) )
					<strong class="text-danger">NG</strong>
				@else
					<strong class="text-success">OK</strong>
				@endif
			</td>
			<td class="text-center">
				@if( $attendance_types['STAMP_WORK_BEGIN'] == ( $trcd_alcohol_check_record['attendance_raw']['attendance_type_id'] ?? null ) )
					出勤
				@elseif( $attendance_types['STAMP_WORK_FINISH'] == ( $trcd_alcohol_check_record['attendance_raw']['attendance_type_id'] ?? null ) )
					退勤
				@endif
			</td>
			<td class="text-center">
				@component('components.btn-display_basic')
					@slot('label') 表示 @endslot
					@slot('button_classs') btn-info @endslot
					@slot('modal_id') 1 @endslot
				@endcomponent
			</td>
		</tr>
	@endforeach
</tbody>
@endsection

@section('paginator')
	{{ $trcd_alcohol_check_records->appends($search_conditions)->links() }}
@endsection

@push('js')
	<script src="{{ asset('js/monthPicker.js?' . now()->format('Ymd')) }}"></script>
	<script src="{{ asset('vendor/select2/dist/js/select2.min.js?' . now()->format('Ymd')) }}"></script>
	<style type="text/css">
		.select2-results__option {
			min-height: 32px;
		}
	</style>
	<script type="text/javascript">
		$(function() {
			var dataObj = {
				clientEmployees: @json($client_employees),
				$clientGroupIdSelector: $('#js-clientGroupId'),
				$clientEmployeeIdSelector: $('#js-clientEmployeeId'),
				selectedClientGroupId: null,
			};
			// 勤怠所属グループID入力値格納
			dataObj.selectedClientGroupId = dataObj.$clientGroupIdSelector.val();

			// 社員セレクタのoptionにdata属性付与
			Object.keys(dataObj.clientEmployees).map( clientEmployeeId => {
				var clientGroupId = dataObj.clientEmployees[clientEmployeeId]['client_group_id'];
				dataObj.$clientEmployeeIdSelector.find('option[value=' + clientEmployeeId + ']').attr('data-client-group-id', clientGroupId);
			});

			// 勤怠所属グループ選択時
			dataObj.$clientGroupIdSelector.change(function(event) {
				// 選択値格納
				dataObj.selectedClientGroupId = $(this).val();
				// 勤怠所属グループが変更されたら社員もクリア
				dataObj.$clientEmployeeIdSelector.val(null).trigger('change');
			});

			// select2初期化
			$('.js-select2').select2({
				width: '100%',
				matcher: matchCustom,
				language: {"noResults": function(){ return "対象社員が見つかりません。";}},
				escapeMarkup: function (markup) { return markup; }
			});

			// 結果一致ロジック
			function matchCustom(params, data) {
				// 空項目は常に表示
				if ( data.text === '' ) {
					return data;
				}

				// 勤怠所属グループ選択値とoptionのdata属性がマッチしていない場合は表示させない
				if ( dataObj.selectedClientGroupId ) {
					if ( dataObj.selectedClientGroupId != $(data.element).data('client-group-id') ) return null;
				}

				// If there are no search terms, return all of the data
				if ($.trim(params.term) === '') {
				  return data;
				}

				// Do not display the item if there is no 'text' property
				if (typeof data.text === 'undefined') {
				  return null;
				}

				// `params.term` should be the term that is used for searching
				// `data.text` is the text that is displayed for the data object
				if (data.text.indexOf(params.term) > -1) {
				  var modifiedData = $.extend({}, data, true);
				  //modifiedData.text += ' (matched)';

				  // You can return modified objects from here
				  // This includes matching the `children` how you want in nested data sets
				  return modifiedData;
				}

				// Return `null` if the term should not be displayed
				return null;
			};
		});
	</script>
@endpush