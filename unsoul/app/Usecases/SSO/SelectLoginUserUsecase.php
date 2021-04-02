<?php
namespace App\Usecases\SSO;

// Services
use App\Services\EmployeeService;
// 統合API用
use App\Libraries\IntegratedAPI\SearchCondition;

class SelectLoginUserUsecase {
	protected $SearchCondition;
	protected $EmployeeService;

	public function __construct(
		EmployeeService $EmployeeService
	) {
		$this->EmployeeService = $EmployeeService;
	}

	public function __invoke(Array $session_data) {
		// 統合API用検索データ生成
		$conditions = $this->buildConditions($session_data);
		// 検索結果取得
		$search_result = $this->EmployeeService->search($conditions);

		// 2020.10.19 返却値にdataがあればその中身を、なければ返却値をそのまま取得
		$search_result = array_key_exists('data', $search_result) ? $search_result['data'] ?? [] : $search_result;

        foreach( $search_result as $key => $employee ) {
            // 企業に属していない場合は除去（企業が論理削除されている場合）
            if ( empty($employee['corporation']) ) unset($search_result[$key]);
        }

        return $search_result;
	}

	/*
		統合API用検索データ生成
		@params Array $search_conditions リクエストされた検索条件
		@return Array
	*/
	protected function buildConditions(Array $session_data) {
		$SearchCondition = SearchCondition::newCondition()
            ->addWhereEqual('person_id', $session_data['eduPersonPrincipalName'])
            ->addWith([
                'person',
                'corporation',
            ])
        ;


		return $SearchCondition->toArray();
	}
}