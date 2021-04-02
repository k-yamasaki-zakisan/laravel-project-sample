<?php
namespace App\Repositories\License;

// 統合API用
use App\Libraries\IntegratedAPI\IntegratedAPIClient;

class LicenseRepository implements LicenseRepositoryInterface {
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
        public function search(Array $conditions = []) {
		$response = $this->IntegratedApiClient->requestByKey('LICENSE_SEARCH', $conditions);

                $ArrResponse = $response->json();

                return $ArrResponse;
        }

	public function edit(Array $conditions = []) {
		$response = $this->IntegratedApiClient->requestByKey('LICENSE_EDIT', $conditions);

                $ArrResponse = $response->json();

                return $ArrResponse;
	}

	public function update(Array $conditions = []) {
		$response = $this->IntegratedApiClient->requestByKey(':', $conditions);

                $ArrResponse = $response->json();

                return $ArrResponse;
	}

	public function create(Array $conditions = []) {
		$response = $this->IntegratedApiClient->requestByKey('LICENSE_REGISTER', $conditions);

                $ArrResponse = $response->json();

                return $ArrResponse;
	}

	public function getLicenseCategoryList(Array $conditions = []) {
		$response = $this->IntegratedApiClient->requestByKey('LICENSE_CATEGORY_LIST', $conditions);

                $ArrResponse = $response->json();

                return $ArrResponse;
	}

	public function getList(Array $conditions = []) {
		$response = $this->IntegratedApiClient->requestByKey('LICENSE_LIST', $conditions);
		$response->throwIfInvalidStatus();

		return $response->getData();
	}

}