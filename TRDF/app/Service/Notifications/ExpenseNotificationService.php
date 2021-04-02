<?php
/***
 * 経費通知サービス
 *
 * @author YuKaneko
 */

namespace App\Services\Notifications;

use App\Services\ServiceBase;
// Repositories
use App\Repositories\Trcd\ExpenseGroupSettingRepositoryInterface AS ExpenseGroupSettingRepository;
use App\Repositories\ClientEmployeeRepositoryInterface AS ClientEmployeeRepository;
use App\Repositories\Trcd\ExpenseSummaryRepositoryInterface AS ExpenseSummaryRepository;
use App\Repositories\Trcd\ExpenseNotificationDestinationRepositoryInterface AS ExpenseNotificationDestinationRepository;
// Models
use App\ExpenseSummary;
// Mailable
use App\Mail\BeingRequestedExpense;
// Etc
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;


class ExpenseNotificationService extends ServiceBase {

	protected $objExpenseGroupSettingRepo;
	protected $objClientEmployeeRepo;
	protected $objExpenseSummaryRepo;
	protected $objExpenseNotificationDestinationRepo;

	/*
		コンストラクタ
	*/
	public function __construct(
		ExpenseGroupSettingRepository $objExpenseGroupSettingRepo,
		ClientEmployeeRepository $objClientEmployeeRepo,
		ExpenseSummaryRepository $objExpenseSummaryRepo,
		ExpenseNotificationDestinationRepository $objExpenseNotificationDestinationRepo
	){
		$this->objExpenseGroupSettingRepo = $objExpenseGroupSettingRepo;
		$this->objClientEmployeeRepo = $objClientEmployeeRepo;
		$this->objExpenseSummaryRepo = $objExpenseSummaryRepo;
		$this->objExpenseNotificationDestinationRepo = $objExpenseNotificationDestinationRepo;
	}

	/*
		経費概要通知処理
	*/
	public function notifyExpenseSummary(ExpenseSummary $ExpenseSummary) {
		// 申請中（承認待ち）の場合は申請通知を行う
		if ( $ExpenseSummary->isBeingRequested() ) $this->notifyRequest($ExpenseSummary);
	}


	/*
		経費申請通知（リアルタイム型用）
	*/
	protected function notifyRequest(ExpenseSummary $ExpenseSummary) {
		if ( !$ExpenseSummary->isBeingRequested() ) return;

		// 担当者・経費所属グループ・経費所属グループ設定取得
		$ClientEmployee = $ExpenseSummary->client_employee;
		$ExpenseGroup = $ClientEmployee->expense_group;;
		$ExpenseGroupSetting = empty($ExpenseGroup)
			? $this->objExpenseGroupSettingRepo->unaffiliated($ClientEmployee->client_id)->first()
			: $ExpenseGroup->expense_group_setting;

		// 経費所属グループ設定がない場合、リアルタイム設定Offの場合は終了
		if ( empty($ExpenseGroupSetting) || empty($ExpenseGroupSetting['realtime_flag']) ) return;

		// 通知先取得
		$destinations = $ExpenseGroupSetting->expense_notification_destinations;

		// 通知先が設定されていない場合は終了
		if ( $destinations->isEmpty() ) return;

		Mail::to($destinations->pluck('email'))
			->send(new BeingRequestedExpense([
				'ExpenseSummaries' => collect([$ExpenseSummary->toArray()]),
				'ClientEmployees' => collect([$ClientEmployee->toArray()])->keyBy('id'),
			]));
	}

	/*
		経費申請リスト通知（バッチ処理用）
	*/
	public function notifyBeingRequestedList(Carbon $NotificatedAt) {
		// 経費所属グループ設定取得 通知先が設定されていないものは除外
		$ExpenseGroupSettings = $this->objExpenseGroupSettingRepo
			->select(['id', 'client_id', 'expense_group_id', 'notificated_at'])
			->where('notificated_at', $NotificatedAt->format('H:i:00'))
			->has('expense_notification_destinations')
			->get()
			->keyBy(function($item) {
				return "{$item['client_id']}_" . $item['expense_group_id'] ?? null;
			});

		// 検索用データ抽出作業など
		$client_ids = [];
		$expense_group_ids = [];
		$expense_group_setting_ids = [];
		$include_unaffiliated_flag = false;

		foreach( $ExpenseGroupSettings as $key => $ExpenseGroup ) {
			$ExpenseGroupSettings[$key]['ExpenseSummaries'] = collect();

			// 検索用データ抽出
			$client_id = $ExpenseGroup['client_id'];
			$expense_group_id = $ExpenseGroup['expense_group_id'];
			$expense_group_setting_id = $ExpenseGroup['id'];

			// 企業ID抽出
			if ( !isset($client_ids[$client_id]) ) $client_ids[$client_id] = $client_id;

			// 経費所属グループID抽出・無所属取得フラグ判定
			if ( !empty($expense_group_id) ) $expense_group_ids[$expense_group_id] = $expense_group_id;
			else if ( !$include_unaffiliated_flag ) $include_unaffiliated_flag = true;

			// 経費所属グループ設定ID抽出
			if ( !isset($expense_group_setting_ids[$expense_group_setting_id]) ) $expense_group_setting_ids[$expense_group_setting_id] = $expense_group_setting_id;
		}


		// 所属社員取得
		$ClientEmployees = $this->objClientEmployeeRepo
			->select(['id', 'client_id', 'name', 'expense_group_id'])
			->whereIn('client_id', $client_ids)
			->where(function($query) use($expense_group_ids, $include_unaffiliated_flag) {
				$query->whereIn('expense_group_id', $expense_group_ids);
				if ( $include_unaffiliated_flag ) $query->orWhereNull('expense_group_id');
			})
			->get()
			->keyBy('id');


		// 経費概要取得
		$ExpenseSummaries = $this->objExpenseSummaryRepo
			->select(['id', 'client_employee_id', 'requested_at', 'content', 'total_amount'])
			->whereIn('client_employee_id', $ClientEmployees->keys())
			->whereIn('status_code', $this->objExpenseSummaryRepo->getStatusCodeListOfBeingRequested()->keys())
			->orderBy('requested_at')
			->get();


		//  経費概要を経費所属グループごとに割り振る
		foreach( $ExpenseSummaries as $ExpenseSummary ) {
			$client_employee_id = $ExpenseSummary['client_employee_id'];

			// （可能性はないはずだが）該当する社員がいない場合は無視
			if ( !isset($ClientEmployees[$client_employee_id]) ) continue;

			$tmpClientEmployee = $ClientEmployees[$client_employee_id];
			$tmpKey = "{$tmpClientEmployee['client_id']}_" . $tmpClientEmployee['expense_group_id'] ?? null;
			
			$ExpenseGroupSettings[$tmpKey]['ExpenseSummaries']->push($ExpenseSummary->toArray());
		}


		// 社員をグループ化し、経費所属グループごとに割り振る
		$ClientEmployees = $ClientEmployees->groupBy(function($item, $key) {
			return "{$item['client_id']}_" . $item['expense_group_id'] ?? null;
		}, true);

		foreach( $ClientEmployees as $key => $values ) {
			$ExpenseGroupSettings[$key]['ClientEmployees'] = $values->toArray();
		}

		// 通知先取得 経費所属グループ設定ごとにグループ化
		$Destinations = $this->objExpenseNotificationDestinationRepo
			->select(['expense_group_setting_id', 'email'])
			->whereIn('expense_group_setting_id', $expense_group_setting_ids)
			->get()
			->groupBy('expense_group_setting_id');
			
		foreach( $ExpenseGroupSettings as $ExpenseGroupSetting ) {
			// 経費概要がないものは無視
			if ( $ExpenseGroupSetting['ExpenseSummaries']->isEmpty() ) continue;

			Mail::to($Destinations[$ExpenseGroupSetting['id']]->pluck('email'))
				->send(new BeingRequestedExpense([
					'subject' => '経費申請リスト通知',
					'ExpenseSummaries' => $ExpenseGroupSetting['ExpenseSummaries'],
					'ClientEmployees' => $ExpenseGroupSetting['ClientEmployees'],
				]));
		}
	}
}
