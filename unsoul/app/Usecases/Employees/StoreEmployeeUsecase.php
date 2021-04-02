<?php
namespace App\Usecases\Employees;

// Services
use App\Services\EmployeeService;
use App\Services\ContactTypeService;
use App\Services\JobCareerStatusService;
// Utilities
use Illuminate\Support\Str;

class StoreEmployeeUsecase {
	protected $EmployeeService;
	protected $ContactTypeService;
	protected $JobCareerStatusService;

	public function __construct(
		EmployeeService $EmployeeService,
		ContactTypeService $ContactTypeService,
		JobCareerStatusService $JobCareerStatusService
	) {
		$this->EmployeeService = $EmployeeService;
		$this->ContactTypeService = $ContactTypeService;
		$this->JobCareerStatusService = $JobCareerStatusService;
	}

	public function __invoke(Array $request_data) {
		$data = $this->buildData($request_data);

		// 保存
		$employee = $this->EmployeeService->store($data);

		return $employee;
	}

	/*
		統合API送信用データ生成
		@params Array $search_conditions リクエストされた検索条件
		@return Array
	*/
	protected function buildData(Array $request_data) {
		$data = [
            		'updated_by' => auth()->user()->person['link_key'], // ToDo:ここでログイン情報を参照するのはどうかと思う
			'employee' => [
				'corporation_id' => auth()->user()->corporation['corporation_id'],
				'code' => $request_data['employee_code'] ?? null,
				'last_name' => $request_data['last_name'],
				'first_name' => $request_data['first_name'],
				'last_name_kana' => $request_data['last_name_kana'],
				'first_name_kana' => $request_data['first_name_kana'],
                		'birthday' => $request_data['birthday'] ?? null,
				'gender_id' => $request_data['gender_id'] ?? null,
                		'hire_date' => $request_data['hire_date'] ?? null,
				'basic_pension_number' => $request_data['joined_basic_pension_number'] ?? null,
			],
            		'person' => [
				'login_id' => Str::random(100)."@".Str::random(100).".com",
				'password' => '12345678',
				'last_name' => $request_data['last_name'],
				'first_name' => $request_data['first_name'],
				'last_name_kana' => $request_data['last_name_kana'],
				'first_name_kana' => $request_data['first_name_kana'],
				'birthday' => $request_data['birthday'] ?? null,
                		'gender_id' => $request_data['gender_id'] ?? null,
                		'basic_pension_number' => $request_data['joined_basic_pension_number'] ?? null,
			],

		];

		#住所関連の入力があればリクエストの作成
		if ( !empty($request_data['zip_code1']) || !empty($request_data['zip_code2']) ||
			!empty($request_data['prefecture_id']) || !empty($request_data['city']) ||
			!empty($request_data['town']) || !empty($request_data['street']) ||
			!empty($request_data['building']) || !empty($request_data['address_kana'])
		) {
			$data['employee_address'] = [
				'zip_code1' => $request_data['zip_code1'] ?? null,
                                'zip_code2' => $request_data['zip_code2'] ?? null,
                                'prefecture_id' => $request_data['prefecture_id'] ?? null,
                                'city' => $request_data['city'] ?? null,
                                'town' => $request_data['town'] ?? null,
                                'street' => $request_data['street'] ?? null,
                                'building' => $request_data['building'] ?? null,
                                'address_kana' => $request_data['address_kana'] ?? null,
			];
		}

		$person_contacts = [];
		// employee_contactsの登録
		// contact_type_id特定用変数を用意
		if ( !empty($request_data['mobile']) || !empty($request_data['email_address']) ) {
			$contact_types_data = collect($this->ContactTypeService->search())->pluck('contact_type_id', 'slug');
		}

        	if ( !empty($request_data['mobile']) ) {
            		$person_contacts[] = [
				'contact_type_id' => $contact_types_data['mobile'],
                		'value' => $request_data['mobile'],
            		];
        	}

        	if ( !empty($request_data['email_address']) ) {
            		$person_contacts[] = [
                		'contact_type_id' => $contact_types_data['email'],
				'value' => $request_data['email_address'],
            		];
        	}

        	if ( !empty($person_contacts) ) $data['employee_contacts'] = $person_contacts;

		// employee_job_careersの登録
		if ( !empty($request_data['hire_date']) && !empty($request_data['employment_status_id']) ) {
			$job_career_statuses_data = collect($this->JobCareerStatusService->search())->pluck('job_career_status_id', 'slug');
			$data['employee_job_careers'][] = [
				'corporation_id' => auth()->user()->corporation['corporation_id'],
				'last_name' => $request_data['last_name'],
				'first_name' => $request_data['first_name'],
				'last_name_kana' => $request_data['last_name_kana'],
				'first_name_kana' => $request_data['first_name_kana'],
				'employment_status_id' => $request_data['employment_status_id'],
				'job_career_status_id' => $job_career_statuses_data['join'] ?? null,
				'applied_at' => $request_data['hire_date'],
			];
		}

		return $data;
	}
}