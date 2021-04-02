<?php
namespace App\Usecases\Employees;

// Services
use App\Services\EmployeeService;
use App\Services\ContactTypeService;
use App\Services\GenderService;
use App\Services\PrefectureService;
use App\Services\EmploymentStatusService;
use App\Services\JobCareerStatusService;
// 統合API用
use App\Libraries\IntegratedAPI\SearchCondition;
// Utilities
use Carbon\Carbon;

class EditEmployeeUsecase {
	protected $SearchCondition;
	protected $EmployeeService;
	protected $ContactTypeService;
	protected $GenderService;
	protected $PrefectureService;
	protected $EmploymentStatusService;
	protected $JobCareerStatusService;

	public function __construct(
		SearchCondition $SearchCondition,
		EmployeeService $EmployeeService,
		ContactTypeService $ContactTypeService,
		GenderService $GenderService,
		PrefectureService $PrefectureService,
		EmploymentStatusService $EmploymentStatusService,
		JobCareerStatusService $JobCareerStatusService
	) {
		$this->SearchCondition = $SearchCondition;
		$this->EmployeeService = $EmployeeService;
		$this->ContactTypeService = $ContactTypeService;
		$this->GenderService = $GenderService;
		$this->PrefectureService = $PrefectureService;
		$this->EmploymentStatusService = $EmploymentStatusService;
		$this->JobCareerStatusService = $JobCareerStatusService;
	}

	public function __invoke($link_key) {
		// 検索結果取得
		$conditions = $this->buildConditions($link_key);
		//link_keyからemployee_idを取得
		$tmp_data = collect($this->EmployeeService->search($conditions))->first();
		//employee_idから関連情報の取得
		$result_data = $this->EmployeeService->findByIdWithRelated($tmp_data['employee_id']);

		$data = $this->buildData($result_data);
//dd($data, $result_data);
		return $data;
	}

	/*
		統合API用検索データ生成
		@params Array $search_conditions リクエストされた検索条件
		@return Array
	*/
	protected function buildConditions($link_key) {
		$this->SearchCondition->addFields([
			'employee_id',
		]);
		$this->SearchCondition->addWhereEqual('link_key', $link_key);

		return $this->SearchCondition->toArray();
	}

	/*
                表示用のデータ整形
                @params Array $result_data(加工するデータ群)
                @return Array
        */
	protected function buildData($result_data) {
		//ナンバーが最も大きな住所の取得
		if( !empty($result_data['employee_addresses']) ) {
			$employee_address = collect($result_data['employee_addresses'])->sortByDesc('number')->first();
		}

		//（携帯とメール）連絡先の取得
		if( !empty($result_data['employee_contacts']) ) {
			$contact_types = collect($this->ContactTypeService->search())->pluck('contact_type_id', 'slug');
			$mobiles = collect($result_data['employee_contacts'])->where('contact_type_id', $contact_types['mobile']);
			$emails = collect($result_data['employee_contacts'])->where('contact_type_id', $contact_types['email']);
		}

		//最新の法人所属経歴を取得
		if( !empty($result_data['employee_job_careers']) ) {
			$job_career_statuses = collect($this->JobCareerStatusService->search())->pluck('job_career_status_id', 'slug');
			$last_employee_job_career = collect($result_data['employee_job_careers'])->sortByDesc('applied_at')->first();
			//$job_career_suspension = collect($result_data['employee_job_careers'])->where('job_career_status_id', $job_career_statuses['suspension'])->sortByDesc('applied_at')->first();
			//$job_career_reinstatement = collect($result_data['employee_job_careers'])->where('job_career_status_id', $job_career_statuses['reinstatement'])->sortByDesc('applied_at')->first();
		}

		// 性別リスト取得
		$res = $this->GenderService->search();
		$genders = collect($res['data'])->pluck('name', 'gender_id');
		// 雇用形態リスト取得
		$res = $this->EmploymentStatusService->search();
		$employment_statuses = collect($res['data'])->sortBy('sort_index')->pluck('name', 'employment_status_id');
		// 都道府県リスト取得
		$res = $this->PrefectureService->search();
		$prefectures = collect($res['data'])->pluck('name', 'prefecture_id');

		return  [
			'code' => $result_data['code'] ?? null,
			'last_name' => $result_data['last_name'],
			'first_name' => $result_data['first_name'],
			'last_name_kana' => $result_data['last_name_kana'],
			'first_name_kana' => $result_data['first_name_kana'],
			'birthday' => $result_data['birthday'] ?? null,
			'now_gender_id' => $result_data['gender_id'] ?? null,
			'now_employment_status_id' => $last_employee_job_career['employment_status_id'] ?? null,
			'zip_code1' => $employee_address['zip_code1'] ?? null,
			'zip_code2' => $employee_address['zip_code2'] ?? null,
			'now_prefecture_id' => $employee_address['prefecture_id'] ?? null,
			'city' => $employee_address['employee_address'] ?? null,
			'town' => $employee_address['town'] ?? null,
			'street' => $employee_address['street'] ?? null,
			'building' => $employee_address['building'] ?? null,
			'address_kana' => $employee_address['address_kana'] ?? null,
			'mobiles' => $mobiles ?? [],
			'emails' => $emails ?? [],
			'hire_date' => !empty($result_data['hire_date']) ? Carbon::parse($result_data['hire_date'])->format('Y-m-d') : null,
			'retirement_date' => !empty($result_data['retirement_date']) ? Carbon::parse($result_data['retirement_date'])->format('Y-m-d') : null,
			//'suspension' => !empty($job_career_suspension['applied_at']) ? Carbon::parse($job_career_suspension['applied_at'])->format('Y-m-d') : null,
			//'reinstatement' => !empty($job_career_reinstatement['applied_at']) ? Carbon::parse($job_career_reinstatement['applied_at'])->format('Y-m-d') : null,
			'basic_pension_number_1' => implode("", array_slice(str_split($result_data['basic_pension_number']), 0, 4)) ?? null,
			'basic_pension_number_2' => implode("", array_slice(str_split($result_data['basic_pension_number']), 4, 9)) ?? null,
			//'basic_pension_number1' => $result_data['basic_pension_number'][0] ?? null,
			//'basic_pension_number2' => $result_data['basic_pension_number'][1] ?? null,
			//'basic_pension_number3' => $result_data['basic_pension_number'][2] ?? null,
			//'basic_pension_number4' => $result_data['basic_pension_number'][3] ?? null,
			//'basic_pension_number5' => $result_data['basic_pension_number'][4] ?? null,
			//'basic_pension_number6' => $result_data['basic_pension_number'][5] ?? null,
			//'basic_pension_number7' => $result_data['basic_pension_number'][6] ?? null,
			//'basic_pension_number8' => $result_data['basic_pension_number'][7] ?? null,
			//'basic_pension_number9' => $result_data['basic_pension_number'][8] ?? null,
			//'basic_pension_number10' => $result_data['basic_pension_number'][9] ?? null,
			'genders' => $genders,
			'employment_statuses' => $employment_statuses,
			'prefectures' => $prefectures,
			'link_key' => $result_data['link_key'],
		];
	}
}