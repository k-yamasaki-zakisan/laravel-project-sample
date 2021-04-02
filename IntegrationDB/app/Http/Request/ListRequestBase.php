<?php

namespace App\Http\Requests\API;

use App\Http\Requests\API\ApiBaseRequest;
use Illuminate\Support\Arr;

abstract class ListRequestBase extends ApiBaseRequest
{
	/*
		取得カラム用ルール追加
		@param Array $columns
		@return void
	*/
	protected function _addFieldsRules(Array $columns) {
		$this->_addRulesOn($this->rules, [
			'fields' => ['bail', 'nullable', 'array'],
			'fields.*' => ['bail', 'in:' . join(',', $columns)]
		]);
	}

	/*
		検索条件用ルール追加
		@param Array $columns
		@return void
	*/
	protected function _addWhereRules(Array $columns) {
		$this->_addRulesOn($this->rules, [
			'where' => ['bail', 'nullable', 'array'],
			'where.*' => ['bail', 'array'],
			'where.*.key' => ['bail', 'string', 'required', 'in:' . join(',', $columns)],
			'where.*.op' => ['bail', 'string', 'required', 'in:' . join(',', data_get($this->_getOperands(), '*.alias'))],
			'where.*.value' => ['bail', 'nullable'],
		]);
	}

	abstract protected function _addWhereValueRules();

	/*
		ソート順用ルール追加
		@param Array $columns
		@return void
	*/
	protected function _addSortsRules(Array $columns) {
		$this->_addRulesOn($this->rules, [
			'sorts' => ['bail', 'nullable', 'array'],
			'sorts.*' => [
				'bail',
				'key_in_strict:' . join(',', $columns),
				'in:' . join(',', $this->_getSortDirections())
			]
		]);
	}

	/*
		論理削除済みオプション用ルール追加
		@return void
	*/
	protected function _addDeletedRules() {
		$this->_addRulesOn($this->rules, [
			'deleted' => ['bail', 'nullable', 'in:' . join(',', $this->_getDeletedOptionValues())]
		]);
	}

	/*
		取得件数用ルール追加
		@return void
	*/
	protected function _addLimitRules() {
		$this->_addRulesOn($this->rules, [
			'limit' => ['bail', 'nullable', 'integer', 'min:1']
		]);
	}

	/*
		検索用の演算子配列取得
		@return Array
	*/
	protected function _getOperands() {
		return config('constants.operands');
	}

	/*
		Nulll許容の検索用演算子配列
		@return Array
	*/
	protected function _getNullableOperands() {
		return Arr::only($this->_getOperands(), ['EQUAL', 'NOT_EQUAL']);
	}

	/*
		ソート方向配列取得
		@return Array
	*/
	protected function _getSortDirections() {
		return config('constants.sort_directions');
	}

	/*
		論理削除済みレコード検索オプション値
		@return Array
	*/
	protected function _getDeletedOptionValues() {
		return config('constants.deleted_options');
	}

	/*
		補正済みデータ取得
		@return Array
	*/
	public function getCorrectedData() {
		$validated = $this->validated();

		// 取得件数未入力の場合に規定値100件
		$validated['limit'] = $validated['limit'] ?? 100;

		if ( !empty($validated['where']) ) {
			// 演算子の置換処理
			$operands = collect($this->_getOperands())->pluck('operand', 'alias');

			foreach( $validated['where'] as $idx => $values) {
				if ( isset($operands[$values['op']]) ) $validated['where'][$idx]['op'] = $operands[$values['op']];
				else throw new \UnexpectedValueException("Unknown operand {$values['op']}.");
			}
		}

		return $validated;
	}
}
