<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Repositories\Trcd\TrcdTerminalRepositoryInterface as TrcdTerminalRepository;
use App\Repositories\Trcd\TrcdTerminalNotificationSettingRepositoryInterface as TrcdTerminalNotificationSettingRepository;

class TrcdTerminalNotificationSettingTableSeeder extends Seeder
{
	protected $objTrcdTerminalRepo;
	protected $TrcdTerminalNotificationSettingRepo;

	public function __construct(
		TrcdTerminalRepository $objTrcdTerminalRepo,
		TrcdTerminalNotificationSettingRepository $TrcdTerminalNotificationSettingRepo

	){
		$this->objTrcdTerminalRepo = $objTrcdTerminalRepo;
		$this->TrcdTerminalNotificationSettingRepo = $TrcdTerminalNotificationSettingRepo;
	}

	/**
	* Run the database seeds.
	*
	* @return void
	*/
	public function run()
	{
		logger(__METHOD__ . " is called");
		// 論理削除済みを含めた全てのTRCD端末を取得
		$ColTrcdTerminals = $this->getAllTrcdTerminals();
		// 対象がない場合は即終了
		if ( $ColTrcdTerminals->isEmpty() ) return;

		DB::beginTransaction();

		try{
			foreach( $ColTrcdTerminals as $trcd_terminal_id => $objTrcdTerminal ) {
				// TRCD端末に紐づいている支店の企業IDを抽出
				$ColClientIds = $objTrcdTerminal->client_branches->pluck('client_id', 'client_id');
				// 企業IDが抽出出来なかった場合はスキップ
				if ( $ColClientIds->isEmpty() ) continue;

				foreach( $ColClientIds as $client_id ) {
					// $trcd_terminal_id, $client_idが一致するレコードを論理削除を含めて検索
					$NotificationSettingAlreadyExist = $this->TrcdTerminalNotificationSettingRepo->withTrashed()->AlreadyExist($trcd_terminal_id, $client_id)->first();

					// 既に登録されている場合はスキップ
					if ( !empty($NotificationSettingAlreadyExist) ) continue;
						
					$notification_setting_save_data = [
						'trcd_terminal_id' => $trcd_terminal_id,
						'client_id'        => $client_id,
						'realtime_flag'    => false,
					];

					// TRCD端末通知設定登録処理
					$result_notification_setting = $this->TrcdTerminalNotificationSettingRepo->create($notification_setting_save_data);

					if ( empty($result_notification_setting) ) {
						throw new \Exception('TRCD端末通知設定の新規作成に失敗しました。 in '  . __FILE__ . ' line at ' . __LINE__ . PHP_EOL . print_r($notification_setting_save_data, true));
					}

					logger($result_notification_setting->toArray());
					// TRCD端末が論理削除済みの場合、作成した通知設定も論理削除する
					if ( !empty($objTrcdTerminal['deleted_at']) ) {
						$delete_notification_setting = $this->TrcdTerminalNotificationSettingRepo->delete($result_notification_setting->id);

						if ( empty($delete_notification_setting) ) throw new \Exception("TrcdTerminalNotificationSettingの削除に失敗しました。");
						logger('DELETED');
					}
				}
			}

			DB::commit();
		} catch( \Exception $e){
			DB::rollBack();
			var_dump($e->getMessage());
			return;
		}
	}

	/*
	 * TRCD端末取得（論理削除済み含む）
	 * @return Collection
	 * */
	private function getAllTrcdTerminals() {
		return  $this->objTrcdTerminalRepo
			->select(['id', 'name', 'deleted_at'])
			->with([
				'client_branches:client_id,name',
			])
			->withTrashed()
			->orderBy('id')
			->get()
			->keyBy('id')
		;
	}
}
