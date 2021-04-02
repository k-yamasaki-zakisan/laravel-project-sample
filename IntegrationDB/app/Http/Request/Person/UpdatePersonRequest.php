<?php

namespace App\Http\Requests\API\Person;

use App\Http\Requests\API\ApiBaseRequest;
// Services
use App\Services\PersonService;
// Models
use App\Models\Person;

class UpdatePersonRequest extends ApiBaseRequest {
	protected $rules = [
		'updated_by' => ['bail', 'nullable', 'exists_soft:persons,link_key'],
	];

	protected $PersonService;

	public function __construct(
		PersonService $PersonService
	) {
		$this->PersonService = $PersonService;
	}

	/**
     	* Determine if the user is authorized to make this request.
     	*
     	* @return bool
     	*/
    	public function authorize() {
		return true;
   	}

	public function rules() {
		//$validated = parent::validated();
		// 人用ルール
		$this->rules['person'] = ['bail', 'required', 'array']; // 必須
		foreach( $this->getPersonRules() as $key => $rule ) {
			$this->rules["person.{$key}"] = $rule;
		}
		// (リクエストがあれば)従業員用ルール追加
		$this->rules['employee'] = ['bail', 'nullable', 'array'];
		if ( !empty($this->employee) ) {
			foreach( $this->getEmployeeRules() as $key => $rule ) {
                        	$this->rules["employee.{$key}"] = $rule;
                	}
		}

		return parent::rules();
	}

	protected function getPersonRules() {
		return [
			'link_key' => ['bail', 'required',  'exists_soft:persons,link_key'],
			//'login_id' => ['bail', 'required', 'string', 'max:255'],
                	//'password' => ['bail', 'required', 'string', 'max:64'],
                	'last_name' => ['bail', 'required', 'string', 'max:64'],
                	'first_name' => ['bail', 'required', 'string', 'max:64'],
                	'last_name_kana' => ['bail', 'required', 'string', 'max:64', 'katakana'],
                	'first_name_kana' => ['bail', 'required', 'string', 'max:64', 'katakana'],
                	'gender_id' => ['bail', 'nullable', 'integer', 'exists_soft:genders,gender_id'],
                	'birthday' => ['bail', 'nullable', 'date_format:Y-m-d'],
		];
	}

	protected function getEmployeeRules() {
		return [
			'code' => ['bail', 'nullable', 'string', 'max:20'],
                	'last_name' => ['bail', 'required', 'string', 'max:64'],
                	'first_name' => ['bail', 'required', 'string', 'max:64'],
                	'last_name_kana' => ['bail', 'required', 'string', 'max:64', 'katakana'],
                	'first_name_kana' => ['bail', 'required', 'string', 'max:64', 'katakana'],
                	'birthday' => ['bail', 'nullable', 'date_format:Y-m-d'],
        	        'hire_date' => ['bail', 'nullable', 'date_format:Y-m-d'],
	                'retirement_date' => ['bail', 'nullable', 'date_format:Y-m-d'],
		];
	}

	public function validated() {
		$validated = parent::validated();

		// 最終更新者設定
		$last_updated_system_id = auth()->user()->system_id ?? null;
		if ( !empty($validated['updated_by']) ) {
			$last_updator_id = Person::linkKey($validated['updated_by'])->value('person_id');
		} else {
			$last_updator_id = null;
		}

		$person = $validated['person'];
		$person['last_updated_system_id'] = $last_updated_system_id;
		$person['last_updated_by'] = $last_updator_id;

		if ( !empty($validated['employee']) ) {
			$employee = $validated['employee'];
			$employee['last_updated_system_id'] = $last_updated_system_id;
			$employee['last_updated_by'] = $last_updator_id;
		}

		$data = [
			'person' => $person,
			'employee' => $employee ?? [],
		];

		return $data;
	}
}