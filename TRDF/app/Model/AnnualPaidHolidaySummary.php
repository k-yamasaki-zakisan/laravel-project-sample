<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class AnnualPaidHolidaySummary extends ModelBase
{
    use SoftDeletes;

    protected $fillable = [
        'client_employee_id',
        'last_base_date',
        'next_base_date',
        'days_granted',
        'days_used',
        'usable_days',
        'days_added',
    ];

    /**
     * バリデーション
     * @var array
     */
    protected $rules = [
        'client_employee_id' => ['bail', 'required', 'integer', 'min:1', 'exists:client_employees,id'],
        'last_base_date' => ['bail', 'nullable', 'date'],
        'next_base_date' => ['bail', 'nullable', 'date'],
        'days_granted' => ['bail', 'nullable', 'regex:/^-?\d{1,3}(\.\d{0,2})?$/'],
        'days_used' => ['bail', 'nullable', 'regex:/^-?\d{1,3}(\.\d{0,2})?$/'],
        'usable_days' => ['bail', 'nullable', 'regex:/^-?\d{1,3}(\.\d{0,2})?$/'],
        'days_added' => ['bail', 'nullable', 'regex:/^-?\d{1,3}(\.\d{0,2})?$/'],
    ];

    // 1:1のリレーション
    public function client_employee()
    {
        return $this->belongsTo('App\ClientEmployee');
    }

    // 1:多のリレーション
    public function annual_paid_holidays()
    {
        return $this->hasMany('App\AnnualPaidHoliday');
    }

    /*
		新規作成用ルール
	*/
    public function buildValidationRulesForCreate($data)
    {
        $rules = $this->rules;

        // 少数は整数部分が3桁以内。小数部分は2桁。か、確認するルール

        if (!empty($data['client_employee_id'])) {
            // 社員一人につき有給概要１レコード
            $rules['client_employee_id'][] = Rule::unique('annual_paid_holiday_summaries')
                ->where('client_employee_id', $data['client_employee_id']);
        }

        return $rules;
    }

    /*
		更新用ルール
	*/
    public function buildValidationRulesForUpdate($data)
    {
        $rules = $this->rules;

        // id必須 論理削除済みのものは更新しない
        $rules['id'] = ['bail', 'required', 'integer', 'min:1', 'exists:annual_paid_holiday_summaries,id,deleted_at,NULL'];

        if (!empty($data['id'])) {
            // 社員一人につき有給概要１レコード
            $rules['client_employee_id'][] = Rule::unique('annual_paid_holiday_summaries')
                ->ignore($data['id']);
        }

        return $rules;
    }

    /*
		@Baba 2020.10.20 追加
		論理削除復活用バリデーションルール取得
		@param Array $data
		@return Array $rules
	*/
    public function buildValidationRulesForRestore($data)
    {
        $rules = $this->rules;

        // id必須
        $rules['id'] = ['bail', 'required', 'integer', 'min:1', 'exists:annual_paid_holiday_summaries'];

        if (!empty($data['id'])) {
            // 社員一人につき有給概要１レコード
            $rules['client_employee_id'][] = Rule::unique('annual_paid_holiday_summaries')
                ->ignore($data['id']);
        }

        return $rules;
    }
}
