<?php
/***
 * クライアント社員サービス
 *
 * @author YuKaneko
 */

namespace App\Services;

// Supports
use DB;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

// Services
use App\Services\ServiceBase;
use App\Services\PasswordService;
use App\Services\ImageService;

// Repositories
use App\Repositories\ClientRepositoryInterface AS ClientRepository;
use App\Repositories\Trcd\ClientNoteTypeRepositoryInterface AS ClientNoteTypeRepository;
use App\Repositories\Trcd\BalanceThresholdRepositoryInterface AS BalanceThresholdRepository;

class ClientService extends ServiceBase
{
	protected $objClientRepository;
	protected $objClientNoteTypeRepo;
	protected $objBalanceThresholdRepository;

	public function __construct(
		ClientRepository $objClientRepository,
		ClientNoteTypeRepository $objClientNoteTypeRepo,
		BalanceThresholdRepository $objBalanceThresholdRepository
	){
		$this->objClientRepository = $objClientRepository;
		$this->objClientNoteTypeRepo = $objClientNoteTypeRepo;
		$this->objBalanceThresholdRepository = $objBalanceThresholdRepository;
	}

	/**
	 * 該当IDのデータを取得する
	 */
	public function getById($client_id){
		return $this->objClientRepository->getById($client_id);
	}

	/*
		新規作成
		@param $data
		@return 成功:結果配列 失敗:false
	*/
	public function create($data) {
		$data = array_only($data, ['name', 'phonetic', 'email', 'tel']);
		$data['login_code'] = $this->generateLoginCode();
		$data['auth_key'] = $this->generateAuthKey();
		$data['md5_hash'] = md5(Carbon::now());

		return $this->objClientRepository->create($data);
	}

	/*
		Web管理画面経由での新規作成
		@param Array $data
		@param EnvService $objEnvService
		@param ClientSettingService $objClientSettingService
		@param QuoteStatusService $objQuoteStatusService
		@param ClientUserService $objClientUserService
		@return bool
	*/
	public function createViaWeb(
		$data,
		$objEnvService,
		$objClientSettingService,
		$objQuoteStatusService,
		$objClientUserService,
		$objTrcdService,
		$objClientEmployeeService
	) {
		$validator = null;

		DB::beginTransaction();

		try{
			// 新規Client追加処理 ------------------------------
			$client = $this->create($data);

			if ( !$client ) {
				$validator = $this->getLastValidator();
				throw new \Exception('クライントの保存に失敗しました。');
			}

			// 新規ClientSetting追加処理 ------------------------------
			$default_env_values = $objEnvService->getDefaultValues();
			$client_setting_save_data = [
				'client_id' => $client['id'],
				'consumption_tax' => $default_env_values['consumption_tax_default'],
				'consumption_tax_rounding_type_id' => $default_env_values['consumption_tax_rounding_type_id_default'],
				'total_amount_rounding_type_id' => $default_env_values['total_amount_rounding_type_id_default'],
			];

			$client_setting = $objClientSettingService->create($client_setting_save_data);

			if ( !$client_setting ) {
				$validator = $objClientSettingService->getLastValidator();
				throw new \Exception('クライント設定の保存に失敗しました。');
			}

			// 見積ステータス既定値追加処理 ------------------------------
			$quote_status_save_data = [
				['client_id' => $client['id'], 'name' => '下書き', 'order_index' => 1],
				['client_id' => $client['id'], 'name' => '作成済み', 'order_index' => 2],
			];
			$quote_status = $objQuoteStatusService->bulkInsert($quote_status_save_data);

			if ( !$quote_status ) {
				$validator = $objQuoteStatusService->getLastValidator();
				throw new \Exception('見積ステータスの保存に失敗しました。');
			}

			// 管理者アカウント追加処理 ------------------------------
			/*
			 * @terada 2019/08/28 業務管理システムの管理者アカウントは別ページで作成することになったのでコメントアウトする
			$client_user_save_data = array_only($data, ['name', 'email', 'password']);
			$client_user_save_data += [
				'client_id' => $client['id'],
				'login_name' => 'admin',
			];
			$client_user = $objClientUserService->create($client_user_save_data);

			if ( !$client_user ) {
				$validator = $objClientUserService->getLastValidator();
				throw new \Exception('管理者アカウントの保存に失敗しました。');
			}
			 */

			// TRCD用 
			// 新規ClientTrcdSetting追加処理 ------------------------------
			$client_trcd_setting = $objTrcdService->CreateClientTrcdSetting($client['id']);

			if ( !$client_trcd_setting ) {
				$validator = $objTrcdService->getLastValidator();
				throw new \Exception('企業TRCD設定の保存に失敗しました。');
            }
            
            // 新規BalanceThreshold作成処理 ------------------------------
			$balance_threshold = $objTrcdService->CreateBalanceThreshold($client['id']);

            if ( !$client_trcd_setting ) {
                    $validator = $objTrcdService->getLastValidator();
                    throw new \Exception('残高閾値の保存に失敗しました。');
            }

			// TRCD管理者用アカウント追加 ------------------------------
			// @terada 2019/08/28 業務管理システムの管理者アカウントは別ページで作成することになったのでコメントアウトする
			//$client_employee_save_data = array_only($data, [/*'name',*/ 'password']);
			/* @terada 2019/08/28 業務管理システムの管理者アカウントは別ページで作成することになったのでコメントアウトする
			$client_employee_save_data += [
				'name' => '管理者',
				'client_id' => $client['id'],
				'login_code' => 'admin',
				'trcd_admin_flag' => true,
				'code' => '0000',
			];
				
			//関連テーブルも同時に生成
			$client_employee = $objClientEmployeeService->createAssociationsAtTheSameTime(
				$client_employee_save_data,
				[],
				[],
				[config('database.trcd.roles.CONST.ADMIN')]
			);

			if ( !$client_employee ) {
				$validator = $objClientEmployeeService->getLastValidator();
				throw new \Exception('TRCDアカウントの保存に失敗しました。');
			}
			 */		 

			// 企業備考テーブルに「休日」を追加 @YuKaneko 2019/10/21--------------------
			$client_note_type_saving_result = $this->objClientNoteTypeRepo->addHolidayTypeRecordOn($client['id']);

			if ( !$client_note_type_saving_result ) {
				throw new \Exception('企業備考種別へ休日を追加する処理に失敗しました。');
			}
		} catch (\Exception $e) {
			DB::rollBack();
			Log::error($e->getMessage());

			if ( !empty($validator) ) Log::error($validator->errors()->toArray());

			return false;
		}


		DB::commit();

		return true;
	}

	/*
		保存
		@param $data
		@return 成功:結果配列 失敗:false
	*/
	public function save($data) {
		//IDがない場合新規作成（不要かもしれない）
		if ( empty($data['id']) ) return $this->create($data);

		//アップロードファイル一時格納情報格納配列
		$tmp_files = [];

		if ( isset($data['logo_binary']) ) $tmp_files['logo'] = ImageService::fetchBase64ImageToTmpDirectory($data['logo_binary']);
		if ( isset($data['seal_binary']) ) $tmp_files['seal'] = ImageService::fetchBase64ImageToTmpDirectory($data['seal_binary']);

		unset($data['logo_binary'], $data['seal_binary']);

		
		try{
			DB::beginTransaction();

			//保存処理
			$save_result = $this->objClientRepository->save($data);

			//画像アップロード時処理
			if ( !empty($tmp_files) ) {
				//公開用ディスク取得
				$public_disk = ImageService::getPublicDisk();
				$client_dir = "clients/{$save_result['id']}";

				foreach($tmp_files as $key => $tmp_file) {
					if ( !$save_result ) {
						//保存失敗時 一時保存しているファイルを削除
						ImageService::remove($tmp_file['path']);
						continue;
					}

					//既存ファイル削除処理
					$old_files = array_where($public_disk->files($client_dir), function($v, $k) use ($key) {
						return starts_with(basename($v), "{$key}");
					});
					$public_disk->delete($old_files);

					//新ファイルパス
					$dest_path = "{$client_dir}/{$key}_{$save_result['md5_hash']}{$tmp_file['extension']}";

					if ( $public_disk->exists($dest_path) ) $public_disk->delete($dest_path);

					ImageService::move($tmp_file['path'], "public/{$dest_path}");

					$save_result[$key] = $dest_path;
				}

				//一度の保存に成功している場合 再度更新
				if ( $save_result ) $save_result = $this->objClientRepository->save($save_result);
			}
		} catch(\Exception $e) {
			DB::rollback();
			throw $e;
		}

		DB::commit();

		return $save_result;
	}

	/*
		ログインコード生成
		@return $new_login_code
	*/
	public function generateLoginCode() {
		//既存のログインコード取得
		$login_codes = $this->objClientRepository->getAllLoginCodes();
		$login_codes = Arr::pluck($login_codes, 'login_code', 'login_code');
		
		$new_login_code = '';
		$is_unique = false;

		//ユニークなログインコードが生成されるまで繰り返し処理
		while( !$is_unique ){
			$new_login_code = PasswordService::generateRandomNumber($this->objClientRepository->getLoginCodeLength());
			$is_unique = empty($login_codes[$new_login_code]);
		}

		return $new_login_code;
	}

	/*
		auth_key生成
		@return $new_auth_key
	*/
	public function generateAuthKey() {
		//既存のauth_key取得（論理削除されているものも含め、システム全体で完全に一意にする）
		$auth_keys = $this->objClientRepository->withTrashed()->pluck('auth_key', 'auth_key');
		
		$new_auth_key = '';
		$is_unique = false;

		//ユニークなauth_keyが生成されるまで繰り返し処理
		while( !$is_unique ){
			$new_auth_key = PasswordService::generateRandomString(32);
			$is_unique = empty($login_codes[$new_auth_key]);
		}

		return $new_auth_key;
	}

	/*
            clientの論理削除
            @return bool
    */
    public function delete($id) {

		// Transaction開始
		DB::beginTransaction();

		try{
			// BalanceThresholdの削除
			$result = $this->objBalanceThresholdRepository->deleteByClientId($id);

			if ( empty($result) ) throw new \Exception("BalanceThresholdの削除に失敗しました。");

			$result = $this->objClientRepository->delete($id);
			
			// 企業の削除
			if ( empty($result) ) throw new \Exception("Clientの削除に失敗しました。");

			DB::commit();
		} catch(\Exception $e) {
			DB::rollback();
			// ログで残す
			logger()->error($e->getMessage());
			return false;
		}

		return true;
	}


}




$balance_threshold_new_create_data = [
	'client_id' => $client['id'],
	'lower_threshold_1yen' => null,
	'lower_threshold_5yen' => null,
	'lower_threshold_10yen' => null,
	'lower_threshold_50yen' => null,
	'lower_threshold_100yen' => null,
	'lower_threshold_500yen' => null,
	'lower_threshold_1k' => null,
	'lower_threshold_5k' => null,
	'lower_threshold_10k' => null,

];
