<?php

namespace App\Http\Controllers\Api\Trcd;

// Services
use App\Services\Trcd\TrcdService;
use App\Services\ClientEmployeeService;
use App\Services\Trcd\ExpenseService;
use App\Services\SendMailService;
use App\Services\Trcd\TrcdTerminalChangeRecord\TrcdTerminalChangeRecordRollbackService;
use App\Services\Trcd\TemporaryPaymentService;
use App\Services\Notifications\TrcdTerminalBalanceNotificationService;

// Requests
use App\Http\Requests\Trcd\TrcdTerminalChangeRecordEntryPostRequest;
use App\Http\Requests\Trcd\Api\TrcdTerminalChangeRecordEntryExpensePostRequest;
use App\Http\Requests\Trcd\Api\TrcdTerminalChangeRecordRollbackExpensePostRequest;
use App\Http\Requests\Trcd\Api\TemporaryPaymentEntryAndWithdrawWithTrcdTerminalChangeRecordEntryPostRequest;
use App\Http\Requests\Trcd\Api\TemporaryPaymentEntryAndWithdrawWithTrcdTerminalChangeRecordRollbackPostRequest;

// Supports
use Illuminate\Support\Facades\Log;
use DB;
use Exception;
use Carbon\Carbon;

class TrcdTerminalChangeRecordsController extends TrcdApiBaseController
{
	protected $objClientEmployeeService;
	protected $objTemporaryPaymentService;
	protected $objTrcdTerminalBalanceNotificationService;

	public function __construct(
		ClientEmployeeService $objClientEmployeeService,
		TemporaryPaymentService $objTemporaryPaymentService,
		TrcdTerminalBalanceNotificationService $objTrcdTerminalBalanceNotificationService
	){
		$this->objClientEmployeeService = $objClientEmployeeService;
		$this->objTemporaryPaymentService = $objTemporaryPaymentService;
		$this->objTrcdTerminalBalanceNotificationService = $objTrcdTerminalBalanceNotificationService;

		parent::__construct();
	}


	/**
	 * TRCD　管理者による入出金記録の登録
	 *
	 * 払い出しでも返金でもなく、管理者による入金、出金
	 *
	 * 中では TrcdTerminalChangeRecordsController::_entry() を利用しています。
	 */
	public function entry_bank(TrcdTerminalChangeRecordEntryPostRequest $request, TrcdService $service){
		switch($request->kubun){
			case 1: // 管理者による入金
				$trcd_terminal_change_type_id = config('database.trcd.trcd_terminal_change_types.CONST.ADMIN_DEPOSIT');
				break;
			case 2: // 管理者による回収
				$trcd_terminal_change_type_id = config('database.trcd.trcd_terminal_change_types.CONST.ADMIN_WITHDRAWAL');
				break;
			case 3: // 管理者によるリセット
				$trcd_terminal_change_type_id = config('database.trcd.trcd_terminal_change_types.CONST.ADMIN_RESET');
				break;
			default:
				return $this->buildResponseArrayError('400');
		}

		return $this->_entry($request, $service, $trcd_terminal_change_type_id);
	}

	/**
	 * TRCD　社員による払い出し、返金記録の登録
	 *
	 * 社員による払い出し、返金
	 *
	 * 中では TrcdTerminalChangeRecordsController::_entry() を利用しています。
	 */
	public function entry_withdraw(TrcdTerminalChangeRecordEntryPostRequest $request, TrcdService $service){
		switch($request->kubun){
			case 1: // 社員による出金
				$trcd_terminal_change_type_id = config('database.trcd.trcd_terminal_change_types.CONST.EMPLOYEE_WITHDRAWAL');
				break;
			case 2: // 社員による返金
				$trcd_terminal_change_type_id = config('database.trcd.trcd_terminal_change_types.CONST.EMPLOYEE_REFUND');
				break;
			default:
				return $this->buildResponseArrayError('400');
		}

		return $this->_entry($request, $service, $trcd_terminal_change_type_id, ['update_client_employee_trcd_state' => true]);
	}

	/**
	 * TRCD　社員による経費精算・仮払い精算記録の登録
	 *
	 *
	 * 中では TrcdTerminalChangeRecordsController::_entry() を利用しています。
	 */
	public function entry_expense(
		TrcdTerminalChangeRecordEntryExpensePostRequest $request,
		TrcdService $objTrcdService,
		ExpenseService $objExpenseService
	){
		$processing_classification = "[経費精算・仮払い精算履歴登録処理]";
		// VALIDATION START ==============================

		// 社員情報チェック処理 ------------------------------
		// TRCDf端末が属する企業ID取得
		$objTrcdTerminal = $this->_getTrcdTerminal();
		$client_ids = $objTrcdTerminal->getClientIds();

		// 社員情報取得 退職者は除く
		$ColClientEmployees = $this->objClientEmployeeService->getByClientId($client_ids, [
			'client_employee_auth_key' => $request->user_id,
			'exclude_retired_employees' => Carbon::today(),
		]);

		if ( $ColClientEmployees->isEmpty() ) return $this->buildResponseArrayError('400', ['user_id' => '該当のユーザーIDは存在しません。']);
		elseif ( $ColClientEmployees->count() > 1 ) return $this->buildResponseArrayError('400', ['user_id' => '該当のユーザーIDが複数存在します。']);

		$objClientEmployee = $ColClientEmployees[0];


		// 入出金区分チェック処理 ------------------------------
		$trcd_terminal_change_type_id = $request->getTrcdTerminalChangeTypeId();

		// 取得できない場合はエラー
		if ( empty($trcd_terminal_change_type_id) ) return $this->buildResponseArrayError('400', ['kubun' => "不正な区分番号が指定されています。"]);


		// 経費データチェック処理 ------------------------------
		$all_expense_ids = $request->getAllExpenseIds();
		$ColExpenseSummaries = $objExpenseService->getById($all_expense_ids, [
			'client_employee_id' => $objClientEmployee['id'], // 自分に対する経費データのみを対象とする
		])->keyBy('id');

		// 送信されてきた経費データの数とDBから取得した数が合わない場合はエラー
		if ( count($all_expense_ids) !== $ColExpenseSummaries->count() ) {
			$err_msg = "不正な経費IDが含まれています。";
			logger()->error("{$processing_classification} {$err_msg}"
				. " request by client_id={$objClientEmployee['id']}"
				. " [requested_ids:" . join(',', $all_expense_ids) . "]"
				. " [db:" . join(',', $ColExpenseSummaries->keys()->all()) . "]"
			);
			return $this->buildResponseArrayError('400', ['expenses' => $err_msg]);
		}

		// 未ロック経費情報が含まれている場合はエラー
		$ColUnlockExpenseSummaries = $ColExpenseSummaries->where('locked_at', null);

		if ( !$ColUnlockExpenseSummaries->isEmpty() ) {
			$err_msg = "不正な経費IDが含まれています。";
			logger()->error("{$processing_classification} {$err_msg}"
				. " [unlock expense ids:" . join(',', $ColUnlockExpenseSummaries->keys()->all()) . "]"
			);
			return $this->buildResponseArrayError('400', ['expenses' => $err_msg]);
		}

		$classified_expenses = $request->classifyExpenseByType(); // 経費IDを分類分けして取得
		$API_EXPENSE_SUMMARY_TYPE_CONSTANS = config('database.api.expense_summary_types.CONST');

		// 経費データ走査
		foreach( $classified_expenses as $expense_type => $expenses ) {
			foreach( $expenses as $expense ) {
				// ステータスコード・入出金額の整合性チェック
				$err_response_status_code = null;

				switch( $expense_type ) {
					case($API_EXPENSE_SUMMARY_TYPE_CONSTANS['EXPENSE']): // 通常経費払出し
						if ( !$ColExpenseSummaries[$expense['id']]->CanWithdrawExpense() ) $err_response_status_code = 409;
						break;
					case($API_EXPENSE_SUMMARY_TYPE_CONSTANS['TEMPORARY_PAYMENT']): // 仮払い金払出し
						if ( !$ColExpenseSummaries[$expense['id']]->CanWithdrawTemporaryPayment() ) $err_response_status_code = 409;
						break;
					case($API_EXPENSE_SUMMARY_TYPE_CONSTANS['TEMPORARY_PAYMENT_EXCESS']): // 仮払い超過分払出し
						if ( !$ColExpenseSummaries[$expense['id']]->CanWithdrawExcess() ) $err_response_status_code = 409;
						break;
					case($API_EXPENSE_SUMMARY_TYPE_CONSTANS['TEMPORARY_PAYMENT_SURPLUS']): // 仮払い余剰分返金
						if ( !$ColExpenseSummaries[$expense['id']]->CanRefundSurplus() ) $err_response_status_code = 409;
						break;
					default:
						$err_response_status_code = 400;
						break;
				}

				if ( !empty($err_response_status_code) ) {
					$err_msg = "不正な経費ID[{$expense['id']}]が指定されています。";
					logger()->error("{$processing_classification} {$err_msg}"
						. " [request expense.type:{$expense_type}]"
						. " [db status:{$ColExpenseSummaries[$expense['id']]['status_code']}]"
					);
					return $this->buildResponseArrayError($err_response_status_code, ['expenses' => $err_msg]);
				}
			}
		}

		// ロック解除対象経費ID（追加分）チェック処理 ------------------------------
		$unlock_target_expense_summary_ids = $ColExpenseSummaries->pluck('id', 'id');
		$extra_unlock_target_expense_ids = $request->getExtraUnlockTargetExpenseIds();

		// 追加分が設定されている場合
		if ( !empty($extra_unlock_target_expense_ids) ) {
			$ColExtraUnlockTargetExpenseSummaries = $objExpenseService->getById($extra_unlock_target_expense_ids, [
				'client_employee_id' => $objClientEmployee['id'], // 自分に対する経費データのみを対象とする
			])->keyBy('id');

			// 送信されてきたロック解除対象経費ID（追加分）の数とDBから取得した数が合わない場合はエラー
			if ( count($extra_unlock_target_expense_ids) !== $ColExtraUnlockTargetExpenseSummaries->count() ) {
				$err_msg = "ロック解除対象経費ID（追加分）に不正なIDが含まれています。";
				logger()->error("{$processing_classification} {$err_msg}"
					. " request by client_id={$objClientEmployee['id']}"
					. " [requested_ids:" . join(',', $extra_unlock_target_expense_ids) . "]"
					. " [db:" . join(',', $ColExtraUnlockTargetExpenseSummaries->keys()->all()) . "]"
				);

				return $this->buildResponseArrayError('400', ['extra_unlock_target_expense_ids' => $err_msg]);
			}

			foreach( $ColExtraUnlockTargetExpenseSummaries as $objExtraUnlockTargetExpenseSummary ) {
				$unlock_target_expense_summary_ids[$objExtraUnlockTargetExpenseSummary['id']] = $objExtraUnlockTargetExpenseSummary['id'];
			}
		}

		// Validation_end ==============================



		// 操作日時取得
		$operation_datetime = $request->getOperationDateTime();
		$response = null;

		DB::beginTransaction();

		try {
			// 入出金履歴保存処理
			$objTrcdTerminalChangeRecord = $this->_doEntry(
				$objTrcdService, $request, $objTrcdTerminal['id'], $trcd_terminal_change_type_id, $objClientEmployee['id']);

			if ( empty($objTrcdTerminalChangeRecord) ) throw new Exception("TRCD端末入出金履歴の登録に失敗しました。");


			//  出勤済み・返金済みフラグ更新
			foreach( $classified_expenses as $expense_type => $expenses ) {
				$expense_ids = data_get($expenses, '*.id');

				switch($expense_type) {
					case($API_EXPENSE_SUMMARY_TYPE_CONSTANS['EXPENSE']): // 通常経費精算
						if ( !$objExpenseService->payOutExpense($operation_datetime, $expense_ids) ) throw new Exception('経費払出し日時の更新に失敗しました。');
						break;
					case($API_EXPENSE_SUMMARY_TYPE_CONSTANS['TEMPORARY_PAYMENT']): // 仮払い金出金
						if ( !$objExpenseService->payOutTemporaryPayment($operation_datetime, $expense_ids) ) throw new Exception('仮払い金払出し日時の更新に失敗しました。');
						break;
					case($API_EXPENSE_SUMMARY_TYPE_CONSTANS['TEMPORARY_PAYMENT_EXCESS']): // 仮払い超過分出金
						if ( !$objExpenseService->payOutExcess($operation_datetime, $expense_ids) ) throw new Exception('仮払い超過分払出し日時の更新に失敗しました。');
						break;
					case($API_EXPENSE_SUMMARY_TYPE_CONSTANS['TEMPORARY_PAYMENT_SURPLUS']): // 仮払い余剰分返金
						if ( !$objExpenseService->refundSurplus($operation_datetime, $expense_ids) ) throw new Exception('仮払い余剰分返金日時の更新に失敗しました。');
						break;
					default:
						throw new Exception('不正な経費種別IDが入力されています。');
						break;
				}
			}


			// アンロック処理
			if ( !$objExpenseService->unlock($unlock_target_expense_summary_ids) ) {
				throw new Exception('ロック解除処理に失敗しました。');
			}

		} catch (Exception $e ) {
			DB::rollBack();
			logger()->error("{$processing_classification} {$e}");
			logger()->error('$request->all()', $request->all());
			return $this->buildResponseArrayError('500', '内部エラーが発生しました。');
		}

		DB::commit();

		return $this->buildResponseArray(['id' => $objTrcdTerminalChangeRecord['id']]);
	}

	/*
		経費入出金履歴ロールバック処理
		ここが呼ばれた時点で管理者への通知は必須
	*/
	public function rollback_expense(
		TrcdTerminalChangeRecordRollbackExpensePostRequest $request,
		TrcdTerminalChangeRecordRollbackService $objRollbackService,
		SendMailService $objSendMailService
	) {
		$processing_classification = "[経費精算・仮払い精算履歴ロールバック処理]";
		$request_params = $request->all();
		$subject = $processing_classification;
		$body = "【日時】" . Carbon::now()->format('Y-m-d H:i:s')
			. "\n【接続元IP】" . $request->ip()
			. "\n【REQUEST_PARAMS】" . print_r($request_params, true)
		;

		// TRCD端末情報取得
		$objTrcdTerminal = $this->_getTrcdTerminal();

		// ロールバック処理 ------------------------------
		$rollback_result = $objRollbackService->rollback(
			$objTrcdTerminal,
			$request_params['user_id'],
			$request_params['history_id'],
			$request_params['expenses']
		);

		if ( $rollback_result['status'] !== 200 ) $subject .= " エラー";

		$body .= "\n\nロールバック処理\n" . print_r($rollback_result, true);

		try {
			// メール送信タスク登録処理 ------------------------------
			$send_mail_result = $objSendMailService->sendMailToAdmin($subject, $body);

			if ( empty($send_mail_result) ) {
				$body .= "\n\n【メール送信タスクの登録に失敗しました。】";
				throw new Exception($body);
			}
		} catch( Exception $e ) {
			logger()->error("{$processing_classification} {$e}");
			$objSendMailService->sendMailToAdminDirectly($subject, $body);
		}

		return $rollback_result['status'] === 200
			? $this->buildResponseArray()
			: $this->buildResponseArrayError($rollback_result['status'], $rollback_result['errors'])
		;
	}

	/**
	 * TRCD　社員による仮払い簡易出金処理
	 *
	 */
	public function entry_and_withdraw_temporary_payment (
		TemporaryPaymentEntryAndWithdrawWithTrcdTerminalChangeRecordEntryPostRequest $request,
		TrcdService $objTrcdService,
		ExpenseService $objExpenseService
	){
		$processing_classification = "[仮払い簡易出金登録処理]";

		// TRCDf端末が属する企業ID取得
		$objTrcdTerminal = $this->_getTrcdTerminal();
		$client_ids = $objTrcdTerminal->getClientIds();

		// 社員情報取得 退職者は除く
		$ColClientEmployees = $this->objClientEmployeeService->getByClientId($client_ids, [
			'client_employee_auth_key' => $request->user_id,
			'exclude_retired_employees' => Carbon::today(),
		]);

		// 社員が特定出来ない場合はエラー
		if ( $ColClientEmployees->isEmpty() ) return $this->buildResponseArrayError('400', ['user_id' => '該当のユーザーIDは存在しません。']);
		elseif ( $ColClientEmployees->count() > 1 ) return $this->buildResponseArrayError('400', ['user_id' => '該当のユーザーIDが複数存在します。']);

		$objClientEmployee = $ColClientEmployees[0];

		// 権限を有していない場合はエラー
		if ( !$objClientEmployee->hasPermissionTo('create temporary_payments', 'trcd') ) return $this->buildResponseArrayError('400', ['user_id' => '仮払い登録権限がありません。']);

		// 仮払い概要候補リスト取得
		$ColTemporaryPaymentSummaryCandidates = $objClientEmployee->client->temporary_payment_summary_candidates->pluck('name', 'name');

		// 登録済みの仮払い概要候補でない場合はエラー
		if ( !isset($ColTemporaryPaymentSummaryCandidates[$request->content]) ) return $this->buildResponseArrayError('400', ['content' => '未登録の仮払い概要が入力されています。']);


		// 操作日時取得
		$operation_datetime = $request->getOperationDateTime();
		// 入出金区分は「社員による経費・仮払い出金」
		$trcd_terminal_change_type_id =  config('database.trcd.trcd_terminal_change_types.CONST.EMPLOYEE_WITHDRAWAL_EXPENSE');
		$response = null;

		DB::beginTransaction();

		try {
			// 仮払い登録処理
			$expense_summary_data = $request->getTemporaryPaymentData();
			$expense_summary_data['client_employee_id'] = $objClientEmployee['id'];

			$result = $this->objTemporaryPaymentService->registerAndCreateDirectoryTask($expense_summary_data, $objClientEmployee['id']);

			if ( empty($result) ) throw new Exception("仮払い登録処理に失敗しました。");

			if ( !$objExpenseService->payOutTemporaryPayment($operation_datetime, $result['expense_summary']['id']) ) throw new Exception('仮払い金払出し日時の更新に失敗しました。');

			// 入出金履歴保存処理
			$objTrcdTerminalChangeRecord = $this->_doEntry($objTrcdService, $request, $objTrcdTerminal['id'], $trcd_terminal_change_type_id, $objClientEmployee['id']);

			if ( empty($objTrcdTerminalChangeRecord) ) throw new Exception("TRCD端末入出金履歴の登録に失敗しました。");

			DB::commit();
		} catch (Exception $e ) {
			DB::rollBack();
			logger()->error("{$processing_classification} {$e}");
			logger()->error('$request->all()', $request->all());
			return $this->buildResponseArrayError('500', '内部エラーが発生しました。');
		}

		return $this->buildResponseArray([
			'history_id' => $objTrcdTerminalChangeRecord['id'],
			'temporary_payment_id' => $result['expense_summary']['id'],
		]);
	}

	/*
		簡易仮払い出金データロールバック処理
		ここが呼ばれた時点で管理者への通知は必須
	*/
	public function rollback_entry_and_withdraw_temporary_payment (
		TemporaryPaymentEntryAndWithdrawWithTrcdTerminalChangeRecordRollbackPostRequest $request,
		TrcdTerminalChangeRecordRollbackService $objRollbackService,
		SendMailService $objSendMailService
	) {
		$processing_classification = "[簡易仮払い出金ロールバック処理]";
		$request_params = $request->all();
		$subject = $processing_classification;
		$body = "【日時】" . Carbon::now()->format('Y-m-d H:i:s')
			. "\n【接続元IP】" . $request->ip()
			. "\n【REQUEST_PARAMS】" . print_r($request_params, true)
		;

		// TRCD端末情報取得
		$objTrcdTerminal = $this->_getTrcdTerminal();

		// ロールバック処理
		$rollback_result = $objRollbackService->rollback_easy_withdraw_temporary_payment(
			$objTrcdTerminal,
			$request_params['user_id'],
			$request_params['history_id'],
			$request_params['temporary_payment_id']
		);

		if ( $rollback_result['status'] !== 200 ) $subject .= " エラー";

		$body .= "\n\nロールバック処理\n" . print_r($rollback_result, true);

		try {
			// メール送信タスク登録処理
			$send_mail_result = $objSendMailService->sendMailToAdmin($subject, $body);

			if ( empty($send_mail_result) ) {
				$body .= "\n\n【メール送信タスクの登録に失敗しました。】";
				throw new Exception($body);
			}
		} catch( Exception $e ) {
			logger()->error("{$processing_classification} {$e}");
			$objSendMailService->sendMailToAdminDirectly($subject, $body);
		}

		return $rollback_result['status'] === 200
			? $this->buildResponseArray()
			: $this->buildResponseArrayError($rollback_result['status'], $rollback_result['errors'])
		;
	}

	/**
	 * 登録処理
	 */
	protected function _entry(
		TrcdTerminalChangeRecordEntryPostRequest $request,
		TrcdService $service,
		$trcd_terminal_change_type_id,
		$options = []
	){
		// ログイン中のTRCD端末情報を取得する
		$trcd = $this->_getTrcdTerminal();

		// client_employees.id を取得する
		$objClientEmployee = $this->objClientEmployeeService->getByAuthKey($request->user_id);

		if ( $objClientEmployee == null ) {
			return $this->buildResponseArrayError('400', ['user_id'=>'該当のユーザーIDは存在しません。']);
		}

/*
		$result = $service->CreateTrcdTerminalChangeRecord(
			$trcd->id,
			$trcd_terminal_change_type_id,
			$objClientEmployee->id,
			$request->amount_10k,
			empty($request->amount_5k) ? 0 : $request->amount_5k,
			$request->amount_1k,
			empty($request->amount_500yen) ? 0 : $request->amount_500yen,
			empty($request->amount_100yen) ? 0 : $request->amount_100yen,
			empty($request->amount_50yen) ? 0 : $request->amount_50yen,
			empty($request->amount_10yen) ? 0 : $request->amount_10yen,
			empty($request->amount_5yen) ? 0 : $request->amount_5yen,
			empty($request->amount_1yen) ? 0 : $request->amount_1yen,
			$request->balance_10k,
			empty($request->balance_5k) ? 0 : $request->balance_5k,
			$request->balance_1k,
			empty($request->balance_500yen) ? 0 : $request->balance_500yen,
			empty($request->balance_100yen) ? 0 : $request->balance_100yen,
			empty($request->balance_50yen) ? 0 : $request->balance_50yen,
			empty($request->balance_10yen) ? 0 : $request->balance_10yen,
			empty($request->balance_5yen) ? 0 : $request->balance_5yen,
			empty($request->balance_1yen) ? 0 : $request->balance_1yen,
			preg_replace('/\//','-',$request->date).' '.$request->time,
			$options
		);
*/
		$result = $this->_doEntry($service, $request, $trcd->id, $trcd_terminal_change_type_id, $objClientEmployee['id'], $options);

		if ( $result == false ) {
			Log::error(__METHOD__.'(): 500 内部エラーが発生しました。TrcdTerminalChangeRecordが保存できませんでした。');
			return $this->buildResponseArrayError('500', '内部エラーが発生しました。');
		}

		// 結果データ配列を形成する
		return $this->buildResponseArray();
	}

	/*
		実登録処理
	*/
	protected function _doEntry(
		TrcdService $objTrcdService,
		TrcdTerminalChangeRecordEntryPostRequest $request,
		$trcd_terminal_id,
		$trcd_terminal_change_type_id,
		$client_employee_id,
		$options = []
	) {
		$result = $objTrcdService->CreateTrcdTerminalChangeRecord(
			$trcd_terminal_id,
			$trcd_terminal_change_type_id,
			$client_employee_id,
			$request->amount_10k,
			empty($request->amount_5k) ? 0 : $request->amount_5k,
			$request->amount_1k,
			empty($request->amount_500yen) ? 0 : $request->amount_500yen,
			empty($request->amount_100yen) ? 0 : $request->amount_100yen,
			empty($request->amount_50yen) ? 0 : $request->amount_50yen,
			empty($request->amount_10yen) ? 0 : $request->amount_10yen,
			empty($request->amount_5yen) ? 0 : $request->amount_5yen,
			empty($request->amount_1yen) ? 0 : $request->amount_1yen,
			$request->balance_10k,
			empty($request->balance_5k) ? 0 : $request->balance_5k,
			$request->balance_1k,
			empty($request->balance_500yen) ? 0 : $request->balance_500yen,
			empty($request->balance_100yen) ? 0 : $request->balance_100yen,
			empty($request->balance_50yen) ? 0 : $request->balance_50yen,
			empty($request->balance_10yen) ? 0 : $request->balance_10yen,
			empty($request->balance_5yen) ? 0 : $request->balance_5yen,
			empty($request->balance_1yen) ? 0 : $request->balance_1yen,
			preg_replace('/\//','-',$request->date).' '.$request->time,
			$options
		);

		// 残高不足リアルタイム通知処理
		if ( !empty($result) ) {
			try {
				$this->objTrcdTerminalBalanceNotificationService->notifyIfLowerThanThreshold($result);
			} catch ( \Exception $e ) {
				logger()->error("残高不足リアルタイム通知処理に失敗しました。trcd_terminal_change_records.id {$result['id']}");
				logger($e);
			}
		}

		return $result;
	}
}
