<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\ApiBaseController;
// Services
use App\Services\OfficeService;
// Requests
use App\Http\Requests\API\Office\FindOfficeRequest;
use App\Http\Requests\API\Office\OfficeListPostRequest;
use App\Http\Requests\API\Office\SaveOfficeRequest;
use App\Http\Requests\API\Office\UpdateOfficeRequest;
use App\Http\Requests\API\Office\SearchOfficeListByPersonIdRequest;
use App\Http\Requests\API\Office\DeleteOfficeRequest;
// Utilities
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class OfficeController extends ApiBaseController
{
	protected $OfficeService;

  	public function __construct(
    		OfficeService $OfficeService
  	) {
    		$this->OfficeService = $OfficeService;
  	}

//  public function index() {
//  }

    	/*
     		単一レコード取得
    	*/
    	public function find(FindOfficeRequest $request) {
      		$validated = $request->validated();
      		$Office = $this->OfficeService->findOrFail($validated['office_id']);

      		return $this->_buildResponseArray($Office);
    	}

	/*
		一覧取得
	*/
	public function getList(OfficeListPostRequest $request) {
		$corrected = $request->getCorrectedData();
		$result = $this->OfficeService->buildQueryForList($corrected)
			->paginate($corrected['limit'])
			->toArray();
		$data = Arr::pull($result, 'data');
		//$result['conditions'] = $request->validated();

		return $this->_buildResponseArray($data, $result);
	}

	/*
		関連情報取得
	*/
	public function getWithRelated(FindOfficeRequest $request) {
		$validated = $request->validated();
		$Office = $this->OfficeService->getWithRelatedOrFail($validated['office_id']);

		return $this->_buildResponseArray($Office);
	}

	/*
                事務所の新規登録
        */
	public function store(SaveOfficeRequest $request) {
		$validated = $request->validated();
		$result = $this->OfficeService->store($validated);

		return $this->_buildResponseArray($result);
	}

	/*
                事務所の編集
        */
	public function update(UpdateOfficeRequest $request) {
		$validated = $request->validated();

		$result = $this->OfficeService->update($validated);

		return $this->_buildResponseArray($result);
	}

	/*
                事務所の削除
        */
        public function delete(DeleteOfficeRequest $request) {
                $validated = $request->validated();

                $result = $this->OfficeService->update($validated);

                return $this->_buildResponseArray($result);
        }

}