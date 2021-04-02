<?php
/***
 * 事務所関連サービス
 *
 * @author umemura
 */

namespace App\Services;

// Services
use App\Services\ServiceBase;
// Models
use App\Models\Office;
use App\Models\Employee;
// Utilities
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;
use DB;
// Traits
use App\Traits\ConditioningQueryBuilder;

class OfficeService extends ServiceBase {

	use ConditioningQueryBuilder;

	protected $Office;
	protected $Employee;

	public function __construct(
		Office $Office,
		Employee $Employee
	) {
		$this->Office = $Office;
		$this->Employee = $Employee;
	}

	/*
		主キーを指定して検索 存在しない場合は例外を投げる
		@param mixed $id
		@param array $columns
		@throw ModelNotFoundException
		@return App\Office
	*/
	public function findOrFail($id, Array $columns = ['*']) {
		return $this->Office->findOrFail($id, $columns);
	}

  	/*
   		クエリビルダ生成
    		@return Illuminate\Database\Eloquent\Builder
  	*/
  	public function newQuery() {
    		return $this->Office->newQuery();
  	}

  	/*
    		テーブルカラム取得
    		@param bool $use_cache
    		@return Array
  	*/
  	public function getTableColumns($use_cache = true) {
    		return $this->Office->getTableColumns($use_cache);
  	}

  	/*
    		主キー名取得
    		@return string
  	*/
  	public function getPrimaryKey() {
    		return $this->Office->getKeyName();
  	}

  	/*
    		論理削除用トレイト利用判定
    		@return bool
  	*/
  	public function useSoftDeletes() {
    		return $this->Office->useSoftDeletes();
  	}

	/*
		一覧取得用クエリ生成
		@param Array $conditions
		@return Illuminate\Database\Eloquent\Builder
	*/
  	public function buildQueryForList(Array $conditions = []) {
    		return $this->buildConditioningQuery($conditions, $this->newQuery(), $this->useSoftDeletes(), $this->getPrimaryKey());
  	}

	/*
		関連情報取得用
		@param mixed $id
		@throw ModelNotFoundException
		@return App\Person
	*/
	public function getWithRelatedOrFail($id) {
		return $this->Office->with([
			'corporation' => function($query) {
				$query->orderBy('corporation_id', 'asc');
			},
		])->findOrFail($id);
	}

	/*
		登録
		@throws ValidationException RunTimeException
		@param Array $data 登録用データ
		@param int $system_id アクセス元システムID
	*/
	public function store(Array $data) {
		$office = Arr::only($data['office'], $this->getTableColumns());

		// 登録処理開始
		DB::beginTransaction();

		try {
			$Office = new Office($office);

			if ( empty($Office->save()) ) throw new \RunTimeException("Failed to save Office.");
			DB::commit();
		} catch( ValidationException $e ) {
			// バリデーションエラー時はそのまま投げる
			DB::rollBack();
			throw $e;
		} catch( \Exception $e ) {
			DB::rollBack();
			logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
			throw $e;
		}

		// 戻り値データ整形
		$result = $Office->toArray();

		return $result;
	}

	/*
                更新
                @throws ValidationException RunTimeException
                @param Array $data 更新用データ
                @param int $system_id アクセス元システムID
        */
	public function update(Array $data) {
		$office = Arr::only($data, $this->getTableColumns());

		// 登録処理開始
                DB::beginTransaction();

                try {
			$Office = $this->Office->where('link_key', $office['link_key'])->first();

			$Office->name = $office['name'];
			$Office->phonetic = $office['phonetic'];
			$Office->zip_code1 = $office['zip_code1'];
			$Office->zip_code2 = $office['zip_code2'];
			$Office->prefecture_id = $office['prefecture_id'];
			$Office->city = $office['city'];
			$Office->town = $office['town'];
			$Office->street = $office['street'];
			$Office->building = $office['building'];
			$Office->last_updated_system_id = $office['last_updated_system_id'];
			$Office->last_updated_by = $office['last_updated_by'];

			if ( empty($Office->save()) ) throw new \RunTimeException("Failed to update Office.");
                        DB::commit();
                } catch( ValidationException $e ) {
                        // バリデーションエラー時はそのまま投げる
                        DB::rollBack();
                        throw $e;
                } catch( \Exception $e ) {
                        DB::rollBack();
                        logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
                        throw $e;
                }

                // 戻り値データ整形
                $result = $Office->toArray();

                return $result;
	}

	/*
                削除
                @throws ValidationException RunTimeException
                @param Array $data 削除用データ
                @param int $system_id アクセス元システムID
        */
	public function delete(Array $data) {
		$office = Arr::only($data, $this->getTableColumns());

		// 登録処理開始
                DB::beginTransaction();

		try {
			$Office = $this->Office->where('link_key', $office['link_key'])->first();

			$Office->last_updated_system_id = $office['last_updated_system_id'];
			$Office->last_updated_by = $office['last_updated_by'];

			//最終更新者を入力後削除
			if ( empty($Office->save()) ) throw new \RunTimeException("Failed to update_last_updated_person Office.");
			if ( empty($Office->delete()) ) throw new \RunTimeException("Failed to delete Office.");
			DB::commit();
		} catch( \Exception $e ) {
                        DB::rollBack();
                        logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
                        throw $e;
                }

		return true;
	}

}