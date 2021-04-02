<?php
namespace App\Repositories\Corporation;

// 統合API用
use App\Libraries\IntegratedAPI\IntegratedAPIClient;

class CorporationRepository implements CorporationRepositoryInterface {
	protected $IntegratedApiClient;

	public function __construct(
		IntegratedApiClient $IntegratedApiClient
	) {
		$this->IntegratedApiClient = $IntegratedApiClient;
	}

	/*
		主キー指定で検索
		@params int $id
		@return Array
	*/
	public function findById($id) {
		$conditions = ['corporation_id' => $id];
		$response = $this->IntegratedApiClient->requestByKey('FIND_CORPORATION', $conditions);

		$ArrResponse = $response->json();

		return $ArrResponse['data'];
	}

	public function findByIdWithRelated($id) {
		$conditions = ['corporation_id' => $id];
		$response = $this->IntegratedApiClient->requestByKey('FIND_CORPORATION_WITH_RELATED', $conditions);

		$ArrResponse = $response->json();

		return $ArrResponse['data'];
	}
	/*
		検索条件を指定して検索
		@params Array $conditions
		@return Array
	*/
	public function search(Array $conditions = []) {
		$response = $this->IntegratedApiClient->requestByKey('CORPORATION_LIST', $conditions);

		$ArrResponse = $response->json();

		return $ArrResponse;
	}

	public function save(Array $data) {
		$response = $this->IntegratedApiClient->requestByKey('SAVE_CORPORATION', $data);
		$response->throwIfInvalidStatus();

		return $response->getData();
	}
}