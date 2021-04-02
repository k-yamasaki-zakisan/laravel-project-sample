<?php
namespace App\Repositories\Office;

// 統合API用
use App\Libraries\IntegratedAPI\IntegratedAPIClient;

class OfficeRepository implements OfficeRepositoryInterface {
        protected $IntegratedApiClient;

        public function __construct(
                IntegratedApiClient $IntegratedApiClient
        ) {
                $this->IntegratedApiClient = $IntegratedApiClient;
        }

	/*
                事務所の新規登録
                @params Array $date
                @return Array
        */
	public function save(Array $data) {
		$response = $this->IntegratedApiClient->requestByKey('SAVE_OFFICE', $data);
		$response->throwIfInvalidStatus();

		return $response->getData();
	}

	/*
		主キー指定で検索
		@params int $id
		@return Array
	*/
	public function findById($id) {
		$conditions = ['office_id' => $id];
		$response = $this->IntegratedApiClient->requestByKey('FIND_OFFICE', $conditions);

		$ArrResponse = $response->json();

		return $ArrResponse['data'];
	}

	/*
                事務所の更新登録
                @params Array $date
                @return Array
        */
        public function update(Array $data) {
                $response = $this->IntegratedApiClient->requestByKey('UPDATE_OFFICE', $data);
		$response->throwIfInvalidStatus();

		return $response->getData();
        }

	/*
                ログインユーザーの所属企業事務所一覧取得
                @params int $person_id
                @return Array
        */
	public function searchListByPersonId($person_id) {
		$condition = ['person_id' => $person_id];
		$response = $this->IntegratedApiClient->requestByKey('SEARCH_OFFICE_LIST_BY_PERSONID', $condition);

		$response->throwIfInvalidStatus();

                return $response->getData();
	}

}