<?php

use App\Services\Superadmin\ExhibitionsService;

namespace App\Http\Controllers\Trcd;

use Illuminate\Http\Request;
// Services
use App\Services\Trcd\TrcdTerminalNotificationSettingService;
use App\Services\Trcd\BalanceThresholService;
// Requests
use App\Http\Requests\Trcd\TrcdTerminalNotificationSetting\TrcdTerminalNotificationSettingUpdateRequest;

class TrcdTerminalNotificationSettingsController extends TrcdBaseController
{

	protected $TrcdTerminalNotificationSettingService;
	protected $BalanceThresholService;

	public function __construct(
		TrcdTerminalNotificationSettingService $TrcdTerminalNotificationSettingService,
		BalanceThresholService $BalanceThresholService
	) {
		$this->TrcdTerminalNotificationSettingService = $TrcdTerminalNotificationSettingService;
		$this->BalanceThresholService = $BalanceThresholService;
	}

	/*
		一覧
	*/
	public function index(Request $request)
	{
		$client_id = $this->_getClientId();

		// 残高閾値設定情報取得
		$balance_threshold = $this->BalanceThresholService->getByClientId($client_id);
		// TRCD端末通知設定情報取得
		$trcd_terminal_notification_settings = $this->TrcdTerminalNotificationSettingService->getByClientId($client_id);

		return view('trcd.trcd_terminal_notification_settings.index', compact(
			'balance_threshold',
			'trcd_terminal_notification_settings'
		));
	}

	/*
                更新（通知設定、通知先、残高閾値）
        */
	public function update(TrcdTerminalNotificationSettingUpdateRequest $request)
	{
		$client_id = $this->_getClientId();
		$validated = $request->validated();
		$result = false;

		try {
			$result = $this->TrcdTerminalNotificationSettingService->updateWithRelationAndBalanceThreshold($client_id, $validated);
		} catch (\Exception $e) {
			logger()->error($e->getMessage());
		}

		if ($result) {
			$request->session()->flash('success_message', '保存が完了しました。');
			return redirect(route('trcd.trcd_terminal_notification_settings.index'));
		} else {
			return back()->withInput()->withErrors(['data' => '保存に失敗しました。']);
		}
	}
}
