<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PersonAddress extends ModelBase
{
	use SoftDeletes;

	protected $table = 'person_addresses';
	protected $primaryKey = 'person_address_id';
	protected $guarded = [
		'person_address_id',
		'link_key',
		'created_at',
		'updated_at',
		'deleted_at',
	];
  	protected static $_cachedTableColumns; // テーブルカラムキャッシュ格納用

	/**
        	* バリデーション
        	* @var array
        */
	protected $rules = [
		'person_id' => ['bail', 'required', 'integer', 'exists_soft:persons,person_id'],
		'number' => ['bail', 'required', 'integer'],
		'zip_code1' => ['bail', 'nullable', 'digits_between:1,3'],
		'zip_code2' => ['bail', 'nullable', 'digits_between:1,4'],
		'prefecture_id' => ['bail', 'nullable', 'exists_soft:prefectures,prefecture_id'],
		'city' => ['bail', 'nullable', 'string', 'max:100'],
		'town' => ['bail', 'nullable', 'string', 'max:100'],
		'street' => ['bail', 'nullable', 'string', 'max:100'],
		'building' => ['bail', 'nullable', 'string', 'max:50'],
		'link_key' => ['bail', 'required', 'link_key'],
		'last_updated_system_id' => ['bail', 'required', 'exists_soft:systems,system_id'],
		'last_updated_by' => ['bail', 'nullable', 'exists_soft:persons,person_id'],
		'address' => ['bail', 'nullable', 'string'],
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

	public function creatingRules(Array $data) {
		$rules = $this->rules;
		$rules['person_address_id'] = ['bail', 'is_null'];

		return $rules;
	}

	public function buildAddress() {
		$values = [];

		if ( isset($this->zip_code1) || isset($this->zip_code2)) {
			$values[] = "{$this->zip_code1}-{$this->zip_code2}";
		}

		if ( isset($this->prefecture_id) || isset($this->city) || isset($this->town) || isset($this->street)) {
			// ToDo:Cacheから取得してくるように改修すること
			$prefecture_name = $this->prefecture->name ?? null;
			$values[] = "{$prefecture_name}{$this->city}{$this->town}{$this->street}";
		}

		if ( isset($this->building) ) $values[] = $this->building;

		if ( empty($values) ) return null;

		$address = join('　', $values);

		return $address;
	}
}