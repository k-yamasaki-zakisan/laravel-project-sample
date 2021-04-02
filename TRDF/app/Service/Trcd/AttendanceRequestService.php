<?php

/***
 * 勤怠申請用サービス
 *
 * @author YuKaneko
 */

namespace App\Services\Trcd;

use DB;
use App\Services\ServiceBase;
use Carbon\Carbon;

// Services
use App\Services\Trcd\TrcdService;

// Repositoires
use App\Repositories\Trcd\AttendanceRequestRepositoryInterface as AttendanceRequestRepository;
use App\Repositories\Trcd\AttendanceHeaderRepositoryInterface as AttendanceHeaderRepository;
use App\Repositories\Trcd\AttendanceDetailRepositoryInterface as AttendanceDetailRepository;
use App\Repositories\Trcd\AttendancePaidHolidayRepositoryInterface as AttendancePaidHolidayRepository;

class AttendanceRequestService extends ServiceBase
{
    // Services
    protected $objTrcdService;
    protected $objAttendanceRequestRepo;
    protected $objAttendanceHeaderRepo;
    protected $objAttendanceDetailRepo;
    protected $objAttendancePaidHolidayRepo;

    // コンストラクタ
    public function __construct(
        TrcdService $objTrcdService,
        AttendanceRequestRepository $objAttendanceRequestRepo,
        AttendanceHeaderRepository $objAttendanceHeaderRepo,
        AttendanceDetailRepository $objAttendanceDetailRepo,
        AttendancePaidHolidayRepository $objAttendancePaidHolidayRepo
    ) {
        $this->objTrcdService = $objTrcdService;
        $this->objAttendanceRequestRepo = $objAttendanceRequestRepo;
        $this->objAttendanceHeaderRepo = $objAttendanceHeaderRepo;
        $this->objAttendanceDetailRepo = $objAttendanceDetailRepo;
        $this->objAttendancePaidHolidayRepo = $objAttendancePaidHolidayRepo;
    }

    /*
		勤怠IDから取得
	*/
    public function getById($attendance_request_id)
    {
        return $this->objAttendanceRequestRepo->getById($attendance_request_id);
    }

    /*
		新規申請を追加
		@param int $client_employee_id 申請対象社員のID
		@param Array $data
		@param Array $options
		@return Array or false
	*/
    public function add($client_employee_id, array $data = [], array $options = [])
    {
        DB::beginTransaction();

        try {
            $attendance_request = $this->objAttendanceRequestRepo->create($client_employee_id, $data);

            if (isset($attendance_request['attendance_header_id'])) {
                //勤怠ヘッダに対する申請の場合 勤怠ヘッダに申請中フラグを付与する
                $objAttendanceHeader = $this->objAttendanceHeaderRepo
                    ->where('request_flag', false)
                    ->find($attendance_request['attendance_header_id']);

                if ($objAttendanceHeader) {
                    $objAttendanceHeader->request_flag = true;
                    $result = $objAttendanceHeader->save();

                    if (!$result) return false;
                }
            }

            // 同時に承認処理も行う
            if (!empty($options['with_approval'])) {
                $objAttendanceRequest = $this->getById($attendance_request['id']);
                $approve_result = null;

                if ($objAttendanceRequest->isAttendanceRequest()) {
                    $approve_result = $this->approveAsAttendance($objAttendanceRequest->id);
                } else if ($objAttendanceRequest->isPaidHolidayRequest()) {
                    $approve_result = $this->approveAsPaidHoliday($objAttendanceRequest->id);
                } else {
                    throw new \UnexpectedValueException('[申請承認同時処理]申請種別が特定できませんでした。');
                }

                if (empty($approve_result)) {
                    throw new \Exception('[申請承認同時処理]申請後の承認処理に失敗しました。');
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error($e->getMessage());
            return false;
        }

        DB::commit();

        return $attendance_request;
    }

    /*
		申請否認
	*/
    public function deny($attendance_request_id)
    {
        $data = [
            'id' => $attendance_request_id,
            'processed_flag' => true,
            'attendance_request_result_id' => config('database.trcd.attendance_request_results.CONST.DENIAL'),
        ];

        return $this->objAttendanceRequestRepo->save($data);
    }

    /*
		申請取り消し
	*/
    public function cancel($attendance_request_id)
    {
        return $this->objAttendanceRequestRepo->cancel($attendance_request_id);
    }

    /*
		勤怠申請として承認
		@param int $attendance_request_id
		@param Array $data 更新用データ
	*/
    public function approveAsAttendance($attendance_request_id, array $data = [])
    {
        $objAttendanceRequest = $this->getById($attendance_request_id); // 元々の申請データを取得
        $is_modified = $objAttendanceRequest->isModifiedBy($data); // 変更箇所があるか確認

        $attendance_request = $objAttendanceRequest->toArray();

        if (!empty($data)) {
            // 変更箇所をデータに反映
            foreach ($attendance_request as $key => $value) {
                if (array_key_exists($key, $data)) $attendance_request[$key] = $data[$key];
            }
        }

        DB::beginTransaction();

        try {
            // 勤怠ヘッダ保存
            $attendance_header = $this->objAttendanceHeaderRepo->saveByAttendanceRequest($attendance_request);
            if (empty($attendance_header)) throw new \Exception('Failed to save attendance_header from attendance_request');

            // 既存の勤怠詳細を削除したのちに新しく追加
            $this->objAttendanceDetailRepo->deleteByAttendanceHeaderId($attendance_header['id']);
            $attendance_details = $this->objAttendanceDetailRepo->createByAttendanceRequest($attendance_header['id'], $attendance_request);
            if (empty($attendance_details)) throw new \Exception('Failed to save attendance_details from attendance_request');

            // Detail保存時にHeaderのattendance_datetime_of_firstが更新されるためHeaderを再度取得（しないと元のattendance_datetime_of_firstで再度更新されてしまう）
            $attendance_header = $this->objAttendanceHeaderRepo->find($attendance_header['id']);

            // 退勤している場合
            $attendance_header['closing_flag'] = isset($attendance_header['attendance_finished_datetime']);
            // 整合性チェック
            $attendance_header['mismatch_flag'] =  !($this->objAttendanceHeaderRepo->checkMatchById($attendance_header['id']) == true);

            // 申請中フラグ回収処理
            $other_request = $this->objAttendanceRequestRepo->select('id')
                ->where('attendance_header_id', $attendance_header['id'])
                ->where('processed_flag', false)
                ->where('request_canceled_at', null)
                ->whereNotIn('id', [$objAttendanceRequest->id])
                ->first();
            $attendance_header['request_flag'] = !empty($other_request);

            // 勤怠ヘッダ更新
            $objAttendanceHeader = $this->objAttendanceHeaderRepo->save($attendance_header->toArray(), ['update_date_for_display' => true]);
            if (empty($objAttendanceHeader)) throw new \Exception('Failed to update attendance_header');


            // 集計処理 ------------------------------
            $objSpecificDate = Carbon::parse($objAttendanceHeader['date_for_display']);
            $update_result = $this->objTrcdService->UpdateWithdrawAmountOfClientEmployeeAtThisMonthIfThisMonthIncludingSpecifiedDate(
                $objAttendanceHeader['client_employee_id'],
                $objSpecificDate
            );

            if (!$update_result) throw new \Exception('集計処理に失敗しました。');


            // 勤怠申請情報更新
            $objAttendanceRequest->attendance_header_id = $objAttendanceHeader->id;
            $objAttendanceRequest->processed_flag = true;
            $objAttendanceRequest->attendance_request_result_id = $is_modified
                ? config('database.trcd.attendance_request_results.CONST.CONDITIONAL_APPROVAL')
                : config('database.trcd.attendance_request_results.CONST.APPROVAL');

            $attendance_request = $this->objAttendanceRequestRepo->save($objAttendanceRequest->toArray());
            if (empty($attendance_request)) throw new \Exception('Failed to update attendance_request');
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error("[勤怠申請承認] {$e}");

            return false;
        }

        DB::commit();

        return true;
    }

    /*
		有給申請として承認
		@param int $attendance_request_id
		@param Array $data 更新用データ
	*/
    public function approveAsPaidHoliday($attendance_request_id, array $data = [])
    {
        $objAttendanceRequest = $this->getById($attendance_request_id); // 元々の申請データを取得
        $is_modified = $objAttendanceRequest->isModifiedBy($data); // 変更箇所があるか確認

        $attendance_request = $objAttendanceRequest->toArray();

        if (!empty($data)) {
            // 変更箇所をデータに反映
            foreach ($attendance_request as $key => $value) {
                if (array_key_exists($key, $data)) $attendance_request[$key] = $data[$key];
            }
        }

        DB::beginTransaction();

        try {
            $AnnualPaidHolidayService = app()->make(\App\Services\Trcd\AnnualPaidHolidayService::class);
            $validate_result = $AnnualPaidHolidayService->ValidateIfCanGetPaidHoliday(
                $attendance_request['client_employee_id'],
                $attendance_request['paid_holiday_date'],
                $attendance_request['paid_holiday_id'],
                $attendance_request['attendance_paid_holiday_id']
            );

            if (empty($validate_result['result'])) {
                throw new \Exception("勤怠有給承認前バリデーションに失敗しました。" . print_r($validate_result['errors'], true));
            }

            // 有給ヘッダ保存
            $attendance_paid_holiday = $this->objAttendancePaidHolidayRepo->saveByAttendanceRequest($attendance_request);

            if (empty($attendance_paid_holiday)) throw new \Exception('Failed to save attendance_paid_holiday from attendance_request');

            // 年次有給系テーブル更新処理
            if (!$AnnualPaidHolidayService->updateByAttendancePaidHolidayId($attendance_paid_holiday['id'])) {
                throw new \Exception("年次有給系テーブルの更新処理に失敗しました。");
            }

            // 集計処理 ------------------------------
            $objSpecificDate = Carbon::parse($attendance_paid_holiday['date']);
            $update_result = $this->objTrcdService->UpdateWithdrawAmountOfClientEmployeeAtThisMonthIfThisMonthIncludingSpecifiedDate(
                $attendance_paid_holiday['client_employee_id'],
                $objSpecificDate
            );

            if (!$update_result) throw new \Exception('集計処理に失敗しました。');

            // 勤怠申請情報更新
            $objAttendanceRequest->attendance_paid_holiday_id = $attendance_paid_holiday['id'];
            $objAttendanceRequest->processed_flag = true;
            $objAttendanceRequest->attendance_request_result_id = $is_modified
                ? config('database.trcd.attendance_request_results.CONST.CONDITIONAL_APPROVAL')
                : config('database.trcd.attendance_request_results.CONST.APPROVAL');

            $attendance_request = $this->objAttendanceRequestRepo->save($objAttendanceRequest->toArray());
            if (empty($attendance_request)) throw new \Exception('Failed to update attendance_request');
        } catch (Exception $e) {
            DB::rollBack();
            logger()->error($e);

            return false;
        }

        DB::commit();

        return true;
    }

    /*
		Validatorに勤怠日時（出退勤・休憩開始・終了）の整合性ルールを追加
		@param Array $data 対象データ
		@param Validator $validator
		@return void
	*/
    public function addAttendanceDatetimeConsistencyRule(array $data, $validator)
    {
        $this->objAttendanceRequestRepo->addAttendanceDatetimeConsistencyRule($data, $validator);
    }
}
