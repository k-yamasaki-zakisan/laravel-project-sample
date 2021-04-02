<?php
/***
 * 法人関連サービス
 *
 * @author YuKaneko
 */

namespace App\Services;

// Services
use App\Services\ServiceBase;
// Models
use App\Models\PersonLicense;
// Utilities
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;
use DB;
// Traits
use App\Traits\ConditioningQueryBuilder;
// Carbon
use Carbon\Carbon;

class PersonLicenseService extends ServiceBase {

	use ConditioningQueryBuilder;

	protected $PersonLicense;

	public function __construct(
		PersonLicense $PersonLicense
	) {
		$this->PersonLicense = $PersonLicense;
	}

	/*
		主キーを指定して検索 存在しない場合は例外を投げる
		@param mixed $id
		@param array $columns
		@throw ModelNotFoundException
		@return App\PersonLicense
	*/
	public function findOrFail($id, Array $columns = ['*']) {
		return $this->PersonLicense->findOrFail($id, $columns);
	}

  	/*
    		クエリビルダ生成
    		@return Illuminate\Database\Eloquent\Builder
  	*/
  	public function newQuery() {
    		return $this->PersonLicense->newQuery();
  	}

  	/*
    		テーブルカラム取得
    		@param bool $use_cache
    		@return Array
  	*/
  	public function getTableColumns($use_cache = true) {
    		return $this->PersonLicense->getTableColumns($use_cache);
  	}

 	/*
    		主キー名取得
    		@return string
  	*/
  	public function getPrimaryKey() {
    		return $this->PersonLicense->getKeyName();
  	}

  	/*
    		論理削除用トレイト利用判定
    		@return bool
  	*/
  	public function useSoftDeletes() {
    		return $this->PersonLicense->useSoftDeletes();
  	}

  	/*
    		一覧取得用クエリ生成
    		@param Array $conditions
    		@return Illuminate\Database\Eloquent\Builder
  	*/
  	public function buildQueryForList(Array $conditions = []) {
    		return $this->buildConditioningQuery($conditions, $this->newQuery(), $this->useSoftDeletes(), $this->getPrimaryKey());
  	}

	public function store(Array $data) {
		$person_license = Arr::only($data, $this->getTableColumns());

		// 登録処理開始
		DB::beginTransaction();

		try {
			$PersonLicense = new PersonLicense($person_license);
			if ( empty($PersonLicense->save()) ) throw new \RunTimeException("Failed to save PersonLicense.");

			DB::commit();
		} catch( ValidationException $e ) {
			throw ValidationException::withMessages($this->addPrefixOnArrayKeys($e->errors(), 'person_license.'));
			// バリデーションエラー時はそのまま投げる
			DB::rollBack();
			throw $e;
		} catch( \Exception $e ) {
			DB::rollBack();
			logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
			throw $e;
		}

		return $PersonLicense;
	}

	public function deleteByLinkKey(Array $data) {
		$person_license = Arr::only($data, $this->getTableColumns());

		// 削除処理開始
                DB::beginTransaction();

		try {
			$PersonLicense = $this->PersonLicense->where('link_key', $person_license['link_key'])->firstOrFail();

			$PersonLicense->last_updated_system_id = $person_license['last_updated_system_id'];
			$PersonLicense->last_updated_by = $person_license['last_updated_by'];
			$PersonLicense->deleted_at = Carbon::now()->format('Y-m-d H:i:s');

			if ( empty($PersonLicense->save()) ) throw new \RunTimeException("Failed to delete PersonLicense-.");
			DB::commit();
		} catch( \Exception $e ) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            throw $e;
        }

		return true;
	}

	public function updateByLinkKey(Array $data) {
		$person_license = Arr::only($data, $this->getTableColumns());

		// 更新処理開始
                DB::beginTransaction();

		try {
			$PersonLicense = $this->PersonLicense->where('link_key', $person_license['link_key'])->firstOrFail();

			if ( empty($PersonLicense->update($person_license)) ) throw new \RunTimeException("Failed to update PersonLicense-.");
			DB::commit();
		} catch( \Exception $e ) {
                        DB::rollBack();
                        logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
                        throw $e;
                }

                return $PersonLicense;
	}
}