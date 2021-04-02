<?php

namespace App;

use Illuminate\Validation\Rule;

class ExpenseNotificationDestination extends ModelBase {

	protected $fillable = [
		'expense_group_setting_id',
		'email',
	];

	protected $rules = [
		'expense_group_setting_id' => ['bail', 'required', 'integer', 'min:1'],
		'email' => ['bail', 'email', 'max:255'],
	];

	public function __construct(array $attributes = []){
		parent::__construct($attributes);

		// 経費所属グループ設定IDは論理削除されていないものが存在すること
		$this->rules['expense_group_setting_id'][] = Rule::exists('expense_group_settings', 'id')->whereNull('deleted_at');
	}

	// belongsTo
	public function expense_group_setting() {
		return $this->belongsTo('App\ExpenseGroupSetting');
	}

	// expense_group_setting_idごとにemailは一意

	/*
		新規作成用ルール
	*/
	public function buildValidationRulesForCreate($data) {
		$rules = $this->rules;

		// emalは経費所属グループ設定単位でユニークであること
		$rules['email'][] = Rule::unique('expense_notification_destinations')
			->where('expense_group_setting_id', $data['expense_group_setting_id']);

		return $rules;
	}

    protected static function boot() {
        parent::boot();

        self::creating(function($model) {
			$data = $model->toArray();
			$validator = validator($data, $model->buildValidationRulesForCreate($data));

			if ( $validator->fails() ) {
				logger()->error("Failed to validate in " . __FILE__ . " at " . __LINE__ . PHP_EOL . print_r($data, true));
				return false;
			}
        });
    }
}
