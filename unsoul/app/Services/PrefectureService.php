<?php
namespace App\Services;

// Repositories
use App\Repositories\Prefecture\PrefectureRepositoryInterface as PrefectureRepository;

class PrefectureService extends ServiceBase {

	protected $PrefectureRepo;

	public function __construct(
		PrefectureRepository $PrefectureRepo
	) {
		$this->PrefectureRepo = $PrefectureRepo;
	}

	/*
		検索処理
		@param Array $conditions
		@return Array
	*/
	public function search(Array $conditions = []) {
		return $this->PrefectureRepo->search($conditions);
	}

}
