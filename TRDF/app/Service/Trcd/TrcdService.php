<?php
/***
 * 社員TRCD状態サービス
 *
 * @author T.Ando
 */

namespace App\Services\Trcd;

use DB;
use App\Services\ServiceBase;

// Repository
use App\Repositories\ClientRepositoryInterface AS ClientRepository;
use App\Repositories\ClientEmployeeRepositoryInterface AS ClientEmployeeRepository;
use App\Repositories\Trcd\ClientEmployeeTrcdStateRepositoryInterface AS ClientEmployeeTrcdStateRepository;
use App\Repositories\Trcd\ClientEmployeeTrcdSettingRepositoryInterface AS ClientEmployeeTrcdSettingRepository;
use App\Repositories\Trcd\ClientTrcdSettingRepositoryInterface AS ClientTrcdSettingRepository;
use App\Repositories\Trcd\TrcdTerminalChangeRecordRepositoryInterface AS TrcdTerminalChangeRecordRepository;
use App\Repositories\Trcd\TrcdMessageRepositoryInterface AS TrcdMessageRepository;
use App\Repositories\Trcd\AttendanceHeaderRepositoryInterface AS AttendanceHeaderRepository;
use App\Repositories\Trcd\AttendanceDetailRepositoryInterface AS AttendanceDetailRepository;

// Service
use App\Services\ClientEmployeeService;
use App\Services\MessageService;

// Log
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class TrcdService extends ServiceBase
{
	protected $objClientRepository;
	protected $objClientEmployeeRepository;
	protected $objClientEmployeeTrcdStateRepository;
	protected $objClientEmployeeTrcdSettingRepository;
	protected $objClientTrcdSettingRepository;
	protected $objTrcdTerminalChangeRecordRepository;
	protected $objTrcdMessageRepository;
	protected $objAttendanceHeaderRepository;
	protected $objAttendanceDetailRepository;

	protected $objClientEmployeeService;

	public function __construct(
		ClientRepository $objClientRepository,
		ClientEmployeeRepository $objClientEmployeeRepository,
		ClientEmployeeTrcdStateRepository $objClientEmployeeTrcdStateRepository,
		ClientEmployeeTrcdSettingRepository $objClientEmployeeTrcdSettingRepository,
		ClientTrcdSettingRepository $objClientTrcdSettingRepository,
		TrcdTerminalChangeRecordRepository $objTrcdTerminalChangeRecordRepository,
		TrcdMessageRepository $objTrcdMessageRepository,
		AttendanceHeaderRepository $objAttendanceHeaderRepository,
		AttendanceDetailRepository $objAttendanceDetailRepository,
		ClientEmployeeService $objClientEmployeeService
	){
		$this->objClientRepository = $objClientRepository;
		$this->objClientEmployeeRepository = $objClientEmployeeRepository;
		$this->objClientEmployeeTrcdStateRepository = $objClientEmployeeTrcdStateRepository;
		$this->objClientEmployeeTrcdSettingRepository = $objClientEmployeeTrcdSettingRepository;
		$this->objClientTrcdSettingRepository = $objClientTrcdSettingRepository;
		$this->objTrcdTerminalChangeRecordRepository = $objTrcdTerminalChangeRecordRepository;
		$this->objTrcdMessageRepository = $objTrcdMessageRepository;
		$this->objAttendanceHeaderRepository = $objAttendanceHeaderRepository;
		$this->objAttendanceDetailRepository = $objAttendanceDetailRepository;
		$this->objClientEmployeeService = $objClientEmployeeService;
	}

	/**
	 * 社員ごとのTRCD情報を算出し取得する
	 *
	 * $optionsは、下記の項目を設定できます。
	 *
	 *
	 * @return array 社員ごとのTRCD払い出し金額や上限金額情報を取得
	 */
	public function GetEmployeeTrcdTodayInfosByClientEmployeeId($client_id, $client_employee_id=null, $client_employee_auth_key=null){
		// 返却用の変数を用意
		$aryResults = array();
		// 該当の社員データを取得
		$options = array();

		if(!is_null($client_employee_id)){ $options['client_employee_id'] = $client_employee_id; }
		if(!is_null($client_employee_auth_key)){ $options['client_employee_auth_key'] = $client_employee_auth_key; }

		$objClientEmployees = $this->objClientEmployeeRepository->getByClientId($client_id, $options);

		// ループでSQLを実行すると遅くなるので、IN()クエリを実行するため、IDの配列変数を生成する。
		$aryTargetClientEmployeeIds = array();

		foreach($objClientEmployees as $objClientEmployee){
			$aryTargetClientEmployeeIds[] = $objClientEmployee->id;
		}

		// 負荷対策の為、対象のデータを先に一括で取得し、IDをキーとした配列に格納する。
		$objClientEmployeeTrcdStates = $this->objClientEmployeeTrcdStateRepository->getById($aryTargetClientEmployeeIds);
		$aryObjClientEmployeeTrcdStates = array();

		foreach($objClientEmployeeTrcdStates as $objClientEmployeeTrcdState){
			$aryObjClientEmployeeTrcdStates[$objClientEmployeeTrcdState->client_employee_id] = $objClientEmployeeTrcdState;
		}

		// 負荷対策の為、対象のデータを先に一括で取得し、IDをキーとした配列に格納する。
		$objClientEmployeeTrcdSettings = $this->objClientEmployeeTrcdSettingRepository->getById($aryTargetClientEmployeeIds);
		$aryObjClientEmployeeTrcdSettings = array();

		foreach($objClientEmployeeTrcdSettings as $objClientEmployeeTrcdSetting){
			$aryObjClientEmployeeTrcdSettings[$objClientEmployeeTrcdSetting->client_employee_id] = $objClientEmployeeTrcdSetting;
		}

		// 社員ごとの本日の入出金概要を取得しておく
		$clientEmployeePaymentSummaries = $this->objTrcdTerminalChangeRecordRepository->GetPaymentSummariesOf(Carbon::today(), $aryTargetClientEmployeeIds);


		// 結果用のデータを取得
		foreach($objClientEmployees as $objClientEmployee){
			$add = array();

			$add['user_id'] = $objClientEmployee->auth_key;

			// 払い出し可能金額を算出。クエリ負荷軽減の為、一括で取得したデータを使い回す。
			$cacheObj = array();
			$cacheObj['client_employee_trcd_state'] = isset($aryObjClientEmployeeTrcdStates[$objClientEmployee->id])
				? $aryObjClientEmployeeTrcdStates[$objClientEmployee->id]
				: null;
			$cacheObj['client_employee_trcd_setting'] = isset($aryObjClientEmployeeTrcdSettings[$objClientEmployee->id])
				? $aryObjClientEmployeeTrcdSettings[$objClientEmployee->id]
				: null;

			// 今月の払い出し可能金額を算出
			$add['withdraw_amount'] = $this->GetHowMuchCanIWithdrawMonthByClientEmployeeId(
				$objClientEmployee->id,
				//$objClientEmployee,
				$cacheObj['client_employee_trcd_state'],
				$cacheObj['client_employee_trcd_setting']
			);

			// 該当のClientEmployeeTrcdStateデータの取得
			$objClientEmployeeTrcdState = isset($aryObjClientEmployeeTrcdStates[$objClientEmployee->id])
				? $aryObjClientEmployeeTrcdStates[$objClientEmployee->id] // 既にデータがある場合
				: $this->GetClientEmployeeTrcdStateByClientEmployeeId($objClientEmployee->id); // データがない場合

			// 本日の払い出し上限金額を算出
/*
			$objClientTrcdSetting = $this->GetClientTrcdSettingByClientId($objClientEmployee->client_id);
			//if($objClientEmployeeTrcdState->withdraw_amount_allowable_this_month){
			if($add['withdraw_amount'] > $objClientTrcdSetting->withdraw_amount_limit_a_day){
				$add['withdraw_limit_amount'] = $objClientTrcdSetting->withdraw_amount_limit_a_day;
			}else{
				$add['withdraw_limit_amount'] = $add['withdraw_amount'];
			}
*/

			$objClientTrcdSetting = $this->GetClientTrcdSettingByClientId($objClientEmployee->client_id);
			// 本日出入金概要
			$paymentSummaryOfToday = $clientEmployeePaymentSummaries[$objClientEmployee->id];
			// 本日実質払出額 出金額よりも入金していれば０とみなす。それ以外は差分を算出
			$paymentOfToday = $paymentSummaryOfToday['withdraw'] < $paymentSummaryOfToday['refund']
				? 0
				: $paymentSummaryOfToday['withdraw'] - $paymentSummaryOfToday['refund'];
				
			// 本日の払出上限額を算出
			$limitOfToday = $objClientTrcdSetting->withdraw_amount_limit_a_day > $paymentOfToday
				? $objClientTrcdSetting->withdraw_amount_limit_a_day - $paymentOfToday
				: 0;

			$add['withdraw_limit_amount'] = $add['withdraw_amount'] > $limitOfToday
				? $limitOfToday
				: $add['withdraw_amount'];


			// 返金上限金額
			$add['refund_limit_amount'] = $objClientEmployeeTrcdState->withdraw_amount_already_this_month;


			$aryResults[] = $add;
		}


		return $aryResults;
	}

// ========== 払い出し金額等の取得 ==========
	/**
	 * 社員の今月の払い出し可能金額を算出
	 *
	 * 上限金額の算出方法
	 * ・社員ごと月ごとに払いだせる上限金額が設定されている (client_employee_trcd_settings.withdraw_amount_limit_a_month)
	 * ・企業ごとに１日に払いだせる上限金額が決まっている
	 *
	 * @return integer
	 */
	protected function GetHowMuchCanIWithdrawMonthByClientEmployeeId($client_employee_id, /*$objClientEmployee=null, */$objClientEmployeeTrcdState=null, $objClientEmployeeTrcdSetting=null){
		//$this->objClientRepository->getByClientEmployeeId($client_employee_id);
/*
		// 該当社員の情報を取得
		if(is_null($objClientEmployeeTrcdState)){
			$objClientEmployeeTrcdState = $this->GetClientEmployeeTrcdStateByClientEmployeeId($client_employee_id);
		}

		// 社員個人の今月払い出し可能残高を取得
		$intWithdrawToday = $objClientEmployeeTrcdState->withdraw_amount_allowable_this_month - $objClientEmployeeTrcdState->withdraw_amount_already_this_month;

		// 社員の月次払い出し上限金額を取得
		if(is_null($objClientEmployeeTrcdSetting)){
			$objClientEmployeeTrcdSetting = $this->GetClientEmployeeTrcdSettingByClientEmployeeId($client_employee_id);
		}
		// 社員個人の今月払い出し可能金額を取得
		if($intWithdrawToday > $objClientEmployeeTrcdSetting->withdraw_amount_limit_a_month){
			$intWithdrawToday = $objClientEmployeeTrcdSetting->withdraw_amount_limit_a_month;
		}

		return $intWithdrawToday;
*/

		// 該当社員の情報を取得
		if(is_null($objClientEmployeeTrcdState)){
			$objClientEmployeeTrcdState = $this->GetClientEmployeeTrcdStateByClientEmployeeId($client_employee_id);
		}

		// 社員の月次払い出し上限金額を取得
		if(is_null($objClientEmployeeTrcdSetting)){
			$objClientEmployeeTrcdSetting = $this->GetClientEmployeeTrcdSettingByClientEmployeeId($client_employee_id);
		}

		// 社員個人の今月払い出し上限額
		// 「今月の前払い可能総額」 > 「社員個人の月に引き出せる上限額」の場合は「社員個人の月に引き出せる上限額」を採用 ※少ない方を採用
		$maximum_withdrawal_amount_this_month = $objClientEmployeeTrcdState->withdraw_amount_allowable_this_month > $objClientEmployeeTrcdSetting->withdraw_amount_limit_a_month
			? $objClientEmployeeTrcdSetting->withdraw_amount_limit_a_month
			: $objClientEmployeeTrcdState->withdraw_amount_allowable_this_month;

		// 社員個人の今月払い出し上限額 - 今月払い出した額 = 今月払い出せる額
		$withdrawable_amount_this_month = $maximum_withdrawal_amount_this_month - $objClientEmployeeTrcdState->withdraw_amount_already_this_month;

		return $withdrawable_amount_this_month;
	}










// ========== Get 単発モデル系 ==========
	/**
	 * ClientTrcdSettingを取得。なければレコードを作成する。
	 *
	 * @param integer $client_id
	 * @return ClientTrcdSetting
	 */
	public function GetClientTrcdSettingByClientId($client_id){
		static $cacheResults = array();

		if(!is_numeric($client_id)){
			trigger_error('引数 $client_id は整数ではありません。');
			exit();
		}

		if(!isset($cacheResults[$client_id])){
			$objClientTrcdSetting = $this->objClientTrcdSettingRepository->getByClientId($client_id);
			if(is_null($objClientTrcdSetting)){
				$this->CreateClientTrcdSetting($client_id);
				$objClientTrcdSetting = $this->objClientTrcdSettingRepository->getByClientId($client_id);
			}
			$cacheResults[$client_id] = $objClientTrcdSetting;
		}

		return $cacheResults[$client_id];
	}

	/**
	 * ClientEmployeeTrcdStateを取得。なければレコードを作成する。
	 *
	 * @param integer $client_employ_id
	 * @param array $cacheAryObjClientEmployeeTrcdStates ループの為にSQLクエリが実行される事の対策です。ID順に配列を形成した取得済みオブジェクトを指定します。
	 * @return ClientTrcdState
	 */
	public function GetClientEmployeeTrcdStateByClientEmployeeId($client_employee_id){
		static $cacheResults = array();

		if(!is_numeric($client_employee_id)){
			trigger_error('引数 $client_employee_id は整数ではありません。');
			exit();
		}

		if(!isset($cacheResults[$client_employee_id])){
			$objClientEmployeeTrcdState = $this->objClientEmployeeTrcdStateRepository->getById($client_employee_id);
			if(is_null($objClientEmployeeTrcdState)){
				$this->CreateClientEmployeeTrcdState($client_employee_id);
				$objClientEmployeeTrcdState = $this->objClientEmployeeTrcdStateRepository->getById($client_employee_id);
			}
			$cacheResults[$client_employee_id] = $objClientEmployeeTrcdState;
		}

		return $cacheResults[$client_employee_id];
	}

	/**
	 * ClientEmployeeTrcdSettingを取得。なければレコードを作成する。
	 *
	 * @param integer $client_employ_id
	 * @return ClientTrcdSetting
	 */
	public function GetClientEmployeeTrcdSettingByClientEmployeeId($client_employee_id){
		static $cacheResults = array();

		if(!is_numeric($client_employee_id)){
			trigger_error('引数 $client_employee_id は整数ではありません。');
			exit();
		}

		if(!isset($cacheResults[$client_employee_id])){
			$objClientEmployeeTrcdSetting = $this->objClientEmployeeTrcdSettingRepository->getById($client_employee_id);
			if(is_null($objClientEmployeeTrcdSetting)){
				$this->CreateClientEmployeeTrcdSetting($client_employee_id);
				$objClientEmployeeTrcdSetting = $this->objClientEmployeeTrcdSettingRepository->getById($client_employee_id);
			}
			$cacheResults[$client_employee_id] = $objClientEmployeeTrcdSetting;
		}

		return $cacheResults[$client_employee_id];
	}



	/**
	 * 勤怠１日の区切り時間を取得します。
	 *
	 * 社員ごと（client_employee_trcd_settings）ごとに設定があればそれを
	 * 社員ごとになければ企業ごと（client_trcd_settings）を取得して返す。
	 */
	public function getTimToJudgeOnDayByClientEmployeeId($client_employee_id){
		// 社員ごとに設定されていればそれを返す。
		$objClientEmployeeTrcdSetting = $this->objClientEmployeeTrcdSettingRepository->getById($client_employee_id);
		if( $objClientEmployeeTrcdSetting != null){
			if(is_numeric($objClientEmployeeTrcdSetting->attendance_pattern_id)){
				$objAttendancePattern = $objClientEmployeeTrcdSetting->attendance_pattern;
				if( $objAttendancePattern != null ){
					if(!empty($objAttendancePattern->time_to_judge_one_day)){
						return $objAttendancePattern->time_to_judge_one_day;
					}
				}
			}

			// 社員ごとに設定されていなければ企業ごとの設定値を返す。
			//$objClientTrcdSetting = $this->objClientTrcdSettingRepository->getById($objClientEmployeeTrcdSetting->client_employee_id);
			// @YuKaneko 2019/07/25 ↑ client_trcd_settings.idを渡すべきだがclient_employees.idが渡されているため下記に修正
			$objClientEmployee = $this->objClientEmployeeService->getById($client_employee_id);
			if ( is_null($objClientEmployee) ) return null;

			$objClientTrcdSetting = $this->objClientTrcdSettingRepository->getByClientId($objClientEmployee->client_id);
			if( $objClientTrcdSetting != null
			 && !is_null($objClientTrcdSetting->time_to_judge_one_day) ){
				return $objClientTrcdSetting->time_to_judge_one_day;
			}
		}

		return null;
	}

// ==================== １回の勤怠が完了したら実施する処理（払い出し金額を増額する） ====================
// 本当はリポジトリでオブジェクト化したかったが時間が無いので取り急ぎ
	public function UpdateWithdrawAmountAtThisMountByClientEmployeeId($client_employee_id, $dbTransaction=true, $settings = []){

		// クライアント毎の設定を取得（集計の際 出勤を使うか退勤を使うか）
		$objClientEmployee = $this->objClientEmployeeRepository->getById($client_employee_id);
		if($objClientEmployee == null){
			return false;
		}

		// 締め日を取得
		$objClientTrcdSetting = $this->objClientTrcdSettingRepository->getByClientId($objClientEmployee->client_id);
		if($objClientTrcdSetting == null){
			return false;
		}

		// 範囲指定されていない場合は当月
		if ( empty($settings['specific_range']) ) {
			// 集計期間の開始を取得
			$objStartDatetime = Carbon::today();
			$objStartDatetime->day = $objClientTrcdSetting->payroll_start_day;
			if($objStartDatetime->isFuture()){
				$objStartDatetime->subMonth();
			}

			// 集計期間の終了を取得
			$objEndDatetime = $objStartDatetime->copy();
			$objEndDatetime->addMonth();
			$objEndDatetime->subSecond();
		} else {
			if ( !is_array($settings['specific_range']) || !isset($settings['specific_range']['from']) || !isset($settings['specific_range']['until']) ) {
				logger()->error("集計指定範囲の値が不正です。" . print_r($settings['specific_range'], true));
				return false;
			} else {
				$objStartDatetime = Carbon::parse($settings['specific_range']['from']);
				$objEndDatetime = Carbon::parse($settings['specific_range']['until'])->endOfDay();
			}
		}

		// 該当の勤務データを取得する
		$options = array(
			'closing_flag'=>true,
			'mismatch_flag'=>false,
			'request_flag'=>false
		);
		$objAttendanceHeaderRepos = $this->objAttendanceHeaderRepository->LoadAttendance($client_employee_id, $objStartDatetime, $objEndDatetime, $options);


		// 有給データの取得
		$objAttendancePaidHolidayService = app()->make(AttendancePaidHolidayService::class);
		$options = array(
			'from'=>$objStartDatetime->toDateString(),
			'to'=>$objEndDatetime->toDateString()
		);
		$objAttendancePaidHolidays = $objAttendancePaidHolidayService->getByClientEmployeeId($client_employee_id, $options);
		// 有休マスタを取得
		if(!$objAttendancePaidHolidays->isEmpty()){
			$objPaidHolidayService = app()->make(PaidHolidayService::class);
			// @2020.07.29 YuKaneko ToDo 削除されている有給種別が利用されている際に
			// あとの処理でエラーとなるため論理削除済みを含めて取得する。
			// 有給種別マスタが削除されても、有給を取得したデータ（事実）が消えるわけではないので、
			// 通常のデータと同じように計算を行っても大丈夫なはず。
			$objPaidHolidays = $objPaidHolidayService->getByClientId($objClientEmployee->client_id, ['withTrashed' => true]);

			$cachePaidHolidays = array();
			foreach($objPaidHolidays as $objPaidHoliday){
				$cachePaidHolidays[$objPaidHoliday->id] = $objPaidHoliday;
			}
		}


		// 前払い金額加算種別を取得（1日出社毎 or 1時間毎）
		$additional_amount_type_id = $this->objClientEmployeeService->getAdditionalConstAmountTypeId($client_employee_id);
		if( !is_numeric($additional_amount_type_id) ){
			// client_employee_trcd_settingsからもclient_trcd_settingsからもadditional_amount_type_idを取得できなかった。未設定の可能性。
			Log::error(__METHOD__."() : client_employee_trcd_settingsからもclient_trcd_settingsからもadditional_amount_type_idを取得できませんでした。未設定の可能性。\$client_employee_id = {$client_employee_id}。 in ".__FILE__." on line ".__LINE__);
			return false;
		}


		// 休憩データの自動挿入
		foreach($objAttendanceHeaderRepos as $objAttendanceHeaderRepo){
			if(is_null($objAttendanceHeaderRepo->objModel->assigned_auto_break_time_at)){
				// 処理が止まると困るので、エラーハンドリングはあえてしない
				//$objAttendanceHeaderRepo->insertBreakTime();

				/* @YuKaneko 2019/07/27
					このあとの丸め処理の箇所で、
					自動挿入された休憩の勤怠詳細データが反映されていないため、
					処理に成功した場合は更新する。（暫定処置）
				*/
				if ( $objAttendanceHeaderRepo->insertBreakTime() ) {
					$objAttendanceHeaderRepo->updateAttendanceDetailData();
				}
			}
		}


		// 有休との重複の場合は、加算しないケースもあるので、有休を取った日付を整理して、日付をキーとした配列に格納しておく。
		$aryMyPaidHolidays = array();
		if(!$objAttendancePaidHolidays->isEmpty()){
			foreach($objAttendancePaidHolidays as $objAttendancePaidHoliday){
				$aryMyPaidHolidays[$objAttendancePaidHoliday->date] = $cachePaidHolidays[$objAttendancePaidHoliday->paid_holiday_id];
			}
		}


		// まるめ処理（まるめ処理は、日給社員の前払い金額には反映されないが、集計画面で利用するため丸める）
		$totalHours = 0; // 月の合計勤務時間（分）
		foreach($objAttendanceHeaderRepos as $objAttendanceHeaderRepo){
			$minutes = 0;
			if ( !empty($settings['force_rounding']) ) {
				// 値が算出されていてもいなくても強制的にまるめ処理を行う
				$minutes = $objAttendanceHeaderRepo->GetAttendanceTotalMinutes(true);
			} else if(is_numeric($objAttendanceHeaderRepo->objModel->attendance_total_minutes)){
				// 勤務時間（分）を取得
				// DBに保存されている集計済み勤務時間を利用
				$minutes = $objAttendanceHeaderRepo->objModel->attendance_total_minutes;
			}else{
				// 初めての計算なので、算出
				$minutes = $objAttendanceHeaderRepo->GetAttendanceTotalMinutes();
			}

			// 加算
			$totalHours += floor($minutes / 60);
		}


		// メッセージサービス
		$objMessageService = app()->make(MessageService::class);

		// 勤務基点設定取得
		$CONST_PAYROLL_USE_TIMING_LIST = config('database.trcd.client_trcd_settings.CONST_PAYROLL_USE_TIMING.LIST');
		$base_point_column_name = ( $objClientTrcdSetting->payroll_use_timing_id == $CONST_PAYROLL_USE_TIMING_LIST['USE_FINISHED_ATTENDANCE_DATETIME'] )
			? 'attendance_finished_datetime'
			: 'attendance_started_datetime';

		// 算出。クラス化すると重くなるので、ロジックで対応
		if($additional_amount_type_id == config('database.trcd.client_trcd_settings.CONST_ADDTIONAL_AMOUNT_TYPE.LIST.DAY')){
			// 日給加算の社員の処理

			// 設定すべき前払い合計金額
			$withdraw_amount_allowable_this_month = 0;

			// 単価を取得
			$amountDay = $this->objClientEmployeeService->getAdditionalConstAmountOnOneDay($client_employee_id);



			// 前払い上限金額
			// 区切り時間内で重複打刻を１打刻として集計する
			$time_to_judge_one_day = $this->getTimToJudgeOnDayByClientEmployeeId($client_employee_id);
			if($time_to_judge_one_day==null){
				$time_to_judge_one_day='00:00:00';
			}
			$objTimeToJudgeOneDay = new Carbon($time_to_judge_one_day);

			// 区切り時間が設定されている。
			$objNextJudgeDatetime = null;
			$blnCreatedMessage = false;

			// 勤務日数カウント用の配列
			$attendanceDays = [];

			foreach($objAttendanceHeaderRepos as $objAttendanceHeaderRepo){
				//$currentDate = new Carbon($objAttendanceHeaderRepo->attendance_started_datetime);
				//$currentDate = new Carbon($objAttendanceHeaderRepo->$base_point_column_name);
				$currentDate = new Carbon($objAttendanceHeaderRepo->date_for_display);

				// 有休を取得している日だったら、有休マスタ設定を確認して、勤怠の打刻の前払いをスキップする。
				if(isset($aryMyPaidHolidays[$currentDate->toDateString()])){
					if($aryMyPaidHolidays[$currentDate->toDateString()]->when_duplicate_attendance_add_amount_flag==false){
						// 打刻の分は加算しない。

//mydump(config('database.paid_holiday_duplicate_process_types.CONST.NOTICE_ENABLE'));
						// メッセージを出すか
						if($blnCreatedMessage == false && $aryMyPaidHolidays[$currentDate->toDateString()]->when_duplicate_attendance_alert_type_id == config('database.paid_holiday_duplicate_process_types.CONST.NOTICE_ENABLE')){
							// メッセージを出す
							$message = array(
								'client_employee_id'=>$client_employee_id,
								'message'=>config('database.messages.CONST.USERMESSAGE.ATTENDANCE_PAID_HOLIDAY_DUPLICATE'),
								'priority_id'=>config('database.trcd.priorities.CONST.WARNING')
							);
							$objMessageService->create($message);
							$blnCreatedMessage = true;
						}

						// 次のループ（打刻データ処理）へ。
						continue;
					}
					// 勤怠の分を加算するので、引き続き、下部の加算処理を実行
				}

				// 勤務日数に加算、date_for_displayが同じ日付であった場合は日数は加算されない
				$attendanceDays[$currentDate->toDateString()] = true;

				/*
				// ループ１回目
				if(is_null($objNextJudgeDatetime)){
					$withdraw_amount_allowable_this_month += $amountDay;
				}else{
					//$objNextJudgeDatetime = $currentDate->copy();
					if($objNextJudgeDatetime->lt($currentDate)){
						$withdraw_amount_allowable_this_month += $amountDay;
					}
				}

				// 次の跨ぎタイミングを生成する
				if(is_null($objNextJudgeDatetime) || $objNextJudgeDatetime->lt($currentDate)){
					$objNextJudgeDatetime = $currentDate->copy();
					$objNextJudgeDatetime->year = $currentDate->year;
					$objNextJudgeDatetime->month = $currentDate->month;
					$objNextJudgeDatetime->day = $currentDate->day;
					$objNextJudgeDatetime->hour = $objTimeToJudgeOneDay->hour;
					$objNextJudgeDatetime->minute = $objTimeToJudgeOneDay->minute;
					$objNextJudgeDatetime->second = 0;
					$objNextJudgeDatetime->addDay();
				}
				*/

			}

			$withdraw_amount_allowable_this_month = count($attendanceDays) * $amountDay;

			// 有休を換算
			if(!$objAttendancePaidHolidays->isEmpty()){
				foreach($objAttendancePaidHolidays as $objAttendancePaidHoliday){
					// $objAttendancePaidHoliday;
					if( isset($cachePaidHolidays[$objAttendancePaidHoliday->paid_holiday_id]) ){
						$withdraw_amount_allowable_this_month += $amountDay * $cachePaidHolidays[$objAttendancePaidHoliday->paid_holiday_id]->add_amount_per_for_daily_employee;
					}
				}
			}

			// 勤務日数を取得
			//$attendanceDays = count($objAttendanceHeaderRepos);
			//$withdraw_amount_allowable_this_month = ($attendanceDays * $amountDay);
		}elseif($additional_amount_type_id == config('database.trcd.client_trcd_settings.CONST_ADDTIONAL_AMOUNT_TYPE.LIST.HOUR')){
			// 時間加算単価を取得
			$amountHour = $this->objClientEmployeeService->getAdditionalConstAmountOnOneHour($client_employee_id);

			// まるめ処理
			$totalHours = 0; // 月の合計勤務時間（分）
			$blnCreatedMessage = false;
			foreach($objAttendanceHeaderRepos as $objAttendanceHeaderRepo){
				//$currentDate = new Carbon($objAttendanceHeaderRepo->attendance_started_datetime);
				//$currentDate = new Carbon($objAttendanceHeaderRepo->$base_point_column_name);
				$currentDate = new Carbon($objAttendanceHeaderRepo->date_for_display);

				// 有休を取得している日だったら、有休マスタ設定を確認して、勤怠の打刻の前払いをスキップする。
				if(isset($aryMyPaidHolidays[$currentDate->toDateString()])){
					if($aryMyPaidHolidays[$currentDate->toDateString()]->when_duplicate_attendance_add_amount_flag==false){
						// 打刻の分は加算しない。

//mydump(config('database.paid_holiday_duplicate_process_types.CONST.NOTICE_ENABLE'));
						// メッセージを出すか
						if($blnCreatedMessage == false && $aryMyPaidHolidays[$currentDate->toDateString()]->when_duplicate_attendance_alert_type_id == config('database.paid_holiday_duplicate_process_types.CONST.NOTICE_ENABLE')){
							// メッセージを出す
							$message = array(
								'client_employee_id'=>$client_employee_id,
								'message'=>config('database.messages.CONST.USERMESSAGE.ATTENDANCE_PAID_HOLIDAY_DUPLICATE'),
								'priority_id'=>config('database.trcd.priorities.CONST.WARNING')
							);
							$objMessageService->create($message);
							$blnCreatedMessage = true;
						}

						// 次のループ（打刻データ処理）へ。
						continue;
					}
					// 勤怠の分を加算するので、引き続き、下部の加算処理を実行
				}


				// まるめ処理は上部で日給社員と共通処理で実施しているので、不要。集計だけ。
				// 加算
				$totalHours += floor($objAttendanceHeaderRepo->objModel->attendance_total_minutes / 60);
			}

			// 前払い上限金額
			$withdraw_amount_allowable_this_month = ($amountHour * $totalHours);


			// 有休を換算
			if(!$objAttendancePaidHolidays->isEmpty()){
				foreach($objAttendancePaidHolidays as $objAttendancePaidHoliday){
					// $objAttendancePaidHoliday;
					if( isset($cachePaidHolidays[$objAttendancePaidHoliday->paid_holiday_id]) ){
						$time = $cachePaidHolidays[$objAttendancePaidHoliday->paid_holiday_id]->add_time_for_hourly_employee;
						list($hour) = preg_split('/:/', $time);
						$withdraw_amount_allowable_this_month += $amountHour * (int)$hour;
					}
				}
			}
		}


		// 前払い上限金額を更新しない設定がされている場合はここでリターン
		if ( isset($settings['update_prepayment_limit']) && empty($settings['update_prepayment_limit']) ) return true;


		// 前払い上限金額を更新する
		$objClientTrcdState = $this->objClientEmployeeTrcdStateRepository->getById($objClientEmployee->id);
		if($objClientTrcdState == null){
			return false;
		}
		// 前払い金額更新
		$objClientTrcdState->withdraw_amount_allowable_this_month = $withdraw_amount_allowable_this_month;
		try{
			$objClientTrcdState->save();
		}catch(\Exception $e){
			Log::error($e->getMessage());
			Log::error(__METHOD__."(): データベースの保存で失敗しました。 in ".__FILE__." on line ".__LINE__);
			return false;
		}


		return true;
	}

	/**
	 * クライントに所属する全ての従業員を更新する処理。
	 * 管理画面などから利用してください。
	 *
	 * また、実装する際は、UpdateWithdrawAmountAtThisMountByClientEmployeeId()をループして呼び出すようにしてください。
	 * （ロジックを共通化してください）
	 */
	public function UpdateWithdrawAmountAtThisMountByClientId($client_id=null, $dbTransaction = true, $settings = []) {
/*
print __METHOD__."(): 必要に応じて実装してください。";
exit();
*/
		// 企業に属する社員リスト取得
		$client_employee_list = $this->objClientEmployeeRepository->getArrayListByClientId($client_id);

		DB::beginTransaction();

		try {
			foreach( $client_employee_list as $client_employee_id => $client_employee_name ) {
				$result = $this->UpdateWithdrawAmountAtThisMountByClientEmployeeId($client_employee_id, $dbTransaction, $settings);

				if ( empty($result) ) {
					$error_msg = "client_employees.id:{$client_employee_id} {$client_employee_name} の前払額更新処理に失敗しました。";
					throw new \Exception($error_msg);
				}
			}
		} catch ( \Exception $e ) {
			DB::rollBack();
			logger()->error("[社員一括前払額更新処理]{$e->getMessage()}");
			logger()->error($e);

			return false;
		}

		DB::commit();

		return true;
	}

	/*
		社員の特定月の勤怠を集計（丸め処理など）
		「当月」の場合は前払い金額の更新なども行う
		@param int $client_employee_id
		@param Carbon $objSpecificDate
		@return bool
	*/
	public function UpdateWithdrawAmountOfClientEmployeeAtThisMonthIfThisMonthIncludingSpecifiedDate($client_employee_id, Carbon $objSpecificDate) {
		// 社員情報取得
		$objClientEmployee = $this->objClientEmployeeRepository->getById($client_employee_id);

		if ( empty($objClientEmployee) ) {
			logger()->error(__METHOD__ . "client_employee_id {$client_employee_id} does not exist.");
			return false;
		}


		// 更新オプション値設定
		$settings = [
			'force_rounding' => true, // 丸め処理強制
		];

		// 当月の範囲を算出
		$this_month_range = $this->CalcMonthRangeOf($objClientEmployee['client_id'], Carbon::now());

		// 指定日が当月に含まれていない場合
		if ( !$objSpecificDate->between($this_month_range['from'], $this_month_range['until']) ) {
			// 指定日を含む「月」を算出
			$specific_range = $this->CalcMonthRangeOf($objClientEmployee['client_id'], $objSpecificDate);

			// オプション値を追加
			$settings['specific_range'] = $specific_range; // 範囲指定
			$settings['update_prepayment_limit'] = false; // 「当月」の前払い金額の更新は行わない
		}

		$update_result = $this->UpdateWithdrawAmountAtThisMountByClientEmployeeId(
			$client_employee_id,
			true,
			$settings
		);

		if ( !$update_result ) {
			logger()->error(__METHOD__ . "前払い可能金額集計に失敗しました。"
				. "\nclient_employee_id={$client_employee_id}"
				. "\nsettings=" . print_r($settings, true)
			);
			return false;
		}

		return true;
	}

// ==================== 締め日系の処理 ====================
	/**
	 * 締め日の処理
	 *
	 * @param integer $today_day 本日の日付けの日部分を指定。実運用の場合はdate('d')を指定するが、テストの為、パラメータを指定出来るようにしている。
	 * @return ClientTrcdSetting
	 */
	public function ResetWithdrawAmount_ClosingProcess($day=0){
		// 存在する日付かを確認
		//if( !checkdate(date('m'), $day, date('y'))){
		//	// 存在しない日付が指定されました。
		//	return false;
		//}

		// 締め処理の該当のクライアントを取得
		$objClientTrcdSettings = $this->GetClientTrcdSettingsByNeedResetWithdrawAmountAndToday($day);

		// 締め処理
		foreach($objClientTrcdSettings as $objClientTrcdSetting){
			// 日付部分を取得
			list($date) = preg_split('/ /', $objClientTrcdSetting->payroll_start_day_reset_at);
			// 本日、締め済みであれば処理しない
			if(
				is_null($objClientTrcdSetting->payroll_start_day_reset_at)
				|| ($date != date('Y-m-d')) ){

				Log::info("締め処理. client_id: ".$objClientTrcdSetting->client_id);

				// 該当の社員IDを取得
				$clientEmployees = $this->objClientEmployeeRepository->getByClientId($objClientTrcdSetting->client_id);
				$aryClientEmployeeIds = array();
				foreach($clientEmployees as $clientEmployee){
					$aryClientEmployeeIds[] = $clientEmployee->id;
				}

				// 締め処理実行
				if(!$this->objClientEmployeeTrcdStateRepository->clearWithdrawAmountStatesByClientEmployeeIds($aryClientEmployeeIds)){
					Log::error("\$this->objClientEmployeeTrcdStateRepository->clearWithdrawAmountStatesByClientEmployeeIds(\$aryClientEmployeeIds) は、falseを返しました。\n\$aryClientEmployeeIids = ".print_r($aryClientEmployeeIds,true));
					return false;
				}

				// 処理が成功したら client_trcd_settings.payroll_start_day_reset_at を更新
				if(!$this->objClientTrcdSettingRepository->updatePayrollStartDayResetAt($objClientTrcdSetting->client_id)){
					Log::error("\$this->objClientTrcdSettingRepository->updatePayrollStartDayResetAt(\$client_id)は、falseを返しました。\n\$client_id = {$objClientTrcdSetting->client_id}");
				}
				//$this->objClientTrcdSettingRepository->getByClientId($objClientTrcdSetting->client_id);
			}
		}

		return true;
	}
	/**
	 * 締め日（DB上は開始日）のリセット処理が当日のClientTrcdSettingを取得。
	 *
	 * @param integer $client_id
	 * @return ClientTrcdSetting
	 */
	public function GetClientTrcdSettingsByNeedResetWithdrawAmountAndToday($day=0){
		static $cacheResults = array();

		// 日付が指定されていなければ本日の日に設定
		if( !is_numeric($day) || $day==0 ){
			$day = date('d');
		}

		if(!isset($cacheResults[$day])){
			$options = array();
			$options['payroll_start_day'] = $day;
			$objClientTrcdSettings = $this->objClientTrcdSettingRepository->getAll($options);
			$cacheResults[$day] = $objClientTrcdSettings;
		}

		return $cacheResults[$day];
	}

	/*
		入出金管理の範囲取得
		@param $client_id
		@param Carbon $start_of_month 該当月
		@return Array [
			'from' => 開始日時
			'to' => 終了日時
		]
	*/
	public function getPayrollRangeOf($client_id, Carbon $start_of_month) {
		/*
			payroll_start_dayが開始日
			payroll_start_dayが3に設定されている場合、YYYY-06-03 00:00:00 ~ YYYY-07-02 23:59:59 を6月分として扱う
		*/
		$client_trcd_setting = $this->objClientTrcdSettingRepository->getByClientId($client_id);
		$start_of_next_month = $start_of_month->copy()->addMonth()->startOfMonth();

		//開始地点
		$start_datetime = Carbon::createMidnightDate($start_of_month->year, $start_of_month->month, $client_trcd_setting->payroll_start_day);
		//終了地点
		$end_datetime = Carbon::createMidnightDate($start_of_next_month->year, $start_of_next_month->month, $client_trcd_setting->payroll_start_day)->subSecond();

		return [
			'from' => $start_datetime,
			'to' => $end_datetime,
		];
	}

	/*
		指定された日付が属する各企業における「月」の範囲を取得
		@param int $client_id
		@param Carbon $specific_date 特定日付
		@return Array [
			'from' => 開始日時
			'to' => 終了日時
		]
	*/
	public function CalcMonthRangeOf($client_id, Carbon $objCarbonSpecificDate) {
		$objClientTrcdSetting = $this->objClientTrcdSettingRepository->getByClientId($client_id);
		$objStartOfMonth = null; // 開始地点
		$objEndOfMonth = null; // 終了地点

		if ( $objCarbonSpecificDate->day >= $objClientTrcdSetting->payroll_start_day ) {
			// 特定日付の日が締め日翌日以降であれば、その年月の締め日翌日を開始地点にする
			$objStartOfMonth = Carbon::createMidnightDate($objCarbonSpecificDate->year, $objCarbonSpecificDate->month, $objClientTrcdSetting->payroll_start_day);
		} else {
			// 特定日付の日が締め日翌日未満であれば、その年月の前月の締め日翌日を開始地点にする
			$objStartOfMonth = $objCarbonSpecificDate->copy()->startOfMonth()->subSecond()->startOfMonth();
			$objStartOfMonth->day = $objClientTrcdSetting->payroll_start_day;
		}

		// 暦上の翌月を取得
		$objActualStartOfNextMonth = $objStartOfMonth->copy()->endOfMonth()->addSecond();

		// 終了地点
		$objEndOfMonth = Carbon::createMidnightDate($objActualStartOfNextMonth->year, $objActualStartOfNextMonth->month, $objClientTrcdSetting->payroll_start_day)->subSecond();

		return [
			'from' => $objStartOfMonth,
			'until' => $objEndOfMonth,
		];
	}

	/*
		企業ごとの設定から勤怠ヘッダの「基点日時」を算出する
		@param int $client_id
		@param $objAttendanceHeader
		@return Carbon $objCarbonBaseDatetime
	*/
	public function CalcBaseDatetimeOfAttendanceHeader($client_id, $objAttendanceHeader) {
		$objClientTrcdSetting = $this->GetClientTrcdSettingByClientId($client_id);
		$PAYROLL_USE_TIMING_LIST = config('database.trcd.client_trcd_settings.CONST_PAYROLL_USE_TIMING.LIST');
		$base_datetime_column = $objClientTrcdSetting->payroll_use_timing_id == $PAYROLL_USE_TIMING_LIST['USE_FINISHED_ATTENDANCE_DATETIME']
			? 'attendance_finished_datetime'
			: 'attendance_started_datetime';

		// 基底カラムに値が設定されていない場合はソート用のカラムを利用する
		if ( !isset($objAttendanceHeader->$base_datetime_column) ) $base_datetime_column = 'attendance_datetime_of_first';

		// ソート用のカラムにも値が設定されていなければ null を返す
		if ( !isset($objAttendanceHeader->$base_datetime_column) ) return null;

		return Carbon::parse($objAttendanceHeader->$base_datetime_column);
	}

// ========== create系 ==========
	/**
	 * ClientTrcdSettingを作成
	 */
	public function CreateClientTrcdSetting($client_id, $rounding_franction=null, $fixed_work_start_time=null, $withdraw_amount_limit_a_day=null){
		return $this->objClientTrcdSettingRepository->create($client_id, $rounding_franction, $fixed_work_start_time, $withdraw_amount_limit_a_day);
	}

	/**
	 * ClientEmployeeTrcdStateを作成
	 */
	public function CreateClientEmployeeTrcdState($client_employee_id, $withdraw_amount_allowable_this_month=null, $withdraw_amount_already_this_month=null){
		return $this->objClientEmployeeTrcdStateRepository->create($client_employee_id, $withdraw_amount_allowable_this_month, $withdraw_amount_already_this_month);
	}

	/**
	 * ClientEmployeeTrcdSettingを作成
	 */
	public function CreateClientEmployeeTrcdSetting($client_employee_id, $withdraw_amount_limit_a_month=null){
		return $this->objClientEmployeeTrcdSettingRepository->create($client_employee_id, $withdraw_amount_limit_a_month=null);
	}



// ========== 入出金 ==========

	/**
	 * TRCDでの入出金を記録する
	 */
	public function CreateTrcdTerminalChangeRecord(
		$trcd_terminal_id,
		$trcd_terminal_change_type_id,
		$client_employee_id,
		$amount_of_change_10k,
		$amount_of_change_5k,
		$amount_of_change_1k,
		$amount_of_change_500,
		$amount_of_change_100,
		$amount_of_change_50,
		$amount_of_change_10,
		$amount_of_change_5,
		$amount_of_change_1,
		$amount_of_balance_10k,
		$amount_of_balance_5k,
		$amount_of_balance_1k,
		$amount_of_balance_500,
		$amount_of_balance_100,
		$amount_of_balance_50,
		$amount_of_balance_10,
		$amount_of_balance_5,
		$amount_of_balance_1,
		$register_datetime,
		$options = []
	){

		$result = $this->objTrcdTerminalChangeRecordRepository->create(
			$trcd_terminal_id,
			$trcd_terminal_change_type_id,
			$client_employee_id,
			$amount_of_change_10k,
			$amount_of_change_5k,
			$amount_of_change_1k,
			$amount_of_change_500,
			$amount_of_change_100,
			$amount_of_change_50,
			$amount_of_change_10,
			$amount_of_change_5,
			$amount_of_change_1,
			$amount_of_balance_10k,
			$amount_of_balance_5k,
			$amount_of_balance_1k,
			$amount_of_balance_500,
			$amount_of_balance_100,
			$amount_of_balance_50,
			$amount_of_balance_10,
			$amount_of_balance_5,
			$amount_of_balance_1,
			$register_datetime
		);

		if ( $result==false ) {
			return false;
		}

		// @2020.01.28 YuKaneko オプションで設定されている場合のみ引き出し金額を更新するように修正
		if ( isset($options['update_client_employee_trcd_state']) && !empty($options['update_client_employee_trcd_state']) ) {
			// 引き出し金額を更新
			$this->UpdateWithdrawAmountAlreadyThisMonth($client_employee_id);
		}

		return $result;
	}


	/**
	 * 社員の引き出し済み金額を更新する
	 */
	public function UpdateWithdrawAmountAlreadyThisMonth($client_employee_id){
		if(!is_numeric($client_employee_id)){
			Log::error(__METHOD__."(): \$client_employee_id に整数ではない値が渡されました。 in ".__FILE__." on line ".__LINE__);
			return false;
		}

		// 該当社員のクライアントIDを取得する
		$objClientEmployee = $this->objClientEmployeeRepository->getById($client_employee_id);

		// 企業ごとの「当月」開始日時を取得
		$objClientTrcdSetting = $this->objClientTrcdSettingRepository->getByClientId($objClientEmployee->client_id);
		$objCarbonNow = Carbon::now();
		$objCarbonStartOfMonth = $objCarbonNow->copy()->startOfMonth();
		// 企業の締め日翌日が未来日付の場合は前月の初日にして渡す
		if ( $objCarbonNow->day < $objClientTrcdSetting->payroll_start_day ) $objCarbonStartOfMonth = $objCarbonStartOfMonth->subDay()->startOfMonth();

		// 集計対象の範囲を取得
		//$payroll_range = $this->getPayrollRangeOf($objClientEmployee->client_id, new Carbon());
		$payroll_range = $this->getPayrollRangeOf($objClientEmployee->client_id, $objCarbonStartOfMonth);

		// 集計対象のデータを取得
		$options = array(
			'register_datetime'=>array(
				'from'=>$payroll_range['from'],
				'to'=>$payroll_range['to']
			),
			'order'=> array('register_datetime'=>'ASC')
		);
		$objTrcdTerminalChangeRecords = $this->objTrcdTerminalChangeRecordRepository->getByClientEmployeeId($client_employee_id, $options);

		// 集計
		$withdraw_amount_already_this_month = 0;
		foreach($objTrcdTerminalChangeRecords as $objTrcdTerminalChangeRecord){
			if($objTrcdTerminalChangeRecord->trcd_terminal_change_type_id == config('database.trcd.trcd_terminal_change_types.CONST.EMPLOYEE_WITHDRAWAL')){
				// 社員による出金
				$withdraw_amount_already_this_month += $objTrcdTerminalChangeRecord->amount_of_change_total;
			}elseif($objTrcdTerminalChangeRecord->trcd_terminal_change_type_id == config('database.trcd.trcd_terminal_change_types.CONST.EMPLOYEE_REFUND')){
				// 社員による返金
				$withdraw_amount_already_this_month -= $objTrcdTerminalChangeRecord->amount_of_change_total;
			}
		}

		// 今月払い出し済み金額を更新
		$result = $this->objClientEmployeeTrcdStateRepository->updateWithdrawAmountAlreadyThisMonthByClientEmployeeId($client_employee_id, $withdraw_amount_already_this_month);

		return $result;
	}

	/**
	 * クライントに所属する全ての従業員の引き出し済み金額を更新する処理
	 * 管理画面などから利用してください。
	 *
	 */
	public function UpdateWithdrawAmountAlreadyThisMonthByClientId($client_id) {
		// 企業に属する社員リスト取得
		$client_employee_list = $this->objClientEmployeeRepository->getArrayListByClientId($client_id);

		DB::beginTransaction();

		try {
			foreach( $client_employee_list as $client_employee_id => $client_employee_name ) {
				$result = $this->UpdateWithdrawAmountAlreadyThisMonth($client_employee_id);

				if ( empty($result) ) {
					$error_msg = "client_employees.id:{$client_employee_id} {$client_employee_name} の引き出し済み金額更新処理に失敗しました。";
					throw new \Exception($error_msg);
				}
			}
		} catch ( \Exception $e ) {
			DB::rollBack();
			logger()->error("[社員一括引き出し済み金額更新処理]{$e->getMessage()}");
			logger()->error($e);

			return false;
		}

		DB::commit();

		return true;
	}


// ========== TRCDアラートのメッセージ ==========

	/**
	 *
	 */
	public function CreateTrcdMessage(
		$trcd_terminal_id,
		//$client_employee_id,
		$title,
		$message,
		$alert_datetime
	){

		return $this->objTrcdMessageRepository->create(
			$trcd_terminal_id,
			//$client_employee_id,
			$title,
			$message,
			$alert_datetime
		);
	}




}
