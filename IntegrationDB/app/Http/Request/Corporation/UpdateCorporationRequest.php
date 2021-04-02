<?php

namespace App\Http\Requests\API\Corporation;

use App\Http\Requests\API\ApiBaseRequest;
// Services
use App\Services\CorporationService;
// Models
use App\Models\ContactType;
use App\Models\Person;
// Utilities
use Illuminate\Support\Arr;


class UpdateCorporationRequest extends ApiBaseRequest {

	protected $rules = [
		'updated_by' => ['bail', 'required', 'exists_soft:persons,link_key'],
	];
	protected $CorporationService;
	protected $_CachedContactTypes;

	public function __construct(
		CorporationService $CorporationService
	) {
		$this->CorporationService = $CorporationService;
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
		// 法人用ルール追加
		$this->rules['corporation'] = ['bail', 'required', 'array']; // 必須
		foreach( $this->getCorporationRules() as $key => $rule ) {
			$this->rules["corporation.{$key}"] = $rule;
		}
		// 本社用ルール追加
		$this->rules['office'] = ['bail', 'nullable', 'array'];
		foreach( $this->getOfficeRules() as $key => $rule ) {
			$this->rules["office.{$key}"] = $rule;
		}
		// 本社連絡先用ルール追加
		$this->rules['office_contacts'] = ['bail', 'nullable', 'array'];
		foreach( $this->getOfficeContactRules() as $key => $rule ) {
			$this->rules["office_contacts.*.{$key}"] = $rule;
		}

		return parent::rules();
    }

	protected function getCorporationRules() {
		return [
			'link_key' => ['bail', 'required', 'exists_soft:corporations,link_key'],
			'name' => ['bail', 'required', 'string', 'max:100'],
			'phonetic' => ['bail', 'nullable', 'string', 'max:255', 'katakana'],
			'corporation_pos' => ['bail', 'nullable', 'boolean'],
			'corporate_number' => ['bail', 'nullable', 'digits_between:1,13'],
			'established_year' => ['bail', 'nullable', 'date_format:Y'],
			'established_month' => ['bail', 'nullable', 'date_format:n'],
			'capital' => ['bail', 'nullable', 'digits_between:1,20'],
			'representative' => ['bail', 'nullable', 'string', 'max:128'],
			'business_description' => ['bail', 'nullable', 'string'],
			'website_url' => ['bail', 'nullable', 'string', 'max:255', 'active_url'],
		];
	}

	protected function getOfficeRules() {
		return [
			'zip_code1' => ['bail', 'nullable', 'digits_between:1,3'],
			'zip_code2' => ['bail', 'nullable', 'digits_between:1,4'],
			'prefecture_id' => ['bail', 'nullable', 'exists_soft:prefectures,prefecture_id'],
			'city' => ['bail', 'nullable', 'string', 'max:100'],
			'town' => ['bail', 'nullable', 'string', 'max:100'],
			'street' => ['bail', 'nullable', 'string', 'max:100'],
			'building' => ['bail', 'nullable', 'string', 'max:50'],
		];
	}

	protected function getOfficeContactRules() {
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

		$corporation = $validated['corporation'];
		$corporation['last_updated_system_id'] = $last_updated_system_id;
		$corporation['last_updated_by'] = $last_updator_id;

		if ( !empty($validated['office']) ) {
			$office = $validated['office'];
			$office['last_updated_system_id'] = $last_updated_system_id;
			$office['last_updated_by'] = $last_updator_id;
		}

		if ( !empty($validated['office_contacts']) ) {
			$office_contacts = $validated['office_contacts'];
			$tmp_office_contacts = collect();
			foreach( $office_contacts as $office_contact ) {
				$office_contact['last_updated_system_id'] = $last_updated_system_id;
				$office_contact['last_updated_by'] = $last_updator_id;
				$tmp_office_contacts->push($office_contact);
			}
		}

		return [
			'corporation' => $corporation,
			'office' => $office ?? [],
			'office_contacts' => $tmp_office_contacts ?? [],
		];
	}