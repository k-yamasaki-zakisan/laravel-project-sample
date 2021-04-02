<?php
namespace App\Usecases\Licenses;

// Services
use App\Services\PersonLicenseService;
// Utilities
use Carbon\Carbon;

class StorePersonLicenseUsecase {
	protected $PersonLicenseService;

	public function __construct(
		PersonLicenseService $PersonLicenseService
	) {
		$this->PersonLicenseService = $PersonLicenseService;
	}

	public function __invoke(Array $request_data) {
		$data = $this->buildData($request_data);

		return $this->PersonLicenseService->save($data);
	}

	/*
		統合API送信用データ生成
		@params Array $request_data リクエストされた登録データ
		@return Array
	*/
	protected function buildData(Array $request_data) {
		if ( isset($request_data['acquisition']) ) {
			$acquisition = new Carbon($request_data['acquisition']);
		}

		$data = [
			'updated_by' => $request_data['updated_by'],
			'person_license' => [
				'person_id' => $request_data['person_id'],
				'license_id' => (int) $request_data['license_id'],
				'acquisition_year' => isset($acquisition) ? (int) $acquisition->format('Y') : Null,
				'acquisition_month' => isset($acquisition) ? (int) $acquisition->format('m') : Null,
				'acquisition_date' => isset($acquisition) ? (int) $acquisition->format('d') : Null,
				'expired_at' => $request_data['expired_at'] ?? Null,
				'note' => $request_data['note'] ?? Null,
			],
		];

		return $data;
	}
}