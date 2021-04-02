<?php

namespace App\Http\Requests\API\Employee;

use App\Http\Requests\API\ApiBaseRequest;

class EmployeeSearchPostRequest extends ApiBaseRequest {

	protected $rules = [
		'corporation_id' => ['bail', 'nullable', 'integer', 'exists_soft:corporations,corporation_id'],
		'employee_code' => ['bail', 'nullable', 'number'],
		'full_name' => ['bail', 'nullable', 'string'],
		'gender_id' => ['bail', 'nullable', 'integer', 'exists_soft:genders,gender_id'],
		'job_status' => ['bail', 'nullable', 'string', 'in:WORKING,RETIRED,ALL'],
		'address' => ['bail', 'nullable', 'string'],
		'contact' => ['bail', 'nullable', 'string'],
		'hire_date' => ['bail', 'nullable', 'date_format:Y-m-d'],
		'limit' => ['bail', 'nullable', 'integer', 'min:0'],
	];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
		return true;
    }
}