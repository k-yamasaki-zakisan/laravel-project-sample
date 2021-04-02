<?php
namespace App\Services;

// Repositories
use App\Repositories\Corporation\CorporationRepositoryInterface as CorporationRepository;

// Pagination
use Illuminate\Pagination\LengthAwarePaginator;

class CorporationService extends ServiceBase {

	protected $CorporationRepo;

	public function __construct(
		CorporationRepository $CorporationRepo
	) {
		$this->CorporationRepo = $CorporationRepo;
	}

	public function findById($corporation_id) {
		return $this->CorporationRepo->findById($corporation_id);
	}

	public function findByIdWithRelated($corporation_id) {
		return $this->CorporationRepo->findByIdWithRelated($corporation_id);
	}

	/*
		検索処理
		@param Array $conditions
		@return Array
	*/
	public function search(Array $conditions = []) {
		return $this->CorporationRepo->search($conditions);
	}

	/*
		ページネーション
		@param Array $conditions
		@return Array
	*/
	public function paginate(Array $conditions = [], $per_page, $current_page, Array $options = []) {
		$search_result = $this->search($conditions);

		$data = new LengthAwarePaginator(
			$search_result['data'],
			$search_result['summary']['total'],
			$per_page,
			$current_page,
			$options
		);

		return $data;
	}

	public function save(Array $data) {
		return $this->CorporationRepo->save($data);
	}
}