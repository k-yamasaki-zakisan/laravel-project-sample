<?php

namespace App\Http\Requests\API\Employee;

use App\Http\Requests\API\ApiBaseRequest;
// Services
use App\Services\EmployeeService;
// Models
use App\Models\ContactType;
use App\Models\Person;
// Utilities
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class StoreEmployeeRequest extends ApiBaseRequest {

	protected $rules = [
		'updated_by' => ['bail', 'required', 'exists_soft:persons,link_key'],
	];
	protected $EmployeeService;
	protected $_CachedContactTypes;

	public function __construct(
		EmployeeService $EmployeeService
	) {
		$this->EmployeeService = $EmployeeService;
	}

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
		// 従業員用ルール追加
		$this->rules['employee'] = ['bail', 'required', 'array']; // 必須
		foreach( $this->getEmployeeRules() as $key => $rule ) {
			$this->rules["employee.{$key}"] = $rule;
		}
		// 法人所属履歴用ルール追加
		$this->rules['employee_job_careers'] = ['bail', 'nullable', 'array'];
		if ( !empty($this->employee_job_careers) ) {
			foreach( $this->getEmployeeJobCareerRules() as $key => $rule ) {
				$this->rules["employee_job_careers.*.{$key}"] = $rule;
			}
		}
		// 人用ルール追加
		$this->rules['person'] = ['bail', 'required', 'array']; // 必須
                foreach( $this->getPersonRules() as $key => $rule ) {
                        $this->rules["person.{$key}"] = $rule;
                }
		// 従業員の連絡先用ルール追加
		$this->rules['employee_contacts'] = ['bail', 'nullable', 'array'];
		if ( !empty($this->employee_contacts) ) {
                	foreach( $this->getEmployeeContactRules() as $key => $rule ) {
                        	$this->rules["employee_contacts.*.{$key}"] = $rule;
                	}
		}
		// 従業員の住所用ルール追加
		$this->rules['employee_address'] = ['bail', 'nullable', 'array'];
		if ( !empty($this->employee_address) ) {
			foreach( $this->getEmployeeAddressRules() as $key => $rule ) {
                                $this->rules["employee_address.{$key}"] = $rule;
                        }
		}

		return parent::rules();
    }

	protected function getEmployeeRules() {
		return [
			'corporation_id' => ['bail', 'required', 'exists_soft:corporations,corporation_id'],
			'person_id' => ['bail', 'nullable', 'integer', 'exists_soft:persons,person_id'],
			'code' => ['bail', 'required', 'alpha_num_symbol', 'max:20'],
			'last_name' => ['bail', 'required', 'string', 'max:64'],
			'first_name' => ['bail', 'required', 'string', 'max:64'],
			'last_name_kana' => ['bail', 'required', 'string', 'max:64', 'katakana'],
			'first_name_kana' => ['bail', 'required', 'string', 'max:64', 'katakana'],
			'birthday' => ['bail', 'nullable', 'date_format:Y-m-d'],
			'hire_date' => ['bail', 'nullable', 'date_format:Y-m-d'],
			'retirement_date' => ['bail', 'nullable', 'date_format:Y-m-d'],
		];
	}

	protected function getPersonRules() {
		return [
			'login_id' => ['bail', 'required', 'string', 'max:255'],
            'password' => ['bail', 'required', 'string', 'max:64'],
        	'last_name' => ['bail', 'required', 'string', 'max:64'],
    		'first_name' => ['bail', 'required', 'string', 'max:64'],
            'last_name_kana' => ['bail', 'required', 'string', 'max:64', 'katakana'],
    		'first_name_kana' => ['bail', 'required', 'string', 'max:64', 'katakana'],
            'gender_id' => ['bail', 'nullable', 'integer', 'exists_soft:genders,gender_id'],
    		'birthday' => ['bail', 'nullable', 'date_format:Y-m-d'],
			'basic_pension_number' => ['bail', 'nullable', 'string', 'size:10'],
		];
	}

	protected function getEmployeeJobCareerRules() {
		return [
			'corporation_id' => ['bail', 'required', 'exists_soft:corporations,corporation_id'],
			'last_name' => ['bail', 'required', 'string', 'max:64'],
			'first_name' => ['bail', 'required', 'string', 'max:64'],
			'last_name_kana' => ['bail', 'required', 'string', 'max:64', 'katakana'],
			'first_name_kana' => ['bail', 'required', 'string', 'max:64', 'katakana'],
			'employment_status_id' => ['bail', 'required', 'exists_soft:employment_statuses,employment_status_id'],
			'job_career_status_id' => ['bail', 'nullable', 'exists_soft:job_career_statuses,job_career_status_id'],
			'applied_at' => ['bail', 'required', 'date_format:Y-m-d'],
		];
	}

	protected function getEmployeeAddressRules() {
		return [
			'zip_code1' => ['bail', 'nullable', 'digits_between:1,3'],
			'zip_code2' => ['bail', 'nullable', 'digits_between:1,4'],
			'prefecture_id' => ['bail', 'nullable', 'exists_soft:prefectures,prefecture_id'],
			'city' => ['bail', 'nullable', 'string', 'max:100'],
			'town' => ['bail', 'nullable', 'string', 'max:100'],
			'street' => ['bail', 'nullable', 'string', 'max:100'],
			'building' => ['bail', 'nullable', 'string', 'max:50'],
			'address_kana' => ['bail', 'nullable', 'string', 'max:255']
		];
	}

	protected function getEmployeeContactRules() {
		return [
			'contact_type_id' => ['bail', 'required', 'exists_soft:contact_types,contact_type_id'],
			'value' => ['bail', 'required', 'string', 'max:255'],
		];
	}

	public function validated() {
		$validated = parent::validated();

		// 最終更新者設定
		$last_updated_system_id = auth()->user()->system_id ?? null;
		$last_updator_id = Person::linkKey($validated['updated_by'])->value('person_id');

		$employee = $validated['employee'];
		$employee['last_updated_system_id'] = $last_updated_system_id;
		$employee['last_updated_by'] = $last_updator_id;

		$person = $validated['person'];
		$person['password'] = Hash::make($person['password']);
		$person['last_updated_system_id'] = $last_updated_system_id;
		$person['last_updated_by'] = $last_updator_id;

		if ( !empty($validated['employee_address']) ) {
			$employee_address = $validated['employee_address'];
			$employee_address['last_updated_system_id'] = $last_updated_system_id;
			$employee_address['last_updated_by'] = $last_updator_id;
		}

		if ( !empty($validated['employee_contacts']) ) {
			$employee_contacts = $validated['employee_contacts'];
			foreach( $employee_contacts as $idx => $contact ) {
				$employee_contacts[$idx]['last_updated_system_id'] = $last_updated_system_id;
				$employee_contacts[$idx]['last_updated_by'] = $last_updator_id;
			}
		}

		if ( !empty($validated['employee_job_careers'])) {
			$employee_job_careers = $validated['employee_job_careers'];
			foreach( $employee_job_careers as $idx => $contact ) {
				$employee_job_careers[$idx]['last_updated_system_id'] = $last_updated_system_id;
				$employee_job_careers[$idx]['last_updated_by'] = $last_updator_id;
			}
		}

		return [
			'person' => $person,
			'employee' => $employee,
			'employee_address' => $employee_address ?? [],
			'employee_contacts' => $employee_contacts ?? [],
			'employee_job_careers' => $employee_job_careers ?? [],
		];
	}

}