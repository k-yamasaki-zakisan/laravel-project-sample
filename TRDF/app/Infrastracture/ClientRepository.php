<?php
/**
 * クライアント用リポジトリ
 *
 * @author T.Ando
 */

namespace App\Infrastracture\Repositories;

use App\Repositories\ClientRepositoryInterface;
use App\Infrastracture\Repositories\BaseRepository;

use App\Client;
use Illuminate\Support\Facades\Log;

use Validator;

class ClientRepository extends BaseRepository implements ClientRepositoryInterface{

	//利用するモデルのクラス指定
	protected static $modelClass = \App\Client::class;

	public function getById($client_employee_id){
		return Client::where('id', $client_employee_id)->first();
	}

	/*
		ログインコード桁数取得
	*/
	public function getLoginCodeLength() {
		return Client::LOGIN_CODE_LENGTH;
	}

	/*
		ログインコード取得
		@param $withTrashed 論理削除されたものも含めるか
	*/
	public function getAllLoginCodes($withTrashed = true) {
		$query = Client::select('login_code');

		if ( $withTrashed ) $query->withTrashed(); 		

		return $query->get();
	}

//	public function getByClientEmployeeId($client_employee_id){
//		static $cacheResult = array();
//		if(!isset($cacheResult[$client_employee_id])){
//			$cacheResult[$client_employee_id] = Client::where('client_employee_id', $client_employee_id)->first();
//		}
//		return $cacheResult[$client_employee_id];
//	}

	/*
		新規追加
		@param $data
		@return 成功:結果配列 失敗:false
	*/
	public function create($data) {
		$objClient = new Client();
		$data = array_only($data, ['login_code', 'name', 'phonetic', 'email', 'tel','auth_key', 'md5_hash']);

		$validationRules = $objClient->buildValidationRulesForInsert($data);
		$validator = $this->Validator($data, $validationRules);

		if ( $validator->fails() ) return false;

		foreach($data as $key => $value){
			$objClient->$key = $data[$key];
		}

		// SQL実行
		try{
			return $objClient->save() ? $objClient->toArray() : false;
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
		$objClient = Client::find($data['id']);

		foreach($data as $key => $value){
			$objClient->$key = $value;
		}

		$tmpData = $objClient->toArray();

		$validationRules = $objClient->buildValidationRulesForUpdate($tmpData);
		$validator = $this->Validator($tmpData, $validationRules);

		if ( $validator->fails() ) return false;

		// SQL実行
		try{
			return $objClient->save() ? $objClient->toArray() : false;
		}catch(\Exception $e){
			Log::Error($e->getMessage());
			return false;
		}
	}

	/*
	 * 削除
	 * $idには存在するclients.idを1件のみ渡すこと
	 * @param int $id
	 * @return bool
	 * */
	public function delete($id) {
		logger(__METHOD__);
		// 存在しない場合はModelNotFoundExceptionを投げる
		$objClient = $this->findOrFail($id);

		// SQL実行
		try{
			$result = $objClient->delete();

			if ( empty($result) ) throw new \Exception("Clientの削除に失敗" . __FILE__);

                }catch(\Exception $e){
                        Log::Error($e->getMessage());
                        return false;
		}

		return true;
	}
}
