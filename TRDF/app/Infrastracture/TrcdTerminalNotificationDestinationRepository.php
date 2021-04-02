<?php
/**
 * BalanceThreshold用リポジトリ
 *
 * @author K.Yamasaki
 */

namespace App\Infrastracture\Repositories\Trcd;

use App\Repositories\Trcd\TrcdTerminalNotificationDestinationRepositoryInterface;
use App\Infrastracture\Repositories\BaseRepository;

use App\TrcdTerminalNotificationDestination;
use Illuminate\Support\Facades\Log;

use Validator;

class TrcdTerminalNotificationDestinationRepository extends BaseRepository implements TrcdTerminalNotificationDestinationRepositoryInterface{

	//利用するモデルのクラス指定
	protected static $modelClass = \App\TrcdTerminalNotificationDestination::class;

	/*
		@params Array $data
		@return objBalanceThreshold
	*/
	public function create($data) {
		$objTrcdTerminalNotificationDestination = new TrcdTerminalNotificationDestination();
		
		// Validate
		$validationRules = $objTrcdTerminalNotificationDestination->buildValidationRulesForCreate($data);
		$validator = $this->Validator($data, $validationRules);
		
		if ( $validator->fails() ) {
			logger()->error($validator->errors()->toArray());
			return false;
		}
		// SQL実行
		try{
			return $objTrcdTerminalNotificationDestination->create($data);
		}catch(\Exception $e){
			Log::Error($e->getMessage());

			return false;
		}
	}

	/*
		@return bool
	*/
	public function delete($id) {
		$objTrcdTerminalNotificationDestination = $this->where('id', $id)->firstOrFail();

		try{
			$result = $objTrcdTerminalNotificationDestination->delete();
			logger($result);

			if ( empty($result) ) throw new \Exception("TrcdTerminalNotificationDestinationの削除に失敗" . __FILE__);

		}catch(\Exception $e){
			logger()->error($e->getMessage());
			return false;
		}

		return true;
	}

}
