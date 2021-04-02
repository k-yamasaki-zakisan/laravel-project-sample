<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PersonContact extends ModelBase
{
	use SoftDeletes;

	protected $primaryKey = 'person_contact_id';
  	protected static $_cachedTableColumns; // テーブルカラムキャッシュ格納用
  	protected $guarded = [
		'person_contact_id',
		'link_key',
		'deleted_at',
		'created_at',
		'updated_at',
	];

	protected $rules = [
                'person_id' => ['bail', 'required', 'exists_soft:persons,person_id'],
                'contact_type_id' => ['bail', 'required', 'exists_soft:contact_types,contact_type_id'],
                'value' => ['bail', 'required', 'string', 'max:255'],
                'link_key' => ['bail', 'required', 'link_key'],
                'last_updated_system_id' => ['bail', 'required', 'exists_soft:systems,system_id'],
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

	// belongsTo
	public function contact_type() {
		return $this->belongsTo(ContactType::class, 'contact_type_id', 'contact_type_id');
	}

	public function creatingRules(Array $data) {
		$rules = $this->rules;
		$rules['person_contact_id'] = ['bail', 'is_null'];

		return $rules;
	}
}