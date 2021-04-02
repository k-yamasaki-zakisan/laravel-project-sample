<?php

namespace App\Http\Controllers\Api\Trcd;

//use Illuminate\Http\Request;
use App\Http\Requests\Trcd\AlcoholCheckRecordEntryPostRequest;
//use App\Http\Controllers\Controller;
use App\Services\Trcd\AlcoholCheckRecordService;
use App\Services\ClientEmployeeService;

// Utilities
use Carbon\Carbon;

class AlcoholCheckRecordsController extends TrcdApiBaseController
{
	protected $objClientEmployeeService;

	public function __construct(ClientEmployeeService $objClientEmployeeService){
		$this->objClientEmployeeService = $objClientEmployeeService;

		parent::__construct();
	}

	public function entry(AlcoholCheckRecordEntryPostRequest $request, AlcoholCheckRecordService $service){
		// ログイン中のTRCD端末情報を取得する
		$trcd = $this->_getTrcdTerminal();

		$validated = $request->validated();
		$options = [
			'exclude_retired_employees' => Carbon::today(), // 退職日が当日未満の社員者を除く
		];
		$objClientEmployee = $this->objClientEmployeeService->getByAuthKey($validated['user_id'], $options);

		if( is_null($objClientEmployee) ) {
			return $this->buildResponseArrayError('400', ['user_id'=>'該当のユーザーIDは存在しません。']);
		}

		try {
			$objTrcdAlcoholCheckRecord = $service->add($trcd->id, $objClientEmployee->id, $validated);

			if ( empty($objTrcdAlcoholCheckRecord) ) {
				// 失敗
				$validator = $service->getLastValidator();

				if ( !empty($validator) ) return $this->buildResponseArrayError('400', $validator->errors());
				else return $this->buildResponseArrayError('500', '内部エラーが発生しました。');
			}
		} catch( \Exception $e ) {
			logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
			return $this->buildResponseArrayError('500', '内部エラーが発生しました。');
		}

		// 結果データ配列を形成する
		return $this->buildResponseArray();
	}

}