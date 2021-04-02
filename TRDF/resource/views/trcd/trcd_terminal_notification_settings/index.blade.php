@extends('layouts.app_trcd')
@section('title', '残高不足通知')

@section('content')
<div class="col-xs-12">
	<form method="POST" action="{{ route('trcd.trcd_terminal_notification_settings.update') }}">
		@csrf
		<div class="box">
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
			<div class="box-header with-border">
				<h3 class="box-title">残高閾値</h3>
			</div>
			<div class="box-body">
				<dl class="currencyList">
					<div class="currency">
						<dt>一万円&nbsp;残枚数</dt>
						<dd>
							<div class="input-group">
								<input type="number" class="form-control" min="0"
									name="Settings[balance_threshold][lower_threshold_10k]"
									value="{{ old('Settings.balance_threshold.lower_threshold_10k', $balance_threshold['lower_threshold_10k'] ?? null) }}">
								<span class="input-group-addon">枚</span>
							</div>
						</dd>
					</div>
					<div class="currency">
						<dt>五千円&nbsp;残枚数</dt>
						<dd>
							<div class="input-group">
								<input type="number" class="form-control" min="0"
									name="Settings[balance_threshold][lower_threshold_5k]"
									value="{{ old('Settings.balance_threshold.lower_threshold_5k', $balance_threshold['lower_threshold_5k'] ?? null) }}">
								<span class="input-group-addon">枚</span>
							</div>
						</dd>
					</div>
					<div class="currency">
						<dt>千円&nbsp;残枚数</dt>
						<dd>
							<div class="input-group">
								<input type="number" class="form-control" min="0"
									name="Settings[balance_threshold][lower_threshold_1k]"
									value="{{ old('Settings.balance_threshold.lower_threshold_1k', $balance_threshold['lower_threshold_1k'] ?? null) }}">
								<span class="input-group-addon">枚</span>
							</div>
						</dd>


					</div>
					<div class="currency">
						<dt>五百円&nbsp;残枚数</dt>
						<dd>
							<div class="input-group">
								<input type="number" class="form-control" min="0"
									name="Settings[balance_threshold][lower_threshold_500yen]"
									value="{{ old('Settings.balance_threshold.lower_threshold_500yen', $balance_threshold['lower_threshold_500yen'] ?? null) }}">
								<span class="input-group-addon">枚</span>
							</div>
						</dd>
					</div>
					<div class="currency">
						<dt>五十円&nbsp;残枚数</dt>
						<dd>
							<div class="input-group">
								<input type="number" class="form-control" min="0"
									name="Settings[balance_threshold][lower_threshold_50yen]"
									value="{{ old('Settings.balance_threshold.lower_threshold_50yen', $balance_threshold['lower_threshold_50yen'] ?? null) }}">
								<span class="input-group-addon">枚</span>
							</div>
						</dd>
					</div>
					<div class="currency">
						<dt>五円&nbsp;残枚数</dt>
						<dd>
							<div class="input-group">
								<input type="number" class="form-control" min="0"
									name="Settings[balance_threshold][lower_threshold_5yen]"
									value="{{ old('Settings.balance_threshold.lower_threshold_5yen', $balance_threshold['lower_threshold_5yen'] ?? null) }}">
								<span class="input-group-addon">枚</span>
							</div>
						</dd>
					</div>
					<div class="currency">
						<dt>百円&nbsp;残枚数</dt>
						<dd>
							<div class="input-group">
								<input type="number" class="form-control" min="0"
									name="Settings[balance_threshold][lower_threshold_100yen]"
									value="{{ old('Settings.balance_threshold.lower_threshold_100yen', $balance_threshold['lower_threshold_100yen'] ?? null) }}">
								<span class="input-group-addon">枚</span>
							</div>
						</dd>
					</div>
					<div class="currency">
						<dt>十円&nbsp;残枚数</dt>
						<dd>
							<div class="input-group">
								<input type="number" class="form-control" min="0"
									name="Settings[balance_threshold][lower_threshold_10yen]"
									value="{{ old('Settings.balance_threshold.lower_threshold_10yen', $balance_threshold['lower_threshold_10yen'] ?? null) }}">
								<span class="input-group-addon">枚</span>
							</div>
						</dd>
					</div>
					<div class="currency">
						<dt>一円&nbsp;残枚数</dt>
						<dd>
							<div class="input-group">
								<input type="number" class="form-control" min="0"
									name="Settings[balance_threshold][lower_threshold_1yen]"
									value="{{ old('Settings.balance_threshold.lower_threshold_1yen', $balance_threshold['lower_threshold_1yen'] ?? null) }}">
								<span class="input-group-addon">枚</span>
							</div>
						</dd>
					</div>
				</dl>
			</div>
		</div>
		<div class="box">
			<div class="box-body">
				<div class="table-responsive">
					<table id="trcd_terminal_notification_settings" class="table table-bordered table-hover">
						<thead>
							<tr>
								<th class="col-sm-5 col-md-4 col-lg-4">端末名</th>
								<th class="col-sm-3 col-md-4 col-lg-4">送信先アドレス</th>
								<th class="col-sm-3 col-md-2 col-lg-2 text-center">リアルタイム</th>
								<th class="col-sm-1 col-md-2 col-lg-2">指定時刻</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($trcd_terminal_notification_settings as $trcd_terminal_notification_setting)
							<tr>
								<!-- 端末名 -->
								<td>{{ $trcd_terminal_notification_setting['trcd_terminal']['name'] }}</td>
								<!-- 送信先アドレス -->
								<td>
									<ol id="email_list_{{ $trcd_terminal_notification_setting['id'] }}"
										class="list-email">
										@if
										(old("Settings.trcd_terminal_notification_settings.{$trcd_terminal_notification_setting['id']}.emails",
										null) != null)
										@foreach(old("Settings.trcd_terminal_notification_settings.{$trcd_terminal_notification_setting['id']}.emails",
										[]) as $idx => $value)
										<li class="js-email-listItem">
											@if
											($errors->has("Settings.trcd_terminal_notification_settings.{$trcd_terminal_notification_setting['id']}.emails."
											. $idx))
											@foreach($errors->get("Settings.trcd_terminal_notification_settings.{$trcd_terminal_notification_setting['id']}.emails."
											. $idx) as $message)
											<ol class="list-error">
												<li class="text-danger">{{ $message }}</li>
											</ol>
											@endforeach
											@endif
											<div class="form-group flex-center-sb">
												<input type="email" class="col-md-11"
													name="Settings[trcd_terminal_notification_settings][{{ $trcd_terminal_notification_setting['id'] }}][emails][]"
													value="{{ $value }}">
												<span class="col-md-1 js-remove-email">&#10005;</span>
											</div>
										</li>
										@endforeach
										@else
										@foreach($trcd_terminal_notification_setting['trcd_terminal_notification_destinations']
										as $trcd_terminal_notification_destination)
										<li class="js-email-listItem">
											<div class="form-group flex-center-sb">
												<input type="email" class="col-md-11"
													name="Settings[trcd_terminal_notification_settings][{{ $trcd_terminal_notification_setting['id'] }}][emails][]"
													value="{{ $trcd_terminal_notification_destination['email'] }}">
												<span class="col-md-1 js-remove-email">&#10005;</span>
											</div>
										</li>
										@endforeach
										@endif
									</ol>
									<p>
										<span class="js-add-email"
											data-dist="email_list_{{ $trcd_terminal_notification_setting['id'] }}"
											data-name="Settings[trcd_terminal_notification_settings][{{ $trcd_terminal_notification_setting['id'] }}][emails][]">
											<i class="icon bg-light-blue fa fa-plus"></i>
											<small>送信先を追加</small>
										</span>
									</p>
								</td>
								<!-- リアルタイム -->
								<td>
									<div class="text-center">
										@if
										($errors->has("Settings.trcd_terminal_notification_settings.{$trcd_terminal_notification_setting['id']}.realtime_flag"))
										<ol class="list-error">
											@foreach($errors->get("Settings.trcd_terminal_notification_settings.{$trcd_terminal_notification_setting['id']}.realtime_flag")
											as $message)
											<li class="text-danger">{{ $message }}</li>
											@endforeach
										</ol>
										@endif
										<input type="checkbox" value="1"
											name="Settings[trcd_terminal_notification_settings][{{ $trcd_terminal_notification_setting['id'] }}][realtime_flag]"
											@if (
											!empty(old("Settings.trcd_terminal_notification_settings.{$trcd_terminal_notification_setting['id']}.realtime_flag",
											$trcd_terminal_notification_setting['realtime_flag'] ?? false)) ) checked
											@endif>
									</div>
								</td>
								<!-- 指定時刻 -->
								<td>
									@if
									($errors->has("Settings.trcd_terminal_notification_settings.{$trcd_terminal_notification_setting['id']}.notificated_at"))
									<ol class="list-error">
										@foreach($errors->get("Settings.trcd_terminal_notification_settings.{$trcd_terminal_notification_setting['id']}.notificated_at")
										as $message)
										<li class="text-danger">{{ $message }}</li>
										@endforeach
									</ol>
									@endif

									@if ( !empty($trcd_terminal_notification_setting['notificated_at']))
									<input type="time"
										name="Settings[trcd_terminal_notification_settings][{{ $trcd_terminal_notification_setting['id'] }}][notificated_at]"
										value="{{ old('Settings.trcd_terminal_notification_settings.' . $trcd_terminal_notification_setting['id'] . '.notificated_at', \Carbon\Carbon::parse($trcd_terminal_notification_setting['notificated_at'])->format('H:i')) }}">
									@else
									<input type="time"
										name="Settings[trcd_terminal_notification_settings][{{ $trcd_terminal_notification_setting['id'] }}][notificated_at]"
										value="{{ old('Settings.trcd_terminal_notification_settings.' . $trcd_terminal_notification_setting['id'] . '.notificated_at') }}">

									@endif
								</td>
							</tr>
							@endforeach
						</tbody>
					</table>
				</div>

				<div class="row text-center">
					<button type="submit" class="btn btn-primary">送信</button>
				</div>
			</div>
		</div>
	</form>
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

	.currencyList {
		display: flex;
		justify-content: space-between;
		align-items: center;
		flex-wrap: wrap;
	}

	.currencyList>.currency {
		width: 33%;
		text-align: center;
		margin-bottom: 5px;
		border: 1px solid #ccc;
	}

	.currencyList>.currency>dt {
		padding: 2px 0;
		background-color: #e6e6e6;
		border-bottom: 1px solid #ccc;
	}

	.currencyList>.currency>dd {
		padding: 7px 0;
		font-size: 16px;
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