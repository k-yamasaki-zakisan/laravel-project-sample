<?php
/**
 * 経費所属グループ設定用リポジトリ
 *
 * @author YuKaneko
 */

namespace App\Infrastracture\Repositories\Trcd;

use App\Repositories\Trcd\ExpenseGroupSettingRepositoryInterface;
use App\Infrastracture\Repositories\BaseRepository;

use App\ExpenseGroupSetting;

use Validator;

class ExpenseGroupSettingRepository extends BaseRepository implements ExpenseGroupSettingRepositoryInterface{

	//利用するモデルのクラス指定
	protected static $modelClass = \App\ExpenseGroupSetting::class;

	/*
		クライアントIDを指定
	*/
	public function getByClientId($client_id) {
		return ExpenseGroupSetting::where('client_id', $client_id)->get();		
	}

	/*
		指定されたクライアントIDの経費所属グループをリスト化して取得
		@param string $key キーにするカラム名
		@param string $value 値にするカラム名
	*/
	public function getListByClientId($client_id, $key = 'id', $value = 'name') {
		return $this->where('client_id', $client_id)->pluck($value, $key);
	}

	/*
		新規作成
		@param $data
		@return 成功:結果配列 失敗:false
	*/
	public function create(Array $data) {
		$objExpenseGroupSetting = new ExpenseGroupSetting();
		$data = array_only($data, $objExpenseGroupSetting->getFillable());

		$validationRules = $objExpenseGroupSetting->buildValidationRulesForCreate($data);
		$validator = $this->Validator($data, $validationRules);

		if ( $validator->fails() ) {
			logger()->error($validator->errors()->toArray());
			return false;
		}

		// SQL実行
		try{
			return $objExpenseGroupSetting->create($data);
		}catch(\Exception $e){
			logger()->error($e->getMessage());
			return false;
		}
	}

	/*
		保存
		@param $data
		@return 成功:結果配列 失敗:false
	*/
	public function save($data) {
		$objExpenseGroupSetting = ExpenseGroupSetting::find($data['id']);
		if ( empty($objExpenseGroupSetting['id']) ) return $this->create($data);

		$objExpenseGroupSetting->fill($data);
		$tmpData = $objExpenseGroupSetting->toArray();

		$validationRules = $objExpenseGroupSetting->buildValidationRulesForUpdate($tmpData);
		$validator = $this->Validator($tmpData, $validationRules);

		if ( $validator->fails() ) {
			logger()->error($validator->errors()->toArray());
			return false;
		}

		// SQL実行
		try{
			return $objExpenseGroupSetting->save() ? $objExpenseGroupSetting : false;
		}catch(\Exception $e){
			logger()->error($e->getMessage());
			return false;
		}
	}

	/*
		IDを指定して削除
		@return bool
	*/
	public function delete($id) {
		try{
			ExpenseGroupSetting::destroy($id);
		}catch(\Exception $e){
		  logger()->error($e->getMessage());
		  return false;
		}

		return true;
	}

	/*
		クライアントIDを指定して新規インスタンス生成
	*/
	public function generateInstanceByClientId($client_id) {
		$obj = new ExpenseGroupSetting;
		$obj->client_id = $client_id;

		return $obj;
	}
}
