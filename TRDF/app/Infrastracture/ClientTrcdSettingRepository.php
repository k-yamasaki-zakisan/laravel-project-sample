<?php
/**
 * 社員TRCD設定用リポジトリ
 *
 * @author T.Ando
 */

namespace App\Infrastracture\Repositories\Trcd;

// Repositories
use App\Repositories\Trcd\ClientTrcdSettingRepositoryInterface;
use App\Infrastracture\Repositories\Trcd\TrcdBaseRepository;

// Models
use App\ClientTrcdSetting;

// Utilities
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Validator;

// Exceptions
use \OutOfBoundsException;

class ClientTrcdSettingRepository extends TrcdBaseRepository implements ClientTrcdSettingRepositoryInterface{

	//利用するモデルのクラス指定
	protected static $modelClass = \App\ClientTrcdSetting::class;

	public function getById($id){
		return ClientTrcdSetting::where('id', $id)->first();
	}

	/*
		企業IDを指定して取得
		@param $client_id
		@param $useCache
		@return $objClientTrcdSetting
	*/
	public function getByClientId($client_id, $useCache=true){
		static $cache = null;

		// cache利用時はcacheされているものを返す
		if($useCache==true && !is_null($cache[$client_id])){
			return $cache;
		}

		$result = ClientTrcdSetting::where('client_id', $client_id)->first();

		// 企業TRCD設定が作成されていない場合は新規作成する
		if($result==null){
			$this->create($client_id);
			$result = ClientTrcdSetting::where('client_id', $client_id)->first();
		}

		// cacheに登録
		$cache = $result;

		return $result;
	}

	public function getAll($options=array()){
		//$objClientTrcdSettings = ClientTrcdSetting::select()->join('clients', 'clients.id', '=', 'client_trcd_settings.client_id')->whereNull('clients.deleted_at')->get();
		$query = ClientTrcdSetting::select()->join('clients', 'clients.id', '=', 'client_trcd_settings.client_id')->whereNull('clients.deleted_at');
		if( isset($options['payroll_start_day']) ){
			$query->where('payroll_start_day', $options['payroll_start_day']);
		}
		$objClientTrcdSettings = $query->get();
		return $objClientTrcdSettings;
	}

	/**
	 * ClientTrcdSettingの登録
	 */
	public function create($client_id, $rounding_franction=null, $fixed_work_start_time=null, $withdraw_amount_limit_a_day=null){
		//createInitialDataを呼び出す
		$data = [
			'rounding_franction' => $rounding_franction,
			'fixed_work_start_time' => $fixed_work_start_time,
			'withdraw_amount_limit_a_day' => $withdraw_amount_limit_a_day,
		];

		return $this->createInitialData($client_id, $data);
/*
		if( ClientTrcdSetting::where('client_id', $client_id)->count() >= 1 ){
			return true;
		}

		$objClientTrcdSetting = new ClientTrcdSetting();
		$objClientTrcdSetting->client_id = $client_id;
		if(is_numeric($rounding_franction)){
			$objClientTrcdSetting->rounding_franction = $rounding_franction;
		}
		if(is_numeric($fixed_work_start_time)){
			$objClientTrcdSetting->fixed_work_start_time = $fixed_work_start_time;
		}
		if(is_numeric($withdraw_amount_limit_a_day)){
			$objClientTrcdSetting->withdraw_amount_limit_a_day = $withdraw_amount_limit_a_day;
		}

		// Validate
		$validator = $this->Validator($objClientTrcdSetting->toArray(), $objClientTrcdSetting->validate);
		if($validator->fails()){
			return false;
		}

		// SQL実行
		try{
			$objClientTrcdSetting->save();
		}catch(\Exception $e){
			Log::Error($e->getMessage());

			return false;
		}

		return true;
*/
	}

	/**
	 * 締め処理日時を現在時刻に更新
	 */
	public function updatePayrollStartDayResetAt($client_id){
		if(is_numeric($client_id)){
			$client_ids = array($client_id);
		}


		if( ClientTrcdSetting::whereIn('client_id', $client_ids)->count() == 0 ){
			return true;
		}

		// SQL実行
		try{
			ClientTrcdSetting::whereIn('client_id', $client_ids)
				->update([
					'payroll_start_day_reset_at'=>'NOW()'
				]);
		}catch(\Exception $e){
			Log::Error($e->getMessage());

			return false;
		}

		return true;
	}

	/*
		$objClientTrcdSettingに$valuesの値をチェックし設定
		※ 本来Serviceで行うもののような気がする
		@author YuKaneko 2019/06/24
		@param $objClientTrcdSetting
		@param $values
		@return $objClientTrcdSetting
	*/
	public function setValuesToClientTrcdSettingObject($objClientTrcdSetting, Array $values) {
		if ( empty($values) ) return $objClientTrcdSetting;

		// 丸め分数
		if ( isset($values['rounding_franction']) && is_numeric($values['rounding_franction']) ){
			$objClientTrcdSetting->rounding_franction = $values['rounding_franction'];
		} else if ( array_key_exists('rounding_franction', $values) && is_null($values['rounding_franction']) ) {
			$objClientTrcdSetting->rounding_franction = null;
		}

		// 固定勤務開始時刻
		if ( isset($values['fixed_work_start_time']) && is_numeric($values['fixed_work_start_time']) ){
			$objClientTrcdSetting->fixed_work_start_time = $values['fixed_work_start_time'];
		}

		// １社員あたりの１日の払い出し金額の上限
		if ( isset($values['withdraw_amount_limit_a_day']) && is_numeric($values['withdraw_amount_limit_a_day']) ){
			$objClientTrcdSetting->withdraw_amount_limit_a_day = $values['withdraw_amount_limit_a_day'];
		}

		// 締め日翌日
		if ( isset($values['payroll_start_day']) && is_numeric($values['payroll_start_day']) ){
			$objClientTrcdSetting->payroll_start_day = $values['payroll_start_day'];
		}

		// TRCD認証キー接頭辞
		if ( isset($values['trcd_employee_code_prefix']) && preg_match('/^[0-9]+$/', $values['trcd_employee_code_prefix']) ) {
			$objClientTrcdSetting->trcd_employee_code_prefix = $values['trcd_employee_code_prefix'];
		}

		// 深夜時間開始時刻
		if ( array_key_exists('late_night_work_start_time',$values) && is_null($values['late_night_work_start_time'])) {
			$objClientTrcdSetting->late_night_work_start_time = null;  
		} elseif (isset($values['late_night_work_start_time']) && Carbon::hasFormat($values['late_night_work_start_time'], 'H:i')) {
			$objClientTrcdSetting->late_night_work_start_time = $values['late_night_work_start_time'] . ':00';
		}

		// 深夜時間終了時刻
		if ( array_key_exists('late_night_work_end_time',$values) && is_null($values['late_night_work_end_time'])) {
			$objClientTrcdSetting->late_night_work_end_time = null;  
		} elseif (isset($values['late_night_work_end_time']) && Carbon::hasFormat($values['late_night_work_end_time'], 'H:i')) {
			$objClientTrcdSetting->late_night_work_end_time = $values['late_night_work_end_time'] . ':00';
		}

		// 勤怠日ベース
		if ( isset($values['payroll_use_timing_id']) && is_numeric($values['payroll_use_timing_id']) ){
			$CLIENT_TRCD_SETTINGS_PAYROLL_USE_TIMING_ID = config('database.trcd.client_trcd_settings.CONST_PAYROLL_USE_TIMING.VALUE');

			if(isset($CLIENT_TRCD_SETTINGS_PAYROLL_USE_TIMING_ID[$values['payroll_use_timing_id']])){
				$objClientTrcdSetting->payroll_use_timing_id = $values['payroll_use_timing_id'];
			} 
		} 

		// 残業時間算出タイプID
		if( isset($values['overtime_calc_type_id']) && is_numeric($values['overtime_calc_type_id']) ){
			$OVERTIME_CALC_TYPE_ID = config('database.trcd.overtime_calc_types.LIST');

			if(isset($OVERTIME_CALC_TYPE_ID[$values['overtime_calc_type_id']])){
				$objClientTrcdSetting->overtime_calc_type_id = $values['overtime_calc_type_id'];
			} 
		}

		// 稼働時間上限
		if( array_key_exists('work_time_limit',$values)){
			if($values['work_time_limit'] === null
				|| preg_match('/^(0?[0-9]{1}|1{1}[0-9]{1}|2{1}[0-3]{1}):(0[0-9]{1}|[1-5]{1}[0-9]{1})(:(0[0-9]{1}|[1-5]{1}[0-9]{1}))?$/',$values['work_time_limit'])
			){
				$objClientTrcdSetting->work_time_limit = $values['work_time_limit'];
			}
		}

		// 基準稼働時間
		if( array_key_exists('base_work_time',$values)){
			if($values['base_work_time'] === null
				|| preg_match('/^(0?[0-9]{1}|1{1}[0-9]{1}|2{1}[0-3]{1}):(0[0-9]{1}|[1-5]{1}[0-9]{1})(:(0[0-9]{1}|[1-5]{1}[0-9]{1}))?$/',$values['base_work_time'])
			){
				$objClientTrcdSetting->base_work_time = $values['base_work_time'];
			}
		}

		// 支給月種別ID
		if( isset($values['supply_month_type_id']) && is_numeric($values['supply_month_type_id']) ){
			$SUPPLY_MONTH_TYPE_IDS = config('database.trcd.client_trcd_settings.CONST_SUPPLY_MONTH_TYPE.VALUE');

			if( isset($SUPPLY_MONTH_TYPE_IDS[$values['supply_month_type_id']]) ){
				$objClientTrcdSetting->supply_month_type_id = $values['supply_month_type_id'];
			} 
		}

		return $objClientTrcdSetting;
	}

	/*
		初期データ作成
		追加カラムに対応するため既に使用されているcreateメソッドとは別に作成した
		@author YuKaneko
	*/
	public function createInitialData($client_id, Array $initial_data=[]) {
		//既に作成されている場合は即リターン
		if( ClientTrcdSetting::where('client_id', $client_id)->exists() ) return true;

		$objClientTrcdSetting = new ClientTrcdSetting();
		$objClientTrcdSetting->client_id = $client_id;
		$objClientTrcdSetting->trcd_employee_code_prefix = sprintf('%03d', $client_id);
		$objClientTrcdSetting = $this->setValuesToClientTrcdSettingObject($objClientTrcdSetting, $initial_data);

		//TRCD認証キーが設定されていなければ設定
		/*
		if ( !isset($objClientTrcdSetting->trcd_employee_code_prefix) ) {
			$objClientTrcdSetting->trcd_employee_code_prefix = $objClientTrcdSetting->generateTrcdEmployeeCodePrefix();
		}
		*/

		// Validate
		$validator = $this->Validator($objClientTrcdSetting->toArray(), $objClientTrcdSetting->validate);

		if($validator->fails()){
			return false;
		}

		// SQL実行
		try{
			$objClientTrcdSetting->save();
		}catch(\Exception $e){
			Log::Error($e->getMessage());

			return false;
		}

		return true;
	}

	/*
		更新
	*/
	public function save($client_id, Array $save_data=[]) {
		$objClientTrcdSetting = $this->getByClientId($client_id);

		if ( is_null($objClientTrcdSetting) ) return $this->createInitialData($client_id, $save_data);

		$objClientTrcdSetting = $this->setValuesToClientTrcdSettingObject($objClientTrcdSetting, $save_data);

		$tmpData = $objClientTrcdSetting->toArray();

		$validationRules = $objClientTrcdSetting->buildValidationRulesForUpdate($tmpData);

		// ここでオリジナルのバリデーションのエラーメッセージを作成する
		$error_messages = [
			'late_night_work_start_time.required' => '深夜時間開始時刻を入力してください。',
			'late_night_work_end_time.required' => '深夜時間終了時刻を入力してください。',
			'late_night_work_start_time.not_in' => '深夜時間終了時刻とは違う時刻を入力してください。',
                        'late_night_work_end_time.not_in' => '深夜時間開始時刻とは違う時刻を入力してください。',
		];

		// Validate
		$validator = $this->Validator($objClientTrcdSetting->toArray(), $validationRules, $error_messages);
		
		if($validator->fails()){
			return false;
		}

		// SQL実行
		try{
			$objClientTrcdSetting->save();
		}catch(\Exception $e){
			Log::Error($e->getMessage());

			return false;
		}

		return true;
	}

	/*
		勤務開始・終了時刻範囲算出
	*/
	public function CalcRangeOfWorkDatetime($objClientTrcdSetting, $date, $start_time, $end_time) {
		$PAYROLL_USE_TIMING = config('database.trcd.client_trcd_settings.CONST_PAYROLL_USE_TIMING');
		$range = [
			'from' => null,
			'to' => null,
		];

		switch($objClientTrcdSetting->payroll_use_timing_id) {
			case($PAYROLL_USE_TIMING['LIST']['USE_STARTED_ATTENDANCE_DATETIME']):
				// 勤務開始時間を使って前払い金額を算出する場合
				$range['from'] = isset($start_time) ? Carbon::parse("{$date} {$start_time}") : null;

				if ( isset($end_time) ) {
					// 終了日時設定
					$range['to'] = Carbon::parse("{$date} {$end_time}");

					if ( $range['to'] < $range['from'] ) {
						// 終了日時が前にくる場合は終了日時を一日後ろ倒す
						$range['to']->addDay();
					}
				}
				break;
			case($PAYROLL_USE_TIMING['LIST']['USE_FINISHED_ATTENDANCE_DATETIME']):
				// 勤務終了時間を使って前払い金額を算出する場合
				$range['to'] = isset($end_time) ? Carbon::parse("{$date} {$end_time}") : null;

				if ( isset($start_time) ) {
					// 開始日時設定
					$range['from'] = Carbon::parse("{$date} {$start_time}");

					// @YuKaneko 2019/10/29 $range['to']がnullの時に1日前倒しされてしまっていたため修正
					//if ( $range['to'] < $range['from'] ) {
					if ( isset($range['to']) && $range['to'] < $range['from'] ) {
						// 開始日時があとにくる場合は開始日時を一日前倒す
						$range['from']->subDay();
					}
				}
				break;
			default:
				$err_msg = "[client_trcd_settings.id:{$objClientTrcdSetting->id}] payroll_use_timing_idに予期せぬ値が設定されています。";
				logger()->error($err_msg);
				throw new \UnexpectedValueException($err_msg);
				break;
		}

		return $range;
	}

	/*
		指定企業の「当月」を返す
		@throws OutOfBoundsException 指定企業が存在しない場合
		@param int $client_id
		@return Carbon
	*/
	public function getCurrentMonthOf($client_id) {
		$objClientTrcdSetting = $this->where('client_id', $client_id)->first();

		if ( is_null($objClientTrcdSetting) ) throw OutOfBoundsException("\client_id = {$client_id} does not exist.");

		$objCarbonNow = Carbon::now();
		// 現在日が締め日翌日以降であれば、その月の開始日時
		// 以前であれば前月の開始日時
		$objCarbonCurrentMonth = ( $objCarbonNow->day >= $objClientTrcdSetting->payroll_start_day )
			? $objCarbonNow->copy()->startOfMonth()
			: $objCarbonNow->copy()->subMonthNoOverflow()->startOfMonth();

		return $objCarbonCurrentMonth;
	}
}
