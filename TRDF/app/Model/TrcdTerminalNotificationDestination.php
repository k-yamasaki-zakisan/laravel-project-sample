<?php

namespace App;

use Illuminate\Validation\Rule;

class TrcdTerminalNotificationDestination extends ModelBase
{
	protected $fillable = [
		'trcd_terminal_notification_setting_id',
		'email',
	];
	
	protected $rules = [
		'trcd_terminal_notification_setting_id' => ['bail', 'required', 'integer', 'min:1'],
		'email' => ['bail', 'email', 'max:255'],
	];

	public function __construct(array $attributes = []){
		parent::__construct($attributes);

		// 端末通知設定IDは論理削除されていないものが存在すること
		$this->rules['trcd_terminal_notification_setting_id'][] = Rule::exists('trcd_terminal_notification_settings', 'id')->whereNull('deleted_at');
	}

	//belongsTo
        public function trcd_terminal_notification_setting(){
                return $this->belongsTo('App\TrcdTerminalNotificationSetting');
	}

	/*
		新規作成用ルール
	*/
	public function buildValidationRulesForCreate($data) {
		$rules = $this->rules;

		// emailは設定単位でユニークであること
		$rules['email'][] = Rule::unique('trcd_terminal_notification_destinations')
			->where('trcd_terminal_notification_setting_id', $data['trcd_terminal_notification_setting_id']);

		return $rules;
	}


}
