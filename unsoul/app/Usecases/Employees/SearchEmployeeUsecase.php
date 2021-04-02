<?php
namespace App\Usecases\Employees;

// Services
use App\Services\EmployeeService;
use App\Services\GenderService;
// Requests
use App\Http\Requests\Employees\SearchEmployeeRequest;
// 統合API用
use App\Libraries\IntegratedAPI\SearchCondition;
// Pagination
use Illuminate\Pagination\LengthAwarePaginator;
// Utilities
use Carbon\Carbon;
use Illuminate\Support\Arr;

class SearchEmployeeUsecase {
	protected $per_page = 50; // 画面に表示する件数
	protected $EmployeeService;
    protected $GenderService;
    protected $ColGenderList;

	public function __construct(
		EmployeeService $EmployeeService,
		GenderService $GenderService
	) {
		$this->EmployeeService = $EmployeeService;
		$this->GenderService = $GenderService;
	}

	public function __invoke(SearchEmployeeRequest $request) {
		$validated = $request->validated();
		$search = $validated['search'] ?? [];
		$page = $validated['page'] ?? null;

		$genders = $this->getGenderList();
		$job_statuses = $this->getJobStatusList();

		// ToDo:ここに書くのは良くないっぽいので修正すること
		validator($search, [
			'gender_id' => ['in:0,'. join(',', $genders->keys()->toArray())],
			'job_status' => ['in:'. join(',', $job_statuses->keys()->toArray())]
		])->validate();

		// 検索結果取得
		$search_conditions = $this->buildSearchConditions($search);
		$search_result = $this->EmployeeService->advancedSearch($search_conditions);
dump($search_result);
		// 一覧表示並び替え(昇順)
		//$search_result = collect($search_result)->sortByDesc($)
		foreach( $search_result['data'] as $key => $employee ) {
			// 住所
			$address = collect($employee['employee_addresses'])
				->sortByDesc(function($value, $key) {
                    			// nullは後ろ、indexが同じ場合はidで判定
                    			return ($value['number'] ?? 0) . "_{$value['employee_address_id']}";
				})->pluck('address')
				->first();

			// 連絡先
			$contact  = collect(Arr::where($employee['employee_contacts'], function($value, $key) {
				$slug = $value['contact_type']['slug'];
				// 携帯 > 固定電話
				return $slug === 'mobile' || $slug === 'tel';
			}))->sortBy(function($value, $key) {
				$slug = $value['contact_type']['slug'];
				// nullは後ろ、indexが同じ場合はidで判定
				return "{$slug}_" . ($value['sort_index'] ?? 'Z') . "_{$value['employee_contact_id']}";
			})->pluck('value')
			->first();

			// 携帯・固定電話が設定されていなければ住所を表示
			if ( empty($contact) ) $contact = $address;
			$search_result['data'][$key]['address'] = $address;
			$search_result['data'][$key]['contact'] = $contact;
		}

		// Paginator生成
		$Paginator = new LengthAwarePaginator(
			$search_result['data'],
			$search_result['summary']['total'],
			$this->per_page,
			$page,
			['path' => $request->url()]
		);
		$Paginator->appends(['search' => $search]);
dd($Paginator);
		return [
			'employees' => $Paginator,
			'genders' => $genders,
			'job_statuses' => $job_statuses,
			'search' => $search,
		];
	}

    /*
        性別リスト取得
    */
    public function getGenderList() {
        $SearchCondition = SearchCondition::newCondition()
            ->addFields([
                'gender_id',
                'name'
            ])
        ;

        $data = $this->GenderService->search($SearchCondition->toArray());

        return collect($data['data'])->pluck('name', 'gender_id');
    }

    /*
        在職状況リスト取得
        return Colection
    */
    public function getJobStatusList() {
        return collect([
            'WORKING' => '在職中',
            'RETIRED' => '退職済み',
            'ALL' => 'すべて',
        ]);
    }

    protected function buildSearchConditions(Array $request_data) {
        // 入社日フォーマットを年月から年月日に変換（API側に合わせるため）
        if ( !empty($request_data['hire_date']) ) $request_data['hire_date'] = Carbon::parse($request_data['hire_date'])->startOfMonth()->format('Y-m-d');

        if ( empty($request_data['gender_id']) ) $request_data['gender_id'] = null;

        $request_data['corporation_id'] = auth()->user()->employee['corporation_id'];
        // 表示件数指定
        $request_data['limit'] = $this->per_page;

        return $request_data;
    }
}