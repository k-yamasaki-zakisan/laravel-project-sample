<?php
namespace App\Usecases\Licenses;

// Services
use App\Services\PersonService;
// Requests
use App\Http\Requests\Licenses\SearchLicenseRequest;
// 統合API用
use App\Libraries\IntegratedAPI\SearchCondition;
// Utilities
use Carbon\Carbon;
use Illuminate\Support\Arr;

class SearchLicenseUsecase {
	protected $SearchCondition;
	protected $PersonService;

	public function __construct(
		SearchCondition $SearchCondition,
		PersonService $PersonService
	) {
		$this->SearchCondition = $SearchCondition;
		$this->PersonService = $PersonService;
	}

	public function __invoke(SearchLicenseRequest $request,$person_id) {
        $data = $this->PersonService->findByIdWithRelated($person_id);

        if ( empty($data) ) return $data;

        $result = [
            'person' => Arr::only($data, [
                'person_id',
                'full_name',
            ]),
            'person_licenses' => [],
        ];

        foreach( $data['person_licenses'] as $person_license ) {
            $tmp_values = Arr::only($person_license, [
               'license_id',
               'note',
               'link_key',
            ]);
            // 資格名
            $tmp_values['name'] = $person_license['license']['name'] ?? null;

            // 取得年月日整形処理
            $date_format = (empty($person_license['acquisition_year']) ? null : 'Y')
                . (empty($person_license['acquisition_month']) ? null : '-n')
                . (empty($person_license['acquisition_date']) ? null : '-j')
            ;
            $acquired_at = null;

            if ( empty($date_format) ) {
                // 未入力の場合は何もしない
            } else if( $date_format === 'Y-n-j' ) {
                // 年月日
                $acquired_at = Carbon::createFromFormat(
                    $date_format,
                    "{$person_license['acquisition_year']}-{$person_license['acquisition_month']}-{$person_license['acquisition_date']}"
                )->format('Y/m/d');
            } else if ( $date_format === 'Y-n' ) {
                // 年月
                $acquired_at = Carbon::createFromFormat(
                    $date_format,
                    "{$person_license['acquisition_year']}-{$person_license['acquisition_month']}"
                )->format('Y/m');
            } else {
                // それ以外の形は仕様上不正な形だが、一旦表示だけしておく
                $acquired_at = $person_license['acquisition_year'] ?? 'YYYY'
                    . '/' . $person_license['acquisition_month'] ?? 'mm'
                    . '/' . $person_license['acquisition_date'] ?? 'dd'
                ;
            }

            $tmp_values['acquired_at'] = $acquired_at;
            $tmp_values['expired_at'] = empty($person_license['expired_at']) ? null : Carbon::parse($person_license['expired_at'])->format('Y/m/d');

            $result['person_licenses'][] = $tmp_values;
        }

        return $result;
	}
}