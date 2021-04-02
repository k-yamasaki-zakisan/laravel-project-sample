@extends('layouts.app_trcd')
@section('title', '年次有給休暇一覧')

@section('content')

<div class="col-xs-12">

	{{-- 社員情報 --}}
	<div class="box">
		<div class="box-body">
			<div class="table-responsive">
				<table id="client_employee" class="table table-bordered table-hover">
					<thead>
						<th>社員番号</th>
						<th>氏名</th>
					</thead>
					<tbody>
						<tr>
							<td>{{ $client_employee->code }}</td>
							<td>{{ $client_employee->name }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	{{-- 年次有給休暇 --}}
	<div class="box box-primary">
		<div class="box-body">
			<div class="table-responsive">
				<table id="annual_paid_holiday" class="table table-bordered table-hover">
					<thead>
						<th>基準日</th>
						<th>勤務日数</th>
						<th>付与日数</th>
						<th>取得日数</th>
						<th>有効期限</th>
						<th>追加付与日数</th>
						<th>取得可能日数</th>
						<th>次の基準日</th>
						<th>日数調整</th>
					</thead>
					<tbody>
						@foreach ($annual_paid_holidays as $annual_paid_holiday)
						<tr>
							<td>{{ $annual_paid_holiday->base_date }}</td>
							<td>{{ $annual_paid_holiday->days_worked }}</td>
							<td>{{ $annual_paid_holiday->days_granted }}</td>
							<td>{{ $annual_paid_holiday->days_used }}</td>
							<td>{{ $annual_paid_holiday->expiration_date }}</td>
							<td>{{ $annual_paid_holiday->days_added }}</td>
							<td>{{ $annual_paid_holiday->usable_days }}</td>
							<td>{{ $annual_paid_holiday->next_base_date }}</td>
							<td>{{--調整ボタン--}}
								{{ Html::link(route('trcd.annual_paid_holidays.edit', $annual_paid_holiday->id), '調整', ['class' => 'btn btn-primary btn-sm']) }}
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>

</div>

@endsection