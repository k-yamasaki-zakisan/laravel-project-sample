<?php
namespace App\Services;

// Repositories
use App\Repositories\Labor\LaborRepositoryInterface as LaborRepository;

// Pagination
use Illuminate\Pagination\LengthAwarePaginator;

class LaborService extends ServiceBase {

	protected $LaborRepo;

	public function __construct(
		LaborRepository $LaborRepo
	) {
		$this->LaborRepo = $LaborRepo;
	}

	public function findById($employee_id) {
		return $this->LaborRepo->findById($employee_id);
	}

	/*
		検索処理
		@param Array $conditions
		@return Array
	*/
	public function search(Array $conditions = []) {
		return $this->LaborRepo->search($conditions);
	}

	/*
		ページネーション
		@param Array $conditions
		@return Array
	*/
	public function paginate(Array $conditions = [], $per_page, $current_page, Array $options = []) {
		$search_result = $this->LaborRepo->search($conditions);

		$data = new LengthAwarePaginator(
			$search_result['data'],
			$search_result['summary']['total'],
			$per_page,
			$current_page,
			$options
		);

		return $data;
	}
}
