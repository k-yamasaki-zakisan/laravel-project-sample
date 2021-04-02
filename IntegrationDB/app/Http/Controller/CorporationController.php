<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\ApiBaseController;
// Services
use App\Services\CorporationService;
// Requests
use App\Http\Requests\API\Corporation\FindCorporationRequest;
use App\Http\Requests\API\Corporation\CorporationListPostRequest;
use App\Http\Requests\API\Corporation\CorporationSearchWithCorporation_nameAndOffice_address;
use App\Http\Requests\API\Corporation\SaveCorporationRequest;
// Utilities
use Illuminate\Support\Arr;
// Pagination
use Illuminate\Pagination\LengthAwarePaginator;

class CorporationController extends ApiBaseController
{
	protected $CorporationService;

	public function __construct(
		CorporationService $CorporationService
	) {
		$this->CorporationService = $CorporationService;
	}

	/*
		単一レコード取得
	*/
	public function find(FindCorporationRequest $request) {
		$validated = $request->validated();
		$Corporation = $this->CorporationService->findOrFail($validated['corporation_id']);

		return $this->_buildResponseArray($Corporation);
	}

	/*
		一覧取得
	*/
	public function getList(CorporationListPostRequest $request) {
		$corrected = $request->getCorrectedData();
		$result = $this->CorporationService->buildQueryForList($corrected)
			->paginate($corrected['limit'])
			->toArray();
		$data = Arr::pull($result, 'data');
		//$result['conditions'] = $request->validated();

		return $this->_buildResponseArray($data, $result);
	}

	/*
		関連情報取得
	*/
	public function getWithRelated(FindCorporationRequest $request) {
		$validated = $request->validated();
		$Corporation = $this->CorporationService->getWithRelatedOrFail($validated['corporation_id']);

		return $this->_buildResponseArray($Corporation);
	}

	/*
		運soul人登録用企業検索
        */
	public function corporationsSearchWithCorporation_nameAndOffice_address(CorporationSearchWithCorporation_nameAndOffice_address $request) {
		$validated = $request->validated();
		$data = $this->CorporationService->buildSearchQueryForList($validated);

		!empty($data) ?  $count = count($data) : $count = 0;

		$result = new LengthAwarePaginator(
			$data,
			$count,
			50,
			1,
			array('path' => $request->url())
		);

		$result = $result->toArray();

		$data = Arr::pull($result, 'data');

		return $this->_buildResponseArray($data, $result);
	}

	/*
		登録
	*/
	public function store(SaveCorporationRequest $request) {
		$validated = $request->validated();
		$result = $this->CorporationService->store($validated);

		return $this->_buildResponseArray($result);
	}
}