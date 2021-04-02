<?php

/**
 * 有給概要用リポジトリ
 *
 * @author YuKaneko
 */

namespace App\Infrastracture\Repositories\Trcd;

use App\Repositories\Trcd\AnnualPaidHolidaySummaryRepositoryInterface;
use App\Infrastracture\Repositories\BaseRepository;

use App\AnnualPaidHolidaySummary;
use DB;

class AnnualPaidHolidaySummaryRepository extends BaseRepository implements AnnualPaidHolidaySummaryRepositoryInterface
{

    //利用するモデルのクラス指定
    protected static $modelClass = \App\AnnualPaidHolidaySummary::class;

    /*
		@param Array $data
		@return false|AnnualPaidHolidaySummary
	*/
    public function create(array $data)
    {
        $AnnualPaidHolidaySummary = new AnnualPaidHolidaySummary();
        $AnnualPaidHolidaySummary->fill($data);

        // Validate
        $validationRules = $AnnualPaidHolidaySummary->buildValidationRulesForCreate($data);
        $validator = $this->Validator($data, $validationRules);

        if ($validator->fails()) {
            logger()->error($validator->errors()->toArray());
            return false;
        }

        // SQL実行
        try {
            return $AnnualPaidHolidaySummary->create($data);
        } catch (\Exception $e) {
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");

            return false;
        }
    }

    /*
		@param Array $data
		@return false|AnnualPaidHolidaySummary
	*/
    public function save(array $data)
    {
        // IDが指定されていない場合は新規作成
        if (empty($data['id'])) return $this->create($data);

        $AnnualPaidHolidaySummary = $this->find($data['id']);

        if (empty($AnnualPaidHolidaySummary)) {
            logger()->error("Does not exist AnnualPaidHolidaySummary.id={$data['id']}.");
            return false;
        }

        $AnnualPaidHolidaySummary->fill($data);
        $data = $AnnualPaidHolidaySummary->toArray();

        // Validate
        $validationRules = $AnnualPaidHolidaySummary->buildValidationRulesForUpdate($data);
        $validator = $this->Validator($data, $validationRules);

        if ($validator->fails()) {
            logger()->error($validator->errors()->toArray());
            return false;
        }

        // SQL実行
        try {
            return $AnnualPaidHolidaySummary->save($data) ? $AnnualPaidHolidaySummary : false;
        } catch (\Exception $e) {
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");

            return false;
        }
    }

    /*
		@Baba 2020.10.19追加
		IDを指定して削除
		@param Integer $client_employee_id
		@return bool
	*/
    public function deleteByClientEmployeeId($client_employee_id)
    {
        DB::beginTransaction();

        try {
            $delete_targets = AnnualPaidHolidaySummary::where('client_employee_id', $client_employee_id)->pluck('id');
            $result = AnnualPaidHolidaySummary::whereIn('id', $delete_targets)->delete();

            // 削除対象数と削除結果数が異なる場合は例外を発生させて失敗とする
            if ($delete_targets->count() !== $result) {
                throw new \Exception("年次有給概要テーブルの削除に失敗しました。client_employee_id={$client_employee_id}");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return false;
        }

        return true;
    }

    /*
		@Baba 2020.10.21追加
		社員IDを指定して復元
		@param Integer $client_employee_id
		@return ClientEmployee|false
	*/
    public function restoreByClientEmployeeId($client_employee_id)
    {
        DB::beginTransaction();

        try {
            $AnnualPaidHolidaySummary = AnnualPaidHolidaySummary::onlyTrashed()->where('client_employee_id', $client_employee_id)->first();

            // 復元するべきものがマッチしなかった場合は true として返す
            if (is_null($AnnualPaidHolidaySummary)) return true;

            // 復元するレコードのバリデーションルールチェックを行う
            $tmpData = $AnnualPaidHolidaySummary->toArray();
            $validationRules = $AnnualPaidHolidaySummary->buildValidationRulesForRestore($tmpData);

            $validator = $this->Validator($tmpData, $validationRules);

            if ($validator->fails()) return false;

            $result = $AnnualPaidHolidaySummary->restore();

            // 復元の成否を確認
            if (empty($result)) {
                throw new \Exception("年次有給概要テーブルの復元に失敗しました。client_employee_id={$client_employee_id}");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return false;
        }

        return true;
    }

    /*
		@Baba 2020.11.4 追加
		社員IDでフィルタして、レコードを更新
		@param Integer $client_employee_id
		@param Array $date
		@return bool
	*/
    public function update($client_employee_id, array $date = [])
    {
        DB::beginTransaction();
        try {
            $result = AnnualPaidHolidaySummary::where('client_employee_id', $client_employee_id)->update($date);

            if ($result !== 1) {
                throw new \Exception("年次有給概要テーブルの更新に失敗しました。client_employee_id={$client_employee_id}");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return false;
        }

        return true;
    }
}
