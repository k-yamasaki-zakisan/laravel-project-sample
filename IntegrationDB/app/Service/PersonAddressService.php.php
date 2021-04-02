<?php
/***
 * 人の住所関連サービス
 *
 * @author YuKaneko
 */

namespace App\Services;

// Services
use App\Services\ServiceBase;
// Models
use App\Models\PersonAddress;
// Utilities
use Illuminate\Support\Facades\Schema;
// Traits
use App\Traits\ConditioningQueryBuilder;

class PersonAddressService extends ServiceBase {

	use ConditioningQueryBuilder;

	protected $PersonAddress;

	public function __construct(
		PersonAddress $PersonAddress
	) {
		$this->PersonAddress = $PersonAddress;
	}

	/*
		主キーを指定して検索 存在しない場合は例外を投げる
		@param mixed $id
		@param array $columns
		@throw ModelNotFoundException
		@return App\PersonAddress
	*/
	public function findOrFail($id, Array $columns = ['*']) {
		return $this->PersonAddress->findOrFail($id, $columns);
	}

  /*
    クエリビルダ生成
    @return Illuminate\Database\Eloquent\Builder
  */
  public function newQuery() {
    return $this->PersonAddress->newQuery();
  }

  /*
    テーブルカラム取得
    @param bool $use_cache
    @return Array
  */
  public function getTableColumns($use_cache = true) {
    return $this->PersonAddress->getTableColumns($use_cache);
  }

 /* 
    主キー名取得
    @return string
  */
  public function getPrimaryKey() {
    return $this->PersonAddress->getKeyName();
  }

  /*
    論理削除用トレイト利用判定
    @return bool
  */
  public function useSoftDeletes() {
    return $this->PersonAddress->useSoftDeletes();
  }

  /*
    一覧取得用クエリ生成
    @param Array $conditions
    @return Illuminate\Database\Eloquent\Builder
  */
  public function buildQueryForList(Array $conditions = []) {
    return $this->buildConditioningQuery($conditions, $this->newQuery(), $this->useSoftDeletes(), $this->getPrimaryKey());
  }

}
