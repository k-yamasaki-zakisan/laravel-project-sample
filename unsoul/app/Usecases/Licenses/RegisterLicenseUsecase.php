<?php
namespace App\Usecases\Licenses;

// Services
use App\Services\LicenseService;
use App\Services\LicenseCategoryService;
// 統合API用
use App\Libraries\IntegratedAPI\SearchCondition;

class RegisterLicenseUsecase {
	protected $LicenseService;
	protected $LicenseCategoryService;
	protected $SearchCondition;

	public function __construct(
		LicenseService $LicenseService,
		LicenseCategoryService $LicenseCategoryService,
		SearchCondition $SearchCondition
	) {
		$this->LicenseService = $LicenseService;
		$this->LicenseCategoryService = $LicenseCategoryService;
		$this->SearchCondition = $SearchCondition;
	}

	public function __invoke(Array $license_request = []) {
		if ( !empty($license_request) ) {
			$conditions = $this->buildConditions($license_request);

			$licenses = $this->LicenseService->getList($conditions);
		} else {
			$licenses = [];
		}

		//資格カテゴリーリスト取得
		$license_categories = $this->LicenseCategoryService->search();
		// 配列の宣言
		$license_category = [];

		// 取得データの整形
		foreach($license_categories as $key => $value){
			if ($value['name'] == 'フリー入力') continue;
	    		$license_category[$key]['license_category_id'] = $value['license_category_id'];
	    		$license_category[$key]['name'] = $value['name'];
	    		$license_category[$key]['sort_index'] = $value['sort_index'];
		}

		return [
			'license_categories' => $license_category,
			'licenses' => $licenses,
		];

	}

	/*
		統合API用検索データ生成
		@params Array $search_conditions リクエストされた検索条件
		@return Array
	*/
	protected function buildConditions(Array $search_conditions) {
		$this->SearchCondition->addFields([
			'license_id',
			'name',
		]);

		foreach( $search_conditions as $key => $value ) {
			switch($key) {
				case('license_category_id'):
					if ( isset($value) ) $this->SearchCondition->addWhereEqual($key, $value);
					break;
				default:
					break;
			}
		}

		return $this->SearchCondition->toArray();
	}

}