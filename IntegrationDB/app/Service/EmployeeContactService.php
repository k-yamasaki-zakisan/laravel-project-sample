<?php

/***
 * 人の連絡先関連サービス
 *
 * @author YuKaneko
 */

namespace App\Services;

// Services
use App\Services\ServiceBase;
// Models
use App\Models\EmployeeContact;
// Utilities
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;
use DB;
// Traits
use App\Traits\ConditioningQueryBuilder;
// Carbon
use Carbon\Carbon;

class EmployeeContactService extends ServiceBase
{

	use ConditioningQueryBuilder;

	protected $EmployeeContact;

	public function __construct(
		EmployeeContact $EmployeeContact
	) {
		$this->EmployeeContact = $EmployeeContact;
	}

	/*
		主キーを指定して検索 存在しない場合は例外を投げる
		@param mixed $id
		@param array $columns
		@throw ModelNotFoundException
		@return App\PersonContact
	*/
	public function findOrFail($id, array $columns = ['*'])
	{
		return $this->EmployeeContact->findOrFail($id, $columns);
	}

	/*
   		 クエリビルダ生成
    		@return Illuminate\Database\Eloquent\Builder
  	*/
	public function newQuery()
	{
		return $this->EmployeeContact->newQuery();
	}

	/*
    		テーブルカラム取得
    		@param bool $use_cache
    		@return Array
  	*/
	public function getTableColumns($use_cache = true)
	{
		return $this->EmployeeContact->getTableColumns($use_cache);
	}

	/*
    		主キー名取得
    		@return string
  	*/
	public function getPrimaryKey()
	{
		return $this->EmployeeContact->getKeyName();
	}

	/*
    		論理削除用トレイト利用判定
    		@return bool
  	*/
	public function useSoftDeletes()
	{
		return $this->EmployeeContact->useSoftDeletes();
	}

	/*
   		一覧取得用クエリ生成
    		@param Array $conditions
    		@return Illuminate\Database\Eloquent\Builder
  	*/
	public function buildQueryForList(array $conditions = [])
	{
		return $this->buildConditioningQuery($conditions, $this->newQuery(), $this->useSoftDeletes(), $this->getPrimaryKey());
	}

	/*
                登録
                @param Array $data
                @return Array
        */
	public function store(array $data)
	{
		$employee_contact = Arr::only($data, $this->getTableColumns());

		// 登録処理開始
		DB::beginTransaction();

		try {
			$EmployeeContact = new EmployeeContact($employee_contact);

			if (empty($EmployeeContact->save())) throw new \RunTimeException("Failed to save EmployeeContact.");
			DB::commit();
		} catch (ValidationException $e) {
			// バリデーションエラー時はそのまま投げる
			DB::rollBack();
			throw $e;
		} catch (\Exception $e) {
			DB::rollBack();
			logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
			throw $e;
		}

		return $EmployeeContact->toArray();
	}

	/*
                更新
                @param Array $data
                @return Array
        */
	public function updateByLinkKey(array $data)
	{
		$employee_contact = Arr::only($data, $this->getTableColumns());

		// 更新処理開始
		DB::beginTransaction();

		try {
			$EmployeeContact = $this->EmployeeContact->where('link_key', $employee_contact['link_key'])->firstOrFail();

			if (empty($EmployeeContact->update($employee_contact))) throw new \RunTimeException("Failed to update EmployeeContact.");
			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
			throw $e;
		}

		return $EmployeeContact->toArray();
	}

	/*
                削除
                @param Array $data
                @return bool
        */
	public function deleteByLinkKey(array $data)
	{
		$employee_contact = Arr::only($data, $this->getTableColumns());

		// 削除処理開始
		DB::beginTransaction();

		try {
			$EmployeeContact = $this->EmployeeContact->where('link_key', $employee_contact['link_key'])->firstOrFail();

			$EmployeeContact->last_updated_system_id = $employee_contact['last_updated_system_id'];
			$EmployeeContact->last_updated_by = $employee_contact['last_updated_by'];
			$EmployeeContact->deleted_at = Carbon::now()->format('Y-m-d H:i:s');

			if (empty($EmployeeContact->save())) throw new \RunTimeException("Failed to delete EmployeeContact.");
			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
			throw $e;
		}

		return true;
	}

	/*
        論理削除の解除
        @param Array $data
        @return なし
    */
	public function restoreByLinkKey(array $data)
	{
		$employee_contact = Arr::only($data, $this->getTableColumns());

		// 削除解除処理開始
		DB::beginTransaction();

		try {
			$EmployeeContact = $this->EmployeeContact->onlyTrashed()->where('link_key', $employee_contact['link_key'])->firstOrFail();

			if (empty($EmployeeContact->restore())) throw new \RunTimeException("Failed to restore EmployeeContact.");
			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
			throw $e;
		}

		return $EmployeeContact->toArray();
	}
}

// 住所
$update_employee_address = $this->EmployeeAddressModel
	->where('employee_id', $Employee['employee_id'])
	->orderBy('number', 'desc')
	->first();
