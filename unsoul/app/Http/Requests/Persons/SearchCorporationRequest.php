<?php

namespace App\Http\Requests\Persons;

use App\Http\Requests\RequestBase;

class SearchCorporationRequest extends RequestBase
{
	protected $rules = [
		'search' => ['bail', 'nullable', 'array'],
		'search.corporation.name' => ['bail', 'nullable', 'string'],
		'search.office.address' => ['bail', 'nullable', 'string'],
		'page' => ['bail', 'nullable', 'integer', 'min:1'],
	];
}
