<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\ApiBaseController;
// Services
use App\Services\PersonAddressService;
// Requests
use App\Http\Requests\API\PersonAddress\FindPersonAddressRequest;
use App\Http\Requests\API\PersonAddress\PersonAddressListPostRequest;
// Utilities
use Illuminate\Support\Arr;

class PersonAddressController extends ApiBaseController
{
        protected $PersonAddressService;

        public function __construct(
                PersonAddressService $PersonAddressService
        ) {
                $this->PersonAddressService = $PersonAddressService;
        }

                /*
                        単一レコード取得
                */
                public function find(FindPersonAddressRequest $request) {
                        $validated = $request->validated();
                        $PersonAddress = $this->PersonAddressService->findOrFail($validated['person_address_id']);

                        return $this->_buildResponseArray($PersonAddress);
                }
                /*
                        一覧取得
                */
                public function getList(PersonAddressListPostRequest $request) {
                        $corrected = $request->getCorrectedData();
                        $result = $this->PersonAddressService->buildQueryForList($corrected)
                                ->paginate($corrected['limit'])
                                ->toArray();
                        $data = Arr::pull($result, 'data');
                        //$result['conditions'] = $request->validated();

                        return $this->_buildResponseArray($data, $result);
                }
}
