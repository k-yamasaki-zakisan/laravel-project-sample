<?php

namespace App\Http\Requests\Persons;

use App\Http\Requests\RequestBase;

class RegisterCorporationRequest extends RequestBase
{
	protected $rules = [
		'corporation' => ['bail', 'nullable','array'],
		'corporation.id' => ['bail', 'nullable', 'integer'],
		'corporation.name' => ['bail', 'nullable', 'string'],
	];

	public function validated() {
		$validated = parent::validated();

		$result = [];

		if ( !empty($validated['corporation']['id']) && $validated['corporation']['name']) {
			//idの数値変換処理
			$result += ['corporation_id' => (int) $validated['corporation']['id']];
			$result += ['corporation_name' => $validated['corporation']['name']];
		};
		return $result;
	}
}