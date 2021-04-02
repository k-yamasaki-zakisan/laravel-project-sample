<?php
/*
	条件付きクエリ生成用トレイト
	@author YuKaneko
*/
namespace App\Traits;

trait ConditioningQueryBuilder {
	/*
		条件付きクエリ生成
		@param Array $conditions
		@param Illuminate\Database\Eloquent\Builder $Query
		@param bool $use_soft_deletes 論理削除用トレイト利用フラグ
		@param string $primary_key 主キー名
		@return $Query
	*/
	public function buildConditioningQuery(Array $conditions, \Illuminate\Database\Eloquent\Builder $Query, $use_soft_deletes, $primary_key) {
		// 出力カラム指定
		if ( !empty($conditions['fields']) && is_array($conditions['fields']) ) {
			$Query->select($conditions['fields']);
		}

		// 条件付与
		if ( !empty($conditions['where']) && is_array($conditions['where']) ) {
			foreach( $conditions['where'] as $idx => $values ) {
				if ( $values['op'] === 'LIKE' ) {
					$Query->where($values['key'], $values['op'], "%{$values['value']}%");
				} else {
					$Query->where($values['key'], $values['op'], $values['value']);
				}
			}
		}

		// 論理削除オプション
		if ( $use_soft_deletes && !empty($conditions['deleted']) ) {
			$deleted_options = config('constants.deleted_options');

			switch($conditions['deleted']) {
				case($deleted_options['WITH']):
					$Query->withTrashed();
					break;
				case($deleted_options['ONLY']):
					$Query->onlyTrashed();
					break;
				default:
					break;
			}
		}

		// 並び順
		if ( !empty($conditions['sorts']) && is_array($conditions['sorts']) ) {
			foreach( $conditions['sorts'] as $column => $value ) {
				$Query->orderBy($column, $value);
			}
		} else {
			// 規定値：主キー昇順
			$Query->orderBy($this->getPrimaryKey(), 'ASC');
		}

		return $Query;
	}
}
