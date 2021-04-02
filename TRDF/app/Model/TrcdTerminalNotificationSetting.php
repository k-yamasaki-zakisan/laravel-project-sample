<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class TrcdTerminalNotificationSetting extends ModelBase
{
	use SoftDeletes;
	protected $fillable = [
		'trcd_terminal_id',
		'client_id',
		'realtime_flag',
		'notificated_at',
	];


        //belongsTo
        public function client() {
                return $this->belongsTo('App\Client');
	}

	public function trcd_terminal() {
                return $this->belongsTo('App\TrcdTerminal');
        }

	// hasMany
	public function trcd_terminal_notification_destinations() {
                return $this->hasMany('App\TrcdTerminalNotificationDestination');
        }

	/*
		trcd_terminal_idとclient_idを指定
	*/
	public function scopeAlreadyExist($query, $trcd_terminal_id, $client_id) {
		return $query->where('trcd_terminal_id', $trcd_terminal_id)
			->where('client_id', $client_id);
	}

        /**
        * バリデーション
        * @var array
        */
    protected $rules = [
		'trcd_terminal_id' => ['bail', 'required', 'integer', 'min:0', 'exists:trcd_terminals,id'],
                'client_id' => ['bail', 'required', 'integer', 'min:0', 'exists:clients,id'],
                'realtime_flag' => ['bail', 'nullable', 'boolean'],
                'notificated_at' => ['bail','nullable','date_format:H:i'],
	];

	/*
		新規作成用ルール
	*/
	public function buildValidationRulesForCreate($data) {
		$rules = $this->rules;

		// trcd_terminal_id+client_idはユニークであること
		$rules['trcd_terminal_id'][] = Rule::unique('trcd_terminal_notification_settings')
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
		$ruels['id'][] = Rule::exists('trcd_terminal_notification_settings')->whereNull('deleted_at');

                // // trcd_terminal_id+client_idはユニークであること
		$rules['trcd_terminal_id'][] = Rule::unique('trcd_terminal_notification_settings')
			->where('client_id', $data['client_id'])
			->whereNull('deleted_at')
			->ignore($data['id']);

                return $rules;
        }

}
