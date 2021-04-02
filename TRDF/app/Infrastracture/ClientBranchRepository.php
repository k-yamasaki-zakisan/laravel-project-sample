<?php
/**
 * クライアント支社、支部用のリポジトリ
 * @author Waka
 */

namespace App\Infrastracture\Repositories;

use App\Repositories\ClientBranchRepositoryInterface;
use App\Infrastracture\Repositories\BaseRepository;

use App\ClientBranch;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;


use Validator;

class ClientBranchRepository extends BaseRepository implements ClientBranchRepositoryInterface {

	//利用するモデルのクラス指定
	protected static $modelClass = \App\ClientBranch::class;

	/**
	 * クライアントIDを条件に全てのデータを取得する
	 */
  public function getByClientId($client_id, $options=array()){
    return ClientBranch::where('client_id', $client_id)->get();
  }

  /**
   * IDから該当のデータを取得する
   */
  public function getById($client_blanch_id, $options=array()){
    return ClientBranch::where('id', $client_blanch_id)->first();
  }

  /**
   * クライアントIDからTrcd端末データのコレクションを取得する
   */
  public function getTrcdTerminalsByClientId($client_id, $options=array()) {
          $client_branch_ids = ClientBranch::where('client_id', $client_id)->pluck('id')->toArray();

          $trcdTerminals = collect();

          foreach($client_branch_ids as $client_branch_id){
                  $trcdTerminals = $trcdTerminals->concat($this->getTrcdTerminalsById($client_branch_id));
          }

          return $trcdTerminals;
  }

  /**
   * IDからTrcd端末データを取得する
   */
  public function getTrcdTerminalsById($client_blanch_id, $options=array()){
		return ClientBranch::find($client_blanch_id)->trcd_terminals;
  }

	public function create($data) {
		$objClientBranch = new ClientBranch();
		$data = Arr::except($data, ['id']);

		$validator = $this->Validator($data, $objClientBranch->validate);

		if ( $validator->fails() ) return false;

		foreach($data as $key => $value){
			$objClientBranch->$key = $value;
		}

		// SQL実行
		try{
			return $objClientBranch->save() ? $objClientBranch : false;
		}catch(\Exception $e){
			Log::Error($e->getMessage());
			return false;
		}
	}

	public function save($data) {
		if ( !isset($data['id']) ) return $this->create($data);

		$objClientBranch = $this->find($data['id']);

		foreach($data as $key => $value){
			$objClientBranch->$key = $value;
		}

		$tmpData = $objClientBranch->toArray();
		$validator = $this->Validator($tmpData, $objClientBranch->validate);

		if ( $validator->fails() ) return false;

		// SQL実行
		try{
			return $objClientBranch->save() ? $objClientBranch : false;
		}catch(\Exception $e){
			Log::Error($e->getMessage());
			return false;
		}
	}

	/*
		IDを指定して削除
		@return bool
	*/
	public function delete($client_branch_id){
    try{
			$this->where('id', $client_branch_id)->delete();
    }catch(\Exception $e){
      Log::Error($e->getMessage());
      return false;
    }

    return true;
	}
}
