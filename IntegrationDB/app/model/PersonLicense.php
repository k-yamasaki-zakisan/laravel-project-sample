<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Carbon\Carbon;

class PersonLicense extends ModelBase
{
	use SoftDeletes;

	protected $primaryKey = 'person_license_id';
  	protected static $_cachedTableColumns; // テーブルカラムキャッシュ格納用
	protected $guarded = [
		'person_license_id',
		'link_key',
		'deleted_at',
		'created_at',
		'updated_at',
	];

	protected $rules = [
		'person_id' => ['bail', 'required', 'integer', 'exists_soft:persons,person_id'],
                'license_id' => ['bail', 'required', 'integer', 'exists_soft:licenses,license_id'],
                'acquisition_year' => ['bail', 'nullable', 'integer', 'min:1970', 'required_with:acquisition_month,acquisition_date'],
                'acquisition_month' => ['bail', 'nullable', 'integer', 'min:1', 'max:12'. 'required_with:acquisition_year,acquisition_date'],
                'acquisition_date' => ['bail', 'nullable', 'integer', 'min:1', 'max:31'],
                'expired_at' => ['bail', 'nullable', 'date_format:Y-m-d'],
                'note' => ['bail', 'nullable', 'string'],
                'last_updated_system_id' => ['bail', 'required', 'exists_soft:systems,system_id'],
                'link_key' => ['bail', 'required', 'string', 'max:64'],
		'last_updated_by' => ['bail', 'nullable', 'exists_soft:persons,person_id'],
	];

	/*
    		テーブルカラム配列取得
    		@param bool $use_cache キャッシュ利用フラグ
    		@return Array
  	*/
  	public function getTableColumns($use_cache = true) {
    		// 未キャッシュフラグOFFの場合、キャッシュ更新
    		if ( empty(SELF::$_cachedTableColumns) || empty($use_cache) ) {
      			SELF::$_cachedTableColumns = $this->_getColumnListing($this->getTable());
    		}

    		return SELF::$_cachedTableColumns;
  	}

  	public function license() {
        	return $this->belongsTo(License::class, 'license_id', 'license_id');
  	}

  	public function person() {
        	return $this->belongsTo(Person::class, 'person_id', 'person_id');
  	}

	public function creatingRules(Array $data) {
		$rules = $this->rules;
		$rules['person_license_id'] = ['bail', 'is_null'];

		//person_idとlicense_idはセットでユニーク
		$rules['person_id'][] = Rule::unique('person_licenses')
			->where('license_id', $data['license_id'])
			->whereNull('deleted_at');

		//有効な日にちとなっているかバリデーション を追加
		$rules['acquisition_date'] =  ['bail', 'nullable', 'integer', 'min:1', 'max:31',
                        function($attribute, $value, $fail) use($data) {
                                if(!empty($data['acquisition_year']) && !empty($data['acquisition_month'])
					&& !empty($data['acquisition_date']) && $this->acquisition_is_num($data)
                                        && !$this->exitDate($data)
                                )
                                {
                                        $fail('取得日に有効な日付を入力してください。');
                                }
                        }
                ];

		//取得日が有効期限内か
		$rules['expired_at'] = ['bail', 'nullable', 'date_format:Y-m-d',
			function($attribute, $value, $fail) use($data) {
				if(!empty($data['acquisition_year']) && !empty($data['acquisition_month'])
                                        && !empty($data['expired_at']) && !$this->withInExpiredAt($data)
                                )
				{
					$fail('取得日が有効期限を超えております。');
				}
			}
		];

		return $rules;
	}

	public function updatingRules(Array $data) {
                $rules = $this->rules;
		$rules['person_license_id'] = ['bail', 'required', 'exists_soft:person_licenses,person_license_id'];

		//person_idとlicense_idはセットでユニーク
                $rules['person_id'][] = Rule::unique('person_licenses')
                        ->where('license_id', $data['license_id'])
                        ->whereNull('deleted_at')
			->ignore($data['person_license_id'], 'person_license_id');

		//有効な日にちとなっているか
                $rules['acquisition_date'] =  ['bail', 'nullable', 'integer', 'min:1', 'max:31',
                        function($attribute, $value, $fail) use($data) {
                                if(!empty($data['acquisition_year']) && !empty($data['acquisition_month'])
                                        && !empty($data['acquisition_date']) && $this->acquisition_is_num($data)
                                        && !$this->exitDate($data)
                                )
                                {
                                        $fail('取得日に有効な日付を入力してください。');
                                }
                        }
                ];

                //取得日が有効期限内か
                $rules['expired_at'] = ['bail', 'nullable', 'date_format:Y-m-d',
                        function($attribute, $value, $fail) use($data) {
                                if(!empty($data['acquisition_year']) && !empty($data['acquisition_month'])
                                        && !empty($data['expired_at']) && !$this->withInExpiredAt($data)
                                )
                                {
                                        $fail('取得日が有効期限を超えております。');
                                }
                        }
                ];

                return $rules;
        }

	/*
		資格取得日のデータ抽出
		@return Array
	*/
	protected function buildAcquisition(Array $data) {
		$acquisition_year = $data['acquisition_year'];
		$acquisition_month = $data['acquisition_month'];
		$acquisition_date = $data['acquisition_date'] ?? 1;

		return [
			'acquisition_year' => $acquisition_year,
			'acquisition_month' => $acquisition_month,
			'acquisition_date' => $acquisition_date
		];
	}

	/*
		整数かチェック
		@return bool
	*/
	protected function acquisition_is_num(Array $data) {
		$buildAcquisition = $this->buildAcquisition($data);

		//文字列が入れられていた場合falseを返す
		foreach( $buildAcquisition as $acquisition) {
			if( !is_numeric($acquisition) ) return false;
		}

		return true;
	}

	/*
		資格取得日と資格有効期限のチェック
		@return bool
	*/
	protected function withInExpiredAt(Array $data) {
		//取得日を呼び出して整形
		$buildAcquisition = $this->buildAcquisition($data);

		$acquisition = Carbon::createFromFormat(
			'Y-n-j',
			"{$buildAcquisition['acquisition_year']}-{$buildAcquisition['acquisition_month']}-{$buildAcquisition['acquisition_date']}"
		)->format('Y-m-d');

		return $acquisition <= $this->expired_at;
	}

	/*
		存在する期日かチェック
		@return bool
	*/
	protected function exitDate(Array $data) {
		//取得日の呼び出し
		$buildAcquisition = $this->buildAcquisition($data);

		return checkdate($buildAcquisition['acquisition_month'], $buildAcquisition['acquisition_date'], $buildAcquisition['acquisition_year']);
	}
}