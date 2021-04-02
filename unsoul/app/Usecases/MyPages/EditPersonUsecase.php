<?php
namespace App\Usecases\MyPages;

// Services
use App\Services\PersonService;
use App\Services\GenderService;
use App\Services\CorporationService;
// 統合API用
use App\Libraries\IntegratedAPI\SearchCondition;

class EditPersonUsecase {
	protected $PersonService;
	protected $GenderService;
	protected $CorporationService;

	public function __construct(
		PersonService $PersonService,
		GenderService $GenderService,
		CorporationService $CorporationService
	) {
		$this->PersonService = $PersonService;
		$this->GenderService = $GenderService;
		$this->CorporationService = $CorporationService;
	}

	public function __invoke($person_id, $corporaiton_id) {
		//人情報の取得
		$person = $this->PersonService->findById($person_id);

		//性別リスト取得
		$genders_data = $this->GenderService->search();
		$genders = collect($genders_data['data'])->pluck('name', 'gender_id');

		//事業所リスト取得
		$corporaiton_data_with_offices = $this->CorporationService->findByIdWithRelated($corporaiton_id);
		$head_office_exist = collect($corporaiton_data_with_offices['offices'])->contains('head_office_flg', true);
		$offices = collect($corporaiton_data_with_offices['offices'])->where('head_office_flg', false)->sortBy('name')->pluck('name', 'link_key');

		return [
			'last_name' => $person['last_name'],
			'first_name' => $person['first_name'],
			'last_name_kana' => $person['last_name_kana'],
			'first_name_kana' => $person['first_name_kana'],
			'birthday' => $person['birthday'],
			'now_gender_id' => $person['gender_id'],
			'genders' => $genders,
			'head_office_exist' => $head_office_exist,
			'offices' => $offices,
		];
	}
}