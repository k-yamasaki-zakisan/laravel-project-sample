<?php
/***
 * 残高不足通知サービス
 *
 * @author YuKaneko
 */

namespace App\Services\Notifications;

use App\Services\ServiceBase;
// Repositories
use App\Repositories\Trcd\TrcdTerminalRepositoryInterface AS TrcdTerminalRepository;
use App\Repositories\Trcd\BalanceThresholdRepositoryInterface AS BalanceThresholdRepository;
use App\Repositories\Trcd\TrcdTerminalNotificationSettingRepositoryInterface AS TrcdTerminalNotificationSettingRepository;
use App\Repositories\Trcd\TrcdTerminalNotificationDestinationRepositoryInterface AS TrcdTerminalNotificationDestinationRepository;
use App\Repositories\Trcd\TrcdTerminalChangeRecordRepositoryInterface AS TrcdTerminalChangeRecordRepository;
// Mailable
use App\Mail\NotifyTrcdTerminalBalanceShortage;
// Etc
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class TrcdTerminalBalanceNotificationService extends ServiceBase {

	protected $objTrcdTerminalRepo;
	protected $objBalanceThresholdRepo;
	protected $objTrcdTerminalNotificationSettingRepo;
	protected $objTrcdTerminalNotificationDestinationRepo;
	protected $objTrcdTerminalChangeRecordRepo;

	/*
		コンストラクタ
	*/
	public function __construct(
		TrcdTerminalRepository $objTrcdTerminalRepo,
		BalanceThresholdRepository $objBalanceThresholdRepo,
		TrcdTerminalNotificationSettingRepository $objTrcdTerminalNotificationSettingRepo,
		TrcdTerminalNotificationDestinationRepository $objTrcdTerminalNotificationDestinationRepo,
		TrcdTerminalChangeRecordRepository $objTrcdTerminalChangeRecordRepo
	){
		$this->objTrcdTerminalRepo = $objTrcdTerminalRepo;
		$this->objBalanceThresholdRepo = $objBalanceThresholdRepo;
		$this->objTrcdTerminalNotificationSettingRepo = $objTrcdTerminalNotificationSettingRepo;
		$this->objTrcdTerminalNotificationDestinationRepo = $objTrcdTerminalNotificationDestinationRepo;
		$this->objTrcdTerminalChangeRecordRepo = $objTrcdTerminalChangeRecordRepo;
	}

	/*
		残高不足通知通知（リアルタイム型用）
		@param App\TrcdTerminalChangeRecord $TrcdTerminalChangeRecord
		@return void
	*/
	public function notifyIfLowerThanThreshold(\App\TrcdTerminalChangeRecord $TrcdTerminalChangeRecord) {
		$TrcdTerminal = $TrcdTerminalChangeRecord->trcd_terminal;

		// （可能性はほぼほぼないが）紐づくTRCD端末が取得出来ない場合は終了
		if ( empty($TrcdTerminal) ) return;

		// 端末に紐づく通知設定（リアルタイムOn）を取得 ToDo:hasでexistsを利用した相関サブクエリが生成されているので注意
		$ColTrcdTerminalNotificationSettings = $this->objTrcdTerminalNotificationSettingRepo
			->select(['id', 'client_id'])
			->where('trcd_terminal_id', $TrcdTerminal['id'])
			->where('realtime_flag', true)
			->has('trcd_terminal_notification_destinations')
			->with(['trcd_terminal_notification_destinations:email,trcd_terminal_notification_setting_id'])
			->get();

		// 通知設定がない場合は終了
		if ( $ColTrcdTerminalNotificationSettings->isEmpty() ) return;

		// 閾値設定を取得
		$ColBalanceThresholds = $this->objBalanceThresholdRepo
			->whereIn('client_id', $ColTrcdTerminalNotificationSettings->pluck('client_id'))
			->get()
			->keyBy('client_id');


		// 送信用データ生成処理
		$tmp_summaries = collect();
		$send_at = now()->format('Y-m-d H:i:s');

		foreach( $ColTrcdTerminalNotificationSettings as $TrcdTerminalNotificationSetting ) {
			try{
				$BalanceThreshold = $ColBalanceThresholds[$TrcdTerminalNotificationSetting['client_id']] ?? null;

				// （おそらくないが）閾値設定のない企業の場合はスキップ
				if ( empty($BalanceThreshold) ) continue;

				// 閾値チェック
				$check_threshold_result = $this->checkThreshold($TrcdTerminalChangeRecord, $BalanceThreshold);

				// 閾値チェックに引っかかっていないものはスキップ
				if ( empty($check_threshold_result) ) continue;
			
				$tmp_summaries->push([
					'trcd_terminal_name' => $TrcdTerminal['name'],
					'send_at' => $send_at,
					'trcd_terminal_destinations' => $TrcdTerminalNotificationSetting['trcd_terminal_notification_destinations']->pluck('email'),
					'summaries' => $check_threshold_result,
				]);
			} catch(\Exception $e){
				logger()->error("{$e->getMessage()} in {$e->getFile()} at Line:{$e->getLine()}");
				logger()->error('リアルタイム型残高不足通知:情報収集処理失敗'. print_r($TrcdTerminalNotificationSetting->toArray(), true));
			}
		}

		// メール送信処理
		foreach( $tmp_summaries as $tmp_summary ) {
			try {
				Mail::to($tmp_summary['trcd_terminal_destinations'])
					->send(new NotifyTrcdTerminalBalanceShortage([
						'subject' => "TRCD端末残高不足リアルタイム通知 【端末名：{$tmp_summary['trcd_terminal_name']}】",
						'trcd_temrnal_name' => $tmp_summary['trcd_terminal_name'],
						'send_at' => $tmp_summary['send_at'],
						'summaries' => $tmp_summary['summaries'],
					]));
			} catch( \Exception $e ) {
				logger()->error("{$e->getMessage()} in {$e->getFile()} at Line:{$e->getLine()}");
				logger()->error('リアルタイム型残高不足通知:' . print_r($tmp_summary, true));
			}
		}
	}

	/*
		残高不足通知（バッチ処理用）
		@param Carbon $NotificatedAt
		@return void
	*/
	public function notifyBelowThresholdList(Carbon $NotificatedAt) {
		// 実行日付からTRCD端末設定を取得
		$TrcdTerminalNotificationSettings = $this->objTrcdTerminalNotificationSettingRepo
			->where('notificated_at', $NotificatedAt->format('H:i:00'))
			->with(['trcd_terminal', 'trcd_terminal_notification_destinations'])
			->has('trcd_terminal_notification_destinations')
			->get();
		
		// settingsがなければ終了
		if( $TrcdTerminalNotificationSettings->isEmpty() ) return;
		
		//trcd_terminal_idとclient_idを取得
		$trcd_terminal_ids = $TrcdTerminalNotificationSettings->pluck('trcd_terminal_id', 'trcd_terminal_id');
		$client_ids = $TrcdTerminalNotificationSettings->pluck('client_id', 'client_id');
		
		// 各TRCD端末の直近入出金履歴を取得
		$latesetTrcdTerminalChangeRecords = [];
		
		foreach( $trcd_terminal_ids as $trcd_terminal_id ) {
			$TrcdTerminalChangeRecord = $this->objTrcdTerminalChangeRecordRepo->latestByTrcdTerminalId($trcd_terminal_id)->first();

			if ( !empty($TrcdTerminalChangeRecord) ) $latesetTrcdTerminalChangeRecords[$trcd_terminal_id] = $TrcdTerminalChangeRecord;
		}

		//関連するBalanceThresholdを取得
		$BalanceThresholds = $this->objBalanceThresholdRepo
			->whereIn('client_id', $client_ids)
			->get()
			->keyBy('client_id');

		// 通知対象の選別・通知データの整形処理開始
		$tmp_summaries = collect();
		$send_at = $NotificatedAt->format('Y-m-d H:i:s');

		foreach($TrcdTerminalNotificationSettings AS $TrcdTerminalNotificationSetting) {
			try{
				$TrcdTerminalChangeRecord = $latesetTrcdTerminalChangeRecords[$TrcdTerminalNotificationSetting['trcd_terminal_id']] ?? null;
				$BalanceThreshold = $BalanceThresholds[$TrcdTerminalNotificationSetting['client_id']] ?? null;

				// 入出金履歴がないTRCD端末、または閾値設定のない企業の場合はスキップ
				if ( empty($TrcdTerminalChangeRecord) || empty($BalanceThreshold) ) continue;

				// 閾値チェック
				$check_threshold_result = $this->checkThreshold($TrcdTerminalChangeRecord, $BalanceThreshold);

				// 閾値チェックに引っかかっていないものはスキップ
				if ( empty($check_threshold_result) ) continue;
				
				$tmp_summaries->push([
					'trcd_terminal_name' => $TrcdTerminalNotificationSetting['trcd_terminal']['name'],
					//'send_at' => $TrcdTerminalNotificationSetting['notificated_at'],
					'send_at' => $send_at,
					'trcd_terminal_destinations' => $TrcdTerminalNotificationSetting['trcd_terminal_notification_destinations']->pluck('email'),
					'summaries' => $check_threshold_result,
				]);
			} catch(\Exception $e){
				logger()->error("{$e->getMessage()} in {$e->getFile()} at Line:{$e->getLine()}");
				logger()->error('残高不足通知メール情報収集失敗:' .print_r($TrcdTerminalNotificationSetting->toArray(), true) );
			}
		}

		// メール送信処理
		foreach($tmp_summaries AS $tmp_summary) {
			try {
				Mail::to($tmp_summary['trcd_terminal_destinations'])
					->send(new NotifyTrcdTerminalBalanceShortage([
					'subject' => "TRCD端末残高不足定時通知 【端末名：{$tmp_summary['trcd_terminal_name']}】",
					'trcd_temrnal_name' => $tmp_summary['trcd_terminal_name'],
					'send_at' => $tmp_summary['send_at'],
					'summaries' => $tmp_summary['summaries'],
				]));
			} catch( \Exception $e ) {
				logger()->error("{$e->getMessage()} in {$e->getFile()} at Line:{$e->getLine()}");
				logger()->error('残高不足通知メール送信失敗:' . print_r($tmp_summary, true));
			}
		}
		
	}

	protected function checkThreshold(\App\TrcdTerminalChangeRecord $TrcdTerminalChangeRecord, \App\BalanceThreshold $BalanceThreshold) {
		$result = [];
		$settings = [
			'1' => ['divide' => 1, 'target' => 'lower_threshold_1yen', 'display_name' => '一円'],
			'5' => ['divide' => 5, 'target' => 'lower_threshold_5yen', 'display_name' => '五円'],
			'10' => ['divide' => 10, 'target' => 'lower_threshold_10yen', 'display_name' => '十円'],
			'50' => ['divide' => 50, 'target' => 'lower_threshold_50yen', 'display_name' => '五十円'],
			'100' => ['divide' => 100, 'target' => 'lower_threshold_100yen', 'display_name' => '百円'],
			'500' => ['divide' => 500, 'target' => 'lower_threshold_500yen', 'display_name' => '五百円'],
			'1k' => ['divide' => 1000, 'target' => 'lower_threshold_1k', 'display_name' => '千円'],
			'5k' => ['divide' => 5000, 'target' => 'lower_threshold_5k', 'display_name' => '五千円'],
			'10k' => ['divide' => 10000, 'target' => 'lower_threshold_10k', 'display_name' => '一万円'],
		];

		foreach( $settings as $key => $value ) {
			$threshold = $BalanceThreshold[$value['target']];

			// 未設定や0の場合はスキップ
			if ( empty($threshold) ) continue;

			$number_of_balance = $TrcdTerminalChangeRecord["amount_of_balance_{$key}"] / $value['divide'];

			if ( $number_of_balance < $threshold ) {
				$result["{$key}yen"] = [
					'amount_of_balance' => $TrcdTerminalChangeRecord["amount_of_balance_{$key}"],
					'number_of_balance' => $number_of_balance,
					'display_name' => $value['display_name'],
				];
			}
		}

		return $result;
	}
}
