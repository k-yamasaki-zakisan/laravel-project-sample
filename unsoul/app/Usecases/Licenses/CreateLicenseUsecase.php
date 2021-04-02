<?php
namespace App\Usecases\Licenses;

// Services
use App\Services\LicenseService;
// Requests
use App\Http\Requests\Licenses\SearchLicenseRequest;
use Illuminate\Http\Request;
// 統合API用
use App\Libraries\IntegratedAPI\SearchCondition;
// Utilities
use Carbon\Carbon;

class UpdateLicenseUsecase {
	protected $LicenseService;

	public function __construct(
		LicenseService $LicenseService
	) {
		$this->LicenseService = $LicenseService;
	}

	public function __invoke(SearchLicenseRequest $request,$license) {

	// 統合API用検索データ生成
	$conditions = $this->buildConditions($license);

	$licenses = $this->LicenseService->update($conditions);

	return $licenses;

	}

	/*
		統合API用検索データ生成
		@params Array $search_conditions リクエストされた検索条件
		@return Array
	*/
	protected function buildConditions(Array $search_conditions) {
		$conditions = [];
// person_id
		if ( isset($search_conditions['person_id']) ) $conditions['person_id'] = $search_conditions['person_id'];
// license_id
		if ( isset($search_conditions['license_id']) ) $conditions['license_id'] = $search_conditions['license_id'];

		if ( isset($search_conditions['acquisition_date']) ) $conditions['acquisition_date'] = $search_conditions['acquisition_date'];

		if ( isset($search_conditions['expiration_date']) ) $conditions['expiration_date'] = $search_conditions['expiration_date'];

		return $conditions;;
	}
}