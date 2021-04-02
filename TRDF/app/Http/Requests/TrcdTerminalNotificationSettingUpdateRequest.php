<?php

namespace App\Http\Requests\Trcd\TrcdTerminalNotificationSetting;

use Illuminate\Foundation\Http\FormRequest;

class TrcdTerminalNotificationSettingUpdateRequest extends FormRequest
{
	public $rules = [
                'Settings' => ['bail', 'required', 'array'],
                'Settings.balance_threshold' => ['bail', 'required', 'array'],
                'Settings.balance_threshold.lower_threshold_1yen' => ['bail', 'nullable', 'integer', 'min:0'],
                'Settings.balance_threshold.lower_threshold_5yen' => ['bail', 'nullable', 'integer', 'min:0'],
                'Settings.balance_threshold.lower_threshold_5yen' => ['bail', 'nullable', 'integer', 'min:0'],
                'Settings.balance_threshold.lower_threshold_10yen' => ['bail', 'nullable', 'integer', 'min:0'],
                'Settings.balance_threshold.lower_threshold_50yen' => ['bail', 'nullable', 'integer', 'min:0'],
                'Settings.balance_threshold.lower_threshold_100yen' => ['bail', 'nullable', 'integer', 'min:0'],
                'Settings.balance_threshold.lower_threshold_500yen' => ['bail', 'nullable', 'integer', 'min:0'],
                'Settings.balance_threshold.lower_threshold_1k' => ['bail', 'nullable', 'integer', 'min:0'],
                'Settings.balance_threshold.lower_threshold_5k' => ['bail', 'nullable', 'integer', 'min:0'],
		        'Settings.balance_threshold.lower_threshold_10k' => ['bail', 'nullable', 'integer', 'min:0'],
		        'Settings.trcd_terminal_notification_settings' => ['bail', 'required', 'array'],
		        'Settings.trcd_terminal_notification_settings.*.emails' => ['bail', 'nullable', 'array'],
                'Settings.trcd_terminal_notification_settings.*.realtime_flag' => ['bail', 'nullable', 'boolean'],
                'Settings.trcd_terminal_notification_settings.*.notificated_at' => ['bail', 'nullable', 'date_format:H:i'],
        ];

    	/**
     	* Determine if the user is authorized to make this request.
     	*
     	* @return bool
     	*/
    	public function authorize()
    	{
        	return true;
    	}

    	/**
     	* Get the validation rules that apply to the request.
     	*
     	* @return array
     	*/
	public function rules() {
		$rules =  $this->rules;

		if( is_array($this->Settings->trcd_terminal_notification_settings) ) {
			// 各グループごとにメールアドレスのバリデーションルールを追加
			foreach( $this->Settings->trcd_terminal_notification_settings as $key => $values ) {
				$rules["Settings.trcd_terminal_notification_settings.{$key}.emails.*"] = ['bail', 'required', 'email', 'distinct'];
			}
		}

		return $rules;
	}

	public function withValidator($validator) {
		$validator->after(function ($validator) {
			// SettingsのキーCheck
			if ( !$this->hasValidSettingKeys() ) $validator->errors()->add('data', '不正な値が入力されています。');
		});
	}

	/*
		SettingsのキーCheck
	*/
	protected function hasValidSettingKeys() {
		if ( !is_array($this->Settings) ) return false;

		foreach( $this->Settings->trcd_terminal_notification_settings as $key => $values ) {
			// $keyが半角数字であること
			if ( !preg_match('/^[0-9]+$/', $key) ) return false;
		}

		return true;
	}

	public function messages() {
		$messages = [
			//'Settings.balance_threshold.lower_threshold_1yen' => '半角数字を入力してください',
			'Settings.balance_threshold.*' => '半角数字を入力してください',
			'Settings.trcd_terminal_notification_settings.*.emails.array' => '不正なリクエストです',
			'Settings.trcd_terminal_notification_settings.*.emails.*.email' => 'メールアドレスの形式が正しくありません',
			'Settings.trcd_terminal_notification_settings.*.emails.*.distinct' => 'メールアドレスが重複しています',
			'Settings.trcd_terminal_notification_settings.*.emails.*.required' => 'メールアドレスを入力してください',
			'Settings.trcd_terminal_notification_settings.*.realtime_flag.boolean' => '入力内容を確認してください',
			'Settings.trcd_terminal_notification_settings.*.notificated_at.dete_format' => '指定時刻の形式が正しくありません',
		];

		return $messages;
	}

	/*
		@override
	*/
	public function validated() {
		$validated = parent::validated();
		$results = [$validated['Settings.balance_threshold']];

		// データ整形処理
		foreach($validated['Settings.trcd_terminal_notification_settings'] as $key => $values) {
			$tmpTrcdTerminalNotificationSettings = [
				'trcd_terminal_id' => $key,
				'realtime_flag' => !empty($values['realtime_flag']) ? true : false,
				'notificated_at' => $values['notificated_at'] ?? null,
				'trcd_terminal_notification_destinations' => [],
			];

			if( !empty($values['emails']) ) {
				foreach($values['emails'] as $email) {
					$tmpData['trcd_terminal_notification_destinations'][] = [
						'email' => $email,
					];
				}
			}
			array_push($results, $tmpTrcdTerminalNotificationSettings);
		}
		dd($results);
		return $results;
	}

}


// array:2 [▼
//   "_token" => "NYCWzZ3Gfh9IaYPWLmBfViOVVoXYmgl5R2oHABep"
//   "Settings" => array:2 [▼
//     "balance_threshold" => array:9 [▼
//       "lower_threshold_10k" => null
//       "lower_threshold_5k" => null
//       "lower_threshold_1k" => null
//       "lower_threshold_500yen" => "24"
//       "lower_threshold_50yen" => null
//       "lower_threshold_5yen" => null
//       "lower_threshold_100yen" => null
//       "lower_threshold_10yen" => "432"
//       "lower_threshold_1yen" => "2"
//     ]
//     "trcd_terminal_notification_settings" => array:4 [▼
//       18 => array:2 [▼
//         "emails" => array:1 [▼
//           0 => "test@test.com"
//         ]
//         "notificated_at" => null
//       ]
//       20 => array:1 [▼
//         "notificated_at" => null
//       ]
//       26 => array:1 [▼
//         "notificated_at" => null
//       ]
//       114 => array:1 [▼
//         "notificated_at" => null
//       ]
//     ]
//   ]
// ]