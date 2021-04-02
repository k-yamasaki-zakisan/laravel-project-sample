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
use App\Models\Corporation;
use App\Models\Office;
// Utilities
use Illuminate\Support\Facades\Schema;
// Traits
use App\Traits\ConditioningQueryBuilder;
// Utilities
use Illuminate\Support\Arr;
use DB;
// Exceptions
use Illuminate\Validation\ValidationException;

class CorporationService extends ServiceBase
{

	use ConditioningQueryBuilder;

	protected $Corporation;
	protected $Office;

	public function __construct(
		Corporation $Corporation,
		Office $Office
	) {
		$this->Corporation = $Corporation;
		$this->Office = $Office;
	}

	/*
		主キーを指定して検索 存在しない場合は例外を投げる
		@param mixed $id
		@param array $columns
		@throw ModelNotFoundException
		@return App\Corporation
	*/
	public function findOrFail($id, array $columns = ['*'])
	{
		return $this->Corporation->findOrFail($id, $columns);
	}

	/*
    		クエリビルダ生成
    	@return Illuminate\Database\Eloquent\Builder
  	*/
	public function newQuery()
	{
		return $this->Corporation->newQuery();
	}

	/*
    		テーブルカラム取得
    		@param bool $use_cache
    		@return Array
  	*/
	public function getTableColumns($use_cache = true)
	{
		return $this->Corporation->getTableColumns($use_cache);
	}

	/*
   		主キー名取得
    		@return string
  	*/
	public function getPrimaryKey()
	{
		return $this->Corporation->getKeyName();
	}

	/*
    		論理削除用トレイト利用判定
    		@return bool
  	*/
	public function useSoftDeletes()
	{
		return $this->Corporation->useSoftDeletes();
	}

	/*
   	一覧取得用クエリ生成
    		@param Array $conditions
    		@return Illuminate\Database\Eloquent\Builder
  	*/
	public function buildQueryForList(array $conditions = [])
	{
		$Query = $this->buildConditioningQuery($conditions, $this->newQuery(), $this->useSoftDeletes(), $this->getPrimaryKey());

		if (!empty($conditions['with'])) {
			$Query->with($conditions['with']);
		}

		return $Query;
	}

	/*
		関連情報取得用
		@param mixed $id
		@throw ModelNotFoundException
		@return App\Corporation
	*/
	public function getWithRelatedOrFail($id, $contactRelationFlag)
	{
		if ($contactRelationFlag) {
			return $this->Corporation->with([
				'offices' => function ($query) {
					$query->orderBy('office_id', 'asc');
				},
				'offices.office_contacts',
			])->findOrFail($id);
		} else {
			return $this->Corporation->with([
				'offices' => function ($query) {
					$query->orderBy('office_id', 'asc');
				},
			])->findOrFail($id);
		};
	}

	/*
                関連情報取得用
                @param Array
                @return Array
        */
	public function buildSearchQueryForList(array $conditions = [])
	{
		if (empty($conditions['search'])) {
			//検索項目がない場合
			$corporations = $this->Corporation->orderBy('corporation_id')->limit(500)->get();
			$corporation_ids = $corporations->pluck('corporation_id');
			$offices = $this->Office->WhereIn('corporation_id', $corporation_ids)->where('head_office_flg', 'true')->get()->keyBy('corporation_id');
		} else {
			if (isset($conditions['search']['corporation']) && isset($conditions['search']['office'])) {
				//corporation.nameとoffice.addressで検索された場合
				$corporation_name = $conditions['search']['corporation']['name'];
				$office_address = $conditions['search']['office']['address'];
				$corporations = $this->Corporation->where('name', 'like', '%' . $corporation_name . '%')->orderBy('corporation_id')->limit(500)->get();
				$offices = $this->Office->where('address', 'like', '%' . $office_address . '%')->where('head_office_flg', 'true')->get()->keyBy('corporation_id');

				$result = collect();
				//他の検索結果とforeachの条件が違うのでここでデータを整形してリターン
				foreach ($corporations as $corporation) {
					//曖昧検索で住所がヒットしてない場合はスキップ
					if (empty($offices[$corporation['corporation_id']])) continue;

					$tmp_data = [
						'corporation_id' => $corporation['corporation_id'],
						'name' => $corporation['name'],
						'address' => $offices[$corporation['corporation_id']]['address'],
					];
					$result->push($tmp_data);
				}
				return $result;
			}

			if (isset($conditions['search']['corporation'])) {
				//corporation.nameで検索された場合
				$corporation_name = $conditions['search']['corporation']['name'];
				$corporations = $this->Corporation->where('name', 'like', '%' . $corporation_name . '%')->orderBy('corporation_id')->limit(500)->get();
				$corporation_ids = $corporations->pluck('corporation_id');
				$offices = $this->Office->WhereIn('corporation_id', $corporation_ids)->where('head_office_flg', 'true')->get()->keyBy('corporation_id');
			}

			if (isset($conditions['search']['office'])) {
				//office.addressで検索された場合
				$office_address = $conditions['search']['office']['address'];
				$offices = $this->Office->where('address', 'like', '%' . $office_address . '%')->where('head_office_flg', 'true')->limit(500)->get()->keyBy('corporation_id');
				$corporation_ids = $offices->pluck('corporation_id');
				$corporations = $this->Corporation->whereIn('corporation_id', $corporation_ids)->get();
			}
		}
		$result = collect();

		foreach ($corporations as $corporation) {
			if (!empty($offices[$corporation['corporation_id']])) {
				$corporation_id = $corporation['corporation_id'];
			} else {
				$corporation_id = Null;
			}
			$tmp_data = [
				'corporation_id' => $corporation['corporation_id'],
				'name' => $corporation['name'],
				'address' => $corporation_id != Null ? $offices[$corporation_id]['address'] : Null,
			];
			$result->push($tmp_data);
		}
		return $result;
	}

	/*
		登録
		@throws ValidationException RunTimeException
		@param Array $data 登録用データ
		@param int $system_id アクセス元システムID
	*/
	public function store(array $data)
	{
		$corporation = Arr::only($data, $this->getTableColumns());

		// 登録処理開始
		DB::beginTransaction();

		try {
			// 法人登録処理
			$Corporation = new Corporation($corporation);

			if (empty($Corporation->save())) throw new \RunTimeException("Failed to save Corporation.");
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

		return $Corporation->toArray();
	}

	/*
		更新
		@throws ValidationException RunTimeException
		@param Array $data 更新用データ
		@param int $system_id アクセス元システムID
	*/
	public function updateByLinkKey(array $data)
	{
		$head_office = Arr::pull($data, 'head_office') ?? [];
		$head_office_contacts = Arr::pull($head_office, 'head_office_contacts') ?? [];
		$corporation = Arr::only($data, $this->getTableColumns());

		// 登録処理開始
		DB::beginTransaction();

		try {
			// 法人更新処理
			try {
				$Corporation = $this->Corporation->where('link_key', $corporation['link_key'])->firstOrFail();

				//$Corporation->name = $corporation['name'];
				//$Corporation->phonetic = $corporation['phonetic'];
				//$Corporation->established_year = $corporation['established_year'];
				//$Corporation->established_month = $corporation['established_month'];
				//$Corporation->capital = $corporation['capital'];
				//$Corporation->representative = $corporation['representative'];
				//$Corporation->last_updated_system_id = $corporation['last_updated_system_id'];
				//$Corporation->last_updated_by = $corporation['last_updated_by'];

				if (empty($Corporation->update($corporation))) throw new \RunTimeException("Failed to update Corporation.");
			} catch (ValidationException $e) {
				// バリデーションエラー時のキー調整
				throw ValidationException::withMessages($this->addPrefixOnArrayKeys($e->errors(), 'corporation.'));
			}

			// 本社更新処理
			if (!empty($head_office)) {
				try {
					$HeadOffice = Office::updateOrCreate(
						//本社事務所が登録されているか確認検索
						['corporation_id' => $Corporation['corporation_id'], 'head_office_flg' => true],
						//更新データの挿入or新規データの作成
						[
							'corporation_id' => $Corporation['corporation_id'],
							'name' => $head_office['name'],
							'phonetic' => $head_office['phonetic'],
							'zip_code1' => $head_office['zip_code1'],
							'zip_code2' => $head_office['zip_code2'],
							'prefecture_id' => $head_office['prefecture_id'],
							'city' => $head_office['city'],
							'town' => $head_office['town'],
							'street' => $head_office['street'],
							'building' => $head_office['building'],
							'head_office_flg' => $head_office['head_office_flg'],
							'last_updated_system_id' => $head_office['last_updated_system_id'],
							'last_updated_by' => $head_office['last_updated_by'],
						]
					);

					if (empty($HeadOffice)) throw new \RunTimeException("Failed to update Head office.");
				} catch (ValidationException $e) {
					// バリデーションエラー時のキー調整
					throw ValidationException::withMessages($this->addPrefixOnArrayKeys($e->errors(), 'head_office.'));
				}
			}

			// 本社連絡先登録処理
			if (!empty($head_office_contacts) && !empty($HeadOffice)) {
				$OfficeContacts = collect();

				foreach ($head_office_contacts as $key => $head_office_contact) {
					try {
						$OfficeContact = OfficeContact::updateOrCreate(
							//同じ種類の連絡先が登録されているか検索
							['office_id' => $HeadOffice['office_id'], 'contact_type_id' => $head_office_contact['contact_type_id']],
							//更新データの挿入or新規データの作成
							[
								'office_id' => $HeadOffice['office_id'],
								'contact_type_id' => $head_office_contact['contact_type_id'],
								'value' => $head_office_contact['value'],
								'last_updated_system_id' => $head_office_contact['last_updated_system_id'],
								'last_updated_by' => $head_office_contact['last_updated_by'],
							]
						);

						if (empty($OfficeContact)) throw new \RunTimeException("Failed to update Head office contact.[{$key}]");

						$OfficeContacts->push($OfficeContact);
					} catch (ValidationException $e) {
						// バリデーションエラー時のキー調整
						throw ValidationException::withMessages($this->addPrefixOnArrayKeys($e->errors(), "head_office_contacts.{$key}."));
					}
				}
			} else if (empty($head_office_contacts) && !empty($HeadOffice)) {
				//連絡先がない場合は削除
				OfficeContact::where('office_id', $HeadOffice['office_id'])->delete();
			}

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

		// 戻り値データ整形
		$result = $Corporation->toArray();

		if (!empty($HeadOffice)) $result['head_office'] = $HeadOffice->toArray();
		if (!empty($OfficeContacts) && !$OfficeContacts->isEmpty()) $result['head_office']['head_office_contacts'] = $OfficeContacts->toArray();

		return $result;
	}
}
