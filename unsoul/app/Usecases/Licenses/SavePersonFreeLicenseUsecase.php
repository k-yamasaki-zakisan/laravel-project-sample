<?php
namespace App\Usecases\Licenses;

// Services
use App\Services\PersonLicenseService;
// 統合API用
use App\Libraries\IntegratedAPI\SearchCondition;
// Utilities
use Carbon\Carbon;

class SavePersonFreeLicenseUsecase {
	protected $PersonLicenseService;
	protected $SearchCondition;

	public function __construct(
		PersonLicenseService $PersonLicenseService,
		SearchCondition $SearchCondition
	) {
		$this->PersonLicenseService = $PersonLicenseService;
		$this->SearchCondition = $SearchCondition;
	}

	public function __invoke(Array $request_data) {
		$data = $this->buildData($request_data);

		$conditions = $this->buildConditions($data['person_license']);
		$free_license = collect($this->PersonLicenseService->search($conditions))->first();

		//登録済みのfree_licenseの有無により登録、更新処理を分岐
		if ( empty($free_license) ) {
			return $this->PersonLicenseService->save($data);
		} else {
			//更新時にlink_key必須のため追加
			$data['person_license'] += ['link_key' => $free_license['link_key']];

			return $this->PersonLicenseService->update($data);
		}
	}

	/*
		登録用データ整形
		@params Array $request_data リクエストされた登録データ
		@return Array
	*/
	protected function buildData(Array $request_data) {
		if ( isset($request_data['acquisition']) ) {
			$acquisition = new Carbon($request_data['acquisition']);
		}

		$data = [
			'updated_by' => $request_data['updated_by'],
			'person_license' => [
				'person_id' => $request_data['person_id'],
				'license_id' => 330,
				'acquisition_year' => isset($acquisition) ? (int) $acquisition->format('Y') : Null,
				'acquisition_month' => isset($acquisition) ? (int) $acquisition->format('m') : Null,
				'acquisition_date' => isset($acquisition) ? (int) $acquisition->format('d') : Null,
				'expired_at' => $request_data['expired_at'] ?? Null,
				'note' => $request_data['note'],
			],
		];

		return $data;
	}

	/*
		統合API用検索データ生成
		@params Array $search_conditions リクエストされた検索条件
		@return Array
	*/
	protected function buildConditions(Array $search_conditions) {
		foreach( $search_conditions as $key => $value ) {
			switch($key) {
				case('person_id'):
				case('license_id'):
					if ( isset($value) ) $this->SearchCondition->addWhereEqual($key, $value);
					break;
				default:
					break;
			}
		}

		return $this->SearchCondition->toArray();
	}
}