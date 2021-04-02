<?php
/***
 * 社員（従業員）関連サービス
 *
 * @author YuKaneko
 */

namespace App\Services;

// Services
use App\Services\ServiceBase;
// Models
use App\Models\PersonContact;
use App\Models\Employee;
use App\Models\Gender;
use App\Models\Person;
use App\Models\PersonAddress;
// Utilities
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;
// Traits
use App\Traits\ConditioningQueryBuilder;
// Carbon
use Carbon\Carbon;
// DB
use DB;

class EmployeeService extends ServiceBase {

	use ConditioningQueryBuilder;

	protected $Employee;

	public function __construct(
		Employee $Employee
	) {
		$this->Employee = $Employee;
	}

	/*
		主キーを指定して検索 存在しない場合は例外を投げる
		@param mixed $id
		@param array $columns
		@throw ModelNotFoundException
		@return App\Employee
	*/
	public function findOrFail($id, Array $columns = ['*']) {
		return $this->Employee->findOrFail($id, $columns);
	}

	/*
   		 クエリビルダ生成
    		@return Illuminate\Database\Eloquent\Builder
  	*/
	public function newQuery() {
   		 return $this->Employee->newQuery();
  	}

	/*
    		テーブルカラム取得
    		@param bool $use_cache
  		@return Array
 	*/
	public function getTableColumns($use_cache = true) {
		return $this->Employee->getTableColumns($use_cache);
	}

	/*
    		主キー名取得
   		@return string
  	*/
	public function getPrimaryKey() {
		return $this->Employee->getKeyName();
	}

	/*
        	論理削除用トレイト利用判定
        	@return bool
    	*/
    	public function useSoftDeletes() {
        	return $this->Employee->useSoftDeletes();
    	}

	/*
    		一覧取得用クエリ生成
    		@param Array $conditions
    		@return Illuminate\Database\Eloquent\Builder
  	*/
	public function buildQueryForList(Array $conditions = []) {
		$Query = $this->buildConditioningQuery($conditions, $this->newQuery(), $this->useSoftDeletes(), $this->getPrimaryKey());

		if ( !empty($conditions['with']) ) {
			$Query->with($conditions['with']);
		}

		return $Query;
	}

	/*
		関連情報取得用
		@param mixed $id
		@throw ModelNotFoundException
		@return App\Person
	*/
	public function getWithRelatedOrFail($id) {
		return $this->Employee->with([
			'person',
			'corporation',
			'employee_addresses',
			'employee_contacts',
			'employee_job_careers',
		])->findOrFail($id);
	}

	public function search(Array $conditions = []) {
		$query = Employee::query();

		// 法人Id
		if ( isset($conditions['corporation_id']) ) $query->where('corporation_id', $conditions['corporation_id']);
	// 従業員コード
		// 従業員コード
		if ( isset($conditions['employee_code']) ) $query->where('code', $conditions['employee_code']);

		// 従業員氏名
		if ( isset($conditions['full_name']) ) $query->where('full_name', 'like', "%{$conditions['full_name']}%");

		// 従業員入社日
		if ( isset($conditions['hire_date']) ) {
			// ToDo:暫定的に年月での範囲指定となっている
			$start_date = Carbon::parse($conditions['hire_date'])->startOfMonth()->format('Y-m-d H:i:s');
			$end_date = Carbon::parse($conditions['hire_date'])->endOfMonth()->format('Y-m-d H:i:s');
			$query->whereBetween('hire_date', [$start_date, $end_date]);
		}

		// 性別
		if( isset($conditions['gender_id']) ){
			$query->whereHas('person.gender', function ($query) use ($conditions) {
				$query->where('gender_id', $conditions['gender_id']);
			});
		}

		// 従業員住所
		if ( isset($conditions['address']) ) {
			$query->whereHas('employee_addresses', function($query) use ($conditions) {
				$query->where('address', 'like', "%{$conditions['address']}%");
			});
		}

		// 従業員連絡先
		if ( isset($conditions['contact']) ) {
			$query->whereHas('employee_contacts', function($query) use ($conditions) {
				$query->where('value', 'like', "%{$conditions['contact']}%");
			});
		}

		// 在職状況
		if ( isset($conditions['job_status']) ) {
			$now = Carbon::now();

			switch($conditions['job_status']) {
				case('WORKING'):
					// ToDo:要件確認
					// 入社日が設定されていないか現在日以前
					//  かつ 退社日未入力か明日以降
					$query->where(function($query) use($now) {
						$query->whereNotNull('hire_date')
						//$query->whereNull('hire_date')
							//->orWhere('hire_date', '<=', $now)
							->where(function($query) use($now) {
								$query->whereNull('retirement_date')
									->orWhere('retirement_date', '>', $now);
							});
						});
					break;
				case('RETIRED'):
					// 退社日が設定され現在日以前
					$query->where(function($query) use($now) {
						$query->whereNotNull('retirement_date')
							->where('retirement_date', '<=', $now);
					});
					break;
				default:
					break;
			}
		}

		// 外部テーブルの値を取得
		$query->with([
			'person:person_id,gender_id,link_key', // リレーション（gender_id）
			//'person.gender:gender_id,name',
			'employee_addresses:employee_address_id,employee_id,address,number',
			'employee_contacts:employee_contact_id,employee_id,contact_type_id,value,sort_index',
			'employee_contacts.contact_type:contact_type_id,slug',
		]);

		return $query;
	}

	/*
		従業員削除
		@param string $link_key
		@param string $system_id
		@param string $updated_by 更新者（人）ID
		@param Array $options
		@return bool
	*/
	public function deleteByLinkKey($link_key, $system_id, $updated_by, Array $options = []) {
		// 該当データがない場合は404を発生させる
		$Employee = Employee::linkKey($link_key)->firstOrFail();

		try {
			$Employee->fill([
				'last_updated_system_id' => $system_id,
				'last_updated_by' => $updated_by,
			]);
			$Employee->deleted_at = Carbon::now()->format('Y-m-d H:i:s');

			$result = $Employee->save();

			return !empty($result);
		} catch( \Exception $e ) {
			logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
			return false;
		}
	}

	/*
                従業員登録
                @param Array $data 登録用データ
        */
	public function store(Array $data) {
		$employee = Arr::only($data, $this->getTableColumns());

		// 登録処理開始
		DB::beginTransaction();

		try {
			$Employee = new Employee($employee);

			if ( empty($Employee->save()) ) throw new \RunTimeException("Failed to save Employee.");
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

		return $Employee->toArray();
	}

	/*
                従業員更新
                @param Array $data 更新用データ
        */
	public function updateByLinkKey(Array $data) {
		$employee = Arr::only($data, $this->getTableColumns());

		// 更新処理開始
                DB::beginTransaction();

                try {
                        $Employee = Employee::where('link_key', $employee['link_key'])->first();

                        if ( empty($Employee->update($employee)) ) throw new \RunTimeException("Failed to update Employee.");

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

                return $Employee->toArray();
        }
}