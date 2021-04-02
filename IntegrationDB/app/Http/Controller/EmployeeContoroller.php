<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\ApiBaseController;
// Services
use App\Services\EmployeeService;
// Requests
use App\Http\Requests\API\Employee\FindEmployeeRequest;
use App\Http\Requests\API\Employee\EmployeeListPostRequest;
// Utilities
use Illuminate\Support\Arr;

class EmployeeController extends ApiBaseController
{       
        protected $EmployeeService;
        
        public function __construct(
                EmployeeService $EmployeeService
        ) {     
                $this->EmployeeService = $EmployeeService;
        }

                /*
                        単一レコード取得
                */
                public function find(FindEmployeeRequest $request) {
                        $validated = $request->validated();
                        $Employee = $this->EmployeeService->findOrFail($validated['employee_id']);

                        return $this->_buildResponseArray($Employee);
                }

                /*
                        一覧取得
                */
                public function getList(EmployeeListPostRequest $request){
                        $corrected = $request->getCorrectedData();
                        $result = $this->EmployeeService->buildQueryForList($corrected)
                                ->paginate($corrected['limit'])
                                ->toArray();
                        $data = Arr::pull($result, 'data');

                        return $this->_buildResponseArray($data, $result);
                }
}
