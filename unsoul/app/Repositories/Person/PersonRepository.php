<?php
namespace App\Repositories\Person;

// 統合API用
use App\Libraries\IntegratedAPI\IntegratedAPIClient;

class PersonRepository implements PersonRepositoryInterface {
        protected $IntegratedApiClient;

        public function __construct(
                IntegratedApiClient $IntegratedApiClient
        ) {
                $this->IntegratedApiClient = $IntegratedApiClient;
        }

        /*
                検索条件を指定して人一覧を検索
                @params Array $conditions
                @return Array
        */
        public function searchPersons(Array $conditions = []) {
		$response = $this->IntegratedApiClient->requestByKey('PERSON_SEARCH', $conditions);

                $ArrResponse = $response->json();

                return $ArrResponse;
        }

	/*
                検索条件を指定して企業一覧を検索
                @params Array $conditions
                @return Array
        */
	public function searchCorporations(Array $conditions = []) {
		$response = $this->IntegratedApiClient->requestByKey('PERSON_REGISTER_CORPORATION_SEARCH', $conditions);

                $ArrResponse = $response->json();

                return $ArrResponse;

	}

	/*
                人の新規登録
                @params Array $date
                @return Array
        */
	public function save(Array $data) {
		$response = $this->IntegratedApiClient->requestByKey('SAVE_PERSON', $data);
		$response->throwIfInvalidStatus();

		return $response->getData();
	}

	/*
                person_idで個人を特定
                @params Array $date
                @return Array
        */
	public function findById($id) {
		$conditions = ['person_id' => $id];
                $response = $this->IntegratedApiClient->requestByKey('FIND_PERSON', $conditions);
                $response->throwIfInvalidStatus();

		return $response->getData();
        }

	/*
                人の更新
                @params Array $date
                @return Array
        */
        public function update(Array $data) {
		$response = $this->IntegratedApiClient->requestByKey('UPDATE_PERSON', $data);
		$response->throwIfInvalidStatus();

                return $response->getData();
        }
}