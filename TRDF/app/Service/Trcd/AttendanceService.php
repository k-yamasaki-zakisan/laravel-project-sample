<?php

/***
 * 勤怠サービス
 *
 * @author YuKaneko
 */

namespace App\Services\Trcd;

use App\Services\ServiceBase;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Arr;

// Repository
use App\Repositories\Trcd\AttendanceHeaderRepositoryInterface as AttendanceHeaderRepository;
use App\Repositories\Trcd\AttendanceRequestRepositoryInterface as AttendanceRequestRepository;
use App\Repositories\Trcd\ClientNoteTypeRepositoryInterface as ClientNoteTypeRepository;
use App\Repositories\Trcd\ClientEmployeeTrcdSettingRepositoryInterface as ClientEmployeeTrcdSettingRepository;
use App\Repositories\AttendancePatternRepositoryInterface as AttendancePatternRepository;

// Service
use App\Services\Trcd\TrcdService;
use App\Services\ClientEmployeeService;
use App\Services\Trcd\PaidHolidayService;
use App\Services\AttendanceSpecialTypeService;
use App\Services\Trcd\AttendanceRequestService;
use App\Services\Trcd\AttendancePaidHolidayService;
use App\Services\Trcd\AttendanceHeaderService;
use App\Services\Trcd\AttendanceDetailService;
use App\Services\Trcd\AttendanceNoteService;
use App\Services\Trcd\AnnualPaidHolidayService;

class AttendanceService extends ServiceBase
{
    // Services
    protected $objTrcdService;
    protected $objClientEmployeeService;
    protected $objPaidHolidayService;
    protected $objAttendanceSpecialTypeService;
    protected $objAttendanceRequestService;
    protected $objAttendancePaidHolidayService;
    protected $objAttendanceHeaderService;
    protected $objAttendanceDetailService;
    protected $objAttendanceNoteService;

    // Repositories
    protected $objAttendanceHeaderRepository;
    protected $objAttendanceRequestRepository;
    protected $objClientNoteTypeRepository;
    protected $objClientEmployeeTrcdSettingRepository;
    protected $objAttendancePatternRepository;

    public function __construct(
        TrcdService $objTrcdService,
        ClientEmployeeService $objClientEmployeeService,
        PaidHolidayService $objPaidHolidayService,
        AttendanceSpecialTypeService $objAttendanceSpecialTypeService,
        AttendanceRequestService $objAttendanceRequestService,
        AttendancePaidHolidayService $objAttendancePaidHolidayService,
        AttendanceHeaderService $objAttendanceHeaderService,
        AttendanceDetailService $objAttendanceDetailService,
        AttendanceNoteService $objAttendanceNoteService,
        AttendanceHeaderRepository $objAttendanceHeaderRepository,
        AttendanceRequestRepository $objAttendanceRequestRepository,
        ClientNoteTypeRepository $objClientNoteTypeRepository,
        ClientEmployeeTrcdSettingRepository $objClientEmployeeTrcdSettingRepository,
        AttendancePatternRepository $objAttendancePatternRepository
    ) {
        $this->objTrcdService = $objTrcdService;
        $this->objClientEmployeeService = $objClientEmployeeService;
        $this->objPaidHolidayService = $objPaidHolidayService;
        $this->objAttendanceSpecialTypeService = $objAttendanceSpecialTypeService;
        $this->objAttendanceRequestService = $objAttendanceRequestService;
        $this->objAttendancePaidHolidayService = $objAttendancePaidHolidayService;
        $this->objAttendanceHeaderService = $objAttendanceHeaderService;
        $this->objAttendanceDetailService = $objAttendanceDetailService;
        $this->objAttendanceNoteService = $objAttendanceNoteService;

        $this->objAttendanceHeaderRepository = $objAttendanceHeaderRepository;
        $this->objAttendanceRequestRepository = $objAttendanceRequestRepository;
        $this->objClientNoteTypeRepository = $objClientNoteTypeRepository;
        $this->objClientEmployeeTrcdSettingRepository = $objClientEmployeeTrcdSettingRepository;
        $this->objAttendancePatternRepository = $objAttendancePatternRepository;
    }

    /*
		勤怠データ、有給データと勤怠備考データを統合して行として表現。配列化する。
		return Array $attendance_rows
	*/
    public function getAttendanceRows($client_employee_id, $from, $to, $options = [])
    {
        // 勤怠ヘッダ・詳細を取得し、勤怠申請と紐づけるために一時格納配列へ代入
        $tmp_attendance_headers = [];
        $attendance_header_ids = [];

        $attendance_headers = $this->objAttendanceHeaderService->LoadAttendance($client_employee_id, $from, $to, $options);

        foreach ($attendance_headers as $key => $attendance_header) {
            // 総労働時間も取得
            $tmp_array = $attendance_header->append(['total_working_time', 'total_break_time'])->toArray();
            $tmp_array['is_attendance_header'] = true;
            $tmp_array['total_break_time_minutes'] = $attendance_header->CalcTotalBreakTimeMinutes($attendance_header->attendance_details_orderby_attendance_datetime_asc->toArray());

            $tmp_attendance_headers[$tmp_array['id']] = $tmp_array;
            $attendance_header_ids[] = $tmp_array['id'];
        }

        // 勤怠申請を取得し勤怠ヘッダと紐づける（処理済・取り消されているものは除く）
        $attendance_requests = $this->objAttendanceRequestRepository->whereIn('attendance_header_id', $attendance_header_ids)
            ->where('processed_flag', false)
            ->where('request_canceled_at', null)
            ->orderBy('created_at', 'ASC')
            ->get();

        foreach ($attendance_requests as $attendance_request) {
            if (!isset($tmp_attendance_headers[$attendance_request['attendance_header_id']])) continue;

            // 申請格納用配列が作成されていない場合に作成
            if (!isset($tmp_attendance_headers[$attendance_request['attendance_header_id']]['requests'])) {
                $tmp_attendance_headers[$attendance_request['attendance_header_id']]['requests'] = [];
            }

            $tmp_attendance_headers[$attendance_request['attendance_header_id']]['requests'][]  = $attendance_request->toArray();
        }


        // 有給ヘッダを取得し、有給申請と紐づけるために一時格納配列へ代入
        $tmp_attendance_paid_holidays = [];
        $attendance_paid_holiday_ids = [];
        $attendance_paid_holidays_options = [
            'from' => $from,
            'to' => $to,
        ];

        $attendance_paid_holidays = $this->objAttendancePaidHolidayService->getByClientEmployeeId($client_employee_id, $attendance_paid_holidays_options);

        foreach ($attendance_paid_holidays as $attendance_paid_holiday) {
            $tmp_array = $attendance_paid_holiday->toArray();
            $tmp_array['is_attendance_paid_holiday'] = true;
            // あとで勤怠ヘッダとマージする際にソート用のカラムとして利用
            //$tmp_array['attendance_started_datetime'] = Carbon::parse($tmp_array['date'])->startOfDay()->format('Y-m-d H:i:s');
            //$tmp_array['attendance_datetime_of_first'] = Carbon::parse($tmp_array['date'])->startOfDay()->format('Y-m-d H:i:s');
            $tmp_array['date_for_display'] = Carbon::parse($tmp_array['date'])->startOfDay()->format('Y-m-d H:i:s');

            $tmp_attendance_paid_holidays[$tmp_array['id']] = $tmp_array;
            $attendance_paid_holiday_ids[] = $tmp_array['id'];
        }

        // 有給申請を取得し有給ヘッダと紐づける（処理済・取り消されているものは除く）
        $attendance_requests = $this->objAttendanceRequestRepository->whereIn('attendance_paid_holiday_id', $attendance_paid_holiday_ids)
            ->where('processed_flag', false)
            ->where('request_canceled_at', null)
            ->orderBy('created_at', 'ASC')
            ->get();

        foreach ($attendance_requests as $attendance_request) {
            if (!isset($tmp_attendance_paid_holidays[$attendance_request['attendance_paid_holiday_id']])) continue;

            // 申請格納用配列が作成されていない場合に作成
            if (!isset($tmp_attendance_paid_holidays[$attendance_request['attendance_paid_holiday_id']]['requests'])) {
                $tmp_attendance_paid_holidays[$attendance_request['attendance_paid_holiday_id']]['requests'] = [];
            }

            $tmp_attendance_paid_holidays[$attendance_request['attendance_paid_holiday_id']]['requests'][]  = $attendance_request->toArray();
        }

        // 勤怠ヘッダと有給ヘッダをマージ
        $attendance_rows = array_merge($tmp_attendance_headers, $tmp_attendance_paid_holidays);

        // 勤怠備考データを取得する

        // @terada 2019/10/23 現在「休日」の備考種別しかないので、有効化フラグが有効かつ、備考種別が「休日」の企業ごとの備考種別を取得する
        $NOTE_TYPE_CONSTANTS = config('database.trcd.note_types.CONST');

        $client_note_type_query = $this->objClientNoteTypeRepository->where('is_enable', true)->where('note_type_id', $NOTE_TYPE_CONSTANTS['HOLIDAY']);

        // 企業IDが$optionsにある場合
        if (isset($options['client_id'])) {
            $client_note_type_query->where('client_id', $options['client_id']);
        }

        $client_note_types = $client_note_type_query->get()->keyBy('id')->toArray();

        $client_note_type_ids = array_keys($client_note_types);

        $tmp_attendance_notes = [];

        $attendance_notes_options = [
            'from' => $from,
            'to' => $to,
        ];

        $attendance_note_query = $this->objAttendanceNoteService->getQueryByClientEmployeeId($client_employee_id, $attendance_notes_options);

        $attendance_notes = $attendance_note_query->whereIn('client_note_type_id', $client_note_type_ids)->get();

        foreach ($attendance_notes as $attendance_note) {
            $tmp_array = $attendance_note->toArray();
            $tmp_array['is_attendance_note'] = true;
            // あとで勤怠ヘッダとマージする際にソート用のカラムとして利用
            $tmp_array['date_for_display'] = Carbon::parse($tmp_array['date'])->startOfDay()->format('Y-m-d H:i:s');
            $tmp_array['client_note_type_name'] = isset($client_note_types[$attendance_note->client_note_type_id]) ? $client_note_types[$attendance_note->client_note_type_id]['name'] : null;
            $tmp_array['attendance_note_id'] = $attendance_note->id;
            $tmp_attendance_notes[] = $tmp_array;
        }

        // 勤怠ヘッダと有給ヘッダがマージされたものと勤怠備考データをマージ
        $attendance_rows = array_merge($attendance_rows, $tmp_attendance_notes);

        // マージしたものをソート
        $attendance_rows = array_values(Arr::sort($attendance_rows, function ($row) {
            //return $row['attendance_started_datetime'];
            //return $row['attendance_datetime_of_first'];
            return $row['date_for_display'];
        }));

        return $attendance_rows;
    }

    /*
		有給種別リスト取得
	*/
    public function getPaidHolidayList($client_id, $options = [])
    {
        return $this->objPaidHolidayService->getList($client_id, $options);
    }

    /*
		特殊勤怠リスト取得
	*/
    public function getAttendanceSpecialTypeList($client_id, $options = [])
    {
        return $this->objAttendanceSpecialTypeService->getList($client_id, $options = []);
    }

    /*
		勤怠種別取得
	*/
    public function getAttendanceTypes()
    {
        return config('database.trcd.attendance_types');
    }

    /*
		申請結果種別取得
	*/
    public function getAttendanceRequestResults()
    {
        return config('database.trcd.attendance_request_results');
    }

    /*
		勤怠詳細登録方法種別
	*/
    public function getCreateByTypes()
    {
        return config('database.trcd.create_by');
    }

    /*
		申請拒否
	*/
    public function denyAttendanceRequest($attendance_request_id)
    {
        return $this->objAttendanceRequestService->deny($attendance_request_id);
    }

    /*
		申請取り消し
	*/
    public function cancelAttendanceRequest($attendance_request_id)
    {
        return $this->objAttendanceRequestService->cancel($attendance_request_id);
    }

    /*
		勤怠ヘッダ削除
	*/
    public function deleteAttendanceHeader($attendance_header_id, $client_employee_id)
    {
        DB::beginTransaction();

        try {
            // 対象の勤怠ヘッダ取得
            $objAttendanceHeader = $this->objAttendanceHeaderService->getById($attendance_header_id);

            if (empty($objAttendanceHeader)) throw new \Exception("勤怠ヘッダID {$attendance_header_id} が見つかりません。");

            // 勤怠ヘッダ削除処理
            $result = $this->objAttendanceHeaderService->delete($objAttendanceHeader['id']);

            if (empty($result)) throw new \Exception("勤怠ヘッダID {$objAttendanceHeader['id']} の削除に失敗しました。");

            // 削除された勤怠ヘッダに対して申請されているのものを否認として処理する
            $attendance_request_ids = $this->objAttendanceRequestRepository->where('attendance_header_id', $objAttendanceHeader['id'])
                ->where('processed_flag', false)
                ->where('request_canceled_at', null)
                ->pluck('id');

            // ToDo 2019/07/22 @YuKaneko 繰り返し処理を行っているので一括更新できるように修正すること
            foreach ($attendance_request_ids as $attendance_request_id) {
                if (!$this->denyAttendanceRequest($attendance_request_id)) throw new \Exception("削除した勤怠ヘッダ[ID:{$objAttendanceHeader['id']}]への申請否認処理に失敗しました。");
            }


            // 集計処理
            $objSpecificDate = Carbon::parse($objAttendanceHeader['date_for_display']);
            $update_result = $this->objTrcdService->UpdateWithdrawAmountOfClientEmployeeAtThisMonthIfThisMonthIncludingSpecifiedDate(
                $objAttendanceHeader['client_employee_id'],
                $objSpecificDate
            );

            if (!$update_result) throw new \Exception('集計処理に失敗しました。');
        } catch (\Exception $e) {
            DB::rollback();
            logger()->error("[勤怠ヘッダ削除処理] {$e}");

            return false;
        }

        DB::commit();

        return true;
    }

    /*
		有給ヘッダ保存
	*/
    public function saveAttendancePaidHoliday($data)
    {
        DB::beginTransaction();

        try {
            $attendance_paid_holiday = $this->objAttendancePaidHolidayService->save($data);

            if (empty($attendance_paid_holiday)) throw new \Exception('有給ヘッダの保存に失敗しました。');

            // 集計処理
            $objSpecificDate = Carbon::parse($attendance_paid_holiday['date']);
            $update_result = $this->objTrcdService->UpdateWithdrawAmountOfClientEmployeeAtThisMonthIfThisMonthIncludingSpecifiedDate(
                $attendance_paid_holiday['client_employee_id'],
                $objSpecificDate
            );

            if (!$update_result) throw new \Exception('集計処理に失敗しました。');
        } catch (\Exception $e) {
            DB::rollback();
            logger()->error("[有給ヘッダ保存処理] {$e}");

            return false;
        }

        DB::commit();

        return $attendance_paid_holiday;
    }

    /*
		有給ヘッダ削除
	*/
    public function deleteAttendancePaidHoliday($attendance_paid_holiday_id, $client_employee_id)
    {
        DB::beginTransaction();

        try {
            // 対象の有給ヘッダ取得
            $objAttendancePaidHoliday = $this->objAttendancePaidHolidayService->getById($attendance_paid_holiday_id);

            if (empty($objAttendancePaidHoliday)) throw new \Exception("有給ヘッダID {$attendance_paid_holiday_id} が見つかりません。");

            // @2020.11.17 YuKaneko 年次有給関連テーブル更新処理
            $AnnualPaidHolidayService = app()->make(AnnualPaidHolidayService::class);
            $result = $AnnualPaidHolidayService->detachByAttendancePaidHolidayId($objAttendancePaidHoliday['id']);

            if (empty($result)) throw new \Exception("年次有給関連テーブル更新処理に失敗しました。AttendancePaidHoliday.id={$objAttendancePaidHoliday['id']}");

            // 有給ヘッダ削除処理
            $result = $this->objAttendancePaidHolidayService->delete($objAttendancePaidHoliday['id']);

            if (empty($result)) throw new \Exception("有給ヘッダ[ID:{$objAttendancePaidHoliday['id']}]の削除に失敗しました。");


            // 削除された有給ヘッダに対して申請されているのものを否認として処理する
            $attendance_request_ids = $this->objAttendanceRequestRepository->where('attendance_paid_holiday_id', $objAttendancePaidHoliday['id'])
                ->where('processed_flag', false)
                ->where('request_canceled_at', null)
                ->pluck('id');

            // ToDo 2019/07/22 @YuKaneko 繰り返し処理を行っているので一括更新できるように修正すること
            foreach ($attendance_request_ids as $attendance_request_id) {
                if (!$this->denyAttendanceRequest($attendance_request_id)) throw new \Exception('削除した有給ヘッダへの申請否認処理に失敗しました。');
            }

            // 集計処理
            $objSpecificDate = Carbon::parse($objAttendancePaidHoliday['date']);
            $update_result = $this->objTrcdService->UpdateWithdrawAmountOfClientEmployeeAtThisMonthIfThisMonthIncludingSpecifiedDate(
                $objAttendancePaidHoliday['client_employee_id'],
                $objSpecificDate
            );

            if (!$update_result) throw new \Exception('集計処理に失敗しました。');
        } catch (\Exception $e) {
            DB::rollback();
            logger()->error($e->getMessage());

            return false;
        }

        DB::commit();

        return true;
    }

    /*
		勤怠詳細保存
	*/
    public function saveAttendanceDetail($data)
    {
        DB::beginTransaction();

        try {
            // 勤怠詳細保存処理 ------------------------------
            $result = $this->objAttendanceDetailService->save($data);

            if (empty($result)) throw new \Exception('勤怠詳細の保存に失敗しました。');


            // 勤怠ヘッダ保存処理 ------------------------------
            $objAttendanceHeader = $this->objAttendanceHeaderService->getById($data['attendance_header_id']);
            $ATTENDANCE_TYPES = $this->getAttendanceTypes();

            if ($data['attendance_type_id'] == $ATTENDANCE_TYPES['CONST']['STAMP_WORK_BEGIN']) {
                // 出勤時刻が変更された場合
                $objAttendanceHeader->attendance_started_datetime = $data['attendance_datetime'];
            } else if ($data['attendance_type_id'] == $ATTENDANCE_TYPES['CONST']['STAMP_WORK_FINISH']) {
                // 退勤時刻が変更された場合
                $objAttendanceHeader->closing_flag = true;
                $objAttendanceHeader->attendance_finished_datetime = $data['attendance_datetime'];
            }

            // 整合性チェック
            $objAttendanceHeader->mismatch_flag = ($this->objAttendanceHeaderService->checkMatchById($objAttendanceHeader->id) == true) ? false : true;

            $objAttendanceHeader = $this->objAttendanceHeaderRepository->save($objAttendanceHeader->toArray(), ['update_date_for_display' => true]);
            if (empty($objAttendanceHeader)) throw new \Exception('勤怠ヘッダの保存に失敗しました。');


            // 集計処理 ------------------------------
            $objSpecificDate = Carbon::parse($objAttendanceHeader['date_for_display']);
            $update_result = $this->objTrcdService->UpdateWithdrawAmountOfClientEmployeeAtThisMonthIfThisMonthIncludingSpecifiedDate(
                $objAttendanceHeader['client_employee_id'],
                $objSpecificDate
            );

            if (!$update_result) throw new \Exception('集計処理に失敗しました。');
        } catch (\Exception $e) {
            DB::rollback();
            logger()->error("[勤怠詳細保存処理] {$e}");

            return false;
        }

        DB::commit();

        return $result;
    }

    /*
		勤怠詳細削除
	*/
    public function deleteAttendanceDetail($attendance_detail_id)
    {
        $objAttendanceDetail = $this->objAttendanceDetailService->getById($attendance_detail_id);

        DB::beginTransaction();

        try {
            // 勤怠詳細削除処理 ------------------------------
            $result = $this->objAttendanceDetailService->delete($objAttendanceDetail->id);

            if (empty($result)) throw new \Exception('勤怠詳細の削除に失敗しました。');


            // 勤怠詳細削除後の勤怠ヘッダ更新処理 ------------------------------
            $objAttendanceHeader = $this->objAttendanceHeaderService->getById($objAttendanceDetail->attendance_header_id);
            $ATTENDANCE_TYPES = $this->getAttendanceTypes();

            if ($objAttendanceDetail->attendance_type_id === $ATTENDANCE_TYPES['CONST']['STAMP_WORK_BEGIN']) {
                // 削除後にも残っている出勤時刻を検索 存在する場合は（ひとまず）最も出勤時刻が早いものを採用する
                $objClientEmployee = $this->objClientEmployeeService->getById($objAttendanceHeader->client_employee_id);
                $objClientTrcdSetting = $this->objTrcdService->GetClientTrcdSettingByClientId($objClientEmployee->client_id);
                $objAttendanceBeginDetailQuery = $objAttendanceDetail->where('attendance_header_id', $objAttendanceHeader->id)
                    ->where('attendance_type_id', $ATTENDANCE_TYPES['CONST']['STAMP_WORK_BEGIN']);

                if ($objClientTrcdSetting->work_start_duplicated_processing_type_id == config('database.trcd.client_trcd_settings.CONST_DUPLICATED.LAST')) {
                    // 遅い方優先で設定されている場合
                    $objAttendanceBeginDetailQuery->orderBy('attendance_datetime', 'desc');
                } else {
                    // その他
                    $objAttendanceBeginDetailQuery->orderBy('attendance_datetime', 'asc');
                }

                $objAttendanceBeginDetail = $objAttendanceBeginDetailQuery->first();
                $objAttendanceHeader->attendance_started_datetime = empty($objAttendanceBeginDetail) ? null : $objAttendanceBeginDetail->attendance_datetime;
            } else if ($objAttendanceDetail->attendance_type_id === $ATTENDANCE_TYPES['CONST']['STAMP_WORK_FINISH']) {
                // 削除後にも残っている退勤時刻を検索 存在する場合は（ひとまず）最も退勤時刻が遅いものを採用する
                $objAttendanceFinishDetail = $objAttendanceDetail->where('attendance_header_id', $objAttendanceHeader->id)
                    ->where('attendance_type_id', $ATTENDANCE_TYPES['CONST']['STAMP_WORK_FINISH'])
                    ->orderby('attendance_datetime', 'desc')
                    ->first();

                if (empty($objAttendanceFinishDetail)) {
                    // 空だった場合は退勤時刻をnull、closingをfalseに変更
                    $objAttendanceHeader->attendance_finished_datetime = null;
                    $objAttendanceHeader->closing_flag = false;
                } else {
                    // 存在する場合は退勤時刻を採用、closingをtrueに変更
                    $objAttendanceHeader->attendance_finished_datetime = $objAttendanceFinishDetail->attendance_datetime;
                    $objAttendanceHeader->closing_flag = true;
                }
            }

            // 整合性チェック
            $objAttendanceHeader->mismatch_flag = ($this->objAttendanceHeaderService->checkMatchById($objAttendanceHeader->id) == true) ? false : true;

            $objAttendanceHeader = $this->objAttendanceHeaderRepository->save($objAttendanceHeader->toArray(), ['update_date_for_display' => true]);
            if (empty($objAttendanceHeader)) throw new \Exception('勤怠詳細削除後の勤怠ヘッダの更新に失敗しました。');


            // 集計処理 ------------------------------
            $objSpecificDate = Carbon::parse($objAttendanceHeader['date_for_display']);
            $update_result = $this->objTrcdService->UpdateWithdrawAmountOfClientEmployeeAtThisMonthIfThisMonthIncludingSpecifiedDate(
                $objAttendanceHeader['client_employee_id'],
                $objSpecificDate
            );

            if (!$update_result) throw new \Exception('集計処理に失敗しました。');
        } catch (\Exception $e) {
            DB::rollback();
            logger()->error("[勤怠詳細削除処理] {$e}");

            return false;
        }

        DB::commit();

        return true;
    }

    /*
		全社員の日毎の勤怠データ、有給データ、勤怠備考データと新規勤怠申請データを統合して行として表現。配列化する。
		return Array $attendance_rows
	*/
    public function getDailyAttendanceRows($clientEmployeeIds, $attendanceDate, $options = [])
    {
        // 勤怠ヘッダ・詳細を取得し、勤怠申請と紐づけるために一時格納配列へ代入
        $tmp_attendance_headers = [];
        $attendance_header_ids = [];

        $attendance_headers = $this->objAttendanceHeaderService->LoadDailyAttendance($clientEmployeeIds, $attendanceDate, $options);

        foreach ($attendance_headers as $key => $attendance_header) {
            // 総労働時間も取得
            $tmp_array = $attendance_header->append(['total_working_time', 'total_break_time'])->toArray();
            $tmp_array['is_attendance_header'] = true;
            $tmp_array['total_break_time_minutes'] = $attendance_header->CalcTotalBreakTimeMinutes($attendance_header->attendance_details_orderby_attendance_datetime_asc->toArray());

            $tmp_attendance_headers[$tmp_array['id']] = $tmp_array;
            $attendance_header_ids[] = $tmp_array['id'];
        }

        // 勤怠申請を取得し勤怠ヘッダと紐づける（処理済・取り消されているものは除く）
        $attendance_requests = $this->objAttendanceRequestRepository->whereIn('attendance_header_id', $attendance_header_ids)
            ->where('processed_flag', false)
            ->where('request_canceled_at', null)
            ->orderBy('created_at', 'ASC')
            ->get();

        foreach ($attendance_requests as $attendance_request) {
            if (!isset($tmp_attendance_headers[$attendance_request['attendance_header_id']])) continue;

            // 申請格納用配列が作成されていない場合に作成
            if (!isset($tmp_attendance_headers[$attendance_request['attendance_header_id']]['requests'])) {
                $tmp_attendance_headers[$attendance_request['attendance_header_id']]['requests'] = [];
            }

            $tmp_attendance_headers[$attendance_request['attendance_header_id']]['requests'][]  = $attendance_request->toArray();
        }

        // 有給ヘッダを取得し、有給申請と紐づけるために一時格納配列へ代入
        $tmp_attendance_paid_holidays = [];
        $attendance_paid_holiday_ids = [];
        $attendance_paid_holidays_options = [
            'date' => $attendanceDate,
        ];

        $attendance_paid_holidays = $this->objAttendancePaidHolidayService->getByClientEmployeeId($clientEmployeeIds, $attendance_paid_holidays_options);

        foreach ($attendance_paid_holidays as $attendance_paid_holiday) {
            $tmp_array = $attendance_paid_holiday->toArray();
            $tmp_array['is_attendance_paid_holiday'] = true;
            // あとで勤怠ヘッダとマージする際にソート用のカラムとして利用
            $tmp_array['date_for_display'] = Carbon::parse($tmp_array['date'])->startOfDay()->format('Y-m-d H:i:s');

            $tmp_attendance_paid_holidays[$tmp_array['id']] = $tmp_array;
            $attendance_paid_holiday_ids[] = $tmp_array['id'];
        }

        // 有給申請を取得し有給ヘッダと紐づける（処理済・取り消されているものは除く）
        $attendance_requests = $this->objAttendanceRequestRepository->whereIn('attendance_paid_holiday_id', $attendance_paid_holiday_ids)
            ->where('processed_flag', false)
            ->where('request_canceled_at', null)
            ->orderBy('created_at', 'ASC')
            ->get();

        foreach ($attendance_requests as $attendance_request) {
            if (!isset($tmp_attendance_paid_holidays[$attendance_request['attendance_paid_holiday_id']])) continue;

            // 申請格納用配列が作成されていない場合に作成
            if (!isset($tmp_attendance_paid_holidays[$attendance_request['attendance_paid_holiday_id']]['requests'])) {
                $tmp_attendance_paid_holidays[$attendance_request['attendance_paid_holiday_id']]['requests'] = [];
            }

            $tmp_attendance_paid_holidays[$attendance_request['attendance_paid_holiday_id']]['requests'][]  = $attendance_request->toArray();
        }

        // 勤怠ヘッダと有給ヘッダをマージ
        $attendance_rows = array_merge($tmp_attendance_headers, $tmp_attendance_paid_holidays);

        // 勤怠備考データを取得する

        // 有効化フラグが有効かつ、備考種別が「休日」の企業ごとの備考種別を取得する
        $NOTE_TYPE_CONSTANTS = config('database.trcd.note_types.CONST');

        $client_note_type_query = $this->objClientNoteTypeRepository->where('is_enable', true)->where('note_type_id', $NOTE_TYPE_CONSTANTS['HOLIDAY']);

        // 企業IDが$optionsにある場合
        if (isset($options['client_id'])) {
            $client_note_type_query->where('client_id', $options['client_id']);
        }

        $client_note_types = $client_note_type_query->get()->keyBy('id')->toArray();

        $client_note_type_ids = array_keys($client_note_types);

        $tmp_attendance_notes = [];

        $attendance_notes_options = [
            'date' => $attendanceDate,
        ];

        $attendance_note_query = $this->objAttendanceNoteService->getQueryByClientEmployeeId($clientEmployeeIds, $attendance_notes_options);

        $attendance_notes = $attendance_note_query->whereIn('client_note_type_id', $client_note_type_ids)->get();

        foreach ($attendance_notes as $attendance_note) {
            $tmp_array = $attendance_note->toArray();
            $tmp_array['is_attendance_note'] = true;
            // あとで勤怠ヘッダとマージする際にソート用のカラムとして利用
            $tmp_array['date_for_display'] = Carbon::parse($tmp_array['date'])->startOfDay()->format('Y-m-d H:i:s');
            $tmp_array['client_note_type_name'] = isset($client_note_types[$attendance_note->client_note_type_id]) ? $client_note_types[$attendance_note->client_note_type_id]['name'] : null;
            $tmp_array['attendance_note_id'] = $attendance_note->id;
            $tmp_attendance_notes[] = $tmp_array;
        }

        // 勤怠ヘッダと有給ヘッダがマージされたものと勤怠備考データをマージ
        $attendance_rows = array_merge($attendance_rows, $tmp_attendance_notes);

        // マージしたものをソート
        $attendance_rows = array_values(Arr::sort($attendance_rows, function ($row) {
            return $row['date_for_display'];
        }));

        // 勤怠ヘッダと紐付いていない申請を取得する

        // 勤怠日ベースを取得する

        // 企業IDが$optionsにない場合
        if (!isset($options['client_id'])) {
            return $attendance_rows;
        }

        $objClientTrcdSetting = $this->objTrcdService->GetClientTrcdSettingByClientId($options['client_id']);

        // 基点対象時刻を取得
        $CONST_PAYROLL_USE_TIMING_LIST = config('database.trcd.client_trcd_settings.CONST_PAYROLL_USE_TIMING.LIST');

        // 基点カラム
        $base_datetime_column = null;

        $tmp_unlinked_attendance_requests = [];

        switch ($objClientTrcdSetting->payroll_use_timing_id) {
            case ($CONST_PAYROLL_USE_TIMING_LIST['USE_STARTED_ATTENDANCE_DATETIME']): // 勤務開始時刻を基点とする

                $base_datetime_column = 'attendance_datetime_work_begin';

                // データベースから取得する申請データの期間
                $from = Carbon::parse($attendanceDate)->startOfDay();
                $to = $from->copy();
                $to = $to->addDays(2);
                $to = $to->subSecond();

                $from_datetime_string = $from->format('Y-m-d H:i:s');
                $to_datetime_string = $to->format('Y-m-d H:i:s');

                // 勤怠ヘッダに紐付いていない勤怠申請を２日分取得する
                $unlinked_attendance_requests = $this->objAttendanceRequestRepository->whereNull('attendance_header_id')
                    ->where('processed_flag', false)
                    ->where('request_canceled_at', null)
                    ->whereIn('client_employee_id', $clientEmployeeIds)
                    ->whereNotNull('attendance_datetime_work_begin')
                    ->whereBetween('attendance_datetime_work_begin', [$from_datetime_string, $to_datetime_string])
                    ->orderBy('created_at', 'ASC')
                    ->get()
                    ->toArray();

                // 指定された日付をカーボンにする
                $attendanceDateCarbon = Carbon::parse($attendanceDate)->startOfDay();
                $endOfAttendanceDateCarbon = $attendanceDateCarbon->copy()->addDay()->subSecond();

                $objClientEmployeeTrcdSettings = $this->objClientEmployeeTrcdSettingRepository->getById($clientEmployeeIds);

                $client_employee_trcd_settings = $objClientEmployeeTrcdSettings->keyBy('client_employee_id')->all();

                $attendance_pattern_ids = array();

                foreach ($client_employee_trcd_settings as $client_employee_trcd_setting) {
                    if (isset($client_employee_trcd_setting['attendance_pattern_id'])) {
                        $attendance_pattern_ids[] = $client_employee_trcd_setting['attendance_pattern_id'];
                    }
                }

                $attendance_pattern_ids = array_unique($attendance_pattern_ids);

                $attendance_patterns = $this->objAttendancePatternRepository->find($attendance_pattern_ids)->keyBy('id')->all();

                $client_employee_time_to_judge_one_day_carbons = array();

                foreach ($clientEmployeeIds as $clientEmployeeId) {
                    if (!isset($client_employee_trcd_settings[$clientEmployeeId]['attendance_pattern_id'])) {
                        // この社員の勤務パターンIDが見つからなかったので、指定日の１日間を対象にする

                        $client_employee_time_to_judge_one_day_carbons[$clientEmployeeId]['from'] =
                            $attendanceDateCarbon;
                        $client_employee_time_to_judge_one_day_carbons[$clientEmployeeId]['to'] =
                            $endOfAttendanceDateCarbon;
                        continue;
                    }
                    $attendance_pattern_id = $client_employee_trcd_settings[$clientEmployeeId]['attendance_pattern_id'];
                    if (!isset($attendance_patterns[$attendance_pattern_id])) {
                        // この社員の勤務パターンIDの勤務パターンが見つからなかったので、指定日の１日間を対象にする

                        $client_employee_time_to_judge_one_day_carbons[$clientEmployeeId]['from'] =
                            $attendanceDateCarbon;
                        $client_employee_time_to_judge_one_day_carbons[$clientEmployeeId]['to'] =
                            $endOfAttendanceDateCarbon;
                        continue;
                    }
                    // 勤怠を1日と判断する区切りの時間をカーボンにする
                    $time_to_judge_one_day_carbon = Carbon::createFromTimeString($attendance_patterns[$attendance_pattern_id]['time_to_judge_one_day']);

                    // 区切り時間の日付を、指定された日付にする
                    $time_to_judge_one_day_carbon->setDate(
                        $attendanceDateCarbon->year,
                        $attendanceDateCarbon->month,
                        $attendanceDateCarbon->day
                    );

                    $client_employee_time_to_judge_one_day_carbons[$clientEmployeeId]['from'] = $time_to_judge_one_day_carbon;
                    $time_to_judge_one_day_of_next_day_carbon = $time_to_judge_one_day_carbon->copy()->addDay()->subSecond();
                    $client_employee_time_to_judge_one_day_carbons[$clientEmployeeId]['to'] = $time_to_judge_one_day_of_next_day_carbon;
                }

                foreach ($unlinked_attendance_requests as $unlinked_attendance_request) {
                    $client_employee_id = $unlinked_attendance_request['client_employee_id'];
                    $attendance_datetime_work_begin_carbon = Carbon::parse($unlinked_attendance_request['attendance_datetime_work_begin']);

                    if (
                        $attendance_datetime_work_begin_carbon >= $client_employee_time_to_judge_one_day_carbons[$client_employee_id]['from'] &&
                        $attendance_datetime_work_begin_carbon <= $client_employee_time_to_judge_one_day_carbons[$client_employee_id]['to']
                    ) {
                        // attendance_datetime_work_beginが個人ごとに設定されている勤怠を1日と判断する区切りの時間内であれば追加する
                        $unlinked_attendance_request['is_unlinked_attendance_request'] = true;
                        $unlinked_attendance_request['date_for_display'] = $unlinked_attendance_request['attendance_datetime_work_begin'];
                        $tmp_unlinked_attendance_requests[] = $unlinked_attendance_request;
                    }
                }

                break;
            case ($CONST_PAYROLL_USE_TIMING_LIST['USE_FINISHED_ATTENDANCE_DATETIME']): // 勤務終了時刻を基点とする
                $base_datetime_column = 'attendance_datetime_work_finish';

                // データベースから取得する申請データの期間
                $attendanceDateCarbon = Carbon::parse($attendanceDate);

                // 勤怠ヘッダに紐付いていない勤怠申請を指定日の１日分取得する
                $unlinked_attendance_requests = $this->objAttendanceRequestRepository->whereNull('attendance_header_id')
                    ->where('processed_flag', false)
                    ->where('request_canceled_at', null)
                    ->whereIn('client_employee_id', $clientEmployeeIds)
                    ->whereNotNull('attendance_datetime_work_finish')
                    ->whereDate('attendance_datetime_work_finish', $attendanceDateCarbon->format('Y-m-d'))
                    ->orderBy('created_at', 'ASC')
                    ->get()
                    ->toArray();

                foreach ($unlinked_attendance_requests as $unlinked_attendance_request) {
                    $unlinked_attendance_request['is_unlinked_attendance_request'] = true;
                    $unlinked_attendance_request['date_for_display'] = $unlinked_attendance_request['attendance_datetime_work_finish'];
                    $tmp_unlinked_attendance_requests[] = $unlinked_attendance_request;
                }

                break;
            default:
                throw \UnexpectedValueException("[全社員の日毎の勤怠、有給、勤怠備考、勤怠申請データ取得処理]client_trcd_settings.id:{$objClientTrcdSetting->id}のpayroll_use_timing_idに予期せぬ値が設定されています。");
        }

        // 勤怠ヘッダ、有給ヘッダ、勤怠備考データがマージされた配列と勤怠ヘッダに紐付いていない勤怠申請をマージ
        $attendance_rows = array_merge($attendance_rows, $tmp_unlinked_attendance_requests);

        return $attendance_rows;
    }
}
