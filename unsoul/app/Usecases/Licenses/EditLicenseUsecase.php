<?php
namespace App\Usecases\Licenses;

// Services
use App\Services\LicenseService;
// Requests
use App\Http\Requests\Licenses\SearchLicenseRequest;
use Illuminate\Http\Request;
// 統合API用
use App\Libraries\IntegratedAPI\SearchCondition;
// Utilities
use Carbon\Carbon;
use Illuminate\Support\Arr;

class EditLicenseUsecase {
	protected $LicenseService;

	public function __construct(
		LicenseService $LicenseService
	) {
		$this->LicenseService = $LicenseService;
	}

	public function __invoke($person_license_link_key) {
	$search_conditions = ['link_key' => $person_license_link_key];
       // 統合API用検索データ生成
        $conditions = $this->buildConditions($search_conditions);

        $arrResponse = $this->LicenseService->edit($conditions);

        // 配列の宣言
        $licenses = [];

	// 取得データの整形
        foreach($arrResponse['data'] as $key => $person_license){
            $tmp_values = Arr::only($person_license, [
                'license_id',
                'person_id',
                'link_key',
                'expired_at',
		'note',
		'acquisition_year',
		'acquisition_month',
		'acquisition_date',
            ]);

            // 資格名
            $tmp_values['name'] = $person_license['license']['name'];

            // 資格取得期間の指定
            $year_range = [];
            for($i = 1970; $i <= Carbon::now()->format('Y'); $i++ ) {
                $year_range[] = $i;
            }
            $tmp_values['year_range'] = $year_range;

            $month_range = [];
            for($i = 1; $i <= 12; $i++ ) {
                $month_range[] = $i;
            }
            $tmp_values['month_range'] = $month_range;

            $date_range = [];
            for($i = 1; $i <= 31; $i++ ) {
                $date_range[] = $i;
            }
            $tmp_values['date_range'] = $date_range;

            // 取得日生成
            // View側でinput[type="date"]になっているので年月日全てが記入されている場合のみView側に渡す
            $acquisition = null;

            if ( !empty($person_license['acquisition_year']) && !empty($person_license['acquisition_month']) && !empty($person_license['acquisition_date']) ) {
                // 年月日
                $acquisition = Carbon::createFromFormat(
                    'Y-n-j',
                    "{$person_license['acquisition_year']}-{$person_license['acquisition_month']}-{$person_license['acquisition_date']}"
                )->format('Y-m-d');
            }
            $tmp_values['acquisition'] = $acquisition;

            $licenses[] = $tmp_values;
        }

	return $licenses;
	}

	/*
		統合API用検索データ生成
		@params Array $search_conditions リクエストされた検索条件
		@return Array
	*/
	protected function buildConditions(Array $search_conditions) {
		$conditions = [];
        // link_key
		if ( isset($search_conditions['link_key']) ) $conditions['link_key'] = $search_conditions['link_key'];

		return $conditions;
	}

}