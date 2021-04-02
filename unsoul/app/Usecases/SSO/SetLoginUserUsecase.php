<?php
namespace App\Usecases\SSO;

// Services
use App\Services\EmployeeService;

class SetLoginUserUsecase {
	protected $EmployeeService;

	public function __construct(
		EmployeeService $EmployeeService
	) {
		$this->EmployeeService = $EmployeeService;
	}

	public function __invoke($employee_id) {
		// 検索結果取得
		$employee = $this->EmployeeService->findByIdWithRelated($employee_id);

		return $employee;
	}
}