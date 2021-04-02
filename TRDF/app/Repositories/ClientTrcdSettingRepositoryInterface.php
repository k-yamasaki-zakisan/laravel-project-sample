<?php
/**
 * 社員TRCD設定リポジトリインターフェイス
 *
 * @author T.Ando
 */

namespace App\Repositories\Trcd;

interface ClientTrcdSettingRepositoryInterface{
        public function getById($id);
        public function getByClientId($client_id);
        public function getAll($options=array());

        public function create($client_id, $rounding_franction=null, $fixed_work_start_time=null, $withdraw_amount_limit_a_day=null);
        //public function delete($id);

        public function createInitialData($client_id, Array $initial_data=[]);
        public function save($client_id, Array $save_data=[]);
        public function updatePayrollStartDayResetAt($client_id);
        public function setValuesToClientTrcdSettingObject($objClientTrcdSetting, Array $values);
        public function CalcRangeOfWorkDatetime($objClientTrcdSetting, $date, $start_time, $end_time);
}
