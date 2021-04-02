<?php
/***
 * 経費所属グループ設定サービス
 *
 * @author YuKaneko
 */

namespace App\Services\Trcd;

use App\Services\ServiceBase;
// Repositories
use App\Repositories\ClientRepositoryInterface AS ClientRepository;
use App\Repositories\Trcd\ExpenseGroupRepositoryInterface AS ExpenseGroupRepository;
use App\Repositories\Trcd\ExpenseGroupSettingRepositoryInterface AS ExpenseGroupSettingRepository;
use App\Repositories\Trcd\ExpenseNotificationDestinationRepositoryInterface AS ExpenseNotificationDestinationRepository;
// Etc
use DB;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class ExpenseGroupSettingService extends ServiceBase {

	protected $objClientRepo;
	protected $objExpenseGroupRepo;
	protected $objExpenseGroupSettingRepo;
	protected $objExpenseNotificationDestinationRepo;

	/*
		コンストラクタ
	*/
	public function __construct(
		ClientRepository $objClientRepo,
		ExpenseGroupRepository $objExpenseGroupRepo,
		ExpenseGroupSettingRepository $objExpenseGroupSettingRepo,
		ExpenseNotificationDestinationRepository $objExpenseNotificationDestinationRepo
	){
		$this->objClientRepo = $objClientRepo;
		$this->objExpenseGroupRepo = $objExpenseGroupRepo;
		$this->objExpenseGroupSettingRepo = $objExpenseGroupSettingRepo;
		$this->objExpenseNotificationDestinationRepo = $objExpenseNotificationDestinationRepo;
	}

	/*
		経費通知画面表示用データ生成
	*/
	public function buildByClientId($client_id) {
		// 経費所属グループリスト取得
		$ColExpenseGroups = $this->objExpenseGroupRepo->where('client_id', $client_id)->orderBy('id')->get();
		$ColExpenseGroupIds = $ColExpenseGroups->pluck('id');
		// 「無所属」経費所属グループ追加
		$ColExpenseGroups->push($this->objExpenseGroupRepo->generateUnaffiliatedGroup($client_id));
		// コレクションのキーを変える
		$ColExpenseGroups = $ColExpenseGroups->keyBy(function($item) {
			return 'expense_group_id_' . $item['id'] ?? null;
		});

		// 経費所属グループ設定取得
		$ColExpenseGroupSettings = $this->objExpenseGroupSettingRepo
			->where('client_id', $client_id)
			->where(function($query) use($ColExpenseGroupIds) {
				$query->whereIn('expense_group_id', $ColExpenseGroupIds)
					->orWhereNull('expense_group_id'); // 無所属用
			})
			->get()
			->keyBy(function($item) {
				// キーを経費所属グループコレクションののキーに合わせる
				return 'expense_group_id_' . $item['expense_group_id'] ?? null;
			});

		// 通知先取得
		$ColExpenseNotificationDestinations = $this->objExpenseNotificationDestinationRepo
			->whereIn('expense_group_setting_id', $ColExpenseGroupSettings->pluck('id'))
			->get()
			->groupBy('expense_group_setting_id'); // 経費所属グループ設定IDでグループ化しておく


		// 返却データ生成処理
		$result = collect();

		foreach( $ColExpenseGroups as $expense_group_key => $ObjExpenseGroup ) {
			$tmpData = [
				'id' => null,
				'expense_group_id' => $ObjExpenseGroup['id'] ?? null,
				'expense_group' => ['name' => $ObjExpenseGroup['name']],
				'realtime_flag' => false,
				'notificated_at' => null,
				'expense_notification_destinations' => collect(),
			];

			// 既存の設定データがある場合は上書き
			if ( isset($ColExpenseGroupSettings[$expense_group_key]) ) {
				$ObjExpenseGroupSetting = $ColExpenseGroupSettings[$expense_group_key];

				$tmpData = array_merge($tmpData, [
					'id' => $ObjExpenseGroupSetting['id'],
					'realtime_flag' => $ObjExpenseGroupSetting['realtime_flag'],
					'notificated_at' => isset($ObjExpenseGroupSetting['notificated_at']) ? Carbon::parse($ObjExpenseGroupSetting['notificated_at'])->format('H:i') : null,
				]);

				// 通知先が設定されている場合は詰め込む
				if ( isset($ColExpenseNotificationDestinations[$tmpData['id']]) ) {
					foreach( $ColExpenseNotificationDestinations[$tmpData['id']] as $ObjExpenseNotificationDestination ) {
						$tmpData['expense_notification_destinations']->push([
							'id' => $ObjExpenseNotificationDestination['id'],
							'email' => $ObjExpenseNotificationDestination['email'],
						]);
					}
				}
			}

			$result->push($tmpData);
		}

		return $result;
	}

	/*
		経費通知画面での一括更新用
	*/
	public function insertOrUpdateMany($client_id, Array $data) {
		$data = collect($data)->keyBy(function($item) {
			return 'expense_group_id_' . $item['expense_group_id'] ?? null;
		});
		// 経費所属グループリスト取得
		$ColExpenseGroups = $this->objExpenseGroupRepo->where('client_id', $client_id)->orderBy('id')->get();
		$ColExpenseGroupIds = $ColExpenseGroups->pluck('id');
		// 「無所属」経費所属グループ追加
		$ColExpenseGroups->push($this->objExpenseGroupRepo->generateUnaffiliatedGroup($client_id));
		// コレクションのキーを変える
		$ColExpenseGroups = $ColExpenseGroups->keyBy(function($item) {
			return 'expense_group_id_' . $item['id'] ?? null;
		});

		// 経費所属グループ設定取得
		$ColExpenseGroupSettings = $this->objExpenseGroupSettingRepo
			->where('client_id', $client_id)
			->where(function($query) use($ColExpenseGroupIds) {
				$query->whereIn('expense_group_id', $ColExpenseGroupIds)
					->orWhereNull('expense_group_id'); // 無所属用
			})
			->get()
			->keyBy(function($item) {
				// キーを経費所属グループコレクションののキーに合わせる
				return 'expense_group_id_' . $item['expense_group_id'] ?? null;
			});



		// 挿入・更新対象
		$ColInsertTargets = collect();
		$ColUpdateTargets = collect();

		foreach( $data as $expense_group_key => $values ) {
			// 不正な経費グループID
			if ( !isset($ColExpenseGroups[$expense_group_key]) ) throw new \DomainException("Invalid expense_group_id {$values['expense_group_id']}.");

			// 既存の経費グループ設定がある場合は上書き・ない場合は新規
			$tmpExpenseGrouopSetting = isset($ColExpenseGroupSettings[$expense_group_key])
				? $ColExpenseGroupSettings[$expense_group_key]
				: $this->objExpenseGroupSettingRepo->generateInstanceByClientId($client_id);

			// 上書きして追加
			$tmpExpenseGrouopSetting->fill($values);
			$tmpExpenseGrouopSetting->expense_notification_destinations = $values['expense_notification_destinations'];

			if ( empty($tmpExpenseGrouopSetting['id']) ) $ColInsertTargets->push($tmpExpenseGrouopSetting);
			else $ColUpdateTargets->push($tmpExpenseGrouopSetting);
		}

		DB::beginTransaction();
		try {
			// 挿入処理
			if ( !$ColInsertTargets->isEmpty() ) {
				foreach( $ColInsertTargets as $ObjInsertTarget ) {
					$expense_notification_destinations = $ObjInsertTarget->expense_notification_destinations;
					$newExpenseGroupSetting = $this->create($ObjInsertTarget->toArray());

					// 挿入失敗
					if ( empty($newExpenseGroupSetting) ) throw new \LogicException("Failed to insert expense_group_setting." . PHP_EOL . print_r($ObjInsertTarget->toArray(), true));

					// 通知先挿入
					$newExpenseNotificationDestinations = $newExpenseGroupSetting->expense_notification_destinations()->createMany($expense_notification_destinations);
					$failures = $newExpenseNotificationDestinations->filter(function($value, $key) {
						return empty($value['id']);
					});

					// 挿入失敗
					if ( !$failures->isEmpty() ) {
						throw new \LogicException("Failed to insert expense_notification_destinations." . PHP_EOL . print_r($failures->toArray(), true));
					}
				}
			}

			// 更新処理
			if ( !$ColUpdateTargets->isEmpty() ) {
				foreach( $ColUpdateTargets as $ObjUpdateTarget ) {
					$expense_notification_destinations = $ObjUpdateTarget->expense_notification_destinations;
					$updateExpenseGroupSetting = $this->save($ObjUpdateTarget->toArray());

					// 挿入失敗
					if ( empty($updateExpenseGroupSetting) ) throw new \LogicException("Failed to save expense_group_setting." . PHP_EOL . print_r($ObjUpdateTarget->toArray(), true));

					// 通知先削除処理
					$expense_notification_destination_counts = $updateExpenseGroupSetting->expense_notification_destinations()->count();

					// 通知先が設定されている場合のみ削除
					if ( !empty($expense_notification_destination_counts) ) {
						// 通知先削除
						$delete_expense_notification_destination_result = $this->objExpenseNotificationDestinationRepo->deleteByExpenseGroupSettingId($updateExpenseGroupSetting['id']);
						
						if ( $expense_notification_destination_counts !== $delete_expense_notification_destination_result ) {
							throw new \LogicException("Failed to delete expense_notification_destinations. original_counts = {$expense_notification_destination_counts}  delete_counts = {$delete_expense_notification_destination_result}");
						}
					}

					// 通知先挿入
					$newExpenseNotificationDestinations = $updateExpenseGroupSetting->expense_notification_destinations()->createMany($expense_notification_destinations);
					$failures = $newExpenseNotificationDestinations->filter(function($value, $key) {
						return empty($value['id']);
					});

					// 挿入失敗
					if ( !$failures->isEmpty() ) {
						throw new \LogicException("Failed to insert expense_notification_destinations." . PHP_EOL . print_r($failures->toArray(), true));
					}
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