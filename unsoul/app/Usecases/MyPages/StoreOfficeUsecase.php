<?php
namespace App\Usecases\MyPages;

// Services
use App\Services\OfficeService;

class StoreOfficeUsecase {
	protected $OfiiceService;

	public function __construct(
		OfficeService $OfficeService
	) {
		$this->OfficeService = $OfficeService;
	}

	public function __invoke(Array $request_data) {
		$data = $this->buildData($request_data);

		// 保存
		$office = $this->OfficeService->save($data);

		return $office;
	}

	/*
		統合API送信用データ生成
		@params Array $search_conditions リクエストされた検索条件
		@return Array
	*/
	protected function buildData(Array $request_data) {
		$data = [
			'updated_by' => '9628d63b756f4e3e8c123783c859f0961a50a7169c14430fa1b6414a91c33cce', // TODO:ログインユーザーID
			'office' => [
				'corporation_id' => $request_data['corporation_id'],
				'name' => $request_data['name'],
				'phonetic' => $request_data['phonetic'] ?? Null,
				'zip_code1' => $request_data['zip_code1'] ?? Null,
				'zip_code2' => $request_data['zip_code2'] ?? Null,
				'prefecture_id' =>  isset($request_data['prefecture_id']) ? (int) $request_data['prefecture_id'] : Null,
				'city' => $request_data['city'] ?? Null,
				'town' => $request_data['town'] ?? Null,
				'street' => $request_data['street'] ?? Null,
				'building' => $request_data['building'] ?? Null,

			],
        ];
		return $data;
	}
}

