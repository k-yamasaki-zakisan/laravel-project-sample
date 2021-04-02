<?php
namespace App\Usecases\Corporations;

// Services
use App\Services\CorporationTypeService;
use App\Services\PrefectureService;
// 統合API用
use App\Libraries\IntegratedAPI\SearchCondition;
// Utilities
use Carbon\Carbon;

class CreateCorporationUsecase {
	protected $CorporationTypeService;
	protected $PrefectureService;

	public function __construct(
		CorporationTypeService $CorporationTypeService,
		PrefectureService $PrefectureService
	) {
		$this->CorporationTypeService = $CorporationTypeService;
		$this->PrefectureService = $PrefectureService;
	}

	public function __invoke() {
		// 法人種別取得
		$res = $this->CorporationTypeService->search();
		$corporation_types = collect($res['data'])->pluck('display_name', 'corporation_type_id');
		// 都道府県取得
		$res = $this->PrefectureService->search();
		$prefectures = collect($res['data'])->pluck('name', 'prefecture_id');

		return [
			'corporation_types' => $corporation_types,
			'prefectures' => $prefectures,
		];
	}
}