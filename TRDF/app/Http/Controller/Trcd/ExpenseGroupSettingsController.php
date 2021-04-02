<?php

namespace App\Http\Controllers\Trcd;

use Illuminate\Http\Request;
// Services
use App\Services\Trcd\ExpenseGroupSettingService;
// Requests
use App\Http\Requests\Trcd\ExpenseGoupSetting\ExpenseGroupSettingUpdateRequest;

class ExpenseGroupSettingsController extends TrcdBaseController {

	protected $ExpenseGroupSettingService;

	public function __construct(
		ExpenseGroupSettingService $ExpenseGroupSettingService
	) {
		$this->ExpenseGroupSettingService = $ExpenseGroupSettingService;
	}

	/*
		一覧
	*/
	public function index(Request $request) {
		$client_id = $this->_getClientId();
		$expense_group_settings = $this->ExpenseGroupSettingService->buildByClientId($client_id);

		return view('trcd.expense_group_settings.index', compact(
			'expense_group_settings'
		));
	}

	/*
		更新
	*/
	public function update(ExpenseGroupSettingUpdateRequest $request) {
		$client_id = $this->_getClientId();
		$validated = $request->validated();
		$result = false;

		try {
			$result = $this->ExpenseGroupSettingService->insertOrUpdateMany($client_id, $validated);
		} catch (\Exception $e) {
			logger()->error($e->getMessage());
		}

		if ( $result ) {
			$request->session()->flash('success_message', '保存が完了しました。');
			return redirect(route('trcd.expense_group_settings.index'));
		} else {
			return back()->withInput()->withErrors(['data' => '保存に失敗しました。']);
		}
	}
}
