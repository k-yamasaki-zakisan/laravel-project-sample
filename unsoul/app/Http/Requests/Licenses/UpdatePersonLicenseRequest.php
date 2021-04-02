<?php

namespace App\Http\Requests\Licenses;

use App\Http\Requests\RequestBase;
use Illuminate\Contracts\Validation\Validator;
use Carbon\Carbon;

class UpdatePersonLicenseRequest extends RequestBase
{
	protected $rules = [
		//'acquisition' => ['bail', 'nullable', 'date_format:Y-m-d'],
		'acquisition_year' => ['bail', 'nullable', 'integer', 'digits:4', 'min:1970', 'required_with:acquisition_month,acquisition_date'],
		'acquisition_month' => ['bail', 'nullable', 'integer', 'between:1,12', 'required_with:acquisition_date'],
		'acquisition_date' => ['bail', 'nullable', 'integer', 'between:1,31'],
		'expired_at' => ['bail', 'nullable', 'date_format:Y-m-d'],
		'note' => ['bail', 'nullable', 'string'],
	];

	public function withValidator(Validator $validator)
	{
		$validator->after(function ($validator) {
			if ($this->filled(['acquisition_year', 'expired_at'])) {
				if ($this->withInExpiredAt() === False) {
					$validator->errors()->add(
						'expired_at',
						'取得日が有効期限を超えております。'
					);
				}
			}
		});
	}

	/*
		資格取得日と資格有効期限のチェック
		@return bool
	*/
	protected function withInExpiredAt()
	{
		//取得日の整形して有効期限内であることのチェック
		$acquisition_year = $this->acquisition_year;
		$acquisition_month = $this->acquisition_month ?? '01';
		$acquisition_date = $this->acquisition_date ?? '01';

		$acquisition = Carbon::createFromFormat(
			'Y-n-j',
			"{$acquisition_year}-{$acquisition_month}-{$acquisition_date}"
		)->format('Y-m-d');

		return $acquisition <= $this->expired_at;
	}
}
