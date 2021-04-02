<?php
namespace App\Usecases\Persons;

// Services
use App\Services\PersonService;

class StorePersonUsecase {
	protected $PersonService;

	public function __construct(
		PersonService $PerosnService
	) {
		$this->PerosnService = $PerosnService;
	}

	public function __invoke(Array $request_data) {
		$data = $this->buildData($request_data);

		// 保存
		$perosn = $this->PerosnService->save($data);

		return $perosn;
	}

	/*
		統合API送信用データ生成
		@params Array $search_conditions リクエストされた検索条件
		@return Array
	*/
	protected function buildData(Array $request_data) {
		$data = [
			'updated_by' => $request_data['updated_by'],
			'person' => [
				'login_id' => $request_data['login_id'],
				'password' => $request_data['password'],
				'last_name' => $request_data['last_name'],
				'first_name' => $request_data['first_name'],
				'last_name_kana' => $request_data['last_name_kana'],
				'first_name_kana' => $request_data['first_name_kana'],
				'gender_id' => !empty($request_data['gender_id']) ?  (int) $request_data['gender_id'] : Null,
				'birthday' => $request_data['birthday'],
			],

			'corporation' => [
				'corporation_id' => (int)$request_data['corporation_id'],
				'name' => $request_data['corporation_name']
			],
		];

		return $data;
	}
}