<?php
/***
 * Trcd端末サービス
 * @author YuKaneko
 */

namespace App\Services\Trcd;

use DB;

use App\TrcdTerminalNotificationSetting;

use App\Services\ServiceBase;

// Repositories
use App\Repositories\Trcd\TrcdTerminalRepositoryInterface as TrcdTerminalRepository;
use App\Repositories\ClientBranchRepositoryInterface as ClientBranchRepository;
use App\Repositories\Trcd\TrcdTerminalNotificationSettingRepositoryInterface as TrcdTerminalNotificationSettingRepository;

class TrcdTerminalService extends ServiceBase {

	// Repositories
	protected $objTrcdTerminalRepo;
	protected $objClientBranchRepo;
	protected $objTrcdTerminalNotificationSettingRepo;

	public function __construct(
		TrcdTerminalRepository $objTrcdTerminalRepo,
		ClientBranchRepository $objClientBranchRepo,
		TrcdTerminalNotificationSettingRepository $objTrcdTerminalNotificationSettingRepo
	){
		$this->objTrcdTerminalRepo = $objTrcdTerminalRepo;
		$this->objClientBranchRepo = $objClientBranchRepo;
		$this->objTrcdTerminalNotificationSettingRepo = $objTrcdTerminalNotificationSettingRepo;
	}

	/*
		TRCD端末の保存と支店との紐づけを行う
		@param $data TRCD端末更新データ
		@param Array $client_branch_ids 関連づける支店ID配列
		@return $objTrcdTerminal
	*/
	public function saveWithAssociation($data, $client_branch_ids) {
		$tmp_str = isset($data['id']) ? '更新' : '登録';
		$err_msg = "[TRCD端末{$tmp_str}処理]";

		DB::beginTransaction();

		try {
			// 保存処理（IDがない場合は登録処理）
			$objTrcdTerminal = $this->objTrcdTerminalRepo->save($data);

			if ( empty($objTrcdTerminal) ) {
				throw new \Exception('TRCD端末の保存に失敗しました。 in ' . __FILE__ . ' line at ' . __LINE__);
			}

			$err_msg .= "trcd_terminals.id:{$objTrcdTerminal->id} ";

			// 支店との紐づけ
			$result_branche = $objTrcdTerminal->client_branches()->sync($client_branch_ids);

			if ( empty($result_branche) ) {
				throw new \Exception('TRCD端末と支店の紐づけに失敗しました。 in ' . __FILE__ . ' line at ' . __LINE__);
			}

			// 通知設定同期処理
			$resul_sync_notification = $this->syncNotificationSetting($objTrcdTerminal, $client_branch_ids ?? []);
		
			if ( empty($resul_sync_notification) ) {
				throw new \Exception('TRCD端末設定の紐づけに失敗しました。 in ' . __FILE__ . ' line at ' . __LINE__);
			}
		} catch( \Exception $e ) {
			DB::rollBack();
			logger("{$err_msg}{$e->getMessage()}");
			logger($e);

			return false;
		}

		DB::commit();

		return $objTrcdTerminal;
	}

	/*
            trcd_terminalの論理削除
            @return bool
	*/
	public function delete($id) {
		DB::beginTransaction();
		try {
			$trcd_terminal_notification_setting_delete_ids = $this->objTrcdTerminalNotificationSettingRepo->where('trcd_terminal_id', $id)->pluck('id');

			foreach($trcd_terminal_notification_setting_delete_ids as $trcd_terminal_notification_setting_delete_id) {

				$result = $this->objTrcdTerminalNotificationSettingRepo->delete($trcd_terminal_notification_setting_delete_id);	

				if ( empty($result) ) throw new \Exception("TrcdTerminalNotificationSettingの削除に失敗しました。");
			
			}
			$result = $this->objTrcdTerminalRepo->delete($id);

			if ( empty($result) ) throw new \Exception("TrcdTerminalの削除に失敗しました。");

			DB::commit();
		} catch(\Exception $e) {
			DB::rollback();
			logger()->error($e->getMessage());
			return false;
		}

		return true;
	}

	private function syncNotificationSetting($objTrcdTerminal, Array $client_branch_ids) {
		// Transactionで囲む
		DB::beginTransaction();
		
		try{
			// 既存の通知設定を取得
			$ColTrcdTerminalNotificationSettings = $objTrcdTerminal->trcd_terminal_notification_settings;

			// 既存の通知設定の紐付けを一旦、全解除
			$notification_setting_delete_ids = $objTrcdTerminal->trcd_terminal_notification_settings()->pluck('id');
			foreach( $notification_setting_delete_ids as $notification_setting_delete_id ) {
				$delete_notification_setting = $this->objTrcdTerminalNotificationSettingRepo->delete($notification_setting_delete_id);

				if ( empty($delete_notification_setting) ) throw new \Exception("TrcdTerminalNotificationSetting.id {$notification_setting_delete_id} の論理削除に失敗しました。");// 例外を投げる
			}

			// 端末IDと企業IDの組み合わせで論理削除済みのものを取得
			// 通知設定の作成
			$executed_client_ids = []; // 操作対象となった企業ID

			foreach( $client_branch_ids as $client_branch_id ) {
				// client_idとtrcd_terminal_idを元に既存のtrcd_terminal_notification_settingsを検索
				$trcd_terminal_id = $objTrcdTerminal['id'];
				$client_id = $this->objClientBranchRepo->where('id', $client_branch_id)->pluck('client_id')->first();

				if ( isset($executed_client_ids[$client_id]) ) continue; // 既に設定されている企業IDのためスルー

				// 既に同じtrcd_terminal_idとclient_idもつデータが保存されているか確認
				$notification_setting_already_exist = $this->objTrcdTerminalNotificationSettingRepo
					->alreadyExist($trcd_terminal_id, $client_id)
					->onlyTrashed()
					->first();

				// もし論理削除済みのデータがあれば復活させる
				if ($notification_setting_already_exist) {
					$result_notification_setting = $this->objTrcdTerminalNotificationSettingRepo->restore($notification_setting_already_exist->id);

					if ( empty($result_notification_setting) ) throw new \Exception("TrcdTerminalNotificationSetting.id {$notification_setting_already_exist['id']} のリストアに失敗しました。");
				} 
				else {
					$notification_setting_save_data = [
						'trcd_terminal_id' => $trcd_terminal_id,
						'client_id'        => $client_id,
						'realtime_flag'    => false,
					];

					$result_notification_setting = $this->objTrcdTerminalNotificationSettingRepo->create($notification_setting_save_data);
					// 新規作成処理に失敗した場合は例外を投げて全体の処理を中断
					if ( empty($result_notification_setting) ) {
						throw new \Exception('TRCD端末通知設定の新規作成に失敗しました。 in '  . __FILE__ . ' line at ' . __LINE__ . PHP_EOL . print_r($notification_setting_save_data, true));
					}
				}

				$executed_client_ids[$client_id] = true;
			}

			DB::commit();
		} catch( \Exception $e ) {
			DB::rollBack();
			logger()->error($e->getMessage());
			return false;
		}

		return true;
	}
}



