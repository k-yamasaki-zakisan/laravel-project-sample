<?php
/***
 * 人関連サービス
 *
 * @author YuKaneko
 */

namespace App\Services;

// Services
use App\Services\ServiceBase;
// Models
use App\Models\Person;
use App\Models\Employee;
use App\Models\Corporation;
use App\Models\PersonContact;
// Utilities
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;
use DB;
// Traits
use App\Traits\ConditioningQueryBuilder;

class PersonService extends ServiceBase {

	use ConditioningQueryBuilder;

	protected $Person;
	protected $Employee;
	protected $Corporation;

	public function __construct(
		Person $Person,
		Employee $Employee,
		Corporation $Corporation
	) {
		$this->Person = $Person;
		$this->Employee = $Employee;
		$this->Corporation = $Corporation;
	}

	/*
		モデル生成
@paramArray $data
@returnApp\Person
	*/
//	public function newPerson(Array $data = []) {
//		return $this->Person->newInstance($data);
//	}

	/*
		クエリビルダ生成
@returnIlluminate\Database\Eloquent\Builder
	*/
	public function newQuery() {
		return $this->Person->newQuery();
	}

	/*
		テーブルカラム取得
@parambool $use_cache
@returnArray
	*/
	public function getTableColumns($use_cache = true) {
		return $this->Person->getTableColumns($use_cache);
	}

	/*
		主キー名取得
@returnstring
	*/
	public function getPrimaryKey() {
		return $this->Person->getKeyName();
	}

	/*
		論理削除用トレイト利用判定
@returnbool
	*/
	public function useSoftDeletes() {
		return $this->Person->useSoftDeletes();
	}

	/*
		主キーを指定して検索 存在しない場合は例外を投げる
@parammixed $id
@paramarray $columns
@throwModelNotFoundException
@returnApp\Person
	*/
	public function findOrFail($data, Array $columns = ['*']) {
		if ( !empty($data['person_id']) ) {
			return $this->Person->findOrFail($data['person_id'], $columns);
		} elseif ( !empty($data['link_key']) ) {
			return $this->Person->where('link_key', $data['link_key'])->firstOrFail();
		}
	}

	/*
		一覧取得用クエリ生成
@paramArray $conditions
@returnIlluminate\Database\Eloquent\Builder
	*/
	public function buildQueryForList(Array $conditions = []) {
		return $this->buildConditioningQuery($conditions, $this->newQuery(), $this->useSoftDeletes(), $this->getPrimaryKey());
	}

	/*
		関連情報取得用
@parammixed $id
@throwModelNotFoundException
@returnApp\Person
	*/
	public function getWithRelatedOrFail($data) {
		$query = Person::query();

		if ( !empty($data['person_id']) ) {
			$query->where('person_id', $data['person_id']);
		} elseif ( !empty($data['link_key']) ) {
			$query->where('link_key', $data['link_key']);
		}

		return $query->with([
				'person_addresses' => function($query) {
					$query->orderBy('person_address_id', 'asc');
				},
				'educational_backgrounds' => function($query) {
					$query->orderBy('educational_background_id', 'asc');
				},
				'person_job_careers' => function($query) {
					$query->orderBy('person_job_career_id', 'asc');
				},
				'person_contacts' => function($query) {
					$query->orderBy('person_contact_id', 'asc');
				},
				'person_licenses' => function($query) {
					$query->with('license');
					$query->orderBy('person_license_id', 'asc');
				},
			])->firstOrFail();
	}

	public function search($request = array()) {

		$link_key = $request->link_key; // リンクキー
		$person_id = $request->person_id; // person_id

		$query = Person::query();

		$query->where('person_id', $person_id);

		return $query;

	}

	public function buildSearchQueryForList(Array $conditions = []){
		try {

			if ( empty($conditions['search'])) {
				//検索項目がない場合
                        	$persons = $this->Person->orderBy('person_id')->limit(500)->get();
				$person_ids = $persons->pluck('person_id' );
                        	$Employees = $this->Employee->whereIn('person_id', $person_ids)->get()->keyBy('person_id');
                        	$corporation_ids = $Employees->pluck('corporation_id', 'corporation_id' );
                        	$corporations = $this->Corporation->whereIn('corporation_id', $corporation_ids)->get()->keyBy('corporation_id');
			} else {

				if ( isset($conditions['search']['person']) && isset($conditions['search']['corporation']) ) {
					//person.full_nameとcorporation.nameで検索された場合
					$full_name = $conditions['search']['person']['full_name'];
					$corporation_name = $conditions['search']['corporation']['name'];
					$persons = $this->Person->where('full_name', 'like', '%'.$full_name.'%')->orderBy('person_id')->limit(500)->get();
					$person_ids = $persons->pluck('person_id');
					$Employees = $this->Employee->whereIn('person_id', $person_ids)->get()->keyBy('person_id');
					$corporation_ids = $Employees->pluck('corporation_id');
                        		$corporations = $this->Corporation->whereIn('corporation_id', $corporation_ids)->where('name', 'like', '%'.$corporation_name.'%')->get()->keyBy('corporation_id');
					$result = collect();
					//他の検索結果とforeachの条件が違うのでここでデータを整形してリターン
					foreach ($persons AS $person) {
						if ( empty($Employees[$person['person_id']]) ) continue;
						$corporation_id = $Employees[$person['person_id']]['corporation_id'];
						//曖昧検索で企業がヒットしてない場合はスキップ
						if ( empty($corporations[$corporation_id]) ) continue;
						$tmp_data = [
                                        		'person_id' => $person['person_id'],
                                        		'full_name' => $person['full_name'],
                                        		'corporation_name' => $corporations[$corporation_id]['name'],
                                        		'birthday' => $person['birthday'] ?? null,
                                		];
						$result->push($tmp_data);
					}

					return $result;
				}

				if ( isset($conditions['search']['person']) ) {
					//person.full_nameのみで検索された場合
                                	$full_name = $conditions['search']['person']['full_name'];
					$persons = $this->Person->where('full_name', 'like', '%'.$full_name.'%')->orderBy('person_id')->limit(500)->get();
					$person_ids = $persons->pluck('person_id');
                                	$Employees = $this->Employee->whereIn('person_id', $person_ids)->get()->keyBy('person_id');
                                	$corporation_ids = $Employees->pluck('corporation_id', 'corporation_id' );
                                	$corporations = $this->Corporation->whereIn('corporation_id', $corporation_ids)->get()->keyBy('corporation_id');
				}

				if ( isset($conditions['search']['corporation']) ) {
					//corporation.nameのみで検索された場合
					$corporation_name = $conditions['search']['corporation']['name'];
					$corporations = $this->Corporation->where('name', 'like', '%'.$corporation_name.'%')->limit(500)->get()->keyBy('corporation_id');
					$corporation_ids = $corporations->pluck('corporation_id');
					$Employees = $this->Employee->whereIn('corporation_id', $corporation_ids)->get()->keyBy('person_id');
					$person_ids = $Employees->pluck('person_id', 'person_id' );
					$persons = $this->Person->whereIn('person_id', $person_ids)->orderBy('person_id')->get();
				}

			}

			$result = collect();
                	foreach ($persons AS $person) {
				//personが企業に所属している、していないの場合わけ
                		if ( !empty($Employees[$person['person_id']])  ) {
					$corporation_id = $Employees[$person['person_id']]['corporation_id'];
				} else {
					$corporation_id = null;
				}

                        	$tmp_data = [
                        		'person_id' => $person['person_id'],
                                	'full_name' => $person['full_name'],
                                	'corporation_name' => $corporation_id != null ? $corporations[$corporation_id]['name'] : null,
                                	'birthday' => $person['birthday'] ?? null,
                        	];
                        	$result->push($tmp_data);
                	}
		} catch(\Exception $e) {
			logger()->error($e->getMessage());
			throw new \Exception("人一覧の検索に失敗しました");
		}

		return $result;
	}

	/*
		パスワード再設定
@returnbool
	*/
	public function resetPassword($person_id, $password, $system_id, $updator_id) {
		$Person = Person::findOrFail($person_id);

		$Person->fill([
			'password' => $password,
			'last_updated_system_id' => $system_id,
			'last_updated_by' => $updator_id,
		]);

		return $Person->save();
	}

	public function store(Array $data) {
		$person = Arr::only($data, $this->getTableColumns());

		// 登録処理開始
		DB::beginTransaction();

		try {
			$Person = new Person($person);

			if ( empty($Person->save()) ) throw new \RunTimeException("Failed to save Person.");
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

		return $Person->toArray();
	}

	public function updateByLinkKey(Array $data) {
		$person = Arr::only($data, $this->getTableColumns());

		// 更新処理開始
		DB::beginTransaction();

		try {
			$Person = Person::where('link_key', $person['link_key'])->firstOrFail();

			if ( empty($Person->update($person)) ) throw new \RunTimeException("Failed to update Person.");

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

		return $Person->toArray();
	}
}

Aacotroneo\Saml2\Saml2Auth