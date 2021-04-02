<?php
namespace App\Usecases\MyPages;

// Services
use App\Services\CorporationService;
use App\Services\PrefectureService;
use App\Services\OfficeService;
// 統合API用
use App\Libraries\IntegratedAPI\SearchCondition;

class EditCorporationUsecase {
	protected $CorporationService;
	protected $PrefectureService;
	protected $OfficeService;

	public function __construct(
		CorporationService $CorporationService,
		PrefectureService $PrefectureService,
		OfficeService $OfficeService
	) {
		$this->CorporationService = $CorporationService;
		$this->PrefectureService = $PrefectureService;
		$this->OfficeService = $OfficeService;
	}

	public function __invoke($person_id) {
		//本社情報取得
		$corporaiton_data_with_offices = $this->CorporationService->findByPersonId($person_id);
		//本社事務所取得
		$headOffice = collect($corporaiton_data_with_offices['offices'])->where('head_office_flg', true)->first();
		//本社電話連絡先
		$headOfficeContactTel = collect($headOffice['office_contacts'])->whereIn('contact_type_id', [1,2])->first();
		//本社FAX連絡先
		$headOfficeContactFax = collect($headOffice['office_contacts'])->where('contact_type_id', 3)->first();
		//その他事務所取得
		$offices = collect($corporaiton_data_with_offices['offices'])->where('head_office_flg', false)->sortBy('name')->pluck('name', 'office_id');

		// 都道府県取得
		$prefecture_data = $this->PrefectureService->search();
		$prefectures = collect($prefecture_data['data'])->pluck('name', 'prefecture_id');

		return [
			'name' => $corporaiton_data_with_offices['name'],
			'phonetic' => $corporaiton_data_with_offices['phonetic'],
			'capital' => $corporaiton_data_with_offices['capital'],
			'established_year' => $corporaiton_data_with_offices['established_year'],
			'established_month' => $corporaiton_data_with_offices['established_month'],
			'representative' => $corporaiton_data_with_offices['representative'],
			'zip_code1' => $headOffice['zip_code1'],
			'zip_code2' => $headOffice['zip_code2'],
			'now_prefecture_id' => $headOffice['prefecture_id'],
			'prefectures' => $prefectures,
			'city' => $headOffice['city'],
			'town' => $headOffice['town'],
			'street' => $headOffice['street'],
			'building' => $headOffice['building'],
			'tel' => $headOfficeContactTel['value'],
			'fax' => $headOfficeContactFax['value'],
			'offices' => $offices,
		];

	}
}