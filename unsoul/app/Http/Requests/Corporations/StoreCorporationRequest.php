<?php

namespace App\Http\Requests\Corporations;

use App\Http\Requests\RequestBase;

class StoreCorporationRequest extends RequestBase
{
	protected $redirectRoute = 'unsoul.corporations.register';
	protected $rules = [
		'corporation_type_id' => ['bail', 'required'],
		'corporation_pos' => ['bail', 'bool', 'nullable'],
		'name' => ['bail', 'required', 'string', 'required'],
		'phonetic' => ['bail', 'string'],
		'capital' => ['bail', 'nullable'],
		'established_year' => ['bail', 'nullable'],
		'established_month' => ['bail', 'nullable'],
		'representative' => ['bail', 'nullable'],
		'zip_code1' => ['bail', 'nullable'],
		'zip_code2' => ['bail', 'nullable'],
		'prefecture_id' => ['bail', 'nullable'],
		'city' => ['bail', 'nullable'],
		'town' => ['bail', 'nullable'],
		'street' => ['bail', 'nullable'],
		'building' => ['bail', 'nullable'],
		'tel' => ['bail', 'nullable'],
		'fax' => ['bail', 'nullable'],
	];
}
