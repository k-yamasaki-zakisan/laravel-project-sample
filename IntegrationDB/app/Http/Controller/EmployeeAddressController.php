<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\ApiBaseController;
// Services
use App\Services\EmployeeAddressService;
// Requests
use App\Http\Requests\API\EmployeeAddress\FindEmployeeAddressRequest;
use App\Http\Requests\API\EmployeeAddress\EmployeeAddressListPostRequest;
// Utilities
use Illuminate\Support\Arr;

class EmployeeAddressController extends ApiBaseController
{
	protected $EmployeeAddressService;

        public function __construct(
                EmployeeAddressService $EmployeeAddressService
        ) {
                $this->EmployeeAddressService = $EmployeeAddressService;
        }

        /*
        	単一レコード取得
        */
        public function find(FindEmployeeAddressRequest $request) {
		$validated = $request->validated();
                $EmployeeAddress = $this->EmployeeAddressService->findOrFail($validated['employee_address_id']);

		return $this->_buildResponseArray($EmployeeAddress);
	}

	/*
		一覧取得
	*/
	public function getList(EmployeeAddressListPostRequest $request) {
        	$corrected = $request->getCorrectedData();
                $result = $this->EmployeeAddressService->buildQueryForList($corrected)
                	->paginate($corrected['limit'])
                        ->toArray();
                $data = Arr::pull($result, 'data');

                return $this->_buildResponseArray($data, $result);
	}
}