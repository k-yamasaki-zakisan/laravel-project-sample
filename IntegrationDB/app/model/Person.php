<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Person extends ModelBase
{
	use SoftDeletes;

	protected $table = 'persons';
	protected $primaryKey = 'person_id';
	protected $hidden = [
		'password',
	];
	protected $guarded = [
		'person_id',
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
		'login_id' => ['bail', 'required', 'email', 'max:255'],
        'password' => ['bail', 'required', 'string', 'min:8', 'max:64'],
        'last_name' => ['bail', 'required', 'string', 'max:64'],
        'first_name' => ['bail', 'required', 'string', 'max:64'],
        'full_name' => ['bail', 'required', 'string', 'max:128'],
        'last_name_kana' => ['bail', 'required', 'string', 'max:64', 'katakana'],
        'first_name_kana' => ['bail', 'required', 'string', 'max:64', 'katakana'],
        'gender_id' => ['bail', 'nullable', 'exists_soft:genders,gender_id'],
        'birthday' => ['bail', 'nullable', 'date_format:Y-m-d'],
        'passed_away_at' => ['bail', 'nullable', 'date_format:Y-m-d'],
        'last_updated_system_id' => ['bail', 'required', 'exists_soft:systems,system_id'],
		'last_updated_by' => ['bail', 'nullable', 'exists_soft:persons,person_id'],
	];

	// belongsTo
    public function gender() {
        return $this->belongsTo(Gender::class, 'gender_id', 'gender_id');
    }

	public function last_updated_system() {
        return $this->belongsTo(System::class, 'last_updated_system_id', 'system_id');
	}

	// hasMany
	public function person_addresses() {
        return $this->hasMany(PersonAddress::class, 'person_id', 'person_id');
	}

	public function educational_backgrounds() {
        return $this->hasMany(EducationalBackground::class, 'person_id', 'person_id');
	}

	public function person_job_careers() {
        return $this->hasMany(PersonJobCareer::class, 'person_id', 'person_id');
	}

	public function person_contacts() {
        return $this->hasMany(PersonContact::class, 'person_id', 'person_id');
	}

	public function person_licenses() {
        return $this->hasMany(PersonLicense::class, 'person_id', 'person_id');
	}

	public function employees() {
        return $this->hasMany(Employee::class, 'person_id', 'person_id');
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
		// 未キャッシュ・フラグOFFの場合、キャッシュ更新
		if ( empty(SELF::$_cachedTableColumns) || empty($use_cache) ) {
			SELF::$_cachedTableColumns = $this->_getColumnListing($this->getTable());
		}

		return SELF::$_cachedTableColumns;
	}

	/*
		link_key指定で取得
	*/
	public function findByLinkKey($link_key) {
		return $this->linkKey($link_key)->first();
	}

	public function creatingRules(Array $data) {
		$rules = $this->rules;
		$rules['person_id'] = ['bail', 'is_null'];
		//login_idのuniqueチェック(論理削除を除く)
		$rules['login_id'][] = Rule::unique('persons', 'login_id')->whereNull('deleted_at');

		return $rules;
	}

	public function updatingRules(Array $data) {
		$rules = $this->rules;
		$rules['person_id'] = ['bail', 'required',  'exists_soft:persons,person_id'];
		//login_idのuniqueチェック(論理削除と本人は除く)
        $rules['login_id'][] = Rule::unique('persons', 'login_id')->whereNull('deleted_at')->ignore($data['person_id'], 'person_id');

		return $rules;
	}

	public function buildFullName() {
		return "{$this->last_name}{$this->first_name}";
	}
}

public function deleteByLinkKey($link_key, $system_id, $updated_by, Array $options = []) {