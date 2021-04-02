<?php
namespace App\Services;

// Repositories
use App\Repositories\Gender\GenderRepositoryInterface as GenderRepository;

class GenderService extends ServiceBase {

	protected $GenderRepo;

	public function __construct(
		GenderRepository $GenderRepo
	) {
		$this->GenderRepo = $GenderRepo;
	}

	/*
		検索処理
		@param Array $conditions
		@return Array
	*/
	public function search(Array $conditions = []) {
		return $this->GenderRepo->search($conditions);
	}

}