<?php
/***
 * ClientSettingサービス
 *
 * @author YuKaneko
 */

namespace App\Services;

use App\Services\ServiceBase;
use App\Repositories\ClientSettingRepositoryInterface AS ClientSettingRepository;

class ClientSettingService extends ServiceBase
{
	protected $objClientSettingRepository;

	public function __construct(ClientSettingRepository $objClientSettingRepository){
		$this->objClientSettingRepository = $objClientSettingRepository;
	}

	/**
	 * クライアントIDを条件に最初の1件のデータを取得する
	 */
	public function getFirstByClientId($client_id){
		return $this->objClientSettingRepository->getFirstByClientId($client_id);
	}

	/**
	 * クライアントIDを条件に全てのデータを取得する
	 */
	public function getByClientId($client_id){
		return $this->objClientSettingRepository->getByClientId($client_id);
	}

	/**
	 * 該当IDのデータを取得する
	 */
	public function getById($client_setting_id){
		return $this->objClientSettingRepository->getById($client_setting_id);
	}

	/*
		新規作成
		@param $data
		@return 成功:結果配列 失敗:false
	*/
	public function create($data) {
		return $this->objClientSettingRepository->create($data);
	}

	/*
		保存
		@param $data
		@return 成功:結果配列 失敗:false
	*/
	public function save($data) {
		//IDがない場合新規作成
		if ( empty($data['id']) ) return $this->create($data);

		try{
			//保存処理
			$save_result = $this->objClientSettingRepository->save($data);
		} catch(\Exception $e) {
			throw $e;
		}

		return $save_result;
	}

	/*
		IDを指定して削除
	*/
	public function delete($client_setting_id) {
		return $this->objClientSettingRepository->delete($client_setting_id);
	}

	/*
		消費税率更新
		@param int $client_setting_id
		@param int $consumption_tax
	*/
	public function updateConsumptionTax($client_setting_id, $consumption_tax) {
		return $this->objClientSettingRepository->save([
			'id' => $client_setting_id,
			'consumption_tax' => $consumption_tax,
		]);
	}
	
	/*
		消費税端数設定更新
		@param int $client_setting_id
		@param int $consumption_tax_rounding_type_id
	*/
	public function updateConsumptionTaxRoundingType($client_setting_id, $consumption_tax_rounding_type_id) {
		return $this->objClientSettingRepository->save([
			'id' => $client_setting_id,
			'consumption_tax_rounding_type_id' => $consumption_tax_rounding_type_id,
		]);
	}

	/*
		合計金額端数設定更新
		@param int $client_setting_id
		@param int $total_amount_rounding_type_id
	*/
	public function updateTotalAmountRoundingType($client_setting_id, $total_amount_rounding_type_id) {
		return $this->objClientSettingRepository->save([
			'id' => $client_setting_id,
			'total_amount_rounding_type_id' => $total_amount_rounding_type_id,
		]);
	}

	/*
		GoogleMapAPIキー更新
		@param int $client_setting_id
		@param int $total_amount_rounding_type_id
	*/
	public function updateGoogleMapsApiKey($client_setting_id, $google_maps_api_key) {
		return $this->objClientSettingRepository->save([
			'id' => $client_setting_id,
			'google_maps_api_key' => $google_maps_api_key,
		]);
	}
}
