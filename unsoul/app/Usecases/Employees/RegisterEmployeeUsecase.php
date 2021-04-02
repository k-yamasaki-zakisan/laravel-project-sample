<?php
namespace App\Usecases\Employees;

// Services
use App\Services\GenderService;
use App\Services\PrefectureService;
use App\Services\EmploymentStatusService;
// 統合API用
//use App\Libraries\IntegratedAPI\SearchCondition;
// Utilities
use Carbon\Carbon;

class RegisterEmployeeUsecase {
	protected $GenderService;
	protected $PrefectureService;
	protected $EmploymentStatusService;

	public function __construct(
		GenderService $GenderService,
		PrefectureService $PrefectureService,
		EmploymentStatusService $EmploymentStatusService
	) {
		$this->GenderService = $GenderService;
		$this->PrefectureService = $PrefectureService;
		$this->EmploymentStatusService = $EmploymentStatusService;
	}

	public function __invoke() {
		// 性別取得
		$res = $this->GenderService->search();
		$genders = collect($res['data'])->pluck('name', 'gender_id');
		// 雇用形態(代表は除く)
		$res = $this->EmploymentStatusService->search();
		$employment_statuses = collect($res['data'])->where('name', '<>', '代表')->sortBy('sort_index')->pluck('name', 'employment_status_id');
		// 都道府県取得
		$res = $this->PrefectureService->search();
		$prefectures = collect($res['data'])->pluck('name', 'prefecture_id');

		return compact(
			'genders',
            		'employment_statuses',
			'prefectures'
		);
	}
}