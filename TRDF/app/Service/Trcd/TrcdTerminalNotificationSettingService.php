<?php
/***
 * 残高不足通知設定サービス
 *
 * @author K.Yamasaki
 */

namespace App\Services\Trcd;

use App\Services\ServiceBase;
// Repositories
use App\Repositories\Trcd\TrcdTerminalNotificationSettingRepositoryInterface AS TrcdTerminalNotificationSettingRepository;
use App\Repositories\Trcd\TrcdTerminalNotificationDestinationRepositoryInterface AS TrcdTerminalNotificationDestinationRepository;
use App\Repositories\Trcd\BalanceThresholdRepositoryInterface AS BalanceThresholdRepository;
// Etc
use DB;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class TrcdTerminalNotificationSettingService extends ServiceBase {


	protected $objTrcdTerminalNotificationSettingRepo;
	protected $objTrcdTerminalNotificationDestinationRepo;
	protected $objBalanceThresholdRepository;

	/*
		コンストラクタ
	*/
	public function __construct(
		TrcdTerminalNotificationSettingRepository $objTrcdTerminalNotificationSettingRepo,
		TrcdTerminalNotificationDestinationRepository $objTrcdTerminalNotificationDestinationRepo,
		BalanceThresholdRepository $objBalanceThresholdRepository
	){
		$this->objTrcdTerminalNotificationSettingRepo = $objTrcdTerminalNotificationSettingRepo;
		$this->objTrcdTerminalNotificationDestinationRepo = $objTrcdTerminalNotificationDestinationRepo;
		$this->objBalanceThresholdRepository = $objBalanceThresholdRepository;
	}

	/*
		残高通知画面表示用データ生成
	*/
	public function getByClientId($client_id, Array $options = []) {
		// 企業に紐づくtrcd_terminal_notification_settingsを取得
		return $this->objTrcdTerminalNotificationSettingRepo
			->where('client_id', $client_id)
			->with([
				'trcd_terminal:id,name',
				'trcd_terminal_notification_destinations:email,trcd_terminal_notification_setting_id',
			])
			->orderBy('trcd_terminal_id')
			->get()
			->keyBy('id');
	}

	/*
		経費通知画面での一括更新用
		@throws Exception 処理失敗時には例外を発生（falseは返さない）
		@param int $client_id 企業ID
		@param Array $data （balance_thresholdと各trcd_terminal_notification_settings, trcd_terminal_notification_destinationsの情報）
		@return true
	*/
	public function updateWithRelationAndBalanceThreshold($client_id, Array $data) {
		//残高閾値の取得
		$BalanceThreshold = $this->objBalanceThresholdRepository->where('client_id', $client_id)->first();

		// 端末通知設定を全て取得
		$ColTrcdTerminalNotificationSettings = $this->objTrcdTerminalNotificationSettingRepo
			->where('client_id', $client_id)
			->get()
			->keyBy('id');

		$ColTrcdTerminalNotificationSettingIds = $ColTrcdTerminalNotificationSettings->pluck('id', 'id');
		
		// 削除用の通知先IDを取得
		$ColTrcdTerminalNotificationDestinationIds = $this->objTrcdTerminalNotificationDestinationRepo->whereIn('trcd_terminal_notification_setting_id', $ColTrcdTerminalNotificationSettingIds)->pluck('id');
		DB::beginTransaction();
                try{
			// 既存のメール送付先の紐付けを全削除
			foreach( $ColTrcdTerminalNotificationDestinationIds AS $TrcdTerminalNotificationDestinationId) {
				$delete_notification_destination = $this->objTrcdTerminalNotificationDestinationRepo->delete($TrcdTerminalNotificationDestinationId);

				if ( empty($delete_notification_destination) ) throw new \Exception("TrcdTerminalNotificationdestination.id {$TrcdTerminalNotificationDestinationId} の削除に失敗しました。");
			}

			// 残高閾値の更新 // ToDo 今後balance_thresholdにカラムが増えたりした場合は、$data['balance_threshold']をmergeする方針にした方が良い？
			$balance_threshold_save_data = [
				'id' => $BalanceThreshold['id'],
				'client_id' => $client_id,
				'lower_threshold_1yen' => $data['balance_threshold']['lower_threshold_1yen'],
				'lower_threshold_5yen' => $data['balance_threshold']['lower_threshold_5yen'],
				'lower_threshold_10yen' => $data['balance_threshold']['lower_threshold_10yen'],
				'lower_threshold_50yen' => $data['balance_threshold']['lower_threshold_50yen'],
				'lower_threshold_100yen' => $data['balance_threshold']['lower_threshold_100yen'],
				'lower_threshold_500yen' => $data['balance_threshold']['lower_threshold_500yen'],
				'lower_threshold_1k' => $data['balance_threshold']['lower_threshold_1k'],
				'lower_threshold_5k' => $data['balance_threshold']['lower_threshold_5k'],
				'lower_threshold_10k' => $data['balance_threshold']['lower_threshold_10k'],
			];

			$updateBalanceThreshold = $this->objBalanceThresholdRepository->save($balance_threshold_save_data);
			
			if ( empty($updateBalanceThreshold) ) throw new \Exception("updateBalanceThreshold.id {$updataBalanceThreshold} の更新に失敗しました。");
			
			// 通知設定の更新と通知の連絡先を登録
			foreach( $data['trcd_terminal_notification_settings'] AS $TrcdTerminalNotificationSetting) {
				$TrcdTerminalNotificationSettingId = $TrcdTerminalNotificationSetting['id'];

				$trcd_terminal_notification_setting_save_data = [
					'id' => $TrcdTerminalNotificationSettingId,
					'trcd_terminal_id' => $ColTrcdTerminalNotificationSettings[$TrcdTerminalNotificationSettingId]['trcd_terminal_id'],
					'client_id' => $client_id,
					'realtime_flag' => $TrcdTerminalNotificationSetting['realtime_flag'],
					'notificated_at' => $TrcdTerminalNotificationSetting['notificated_at'],
				];
				
				$updateTrcdTerminalNotificationSetting = $this->objTrcdTerminalNotificationSettingRepo->save($trcd_terminal_notification_setting_save_data);

				if ( empty($updateTrcdTerminalNotificationSetting) ) throw new \Exception("updateTrcdTerminalNotificationSetting.id {$updateTrcdTerminalNotificationSetting} の更新に失敗しました。");
				foreach( $TrcdTerminalNotificationSetting['trcd_terminal_notification_destinations'] AS $TrcdTerminalNotificationDestination ) {
					$trcd_terminal_notification_destination_create_data = [
						'trcd_terminal_notification_setting_id' => $TrcdTerminalNotificationSettingId,
						'email' => $TrcdTerminalNotificationDestination,
					];

					$resultNotificationDestination = $this->objTrcdTerminalNotificationDestinationRepo->create($trcd_terminal_notification_destination_create_data);
					
					if ( empty($resultNotificationDestination) ) throw new \Exception("resultNotificationDestination.id {$resultNotificationDestination} の更新に失敗し>ました。");
				}
			}
			DB::commit();
		} catch(\Exception $e){
			DB::rollBack();
			throw $e;
		}

		return true;
	}

	/*
		新規追加
	*/
	public function create(Array $data) {
		return $this->objExpenseGroupSettingRepo->create($data);
	}

	/*
		保存	
	*/
	public function save(Array $data) {
		return $this->objExpenseGroupSettingRepo->save($data);
	}
}