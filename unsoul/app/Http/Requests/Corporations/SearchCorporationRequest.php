<?php

namespace App\Http\Requests\Corporations;

use App\Http\Requests\RequestBase;

class SearchCorporationRequest extends RequestBase
{
	protected $rules = [
		'search' => ['bail', 'nullable', 'array'],
		'search.corporation_id' => ['bail', 'nullable'],
		'search.name' => ['bail', 'nullable'],
		'page' => ['bail', 'nullable', 'integer', 'min:1'],
	];
}
