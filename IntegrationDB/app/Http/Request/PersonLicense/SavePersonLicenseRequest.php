<?php

namespace App\Http\Requests\API\PersonLicense;

use Illuminate\Contracts\Validation\Validator;

use App\Http\Requests\API\ApiBaseRequest;
// Services
use App\Services\PersonService;
// Models
use App\Models\Person;
// Utilities
use Carbon\Carbon;
// Traits
use \App\Http\Requests\API\PersonLicense\AcquisitionTrait;

class SavePersonLicenseRequest extends ApiBaseRequest
{
	use AcquisitionTrait;

	protected $rules = [
		'updated_by' => ['bail', 'required', 'exists_soft:persons,link_key'],
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
		$this->rules['person_license'] = ['bail', 'required', 'array']; // 必須
		foreach( $this->getPersonLicenseRules() as $key => $rule ) {
			$this->rules["person_license.{$key}"] = $rule;
		}

		// 取得日が設定されている場合は、有効期限との整合性をとる
		$Acquitision = $this->buildAcquisition(
			$this->person_license['acquisition_year'],
			$this->person_license['acquisition_month'],
			$this->person_license['acquisition_date']
		);

		if ( !empty($Acquitision) ) {
			$this->rules['person_license.expired_at'][] = "after_or_equal:" . $Acquitision->format('Y-m-d');
		}

		return parent::rules();
	}

	protected function getPersonLicenseRules() {
		return [
			'person_id' => ['bail', 'required', 'integer', 'exists_soft:persons,person_id'],
                	'license_id' => ['bail', 'required', 'integer', 'exists_soft:licenses,license_id'],
                	'acquisition_year' => ['bail', 'nullable', 'integer', 'min:1970', 'required_with:person_license.acquisition_month,person_license.acquisition_date', 'year_month_or_year_month_date:acquisition_year,acquisition_month,acquisition_date'],
                	'acquisition_month' => ['bail', 'nullable', 'integer', 'date_format:n', 'required_with:person_license.acquisition_year,person_license.acquisition_date'],
                	'acquisition_date' => ['bail', 'nullable', 'integer', 'date_format:j'],
                	'expired_at' => ['bail', 'nullable', 'date_format:Y-m-d'],
                	'note' => ['bail', 'nullable', 'string'],
		];
	}

	public function validated() {
		$validated = parent::validated();

		// 最終更新者設定
		$last_updated_system_id = auth()->user()->system_id ?? null;
		$last_updator_id = Person::linkKey($validated['updated_by'])->value('person_id');

		$person_license = $validated['person_license'];
		$person_license['last_updated_system_id'] = $last_updated_system_id;
		$person_license['last_updated_by'] = $last_updator_id;

		return [
			'person_license' => $person_license,
		];
	}

}