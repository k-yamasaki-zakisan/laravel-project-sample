<?php

namespace App\Http\Requests\API\Person;

use App\Http\Requests\API\ApiBaseRequest;
use Illuminate\Contracts\Validation\Validator;

class FindPersonRequest extends ApiBaseRequest
{
	protected $rules = [
		'person_id' => ['bail', 'nullable'],
		'link_key' => ['bail', 'nullable', 'string'],
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
		$this->_addRulesOn($this->rules['person_id'], $this->_getNaturalBigIntegerRules());

		return parent::rules();
    }

    public function withValidator(Validator $validator) {
        $validator->after(function ($validator) {
            if ($this->filled(['person_id', 'link_key'])) {
                $validator->errors()->add(
                    'person_id', '主キー と link_key は両方指定できません。どちらか一方を指定してください。'
                );
            } elseif (!$this->anyFilled(['person_id', 'link_key'])) {
                $validator->errors()->add(
                    'person_id', '主キー と link_key が空です。どちらか一方を指定してください。'
                );
            }
        });
    }
}