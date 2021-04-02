<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Office extends ModelBase
{
	use SoftDeletes;

	protected $primaryKey = 'office_id';
	protected static $_cachedTableColumns; // テーブルカラムキャッシュ格納用
	protected $guarded = [
		'office_id',
		'link_key',
		'deleted_at',
		'created_at',
		'updated_at',
	];

	// belongsTo
	public function corporation() {
		return $this->belongsTo(Corporation::class, 'corporation_id', 'corporation_id');
	}

	public function prefecture() {
		return $this->belongsTo(Prefecture::class, 'prefecture_id', 'prefecture_id');
	}

	// hasMany
	public function vehicles() {
		return $this->hasMany(Vehicle::class, $this->primaryKey, $this->primaryKey);
	}

	public function office_contacts() {
		return $this->hasMany(OfficeContact::class, $this->primaryKey, $this->primaryKey);
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
		$rules = [
			'office_id' => ['bail', 'is_null'],
			'corporation_id' => ['bail', 'required', 'exists_soft:corporations,corporation_id'],
			'name' => ['bail', 'required', 'string', 'max:255'],
			'phonetic' => ['bail', 'nullable', 'string', 'max:255', 'katakana'],
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
			'head_office_flg' => ['bail', 'nullable', 'bool'],
			'address' => ['bail', 'nullable', 'string'],
		];

		// １法人１本社
		if ( isset($data['corporation_id']) && !empty($data['head_office_flg']) ) {
			$rules['head_office_flg'] = Rule::unique('offices')->where(function($query) use($data) {
				return $query->where('corporation_id', $data['corporation_id']);
			});
		}

		return $rules;
	}

	public function updatingRules(Array $data) {
		$rules = [
                        'office_id' => ['bail', 'required', 'exists_soft:offices,office_id'],
                        'corporation_id' => ['bail', 'required', 'exists_soft:corporations,corporation_id'],
                        'name' => ['bail', 'required', 'string', 'max:255'],
                        'phonetic' => ['bail', 'nullable', 'string', 'max:255', 'katakana'],
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
                        'head_office_flg' => ['bail', 'nullable', 'bool'],
                        'address' => ['bail', 'nullable', 'string'],
                ];

		// １法人１本社（本社事務所アップデート時は除く）
                if ( isset($data['corporation_id']) && !empty($data['head_office_flg']) ) {
                        $rules['head_office_flg'] = Rule::unique('offices')->where(function($query) use($data) {
                                return $query->where('corporation_id', $data['corporation_id']);
                        })->ignore($data['office_id'], 'office_id');
                }

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