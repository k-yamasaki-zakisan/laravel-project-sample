<?php
namespace App\Repositories\Prefecture;

// 統合API用
use App\Libraries\IntegratedAPI\IntegratedAPIClient;

class PrefectureRepository implements PrefectureRepositoryInterface {
	protected $IntegratedApiClient;

	public function __construct(
		IntegratedApiClient $IntegratedApiClient
	) {
		$this->IntegratedApiClient = $IntegratedApiClient;
	}

	/*
		検索条件を指定して検索
		@params Array $conditions
		@return Array
	*/
	public function search(Array $conditions = []) {
		$response = $this->IntegratedApiClient->requestByKey('PREFECTURE_LIST', $conditions);

		$ArrResponse = $response->json();

		return $ArrResponse;
	}
}
