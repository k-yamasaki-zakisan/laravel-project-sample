<?php
/**
 * BalanceThreshold用リポジトリ
 *
 * @author K.Yamasaki
 */

namespace App\Infrastracture\Repositories\Trcd;

use App\Repositories\Trcd\BalanceThresholdRepositoryInterface;
use App\Infrastracture\Repositories\BaseRepository;

use App\BalanceThreshold;
use Illuminate\Support\Facades\Log;

use Validator;

class BalanceThresholdRepository extends BaseRepository implements BalanceThresholdRepositoryInterface{

	//利用するモデルのクラス指定
	protected static $modelClass = \App\BalanceThreshold::class;

	/*
		@return objBalanceThreshold
	*/
	public function create($client_id) {
		$objBalanceThreshold = new BalanceThreshold();
		$data = ['client_id' => $client_id];
		// Validate
		$validationRules = $objBalanceThreshold->buildValidationRulesForCreate($data);
		$validator = $this->Validator($data, $validationRules);

		if ( $validator->fails() ) {
			logger()->error($validator->errors()->toArray());
			return false;
		}
		// SQL実行
		try{
			return $objBalanceThreshold->create($data);
		}catch(\Exception $e){
			Log::Error($e->getMessage());

			return false;
		}
	}

	/*
		Arry $data
                @return objBalanceThreshold
	 */
	public function save($data) {
		$objBalanceThreshold = $this->where('client_id', $data['client_id'])->first();

		$objBalanceThreshold->fill($data);
		$tmpData = $objBalanceThreshold->toArray();

		$validationRules = $objBalanceThreshold->buildValidationRulesForUpdate($tmpData);
                $validator = $this->Validator($tmpData, $validationRules);
		// Validate
		if ( $validator->fails() ) {
                        logger()->error($validator->errors()->toArray());
                        return false;
		}
		// SQL実行
            try{
                    return $objBalanceThreshold->save() ? $objBalanceThreshold : false;
            }catch(\Exception $e){
                    Log::Error($e->getMessage());

                    return false;
            }

	}
	
	/*
		IDを指定して削除
		注意：$client_idを指定してモデルが取得できない場合は例外
		@param int $client_id
		@return bool
	*/
	public function deleteByClientId($client_id) {
		$objBalanceThreshold = $this->where('client_id', $client_id)->firstOrFail();

		try{
			$result = $objBalanceThreshold->delete();
			logger($result);

			if ( empty($result) ) throw new \Exception("BalanceThresholdの削除に失敗" . __FILE__);

		}catch(\Exception $e){
		  logger()->error($e->getMessage());
		  return false;
		}

		return true;
	}

}
