<?php
namespace App\Usecases\MyPages;

// Services
use App\Services\PersonService;

class UpdatePersonUsecase {
	protected $PersonService;

	public function __construct(
		PersonService $PerosnService
	) {
		$this->PerosnService = $PerosnService;
	}

	public function __invoke(Array $request_data) {
		$data = $this->buildData($request_data);

		// 保存
		$perosn = $this->PerosnService->update($data);

		return $perosn;
	}

	/*
		統合API送信用データ生成
		@params Array $search_conditions リクエストされた検索条件
		@return Array
	*/
	protected function buildData(Array $request_data) {
		$data = [
			'updated_by' => '9628d63b756f4e3e8c123783c859f0961a50a7169c14430fa1b6414a91c33cce', // TODO:ログインユーザーID
			'person' => [
				'person_id' => $request_data['person_id'],
				'last_name' => $request_data['last_name'],
				'first_name' => $request_data['first_name'],
				'last_name_kana' => $request_data['last_name_kana'],
				'first_name_kana' => $request_data['first_name_kana'],
				'gender_id' =>  isset($request_data['gender_id']) ? (int) $request_data['gender_id'] : Null,
				'birthday' => $request_data['birthday'] ?? Null,
			],
		];
		return $data;
	}
}