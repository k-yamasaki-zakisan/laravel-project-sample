<?php

namespace App\Http\Requests\API\Employee;

use App\Http\Requests\API\ListRequestBase;
use Illuminate\Support\Arr;
// Services
use App\Services\EmployeeService;

class EmployeeListPostRequest extends ListRequestBase {

	protected $rules = [];
	protected $EmployeeService;

	public function __construct(
		EmployeeService $EmployeeService
	) {
		$this->EmployeeService = $EmployeeService;
	}

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
		return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
		$columns = $this->EmployeeService->getTableColumns();

		$this->_addFieldsRules($columns);
		$this->_addWhereRules($columns);
		$this->_addWhereValueRules($columns);
		$this->_addSortsRules($columns);
		if ( $this->EmployeeService->useSoftDeletes() ) $this->_addDeletedRules();
		$this->_addWithRules($this->getEnableRelations());
		$this->_addLimitRules();

		return parent::rules();
    }

	/*
		各Where条件の値にルールを付与
		@implement
		@param Array $column
		@return void
	*/
	protected function _addWhereValueRules() {
		if ( empty($this->where) || !is_array($this->where) ) return;

		$natural_bigint_rules = $this->_getNaturalBigIntegerRules();
		$natural_smallint_rules = $this->_getNaturalSmallIntegerRules();
		// Null許容の演算子取得
		$nullable_operands = collect($this->_getNullableOperands())->pluck('alias', 'alias');

		foreach( $this->where as $idx => $values ) {
			// key, opが空の場合は無視（あとのValidationではじかれる）
			if ( empty($values['key']) || empty($values['op']) ) continue;

			$tmp_rules = [];

			switch($values['key']) {
				case('employee_id'):
				case('corporation_id'):
				case('person_id'):
				case('last_updated_by'):
					$tmp_rules = array_merge($tmp_rules, $natural_bigint_rules);
					break;
				case('last_updated_system_id'):
					$tmp_rules = array_merge($tmp_rules, $natural_smallint_rules);
					break;
				case('birthday'):
				case('hire_date'):
				case('retirement_date'):
				case('deleted_at'):
				case('created_at'):
				case('updated_at'):
					$tmp_rules = array_merge($tmp_rules, ['date']);
					break;
				default:
					$tmp_rules = array_merge($tmp_rules, ['string']);
					break;

			}

			// 演算子の種類によってnull許容
			if ( !empty($nullable_operands[$values['op']]) ) array_unshift($tmp_rules, 'nullable');

			array_unshift($tmp_rules, 'bail');
			$this->rules["where.{$idx}.value"] = $tmp_rules;
		}
	}

	/*
		利用可能リレーション一覧取得
		@return Array
	*/
	protected function getEnableRelations() {
		return [
			'person',
			'corporation',
		];
	}
}