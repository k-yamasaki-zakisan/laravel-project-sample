<?php

/**
 * 年次有給休暇用リポジトリ
 *
 * @author Baba
 */

namespace App\Infrastracture\Repositories\Trcd;

use App\Repositories\Trcd\AnnualPaidHolidayRepositoryInterface;
use App\Infrastracture\Repositories\BaseRepository;

use App\AnnualPaidHoliday;
use DB;

class AnnualPaidHolidayRepository extends BaseRepository implements AnnualPaidHolidayRepositoryInterface
{

    // 利用するモデルのクラス指定
    protected static $modelClass = \App\AnnualPaidHoliday::class;

    /*
		新規作成
		@param Array $data
		@return false|AnnualPaidHoliday
	*/
    public function create(array $data)
    {
        unset($data['id']);
        $AnnualPaidHoliday = new AnnualPaidHoliday();
        $AnnualPaidHoliday->fill($data);

        // Validate
        $data = $AnnualPaidHoliday->toArray();
        $validationRules = $AnnualPaidHoliday->buildValidationRulesForCreate($data);
        $validator = $this->Validator($data, $validationRules);

        if ($validator->fails()) {
            logger()->error($validator->errors()->toArray());
            return false;
        }

        // SQL実行
        try {
            return $AnnualPaidHoliday::create($data);
        } catch (\Exception $e) {
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return false;
        }
    }

    /*
		更新
		@param Integer annual_paid_holiday_id
		@param Array data

		@return false|AnnualPaidHoliday
	*/
    public function update($annual_paid_holiday_id, array $data)
    {
        try {
            $annual_paid_holiday = AnnualPaidHoliday::find($annual_paid_holiday_id);

            if (empty($annual_paid_holiday)) {
                logger()->error("id={$annual_paid_holiday_id} does not exist. in " . __METHOD__ . ':' . __LINE__);
                return false;
            }

            $annual_paid_holiday->fill($data);

            // Validate
            $data = $annual_paid_holiday->toArray();
            $validationRules = $annual_paid_holiday->buildValidationRulesForUpdate($data);
            $validator = $this->Validator($data, $validationRules);

            if ($validator->fails()) {
                logger()->error($validator->errors()->toArray());
                return false;
            }

            /*
			// 更新する値を入れる処理 ※fill通過したから、モデル内にあるカラムの予定
			foreach ($data as $column_name => $column_value) {
				$annual_paid_holiday->$column_name = $column_value;
			}
            */

            $result = $annual_paid_holiday->save();

            if ($result) return $annual_paid_holiday;

            throw new \Exception("年次有給休暇更新処理失敗。AnnualPaidHoliday.id={$annual_paid_holiday_id}");
        } catch (\Exception $e) {
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return false;
        }
    }
}
