<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class AnnualPaidHoliday extends ModelBase
{
    use SoftDeletes;

    protected $fillable = [
        'annual_paid_holiday_summary_id',
        'base_date',
        'next_base_date',
        'days_worked',
        'days_granted',
        'days_used',
        'expiration_date',
        'days_added',
        'usable_days',
    ];

    /*
		バリデーション
		@var array
	*/
    protected $rules = [
        'annual_paid_holiday_summary_id' => ['bail', 'required', 'integer', 'min:1', 'exists:annual_paid_holiday_summaries,id'],
        'base_date' => ['bail', 'required', 'date'],
        'next_base_date' => ['bail', 'required', 'date'],
        'days_worked' => ['bail', 'required', 'integer'],
        'days_granted' => ['bail', 'required', 'integer'],
        'days_used' => ['bail', 'regex:/^-?\d{1,3}(\.\d{0,2})?$/'],
        'expiration_date' => ['bail', 'required', 'date'],
        'days_added' => ['bail', 'regex:/^-?\d{1,3}(\.\d{0,2})?$/'],
        'usable_days' => ['bail', 'regex:/^-?\d{1,3}(\.\d{0,2})?$/'],
    ];

    /*
		所属している1:多のリレーション
	*/
    public function annual_paid_holiday_summary()
    {
        return $this->belongsTo('App\AnnualPaidHolidaySummary');
    }

    // belongsToMany
    public function attendance_paid_holidays()
    {
        return $this->belongsToMany('App\AttendancePaidHoliday', 'attendance_paid_holiday_metas', 'annual_paid_holiday_id', 'attendance_paid_holiday_id');
    }

    /*
		新規作成用ルール
	*/
    public function buildValidationRulesForCreate($data)
    {
        $rules = $this->rules;

        // 新規作成時にキーがある場合は必須（キーがない場合はdefault値が適用されるはず）
        if (array_key_exists('days_used', $data)) $rules['days_used'][] = 'required';
        if (array_key_exists('days_added', $data)) $rules['days_added'][] = 'required';
        if (array_key_exists('usable_days', $data)) $rules['usable_days'][] = 'required';

        return $rules;
    }

    /*
		更新用ルール
	*/
    public function buildValidationRulesForUpdate($data)
    {
        $rules = $this->rules;

        // 付与日数と追加付与日数の合計が0を下回らないルールを加える
        $rules['days_added'] = array_merge($rules['days_added'], ["gte:" . gmp_neg($data['days_granted']), 'required']);

        // 更新時は値が入っているものとする
        $rules['days_used'][] = 'required';
        $rules['usable_days'][] = 'required';

        return $rules;
    }
}
