<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class ExpenseGroupSetting extends ModelBase {

	use SoftDeletes;

	protected $fillable = [
		'client_id',
		'expense_group_id',
		'realtime_flag',
		'notificated_at',
	];

	// 基本ルール
	protected $rules = [
		'client_id' => ['bail', 'required', 'integer', 'min:1'],
		'expense_group_id' => ['bail', 'integer', 'min:1'],
		'realtime_flag' => ['bail', 'nullable', 'boolean'],
		'notificated_at' => ['bail', 'nullable', 'date_format:H:i'],
	];

	protected $dates = [
		'deleted_at'
	];

	public function __construct(array $attributes = []){
		parent::__construct($attributes);

		// 企業IDは論理削除されていないものが存在すること
		$this->rules['client_id'][] = Rule::exists('clients', 'id')->whereNull('deleted_at');
	}

	// belongsTo
	public function client() {
		return $this->belongsTo('App\Client');
	}

	public function expense_group() {
		return $this->belongsTo('App\ExpenseGroup');
	}

	// hasMany
	public function expense_notification_destinations() {
		return $this->hasMany(ExpenseNotificationDestination::class);
	}

	/*
		「無所属」グループ
	*/
	public function scopeUnaffiliated($query, $client_id) {
		return $query->where('client_id', $client_id)
			->whereNull('expense_group_id');
	}

	/*
		新規作成用ルール
	*/
	public function buildValidationRulesForCreate($data) {
		$rules = $this->rules;

		// expense_group_id === null（無所属設定）が存在しない場合はnull許容
		// ある場合は経費所属グループID必須
		if ( $this->where('client_id', $data['client_id'])->whereNull('expense_group_id')->doesntExist() ) {
			array_splice($rules['expense_group_id'], 1, 0, 'nullable');
		}

		// expense_group_idは企業単位でユニークであること
		$rules['expense_group_id'][] = Rule::unique('expense_group_settings')
			->whereNull('deleted_at')
			->where('client_id', $data['client_id']);

		return $rules;
	}

	/*
		更新用ルール
	*/
	public function buildValidationRulesForUpdate($data) {
		$rules = $this->rules;

		// id必須
		$rules['id'] = ['bail', 'required', 'integer', 'min:1'];
		$ruels['id'][] = Rule::exists('expense_group_settings')->whereNull('deleted_at');

		// 指定されたIDが無所属設定の場合のみnull許容
		if ( empty($data['expense_group_id'])
			&& $this->where('client_id', $data['client_id'])->where('id', $data['id'])->whereNull('expense_group_id')->exists()
		) {
			array_splice($rules['expense_group_id'], 1, 0, 'nullable');
		}

		// expense_group_idは企業単位でユニークであること
		$rules['expense_group_id'][] = Rule::unique('expense_group_settings')
			->whereNull('deleted_at')
			->where('client_id', $data['client_id'])
			->ignore($data['id']);

		return $rules;
	}

	@if (old("Settings.trcd_terminal_notification_settings.{$trcd_terminal_notification_setting['trcd_terminal_id']}.emails", null) != null)
        @foreach(old("Settings.trcd_terminal_notification_settings.{$trcd_terminal_notification_setting['trcd_terminal_id']}.emails", []) as $idx => $value)
            <li class="js-email-listItem">
                @if ($errors->has("Settings.trcd_terminal_notification_settings.{$trcd_terminal_notification_setting['trcd_terminal_id']}.emails." . $idx))
                 	@foreach($errors->get("Settings.trcd_terminal_notification_settings.{$trcd_terminal_notification_setting['trcd_terminal_id']}.emails." . $idx) as $message)
                		<ol class="list-error">
                            <li class="text-danger">{{ $message }}</li>
                        </ol>
                    @endforeach
                @endif
            	<div class="form-group flex-center-sb">
                    <input type="email" class="col-md-11"
                        name="Settings[trcd_terminal_notification_settings][{{ $trcd_terminal_notification_setting['trcd_terminal_id'] }}][emails][]"
                        value="{{ $value }}"
                    >
                	<span class="col-md-1 js-remove-email">&#10005;</span>
            	</div>
            </li>
        @endforeach
	@else
	
	@if ($errors->has('Settings.trcd_terminal_notification_settings.' . $trcd_terminal_notification_setting['trcd_terminal_id'] . '.realtime_flag'))
       	<ol class="list-error">
           	@foreach($errors->get('Settings.trcd_terminal_notification_settings.' . $trcd_terminal_notification_setting['trcd_terminal_id'] . '.realtime_flag') as $message)
               	<li class="text-danger">{{ $message }}</li>
            @endforeach
        </ol>
	@endif

	@if ($errors->has('Settings.' . $trcd_terminal_notification_setting['trcd_terminal_id'] . '.realtime_flag'))
       	<ol class="list-error">
           	@foreach($errors->get('Settings.' . $trcd_terminal_notification_setting['trcd_terminal_id'] . '.realtime_flag') as $message)
               	<li class="text-danger">{{ $message }}</li>
            @endforeach
        </ol>
	@endif



	@if ($errors->has('Settings.' . $trcd_terminal_notification_setting['trcd_terminal_id'] . '.emails.*'))
                                                                                                @foreach(old('Settings.' . $trcd_terminal_notification_setting['trcd_terminal_id'] . '.emails', []) as $idx => $value)
                                                                                                        <li class="js-email-listItem">
                                                                                                                @if ($errors->has('Settings.' . $trcd_terminal_notification_setting['trcd_terminal_id'] . '.emails.' . $idx))
                                                                                                                        @foreach($errors->get('Settings.' . $trcd_terminal_notification_setting['trcd_terminal_id'] . '.emails.' . $idx) as $message)
                                                                                                                                <ol class="list-error">
                                                                                                                                        <li class="text-danger">{{ $message }}</li>
                                                                                                                                </ol>
                                                                                                                        @endforeach
                                                                                                                @endif
                                                                                                                <div class="form-group flex-center-sb">
                                                                                                                        <input type="email" class="col-md-11"
                                                                                                                                name="Settings[trcd_terminal_notification_settings][{{ $trcd_terminal_notification_setting['trcd_terminal_id'] }}][emails][]"
                                                                                                                                value="{{ $value }}"
                                                                                                                        >
                                                                                                                        <span class="col-md-1 js-remove-email">&#10005;</span>
                                                                                                                </div>
                                                                                                        </li>
                                                                                                @endforeach
                                                                                        @else


}
