<?php
/**
 * TRCD端末残高不足通知設定
 * @author K.Yamasaki
 */

namespace App\Infrastracture\Repositories\Trcd;

use App\Repositories\Trcd\TrcdTerminalNotificationSettingRepositoryInterface;
use App\Infrastracture\Repositories\BaseRepository;

use App\TrcdTerminalNotificationSetting;
use App\ClientBranch;
use Illuminate\Support\Facades\Log;

use Validator;

class TrcdTerminalNotificationSettingRepository extends BaseRepository implements TrcdTerminalNotificationSettingRepositoryInterface {

	//利用するモデルのクラス指定
	protected static $modelClass = \App\TrcdTerminalNotificationSetting::class;
	
	/*
                @return save_object
        */
	public function create($data) {
		$objTrcdTerminalNotificationSetting = new TrcdTerminalNotificationSetting();

		// Validate
		$validationRules = $objTrcdTerminalNotificationSetting->buildValidationRulesForCreate($data);
		$validator = $this->Validator($data, $validationRules);
		if ( $validator->fails() ) {
			logger()->error($validator->errors()->toArray());
			return false;
		}

		// SQL実行
		try{
			return $objTrcdTerminalNotificationSetting->create($data);
		}catch(\Exception $e){
			Log::Error($e->getMessage());

			return false;
		}
	}

	/*
                @return save_object
	 */
	public function save($data) {
		$objTrcdTerminalNotificationSetting = $this->where('id', $data['id'])->first();

		$objTrcdTerminalNotificationSetting->fill($data);

		$tmpData = $objTrcdTerminalNotificationSetting->toArray();

		$validationRules = $objTrcdTerminalNotificationSetting->buildValidationRulesForUpdate($tmpData);
		$validator = $this->Validator($tmpData, $validationRules);
		
		// Validate
		if ( $validator->fails() ) {
                        return false;
		}

		// SQL実行
		try{
			return $objTrcdTerminalNotificationSetting->save() ? $objTrcdTerminalNotificationSetting : false;
            	}catch(\Exception $e){
                    Log::Error($e->getMessage());

                    return false;
            }


	}
	
	/*
	 * 	（論理）削除
		@return bool
	*/
	public function delete($trcd_terminal_notification_setting_id){
		$objTrcdTerminalNotificationSetting = $this->where('id', $trcd_terminal_notification_setting_id)->firstOrFail();

		try{
			$result = $objTrcdTerminalNotificationSetting->delete();
			logger($result);

			if ( empty($result) ) throw new \Exception("TrcdTerminalNotificationSettingの削除に失敗" . __FILE__);

		}catch(\Exception $e){
		  logger()->error($e->getMessage());
		  return false;
		}

		return true;
	}

	/*
	 * 	リストア（論理削除済みを復元）
                @return bool
        */
        public function restore($trcd_terminal_notification_setting_id){
		// 論理削除済みのもののみ受け入れる
		$objTrcdTerminalNotificationSetting = $this->where('id', $trcd_terminal_notification_setting_id)
			->onlyTrashed()
			->firstOrFail();

                try{
                        $result = $objTrcdTerminalNotificationSetting->restore();
                        logger($result);

                        if ( empty($result) ) throw new \Exception("TrcdTerminalNotificationSettingの論理削除の解除に失敗" . __FILE__);

                }catch(\Exception $e){
                  logger()->error($e->getMessage());
                  return false;
                }

                return true;
        }

}
