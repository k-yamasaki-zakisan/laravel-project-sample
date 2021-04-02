<?php
namespace App\Services;

// Repositories
use App\Repositories\Person\PersonRepositoryInterface as PersonRepository;

// Pagination
use Illuminate\Pagination\LengthAwarePaginator;

class PersonService extends ServiceBase {

	protected $PorsonRepo;

	public function __construct(
		PersonRepository $PersonRepo
	) {
		$this->PersonRepo = $PersonRepo;
	}

	/*
		人一覧検索処理
		@param Array $conditions
		@return Array
	*/
	public function searchPersons(Array $conditions = [], $per_page, $current_page, Array $options = []) {
		$search_result =  $this->PersonRepo->searchPersons($conditions);

		$data = $this->paginate($search_result, $per_page, $current_page, $options);

		return $data;
	}

	/*
                人新規登録時企業一覧検索処理
                @param Array $conditions
                @return Array
        */
	public function searchCorporations(Array $conditions = [], $per_page, $current_page, Array $options = []) {
                $search_result =  $this->PersonRepo->searchCorporations($conditions);

		$data = $this->paginate($search_result, $per_page, $current_page, $options);

                return $data;
        }


	/*
		ページネーション
		@param Array $search_result
		@return Array
	*/
	public function paginate(Array $search_result, $per_page, $current_page, Array $options = []) {
		$items = collect($search_result['data']);

		$data = new LengthAwarePaginator(
			$items->forpage($current_page, $per_page),
			$search_result['summary']['total'] ?? 0,
			$per_page,
			$current_page,
			$options
		);

		return $data;
	}

	/*
                人新規登録処理
                @param Array $conditions
                @return Array
        */
	public function save(Array $data) {
		return $this->PersonRepo->save($data);
	}

	/*
                マイページ情報取得処理
                @param int $person_id
                @return Array
        */
	public function findById($person_id) {
                return $this->PersonRepo->findById($person_id);
        }

	/*
                人更新処理
                @param Array $conditions
                @return Array
        */
	public function update(Array $data) {
                return $this->PersonRepo->update($data);
        }
}