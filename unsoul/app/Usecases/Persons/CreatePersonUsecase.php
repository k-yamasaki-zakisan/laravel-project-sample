<?php
namespace App\Usecases\Persons;

// Services
use App\Services\GenderService;
// 統合API用
use App\Libraries\IntegratedAPI\SearchCondition;

class CreatePersonUsecase {
	protected $GenderService;

	public function __construct(
		GenderService $GenderService
	) {
		$this->GenderService = $GenderService;
	}

	public function __invoke() {
		// 性別取得
		$res = $this->GenderService->search();

		$genders = collect($res['data'])->pluck('name', 'gender_id');

		return ['genders' => $genders];
	}
}