<?php

namespace App\Http\Requests\Trcd\ExpenseGroupSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ExpenseGroupSettingUpdateRequest extends FormRequest {
	public $rules = [
		'Settings' => ['bail', 'required', 'array'],
		'Settings.*.emails' => ['bail', 'nullable', 'array'],
		'Settings.*.realtime_flag' => ['bail', 'nullable', 'boolean'],
		'Settings.*.notificated_at' => ['bail', 'nullable', 'date_format:H:i'],
	];

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		$rules =  $this->rules;

		if( is_array($this->Settings) ) {
			// 各グループごとにメールアドレスのバリデーションルールを追加
			foreach( $this->Settings as $key => $values ) {
				$rules["Settings.{$key}.emails.*"] = ['bail', 'required', 'email', 'distinct'];
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

		foreach( $this->Settings as $key => $values ) {
			// eg_<expense_group_id>であること
			if ( !preg_match('/^eg_([1-9][0-9]*)?$/', $key) ) return false;
		}

		return true;
	}

	protected function extractExpenseGroupId($key) {
		return preg_replace('/^eg_/', '', $key);
	}

	public function messages() {
		$messages = [
			'Settings.*.emails.array' => '不正なリクエストです',
			'Settings.*.emails.*.email' => 'メールアドレスの形式が正しくありません',
			'Settings.*.emails.*.distinct' => '重複しています',
			'Settings.*.emails.*.required' => 'メールアドレスを入力してください',
			'Settings.*.realtime_flag.boolean' => '入力内容を確認してください',
			'Settings.*.notificated_at.dete_format' => '形式が正しくありません',
		];

		return $messages;
	}

	/*
		@override
	*/
	public function validated() {
		$validated = parent::validated();
		$results = [];

		// データ整形処理
		foreach($validated['Settings'] as $key => $values) {
			$expense_group_id = $this->extractExpenseGroupId($key);

			$tmpData = [
				'expense_group_id' => !empty($expense_group_id) ? $expense_group_id : null,
				'realtime_flag' => !empty($values['realtime_flag']) ? true : false,
				'notificated_at' => $values['notificated_at'] ?? null,
				'expense_notification_destinations' => [],
			];

			if( !empty($values['emails']) ) {
				foreach($values['emails'] as $email) {
					$tmpData['expense_notification_destinations'][] = [
						'email' => $email,
					];
				}
			}

			$results[] = $tmpData;
		}

		return $results;
	}
}


// array:2 [▼
//   "_token" => "xrrz5RUcX4ngmQrjVUVbl8frVEE0CQXvDoCEEiaI"
//   "Settings" => array:2 [▼
//     "eg_23" => array:2 [▼
//       "emails" => array:1 [▼
//         0 => "rr@rr.com"
//       ]
//       "notificated_at" => null
//     ]
//     "eg_" => array:2 [▼
//       "emails" => array:1 [▼
//         0 => "test@test.com"
//       ]
//       "notificated_at" => "13:00"
//     ]
//   ]
// ]
