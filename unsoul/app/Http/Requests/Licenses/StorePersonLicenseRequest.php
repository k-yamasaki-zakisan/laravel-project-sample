<?php

namespace App\Http\Requests\Licenses;

use App\Http\Requests\RequestBase;
use Illuminate\Contracts\Validation\Validator;
// Utilities
use Carbon\Carbon;
// Traits
use \App\Http\Requests\Licenses\AcquisitionTrait;

class StorePersonLicenseRequest extends RequestBase
{
    use AcquisitionTrait;

	protected $rules = [
		'acquisition_year' => ['bail', 'nullable', 'integer', 'min:1970', 'within_now_year', 'required_with:acquisition_month,acquisition_date', 'year_month_or_year_month_date:acquisition_year,acquisition_month,acquisition_date'],
		'acquisition_month' => ['bail', 'nullable', 'integer', 'date_format:n', 'required_with:acquisition_year,acquisition_date'],
		'acquisition_date' => ['bail', 'nullable', 'integer', 'date_format:j'],
		'expired_at' => ['bail', 'nullable', 'date_format:Y-m-d'],
		'license_id' => ['bail', 'required', 'integer'],
		'note' => ['bail', 'nullable', 'string'],
	];

    public function rules() {
        // 取得日が設定されている場合は、有効期限との整合性をとる
        $Acquitision = $this->buildAcquisition(
            $this->acquisition_year,
            $this->acquisition_month,
            $this->acquisition_date
        );

        if ( !empty($Acquitision) ) {
            $this->rules['expired_at'][] = "after_or_equal:" . $Acquitision->format('Y-m-d');
        }

        return parent::rules();
    }

	public function attributes() {
		return [
			'acquisition_year' => '取得日(年)',
			'acquisition_month' => '取得日(月)',
			'acquisition_date' => '取得日(日)',
			'expired_at' => '有効期限',
			'license_id' =>'資格',
		];
	}

    public function messages() {
        return [
            'acquisition_year.year_month_or_year_month_date' => '取得日に有効な日付を入力してください。',
            'expired_at.after_or_equal' => '取得日が有効期限を超えております。',
        ];
    }


    /*
        取得日を取得
        @return Carbon
    */
/*
    protected function buildAcquisition($year, $month, $date) {
        $pattern = '/^[0-9]+$/';

        // 前提条件：年月に設定されているのは半角数字のみ
        if ( ! ( isset($year) && preg_match($pattern, $year) ) ) return null;
        if ( ! ( isset($month) && preg_match($pattern, $month) ) ) return null;

        // 整数化
        $year = intval($year);
        $month = intval($month);

        // 日にち処理
        if ( !isset($date) ) {
            // セットされていない場合は1日を仮代入して処理を行う
            $date = 1;
        } else if ( !preg_match($pattern, $date) ) {
            // 半角数字以外がセットされている場合は、nullを返す
            return null;
        } else {
            // 整数化
            $date = intval($date);
        }

        // 有効な日付でない場合は、nullを返す
        if ( !checkdate($month, $date, $year) ) return null;

        // Carbon生成
        return Carbon::create($year, $month, $date)->startOfDay();
    }
*/

/*
	public function withValidator(Validator $validator) {
		$validator->after(function ($validator) {
			if ( $this->filled(['acquisition_year', 'acquisition_month']) && $this->acquisition_is_num() ) {
				//取得日が有効期限を超えている場合
				if( $this->filled(['expired_at']) && !$this->withInExpiredAt() ) {
					$validator->errors()->add(
						'expired_at', '取得日が有効期限を超えております。'
					);
				}
				//存在しない期日の場合
				if( !$this->exitDate() ) {
					$validator->errors()->add(
                                                'acquisition_year', '取得日に有効な日付を入力してください。'
                                        );
				}
			}
		});
	}
*/

	/*
                資格取得日のデータ抽出
                @return Array
        */
/*
	protected function buildAcquisition() {
		$acquisition_year = $this->acquisition_year;
                $acquisition_month = $this->acquisition_month;
                $acquisition_date = $this->acquisition_date ?? 1;

		return [
			'acquisition_year' => $acquisition_year,
			'acquisition_month' => $acquisition_month,
			'acquisition_date' => $acquisition_date
		];
	}
*/

	/*
                整数かチェック
                @return bool
        */
/*
	protected function acquisition_is_num() {
		$buildAcquisition = $this->buildAcquisition();

		//文字列が入れられていた場合falseを返す
		foreach( $buildAcquisition as $acquisition) {
                        if( !is_numeric($acquisition) ) return false;
                }

		return true;
	}
*/

	/*
		資格取得日と資格有効期限のチェック
		@return bool
	*/
/*
	protected function withInExpiredAt() {
		//取得日を呼び出して整形
		$buildAcquisition = $this->buildAcquisition();

		$acquisition = Carbon::createFromFormat(
			'Y-n-j',
			"{$buildAcquisition['acquisition_year']}-{$buildAcquisition['acquisition_month']}-{$buildAcquisition['acquisition_date']}"
		)->format('Y-m-d');

		return $acquisition <= $this->expired_at;
	}
*/

	/*
                存在する期日かチェック
                @return bool
        */
/*
	protected function exitDate() {
		//取得日の呼び出し
		$buildAcquisition = $this->buildAcquisition();

		return checkdate($buildAcquisition['acquisition_month'], $buildAcquisition['acquisition_date'], $buildAcquisition['acquisition_year']);
	}
*/
}