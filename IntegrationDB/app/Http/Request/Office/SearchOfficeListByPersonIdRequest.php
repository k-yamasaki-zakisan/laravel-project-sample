<?php

namespace App\Http\Requests\API\Office;

use App\Http\Requests\API\ApiBaseRequest;

class SearchOfficeListByPersonIdRequest extends ApiBaseRequest
{
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
    	public function rules()
    	{
        	$rules = [
			'person_id' => ['bail', 'required', 'integer', 'exists_soft:persons,person_id'],
		];

		return $rules;
    }

}