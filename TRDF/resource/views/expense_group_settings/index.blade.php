@extends('layouts.app_trcd')
@section('title', '経費通知')

@section('content')
<div class="col-xs-12">
	<div class="box">
		<div class="box-body">
			@if ($errors->has('data'))
				<div class="alert alert-danger alert-dismissible">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
					<h4><i class="icon fa fa-ban"></i> Alert!</h4>
					@foreach($errors->get('data') as $message)
						<ol>
							<li>{{ $message }}</li>
						</ol>
					@endforeach
				</div>
			@endif
			<form method="POST" action="{{ route('trcd.expense_group_settings.update') }}">
				@csrf
				<div class="table-responsive">
					<table id="expense_group_settings" class="table table-bordered table-hover">
						<thead>
							<tr>
								<th class="col-sm-5 col-md-4 col-lg-4">経費所属グループ名</th>
								<th class="col-sm-3 col-md-4 col-lg-4">送信先アドレス</th>
								<th class="col-sm-3 col-md-2 col-lg-2 text-center">リアルタイム</th>
								<th class="col-sm-1 col-md-2 col-lg-2">指定時刻</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($expense_group_settings as $expense_group_setting)
								<tr>
									<!-- 経費所属グループ名 -->
									<td>{{ $expense_group_setting['expense_group']['name'] ?? null }}</td>
									<!-- 送信先アドレス -->
									<td>
										<ol id="email_list_{{ $expense_group_setting['expense_group_id'] }}" class="list-email">
											@if (old('Settings.eg_' . $expense_group_setting['expense_group_id'] . '.emails', null) != null)
												@foreach(old('Settings.eg_' . $expense_group_setting['expense_group_id'] . '.emails', []) as $idx => $value)
													<li class="js-email-listItem">
														@if ($errors->has('Settings.eg_' . $expense_group_setting['expense_group_id'] . '.emails.' . $idx))
															@foreach($errors->get('Settings.eg_' . $expense_group_setting['expense_group_id'] . '.emails.' . $idx) as $message)
																<ol class="list-error">
																	<li class="text-danger">{{ $message }}</li>
																</ol>
															@endforeach
														@endif
														<div class="form-group flex-center-sb">
															<input type="email" class="col-md-11"
																name="Settings[eg_{{ $expense_group_setting['expense_group_id'] }}][emails][]"
																value="{{ $value }}"
															>
															<span class="col-md-1 js-remove-email">&#10005;</span>
														</div>
													</li>
												@endforeach
											@else
{{--
											@if ($errors->has('Settings.eg_' . $expense_group_setting['expense_group_id'] . '.emails.*'))
												@foreach(old('Settings.eg_' . $expense_group_setting['expense_group_id'] . '.emails', []) as $idx => $value)
													<li class="js-email-listItem">
														@if ($errors->has('Settings.eg_' . $expense_group_setting['expense_group_id'] . '.emails.' . $idx))
															@foreach($errors->get('Settings.eg_' . $expense_group_setting['expense_group_id'] . '.emails.' . $idx) as $message)
																<ol class="list-error">
																	<li class="text-danger">{{ $message }}</li>
																</ol>
															@endforeach
														@endif
														<div class="form-group flex-center-sb">
															<input type="email" class="col-md-11"
																name="Settings[eg_{{ $expense_group_setting['expense_group_id'] }}][emails][]"
																value="{{ $value }}"
															>
															<span class="col-md-1 js-remove-email">&#10005;</span>
														</div>
													</li>
												@endforeach
											@else
--}}
												@foreach($expense_group_setting['expense_notification_destinations'] as $expense_notification_destination)
													<li class="js-email-listItem">
														<div class="form-group flex-center-sb">
															<input type="email" class="col-md-11"
																name="Settings[eg_{{ $expense_group_setting['expense_group_id'] }}][emails][]"
																value="{{ $expense_notification_destination['email'] }}"
															>
															<span class="col-md-1 js-remove-email">&#10005;</span>
														</div>
													</li>
												@endforeach
											@endif
										</ol>
										<p>
											<span class="js-add-email"
												data-dist="email_list_{{ $expense_group_setting['expense_group_id'] }}"
												data-name="Settings[eg_{{ $expense_group_setting['expense_group_id'] }}][emails][]"
											>
												<i class="icon bg-light-blue fa fa-plus"></i>
												<small>送信先を追加</small>
											</span>
										</p>
									</td>
									<!-- リアルタイム -->
									<td>
										<div class="text-center">
											@if ($errors->has('Settings.eg_' . $expense_group_setting['expense_group_id'] . '.realtime_flag'))
												<ol class="list-error">
													@foreach($errors->get('Settings.eg_' . $expense_group_setting['expense_group_id'] . '.realtime_flag') as $message)
														<li class="text-danger">{{ $message }}</li>
													@endforeach
												</ol>
											@endif
											<input type="checkbox"
												value="1"
												name="Settings[eg_{{ $expense_group_setting['expense_group_id'] }}][realtime_flag]"
												@if ( !empty(old('Settings.eg_' . $expense_group_setting['expense_group_id'] . '.realtime_flag', $expense_group_setting['realtime_flag'] ?? false)) ) checked @endif
											>
										</div>
									</td>
									<!-- 指定時刻 -->
									<td>
										@if ($errors->has('Settings.eg_' . $expense_group_setting['expense_group_id'] . '.notificated_at'))
											<ol class="list-error">
												@foreach($errors->get('Settings.eg_' . $expense_group_setting['expense_group_id'] . '.notificated_at') as $message)
													<li class="text-danger">{{ $message }}</li>
												@endforeach
											</ol>
										@endif
										<input type="time"
											name="Settings[eg_{{ $expense_group_setting['expense_group_id'] }}][notificated_at]"
											value="{{ old('Settings.eg_' . $expense_group_setting['expense_group_id'] . '.notificated_at', $expense_group_setting['notificated_at'] ?? null) }}"
										>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>

				<div class="row text-center">
					<button type="submit" class="btn btn-primary">送信</button>
				</div>
			</form>
		</div>
	</div>
</div>
<style>
.flex-center-sb {
	display: flex;
	align-items: center;
	justify-content: space-between;
}
.js-add-email {
	display: inline-flex;
	align-items: center;
	cursor: pointer;
	padding: 2px;
}
.js-add-email .icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 5px;
    margin-right: 5px;
}
.js-remove-email {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 20px;
	height: 20px;
	color: silver;
	border-radius: 5px;
	cursor: pointer;
	margin-left: 5px;
	padding-left: 5px;
	padding-right: 5px;
}
.js-add-email:before,
.js-remove-email:before {
	font-size: 15px;
}
ol {
	padding-left: 0;
	list-style: none;
}
.list-email {
	margin-bottom: 0;
	padding-left: 0;
}
</style>
@endsection

@push('js')
{{-- dummy用 --}}
<li id="js-dummy-email-listItem" class="hidden js-email-listItem">
	<div class=" form-group flex-center-sb">
		<input type="email" class="col-md-11">
		<span class="col-md-1 js-remove-email">&#10005;</span>
	</div>
</li>
<script type="text/javascript">
$(function() {
	var undefined;
	var data = {};

	// 追加イベント
	$('.js-add-email').click(function() {
		var dist = $(this).data('dist');
		var name = $(this).data('name');

		if ( dist === undefined || name === undefined || data['emailListItem'] === undefined ) return;

		var emailListItem = data['emailListItem'].clone(true);
		emailListItem.find('input').attr('name', name);
		
		$('#' + dist).append(emailListItem);
	});

	// 削除イベント
	$('.js-remove-email').click(function() {
		$(this).closest('.js-email-listItem').remove();
	});

	// ダミーの複製
	var dummyEmailListItem = $('#js-dummy-email-listItem');
	data['emailListItem'] = dummyEmailListItem.clone(true).removeAttr('id').removeClass('hidden');
	dummyEmailListItem.remove();

});
</script>
@endpush
