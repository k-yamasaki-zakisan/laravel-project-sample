<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\ApiBaseController;
// Services
use App\Services\PersonContactService;
// Requests
use App\Http\Requests\API\PersonContact\FindPersonContactRequest;
use App\Http\Requests\API\PersonContact\PersonContactListPostRequest;
// Utilities
use Illuminate\Support\Arr;

class PersonContactController extends ApiBaseController
{
	protected $PersonContactService;

	public function __construct(
		PersonContactService $PersonContactService
	) {
		$this->PersonContactService = $PersonContactService;
	}

		/*
			単一レコード取得
		*/
		public function find(FindPersonContactRequest $request) {
			$validated = $request->validated();
			$PersonContact = $this->PersonContactService->findOrFail($validated['person_contact_id']);

			return $this->_buildResponseArray($PersonContact);
		}
		/*
			一覧取得
		*/
		public function getList(PersonContactListPostRequest $request) {
			$corrected = $request->getCorrectedData();
			$result = $this->PersonContactService->buildQueryForList($corrected)
				->paginate($corrected['limit'])
				->toArray();
			$data = Arr::pull($result, 'data');
			//$result['conditions'] = $request->validated();

			return $this->_buildResponseArray($data, $result);
		}
}