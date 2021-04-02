<?php

/**
 * 有給ヘッダモデル
 *
 * @author YuKaneko
 */

namespace App;

use Illuminate\Validation\Rule;

class AttendancePaidHoliday extends ModelBase
{

    protected $fillable = [
        'client_employee_id',
        'paid_holiday_id',
        'date',
        'note',
    ];

    public $validate = [
        'client_employee_id' => ['bail', 'required', 'integer', 'exists:client_employees,id'],
        'paid_holiday_id' => ['bail', 'required', 'integer', 'min:1'],
        'date' => ['bail', 'required', 'date'],
        'note' => ['bail', 'nullable', 'string', 'max:1000'],
    ];

    //belongsTo
    public function client_employee()
    {
        return $this->belongsTo('App\ClientEmployee');
    }

    public function paid_holiday()
    {
        return $this->belongsTo('App\PaidHoliday');
    }

    // belongsToMany
    public function annual_paid_holidays()
    {
        return $this->belongsToMany('App\AnnualPaidHoliday', 'attendance_paid_holiday_metas', 'attendance_paid_holiday_id', 'annual_paid_holiday_id')
            ->withPivot('days_used')
            ->as('meta');
    }

    /*
		挿入可能なカラム名を取得
		@return Array $fillable
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

        // ルール生成処理
        //社員・休暇日で複合ユニーク
        $validation_rules['date'][] = Rule::unique('attendance_paid_holidays')->where(function ($query) use ($data) {
            $query->where('client_employee_id', $data['client_employee_id']);
        });

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

        // ルール生成処理
        //社員・休暇日で複合ユニーク
        $validation_rules['date'][] = Rule::unique('attendance_paid_holidays')->where(function ($query) use ($data) {
            $query->where('client_employee_id', $data['client_employee_id']);
        })->ignore($data['id']);

        return $validation_rules;
    }
}
