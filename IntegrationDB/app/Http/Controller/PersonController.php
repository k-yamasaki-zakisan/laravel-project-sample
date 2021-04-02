<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\ApiBaseController;
// Services
use App\Services\PersonService;
// Requests
use App\Http\Requests\API\Person\FindPersonRequest;
use App\Http\Requests\API\Person\PersonListPostRequest;
use App\Http\Requests\API\Person\PersonSearchWithFull_nameAndCorporation_name;
use App\Http\Requests\API\Person\ResetPersonPasswordPostRequest;
use App\Http\Requests\API\Person\SavePersonRequest;
use App\Http\Requests\API\Person\UpdatePersonRequest;
// Usecase
use App\Usecases\Person\ResetPersonPasswordUseCase;
use App\Usecases\Person\StorePersonUsecase;
use App\Usecases\Person\UpdatePersonUsecase;
// Utilities
use Illuminate\Support\Arr;
// Pagination
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class PersonController extends ApiBaseController
{
	protected $PersonService;

	public function __construct(
		PersonService $PersonService
	) {
		$this->PersonService = $PersonService;
	}

	/*
		単一レコード取得
	*/
	public function find(FindPersonRequest $request) {
		$validated = $request->validated();
		$Person = $this->PersonService->findOrFail($validated);

		return $this->_buildResponseArray($Person);
	}

	/*
		一覧取得
	*/
	public function getList(PersonListPostRequest $request) {
		$corrected = $request->getCorrectedData();
		$result = $this->PersonService->buildQueryForList($corrected)
			->paginate($corrected['limit'])
			->toArray();

		$data = Arr::pull($result, 'data');
		$result['conditions'] = $request->validated();

		return $this->_buildResponseArray($data, $result);
	}

	/*
		関連情報取得
	*/
	public function getWithRelated(FindPersonRequest $request) {
		$validated = $request->validated();
		$Person = $this->PersonService->getWithRelatedOrFail($validated);

		return $this->_buildResponseArray($Person);
	}

	/*
                運soul人一覧検索取得用
        */
	public function personSearchWithPerson_full_nameAndCorporation_name(PersonSearchWithFull_nameAndCorporation_name $request) {
		$validated = $request->validated();
		$data = $this->PersonService->buildSearchQueryForList($validated);

		//データの総数の指定
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

	public function store(SavePersonRequest $request, StorePersonUsecase $usecase) {
		$result = $usecase($request);

		return $this->_buildResponseArray($result);
	}

	public function reset_password(ResetPersonPasswordPostRequest $request, ResetPersonPasswordUseCase $usecase) {
		$result = $usecase($request);

		return $this->_buildResponseArray($result);
	}

	public function update(UpdatePersonRequest $request, UpdatePersonUsecase $usecase) {
		$result = $usecase($request);

		return $this->_buildResponseArray($result);
	}
}