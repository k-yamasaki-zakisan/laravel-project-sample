<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class PaidHoliday extends ModelBase
{
    // 論理削除
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'name',
        'add_amount_per_for_daily_employee',
        'add_time_for_hourly_employee',
        'when_duplicate_attendance_alert_type_id',
        'when_duplicate_attendance_add_amount_flag',
        'rate',
    ];

    // hasOne

    // hasMany
    public function attendance_requests()
    {
        return $this->hasMany('App\AttendanceRequest');
    }

    // belongsTo
    public function client()
    {
        return $this->belongsTo('App\Client');
    }

    /**
     * バリデーション
     * @var array
     */
    public $validate = [
        'client_id' => ['bail', 'required', 'numeric'],
        'name' => ['bail', 'required', 'string', 'max:50'],
        //'add_amount_for_daily_employee' => ['required', 'boolean'],
        'add_amount_per_for_daily_employee' => ['bail', 'nullable', 'regex:/^(0|0\.[0-9]|1(\.0)?)$/'],
        //'add_time_for_hourly_employee' => ['required'],
        'add_time_for_hourly_employee' => ['bail', 'nullable'],
        'when_duplicate_attendance_alert_type_id' => ['bail', 'integer', 'in:10,20'],
        'when_duplicate_attendance_add_amount_flag' => ['bail', 'boolean'],
        //'rate' => ['bail', 'required', 'regex:/^(0|0\.[0-9]{1,2}|1(\.0)?)$/'],
        'rate' => ['bail', 'required', 'regex:/^(0(\.[0-9]{1,2})?||1(\.0)?)$/'],
    ];

    /*
		特定クライアント制限
	*/
    public function scopeSpecificClient($query, $client_id)
    {
        return $query->where('client_id', $client_id);
    }

    /*
		リスト化
	*/
    public function scopeList($query, $key = 'id', $value = 'name')
    {
        return $query->pluck($value, $key);
    }

    /*
		入力可能カラム取得
	*/
    public function getFillableAttributes()
    {
        return $this->fillable;
    }

    /*
		新規追加用バリデーションルール取得
		@param Array $data
		@return Array $validation_rules
	*/
    public function buildValidationRulesForInsert($data)
    {
        $validation_rules = $this->validate;

        return $validation_rules;
    }

    /*
		更新用バリデーションルール取得
		@param Array $data
		@return Array $validation_rules
	*/
    public function buildValidationRulesForUpdate($data)
    {
        $validation_rules = $this->validate;

        return $validation_rules;
    }
}
