<?php

namespace App\Services;

use DB;

// Services
use App\Services\ServiceBase;
use App\Services\Trcd\TrcdService;

use App\Repositories\AttendancePatternRepositoryInterface as AttendancePatternRepository;
use App\Repositories\Trcd\ClientEmployeeTrcdSettingRepositoryInterface as ClientEmployeeTrcdSettingRepository;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class AttendancePatternService extends ServiceBase
{
    protected $objAttendancePatternRepository;

    public function __construct(
        AttendancePatternRepository $objAttendancePatternRepository,
        ClientEmployeeTrcdSettingRepository $objClientEmployeeTrcdSettingRepository
    ) {
        $this->objAttendancePatternRepository = $objAttendancePatternRepository;
        $this->objClientEmployeeTrcdSettingRepository = $objClientEmployeeTrcdSettingRepository;
    }

    public function getByClientId($client_id)
    {
        return $this->objAttendancePatternRepository->getByClientId($client_id);
    }

    public function create($data)
    {
        $data = array_only($data, [
            'client_id',
            'name',
            'start_time',
            'end_time',
            'time_to_judge_one_day',
            'overtime_calc_type_id',
            'base_work_time',
        ]);

        return $this->objAttendancePatternRepository->create($data);
    }

    public function save($data)
    {
        if (empty($data['id'])) return $this->create($data);

        return $this->objAttendancePatternRepository->save($data);
    }

    public function delete($attendance_pattern_id)
    {
        return $this->objAttendancePatternRepository->delete($attendance_pattern_id);
    }

    /*
		保存後に企業に属する社員の前払い額を更新
		@param $client_id
		@param $data
		@return $save_result or false
	*/
    public function saveAndUpdateWithdrawAmount($client_id, $data)
    {
        DB::beginTransaction();

        try {
            $save_result = $this->save($data);

            if (empty($save_result)) {
                throw new \Exception("client_id:{$client_id}の勤務タイプ保存処理に失敗しました");
            }

            // 属する社員の前払額更新処理
            $objTrcdService = app()->make(TrcdService::class);
            $settings = [
                'force_rounding' => true,
            ];
            $update_result = $objTrcdService->UpdateWithdrawAmountAtThisMountByClientId(
                $client_id,
                true,
                $settings
            );

            if (empty($update_result)) {
                throw new \Exception("client_id:{$client_id}の社員一括前払額更新処理に失敗しました");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("[勤務タイプ更新処理]{$e->getMessage()}");
            logger()->error($e);

            return false;
        }

        DB::commit();

        return $save_result;
    }

    /*
		削除後に企業に属する社員の前払い額を更新
		@param $client_id
		@param $attendance_pattern_id
		@return $delete_result or false
	*/
    public function deleteAndUpdateWithdrawAmount($client_id, $attendance_pattern_id)
    {
        DB::beginTransaction();

        try {
            $delete_result = $this->delete($attendance_pattern_id);

            if (empty($delete_result)) {
                throw new \Exception("client_id:{$client_id}の勤務タイプ削除処理に失敗しました");
            }

            // 属する社員の前払額更新処理
            $objTrcdService = app()->make(TrcdService::class);
            $settings = [
                'force_rounding' => true,
            ];
            $update_result = $objTrcdService->UpdateWithdrawAmountAtThisMountByClientId(
                $client_id,
                true,
                $settings
            );

            if (empty($update_result)) {
                throw new \Exception("client_id:{$client_id}の社員一括前払額更新処理に失敗しました");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("[勤務タイプ削除処理]{$e->getMessage()}");
            logger()->error($e);

            return false;
        }

        DB::commit();

        return $delete_result;
    }

    /*
		クライアント社員IDの配列で勤務パターンを取得する
	*/
    public function getByClientEmployeeIds($client_employee_ids)
    {

        $result = array();

        // 社員の勤務パターンIDを取得
        $attendance_pattern_ids = $this->objClientEmployeeTrcdSettingRepository->whereIn('client_employee_id', $client_employee_ids)
            ->whereNotNull('attendance_pattern_id')
            ->get()
            ->pluck('attendance_pattern_id', 'client_employee_id')
            ->all();

        // 勤務パターンを取得
        $attendance_patterns = $this->objAttendancePatternRepository->find(array_unique($attendance_pattern_ids))
            ->keyBy('id')
            ->toArray();

        foreach ($client_employee_ids as $client_employee_id) {
            if (isset($attendance_pattern_ids[$client_employee_id]) && isset($attendance_patterns[$attendance_pattern_ids[$client_employee_id]])) {
                $result[$client_employee_id] = $attendance_patterns[$attendance_pattern_ids[$client_employee_id]];
            } else {
                $result[$client_employee_id] = null;
            }
        }

        return $result;
    }
}
