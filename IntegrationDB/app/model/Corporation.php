<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Corporation extends ModelBase
{
	use SoftDeletes;

	protected $primaryKey = 'corporation_id';
	protected static $_cachedTableColumns; // テーブルカラムキャッシュ格納用
	protected $guarded = [
		'corporation_id',
		'link_key',
		'deleted_at',
		'created_at',
		'updated_at',
	];

	protected $rules = [
		'name' => ['bail', 'required', 'string', 'max:100'],
                'phonetic' => ['bail', 'nullable', 'string', 'max:255', 'katakana'],
                'corporation_type_id' => ['bail', 'required', 'exists_soft:corporation_types,corporation_type_id'],
                'corporation_pos' => ['bail', 'nullable', 'boolean'],
                'corporate_number' => ['bail', 'nullable', 'digits_between:1,13'],
                'established_year' => ['bail', 'nullable', 'date_format:Y'],
                'established_month' => ['bail', 'nullable', 'date_format:n'],
                'capital' => ['bail', 'nullable', 'digits_between:1,20'],
                'representative' => ['bail', 'nullable', 'string', 'max:128'],
                'business_description' => ['bail', 'nullable', 'string'],
                'website_url' => ['bail', 'nullable', 'string', 'max:255', 'active_url'],
                'link_key' => ['bail', 'required', 'string'],
                'last_updated_system_id' => ['bail', 'required', 'exists_soft:systems,system_id'],
        	'last_updated_by' => ['bail', 'nullable', 'exists_soft:persons,person_id'],
	];

	// belongsTo
	public function prefecture() {
		return $this->belongsTo(Prefecture::class, 'prefecture_id', 'prefecture_id');
	}

	// hasMany
	public function offices() {
		return $this->hasMany(Office::class, 'corporation_id', 'corporation_id');
	}

	public function employees() {
		return $this->hasMany(Employee::class, 'corporation_id', 'corporation_id');
	}

	// hasOne
	public function head_office() {
		return $this->hasOne(Office::class, 'corporation_id', 'corporation_id')->where('head_office_flg', true);
	}

	// scopes
	public function scopeLinkKey($query, $key) {
		return $query->where('link_key', $key);
	}

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
		$rules['corporation_id'] = ['bail', 'is_null'];

		return $rules;
	}

	public function updatingRules(Array $data) {
                $rules = $this->rules;
                $rules['corporation_id'] = ['bail', 'required', 'exists_soft:corporations,corporation_id'];

                return $rules;
        }
}