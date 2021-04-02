<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Employee extends ModelBase
{
	use SoftDeletes;

	protected $table = 'employees';
	protected $primaryKey = 'employee_id';
	protected static $_cachedTableColumns; // テーブルカラムキャッシュ格納用
	protected $guarded = [
		'employee_id',
		'link_key',
		'created_at',
		'updated_at',
		'deleted_at',
	];

	/**
        	* バリデーション
        	* @var array
        */
	protected $rules = [
        	'corporation_id' => ['bail', 'required', 'integer', 'exists_soft:corporations,corporation_id'],
                'person_id' => ['bail', 'required', 'integer', 'exists_soft:persons,person_id'],
                'code' => ['bail', 'required', 'alpha_num_symbol', 'max:20'],
                'last_name' => ['bail', 'required', 'string', 'max:64'],
                'first_name' => ['bail', 'required', 'string', 'max:64'],
                'full_name' => ['bail', 'required', 'string', 'max:128'],
                'last_name_kana' => ['bail', 'required', 'string', 'max:64', 'katakana'],
                'first_name_kana' => ['bail', 'required', 'string', 'max:64', 'katakana'],
                'birthday' => ['bail', 'nullable', 'date_format:Y-m-d'],
                'hire_date' => ['bail', 'nullable', 'date_format:Y-m-d'],
                'retirement_date' => ['bail', 'nullable', 'date_format:Y-m-d', 'after:hire_date'],
                'last_updated_system_id' => ['bail', 'required', 'exists_soft:systems,system_id'],
                'last_updated_by' => ['bail', 'nullable', 'exists_soft:persons,person_id'],
	];

	public function corporation() {
		return $this->belongsTo(Corporation::class, 'corporation_id', 'corporation_id');
	}

	public function person() {
                return $this->belongsTo(Person::class, 'person_id', 'person_id');
        }

	public function person_addresses() {
		return $this->hasMany(PersonAddress::class, 'person_id', 'person_id');
	}

	public function person_contacts() {
                return $this->hasMany(PersonContact::class, 'person_id', 'person_id');
        }

	public function employee_addresses() {
		return $this->hasMany(EmployeeAddress::class, 'employee_id', 'employee_id');
	}

	public function employee_contacts() {
		return $this->hasMany(EmployeeContact::class, 'employee_id', 'employee_id');
	}

	public function employee_job_careers() {
		return $this->hasMany(EmployeeJobCareer::class, 'employee_id', 'employee_id');
	}

	public function last_updated_system() {
		return $this->belongsTo(System::class, 'last_updated_system_id', 'system_id');
	}

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
		$rules['employee_id'] = ['bail', 'is_null'];

		//person_idとcorporation_idはセットでユニーク
		$rules['person_id'][] = Rule::unique('employees')
                        ->where('corporation_id', $data['corporation_id'])
                        ->whereNull('deleted_at');

		return $rules;
	}

	public function updatingRules(Array $data) {
		$rules = $this->rules;
		$rules['employee_id'] = ['bail', 'required', 'exists_soft:employees,employee_id'];

		$rules['person_id'][] = Rule::unique('employees')
                        ->where('corporation_id', $data['corporation_id'])
                        ->whereNull('deleted_at')
                        ->ignore($data['employee_id'], 'employee_id');

		return $rules;
	}

	public function buildFullName() {
		return "{$this->last_name}{$this->first_name}";
	}
}