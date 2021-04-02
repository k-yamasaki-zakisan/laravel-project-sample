<?php
/**
 * ClientSetting用リポジトリ
 *
 * @author YuKaneko
 */

namespace App\Infrastracture\Repositories;

use App\Repositories\ClientSettingRepositoryInterface;
use App\Infrastracture\Repositories\BaseRepository;

use App\ClientSetting;
use Illuminate\Support\Facades\Log;

use Validator;

class ClientSettingRepository extends BaseRepository implements ClientSettingRepositoryInterface{

	//利用するモデルのクラス指定
	protected static $modelClass = \App\ClientSetting::class;

	/**
	 * クライアントIDを条件に最初の1件のデータを取得する
	 */
	public function getFirstByClientId($client_id){
		return ClientSetting::where('client_id', $client_id)->first();
	}

	/**
	 * クライアントIDを条件に全てのデータを取得する
	 */
	public function getByClientId($client_id){
		return ClientSetting::where('client_id', $client_id)->get();
	}

	/**
	 * IDから該当のデータを取得する
	 */
	public function getById($client_setting_id){
		return ClientSetting::where('id', $client_setting_id)->first();
	}

	/*
		新規作成
		@param $data
		@return 成功:結果配列 失敗:false
	*/
	public function create($data) {
		$objClientSetting = new ClientSetting();
		$data = array_only($data, ['client_id', 'consumption_tax', 'consumption_tax_rounding_type_id', 'total_amount_rounding_type_id']);

		$validator = $this->Validator($data, $objClientSetting->validate);

		if ( $validator->fails() ) return false;

		foreach($data as $key => $value){
			$objClientSetting->$key = $value;
		}

		// SQL実行
		try{
			return $objClientSetting->save() ? $objClientSetting->toArray() : false;
		}catch(\Exception $e){
			Log::Error($e->getMessage());
			return false;
		}
	}

	/*
		保存
		@param $data
		@return 成功:結果配列 失敗:false
	*/
	public function save($data) {
		$objClientSetting = ClientSetting::find($data['id']);

		foreach($data as $key => $value){
			$objClientSetting->$key = $value;
		}

		$validator = $this->Validator($objClientSetting->toArray(), $objClientSetting->validate);

		if ( $validator->fails() ) return false;

		// SQL実行
		try{
			return $objClientSetting->save() ? $objClientSetting->toArray() : false;
		}catch(\Exception $e){
			Log::Error($e->getMessage());
			return false;
		}
	}

	/*
		IDを指定して削除
		@return bool
	*/
	public function delete($client_setting_id){
    try{
			ClientSetting::destroy($client_setting_id);
    }catch(\Exception $e){
      Log::Error($e->getMessage());
      return false;
    }

    return true;
	}
}
