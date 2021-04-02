<?php
namespace App\Libraries\IntegratedAPI;

class SearchCondition {

	protected $_fields;
	protected $_where;
	protected $_sorts;
	protected $_limit;
	protected $_deleted;
	
	/*
		配列化
		@return Array
	*/
	public function toArray() {
		$tmp_array = [];

		if ( !empty($this->_fields) ) $tmp_array['fields'] = $this->_fields;
		if ( !empty($this->_where) ) $tmp_array['where'] = $this->_where;
		if ( !empty($this->_sorts) ) $tmp_array['sorts'] = $this->_sorts;
		if ( isset($this->_limit) ) $tmp_array['limit'] = $this->_limit;
		if ( isset($this->_deleted) ) $tmp_array['deleted'] = $this->_deleted;

		return $tmp_array;
	}

	/*
		取得フィールド追加
		@params Array $fields
		@return $this
	*/
	public function addFields(Array $fields) {
		if ( !isset($this->_fields) ) $this->_fields = [];

		// 既存のものとマージして同じ値が含まれないようにする
		$tmp_array = array_merge($this->_fields, $fields);
		$tmp_array = array_unique($tmp_array);
		$tmp_array = array_values($tmp_array);

		$this->_fields = $tmp_array;

		return $this;
	}

	/*
		検索条件(=)追加
		@params string $key
		@params mix
		@return $this
	*/
	public function addWhereEqual($key, $value) {
		return $this->_addWhere($key, 'eq', $value);
	}

	/*
		検索条件(<)追加
		@params string $key
		@params mix
		@return $this
	*/
	public function addWhereLessThan($key, $value) {
		return $this->_addWhere($key, 'lt', $value);
	}

	/*
		検索条件(>)追加
		@params string $key
		@params mix
		@return $this
	*/
	public function addWhereGraterThan($key, $value) {
		return $this->_addWhere($key, 'gt', $value);
	}

	/*
		検索条件(<=)追加
		@params string $key
		@params mix
		@return $this
	*/
	public function addWhereLessThanEqual($key, $value) {
		return $this->_addWhere($key, 'lte', $value);
	}

	/*
		検索条件(>=)追加
		@params string $key
		@params mix
		@return $this
	*/
	public function addWhereGraterThanEqual($key, $value) {
		return $this->_addWhere($key, 'gte', $value);
	}

	/*
		検索条件(<>)追加
		@params string $key
		@params mix
		@return $this
	*/
	public function addWhereNotEqual($key, $value) {
		return $this->_addWhere($key, 'ne', $value);
	}

	/*
		検索条件(LIKE)追加
		@params string $key
		@params mix
		@return $this
	*/
	public function addWhereLike($key, $value) {
		return $this->_addWhere($key, 'lk', $value);
	}

	/*
		検索条件追加
		@params string $key
		@params mix
		@return $this
	*/
	protected function _addWhere($key, $operand, $value) {
		if ( !isset($this->_where) ) $this->_where = [];

		$this->_where[] = [
			'key' => $key,
			'op' => $operand,
			'value' => $value,
		];

		return $this;
	}

	/*
		昇順ソート追加
		@params string $key
		@return $this
	*/
	public function addSortAsc($key) {
		return $this->_addSort($key, 'asc');
	}

	/*
		降順ソート追加
		@params string $key
		@return $this
	*/
	public function addSortDesc($key) {
		return $this->_addSort($key, 'desc');
	}

	/*
		ソート順追加
		@params string $key
		@params string $direction
		@return $this
	*/
	protected function _addSort($key, $direction) {
		if ( !isset($this->_sorts) ) $this->_sorts = [];

		$this->_sorts[$key] = $direction;

		return $this;
	}

	/*
		取得件数設定
		@params int $limit
		@return $this
	*/
	public function setLimit($limit) {
		$this->_limit = $limit;

		return $this;
	}

	/*
		論理削除済みを含めない
		@return $this
	*/
	public function excludeDeleted() {
		$this->_deleted = 'none';

		return $this;
	}

	/*
		論理削除済みを含める
		@return $this
	*/
	public function withDeleted() {
		$this->_deleted = 'with';

		return $this;
	}

	/*
		論理削除済みのみ
		@return $this
	*/
	public function onlyDeleted() {
		$this->_deleted = 'only';

		return $this;
	}
}

if ( !emply($conditions['where'])) {
    logger('miteru');
};
