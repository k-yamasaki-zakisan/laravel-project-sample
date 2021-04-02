<?php
namespace App\Usecases\MyPages;

// Services
use App\Services\CorporationService;
use App\Services\PrefectureService;
use App\Services\OfficeService;
// 統合API用
use App\Libraries\IntegratedAPI\SearchCondition;

class EditOfficeUsecase {
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

	public function __invoke($office_link_key, $corporaiton_id) {
		// 都道府県取得
		$prefecture_data = $this->PrefectureService->search();
		$prefectures = collect($prefecture_data['data'])->pluck('name', 'prefecture_id');

		//事業所リスト取得
		$corporaiton_data_with_offices = $this->CorporationService->findByIdWithRelated($corporaiton_id);
		$offices = collect($corporaiton_data_with_offices['offices'])->where('head_office_flg', false)->sortBy('name')->pluck('name', 'link_key');

		//事務所単体の情報取得
		$office = collect($corporaiton_data_with_offices['offices'])->where('link_key', $office_link_key)->first();
		if (empty($office) ) throw new \RuntimeException("Failed to find office.");

		$data = [
                        'prefectures' => $prefectures,
                        'offices' => $offices
                ];

		$data += $office;

		return $data;
	}
}