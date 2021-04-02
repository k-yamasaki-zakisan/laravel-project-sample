<?php
namespace App\Usecases\Corporations;

// Services
use App\Services\CorporationService;

class StoreCorporationUsecase {
	protected $CorporationService;

	public function __construct(
		CorporationService $CorporationService
	) {
		$this->CorporationService = $CorporationService;
	}

	public function __invoke(Array $request_data) {
		$data = $this->buildData($request_data);

		// 保存
		$corporation = $this->CorporationService->save($data);

		return $corporation;
	}

	/*
		統合API送信用データ生成
		@params Array $search_conditions リクエストされた検索条件
		@return Array
	*/
	protected function buildData(Array $request_data) {
		$data = [
			'updated_by' => '9628d63b756f4e3e8c123783c859f0961a50a7169c14430fa1b6414a91c33cce', // TODO:ログインユーザーID
			'corporation' => [
				'corporation_type_id' => $request_data['corporation_type_id'] ?? null,
				'corporation_pos' => empty($request_data['corporation_pos']),
				'name' => $request_data['name'] ?? null,
				'phonetic' => $request_data['phonetic'] ?? null,
				'capital' => $request_data['capital'] ?? null,
				'established_year' => $request_data['established_year'] ?? null,
				'established_month' => $request_data['established_month'] ?? null,
				'representative' => $request_data['representative'] ?? null,
				'head_office' => [
					'name' => '本社',
					'phonetic' => 'ホンシャ',
					'zip_code1' => $request_data['zip_code1'] ?? null,
					'zip_code2' => $request_data['zip_code2'] ?? null,
					'prefecture_id' => $request_data['prefecture_id'] ?? null,
					'city' => $request_data['city'] ?? null,
					'town' => $request_data['town'] ?? null,
					'street' => $request_data['street'] ?? null,
					'building' => $request_data['building'] ?? null,
					'head_office_flg' => true,
				],
			],
		];

		// 資本金データ補正
		if ( isset($data['corporation']['capital']) ) $data['corporation']['capital'] = intval($data['corporation']['capital'] * 10000);

		// 本社連絡先
		$head_office_contacts = [];

		// 固定電話
		if ( isset($request_data['tel']) ) {
			$head_office_contacts[] = [
				'slug' => 'tel',
				'value' => $request_data['tel'],
			];
		}

		// FAX
		if ( isset($request_data['fax']) ) {
			$head_office_contacts[] = [
				'slug' => 'fax',
				'value' => $request_data['fax'],
			];
		}

		if ( !empty($head_office_contacts) ) $data['corporation']['head_office']['head_office_contacts'] = $head_office_contacts;

		return $data;
	}
}