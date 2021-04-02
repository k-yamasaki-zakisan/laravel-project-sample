<?php
namespace App\Usecases\Licenses;

// Services
use App\Services\PersonLicenseService;
// Utilities
use Carbon\Carbon;

class UpdatePersonLicenseUsecase {
	protected $PersonLicenseService;

	public function __construct(
		PersonLicenseService $PersonLicenseService
	) {
		$this->PersonLicenseService = $PersonLicenseService;
	}

	public function __invoke(Array $request_data) {
		$data = $this->buildData($request_data);
		$license = $this->PersonLicenseService->update($data);

		return $license;
	}

	/*
                編集用データ整形
                @params Array $request_data リクエストされた登録データ
                @return Array
        */
        protected function buildData(Array $request_data) {
                //if ( isset($request_data['acquisition']) ) {
                //        $acquisition = new Carbon($request_data['acquisition']);
                //}

                $data = [
                        'updated_by' => $request_data['updated_by'],
                        'person_license' => [
                                'link_key' => $request_data['link_key'],
                                'acquisition_year' => !empty($request_data['acquisition_year']) ? (int) $request_data['acquisition_year'] : Null,
                                'acquisition_month' => !empty($request_data['acquisition_month']) ? (int) $request_data['acquisition_month'] : Null,
                                'acquisition_date' => !empty($request_data['acquisition_date']) ? (int) $request_data['acquisition_date'] : Null,
                                'expired_at' => $request_data['expired_at'] ?? Null,
                                'note' => $request_data['note'] ?? Null,
                        ],
                ];

                return $data;
        }
}