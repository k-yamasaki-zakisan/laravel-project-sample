<?php

namespace App\Http\Requests\API\Office;

use App\Http\Requests\API\ApiBaseRequest;

class FindOfficeRequest extends ApiBaseRequest
{
	protected $rules = [
		'office_id' => ['bail', 'required'],
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
		$this->_addRulesOn($this->rules['office_id'], $this->_getNaturalBigIntegerRules());

		return parent::rules();
    }
}
