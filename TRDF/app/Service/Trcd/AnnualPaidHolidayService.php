<?php

/***
 * 年次有給関連のサービス
 *
 * @author YuKaneko
 */

namespace App\Services\Trcd;

// Services
use App\Services\ServiceBase;

// Respositories
use App\Repositories\Trcd\AnnualPaidHolidaySummaryRepositoryInterface as AnnualPaidHolidaySummaryRepository;
use App\Repositories\Trcd\AnnualPaidHolidayRepositoryInterface as AnnualPaidHolidayRepository;
use App\Repositories\ClientEmployeeRepositoryInterface as ClientEmployeeRepository;
use App\Repositories\Trcd\AttendancePaidHolidayRepositoryInterface as AttendancePaidHolidayRepository;
use App\Repositories\Trcd\AttendanceHeaderRepositoryInterface as AttendanceHeaderRepository;
use App\Repositories\Trcd\PaidHolidayRepositoryInterface as PaidHolidayRepository;
// Models
use App\AnnualPaidHoliday;
use App\ClientEmployee;
use App\AttendancePaidHoliday;
// Utilities
use Carbon\Carbon;
use DB;

class AnnualPaidHolidayService extends ServiceBase
{
    protected $AnnualPaidHolidaySummaryRepo;
    protected $AnnualPaidHolidayRepo;
    protected $ClientEmployeeRepo;
    protected $AttendancePaidHolidayRepo;
    protected $AttendanceHeaderRepo;
    protected $PaidHolidayRepo;

    public function __construct(
        AnnualPaidHolidaySummaryRepository $AnnualPaidHolidaySummaryRepo,
        AnnualPaidHolidayRepository $AnnualPaidHolidayRepo,
        ClientEmployeeRepository $ClientEmployeeRepo,
        AttendancePaidHolidayRepository $AttendancePaidHolidayRepo,
        AttendanceHeaderRepository $AttendanceHeaderRepo,
        PaidHolidayRepository $PaidHolidayRepo
    ) {
        $this->AnnualPaidHolidaySummaryRepo = $AnnualPaidHolidaySummaryRepo;
        $this->AnnualPaidHolidayRepo = $AnnualPaidHolidayRepo;
        $this->ClientEmployeeRepo = $ClientEmployeeRepo;
        $this->AttendancePaidHolidayRepo = $AttendancePaidHolidayRepo;
        $this->AttendanceHeaderRepo = $AttendanceHeaderRepo;
        $this->PaidHolidayRepo = $PaidHolidayRepo;
    }

    /*
		年次有給休暇を更新する
		@param Integer $annual_paid_holiday_id
		@param Array $data_annual_paid_holiday

		@return false|AnnualPaidHoliday
	*/
    public function update($annual_paid_holiday_id, $data_annual_paid_holiday)
    {

        // @2020.11.12 テスト用に追加
        /*
        @param int $client_employee_id 対象社員ID
        @param string $date 取得したい日付
        @param int $paid_holiday_id 取得したい有給の種別ID
        */
        //$this->CanGetPaidHoliday(711, '2019-11-01', 6);

        $objAnnualPaidHoliday = $this->AnnualPaidHolidayRepo->find($annual_paid_holiday_id);
        $objAnnualPaidHolidaySummary = $objAnnualPaidHoliday->annual_paid_holiday_summary;

        // 該当年次有給休暇概要がない場合は例外を投げる
        if (empty($objAnnualPaidHolidaySummary)) throw new \InvalidArgumentException("年次有給休暇ID:{$annual_paid_holiday_id}に紐づく年次有給休暇概要が存在しません。");

        $objClientEmployee = $objAnnualPaidHolidaySummary->client_employee;

        // 該当社員がいない場合は例外を投げる
        if (empty($objClientEmployee)) throw new \InvalidArgumentException("年次有給概要ID:{$objAnnualPaidHolidaySummary->id}に紐づく社員が存在しません。");

        // 社員が所属する企業のTRCD設定情報取得
        $objClientTrcdSetting = $objClientEmployee->client->client_trcd_setting;

        // 企業TRCD設定が取得できない場合は例外を投げる
        if (empty($objClientTrcdSetting)) throw new \InvalidArgumentException("社員ID:{$client_employee_id}に紐づく企業TRCD設定がありません。");

        DB::beginTransaction();

        try {
            // 年次有給更新データの生成
            // 消化日数を取得する
            //$data_annual_paid_holiday['days_used'] = $this->getDaysUsed($objClientEmployee->id, $objClientEmployee->client_id, $data_annual_paid_holiday['base_date'], $data_annual_paid_holiday['next_base_date']);

            // 取得可能日数を計算する (付与日数 + 付加日数) - 消費日数
            //$data_annual_paid_holiday['usable_days'] = ( $data_annual_paid_holiday['days_granted'] + $data_annual_paid_holiday['days_added'] ) - $data_annual_paid_holiday['days_used'];
            $data_annual_paid_holiday['usable_days'] = $this->bcsub($this->bcadd($data_annual_paid_holiday['days_granted'], $data_annual_paid_holiday['days_added']), $data_annual_paid_holiday['days_used']);

            // 年次有給を更新
            $update_result_annual_paid_holiday = $this->AnnualPaidHolidayRepo->update($annual_paid_holiday_id, $data_annual_paid_holiday);

            if (empty($update_result_annual_paid_holiday)) throw new \Exception("年次有給休暇更新処理失敗。client_employee_id={$objClientEmployee->id}, annual_paid_holiday_summary_id={$objAnnualPaidHolidaySummary->id}, annual_paid_holiday_id={$annual_paid_holiday_id}");

            // 年次有給概要更新用データ生成（集計処理）
            $data_annual_paid_holiday_summary = $this->createAnnualPaidHolidaySummaryDate($objClientTrcdSetting->paid_holiday_carry_forward_days, $objAnnualPaidHolidaySummary->id, $objAnnualPaidHolidaySummary->last_base_date);

            // 年次有給概要を更新
            $update_result_annual_paid_holiday_summary = $this->AnnualPaidHolidaySummaryRepo->save($data_annual_paid_holiday_summary);

            if (empty($update_result_annual_paid_holiday_summary)) throw new \Exception("年次有給休暇概要更新処理失敗。client_employee_id={$objClientEmployee->id}, annual_paid_holiday_summary_id={$objAnnualPaidHolidaySummary->id}");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return false;
        }

        return true;
    }

    /*
        社員IDを受け取って、年次有給休暇を追加する関数
        @throws InvalidArgumentException
            存在しない社員ID
            入社日がnullの社員IDが渡されてきた時
            社員が所属する企業のTRCD設定情報が取得できない場合
            社員に紐づく年次有給概要がない場合
        @param Integer $client_employee_id
        @param Array $option
        
        @return bool
        @return false|Collection
    */
    public function updateByClientEmployeeId($client_employee_id, array $option = [])
    {
        $objClientEmployee = $this->ClientEmployeeRepo->find($client_employee_id);

        // 該当社員がいない場合は例外を投げる
        if (empty($objClientEmployee)) throw new \InvalidArgumentException("社員ID:{$client_employee_id}が存在しません。");

        // 入社日がnullの場合は例外を投げる
        if (empty($objClientEmployee['hire_date'])) throw new \InvalidArgumentException("社員ID:{$client_employee_id}の入社日がnullです。");

        // 社員が所属する企業のTRCD設定情報取得
        $objClientTrcdSetting = $objClientEmployee->client->client_trcd_setting;

        // 企業TRCD設定が取得できない場合は例外を投げる
        if (empty($objClientTrcdSetting)) throw new \InvalidArgumentException("社員ID:{$client_employee_id}に紐づく企業TRCD設定がありません。");

        // 年次有給概要取得
        $objAnnualPaidHolidaySummary = $objClientEmployee->annual_paid_holiday_summary;

        // 紐づく年次有給概要がない社員の場合は例外を投げる
        if (empty($objAnnualPaidHolidaySummary)) throw new \InvalidArgumentException("社員ID{$client_employee_id}に紐づく年次有給概要がありません。");

        // 入社日から最終基準日を算出する処理
        $now = now()->startOfDay();

        // 初回の基準日
        $first_base_date = Carbon::parse($objClientEmployee['hire_date'])->addMonthsNoOverflow(6)->startOfDay();

        // 最終の基準日を算出
        $last_base_date = $first_base_date->copy();
        $last_base_date->year = now()->format('Y');

        // 最終基準日が未来日付の場合は一年前に戻す
        if ($last_base_date->gt($now)) {
            $last_base_date->subYearNoOverflow();
        }

        $target_base_dates = [];

        // 時効分繰り返す
        $tmp_last_base_date = $last_base_date->copy();

        for ($i = 0; $i < $objClientTrcdSetting['paid_holiday_expiration']; $i++) {
            $target_base_dates[] = $tmp_last_base_date->format('Y-m-d');
            $tmp_last_base_date->subYearNoOverflow();
        }

        $ColAnnualPaidHolidays = collect();

        // ToDo:ロジックが複雑なためパフォーマンスは一旦無視してforeachで検索する。あとで直すこと
        foreach ($target_base_dates as $key => $target_base_date) {
            $tmpAnnualPaidHoliday = $this->AnnualPaidHolidayRepo
                ->where('annual_paid_holiday_summary_id', $objAnnualPaidHolidaySummary['id'])
                ->where('base_date', '<=', $target_base_date)
                //->where('next_base_date', '>=', $target_base_date)
                ->where('next_base_date', '>', $target_base_date)
                ->first();

            // 該当年次に既にデータがある場合
            if (!empty($tmpAnnualPaidHoliday)) {
                // データ追加
                $ColAnnualPaidHolidays->push($tmpAnnualPaidHoliday);
                // ターゲットから削除（新たに作成や更新はしない仕様）
                unset($target_base_dates[$key]);
            }
        }

        // 新たに追加する必要がない場合はここで処理終了
        if (empty($target_base_dates)) return true;

        DB::beginTransaction();

        try {
            // 年次有給データ作成処理
            foreach ($target_base_dates as $target_base_date) {
                // 社員の年次内の勤務日数算出用関数
                $days_worked = $this->calculateRangeWorkingDays($objClientEmployee, Carbon::parse($target_base_date));

                // 勤務日数から年次有給付与日数を算出する関数
                $days_granted = $this->calculateDaysGranted($objClientEmployee, $days_worked, Carbon::parse($target_base_date));
                // 年次有給休暇を更新するデータ
                $update_column_annual_paid_holiday = [
                    'annual_paid_holiday_summary_id' => $objAnnualPaidHolidaySummary['id'],
                    'base_date' => $target_base_date,
                    'next_base_date' => Carbon::parse($target_base_date)->addYear()->format('Y-m-d'), // 次の予定基準日
                    'days_worked' => $days_worked,
                    'days_granted' => $days_granted,
                    'days_used' => $this->getDaysUsed($objClientEmployee->id, $objClientEmployee->client_id, $target_base_date, Carbon::parse($target_base_date)->addYear()->format('Y-m-d')),
                    'expiration_date' => Carbon::parse($target_base_date)->addYears($objClientTrcdSetting['paid_holiday_expiration'])->format('Y-m-d'), // 有効期限
                ];

                //$update_column_annual_paid_holiday['usable_days'] = $update_column_annual_paid_holiday['days_granted'] - $update_column_annual_paid_holiday['days_used'];
                $update_column_annual_paid_holiday['usable_days'] = $this->bcsub($update_column_annual_paid_holiday['days_granted'], $update_column_annual_paid_holiday['days_used']);

                // 年次有給休暇を作成する関数
                $tmpAnnualPaidHoliday = $this->AnnualPaidHolidayRepo->create($update_column_annual_paid_holiday);

                if (!$tmpAnnualPaidHoliday) throw new \Exception("年次有給休暇作成処理失敗。client_employee_id={$client_employee_id}, target_base_date={$target_base_date}");

                // @2020.11.16 YuKaneko attendance_paid_holiday_metas作成
                $result_create_attendance_paid_holiday_metas = $this->CreateAttendancePaidHoidayMetasByAnnualPaidHoliday($tmpAnnualPaidHoliday);

                if (empty($result_create_attendance_paid_holiday_metas)) throw new \Exception("年次有給と勤怠有給との紐付けに失敗。client_employee_id={$client_employee_id}, target_base_date={$target_base_date}");

                // 作成された年次有給をコレクションに格納
                $ColAnnualPaidHolidays->push($tmpAnnualPaidHoliday);
            }

            // 年次有給コレクションが空の場合は例外を投げる（正常系ではあり得ない？）
            if ($ColAnnualPaidHolidays->isEmpty()) throw new \Exception("年次有給のデータがありません。");

            // 年次有給概要更新処理（年次有給データ集計処理）
            $ColAnnualPaidHolidays = $ColAnnualPaidHolidays->sortBy('base_date');
            // 直近年次有給データ
            //$LastAnnualPaidHoliday = $ColAnnualPaidHolidays[0];
            $LastAnnualPaidHoliday = $ColAnnualPaidHolidays->last();

            // 年次有給概要更新用データ
            $update_annual_paid_holiday_summary_data = [
                'id' => $objAnnualPaidHolidaySummary['id'],
                'last_base_date' => $LastAnnualPaidHoliday['base_date'],
                'next_base_date' => $LastAnnualPaidHoliday['next_base_date'],
                'days_granted' => 0.00,
                'days_used' => 0.00,
                'usable_days' => 0.00,
                'days_added' => 0.00,
            ];

            foreach ($ColAnnualPaidHolidays as $objAnnualPaidHoliday) {
                //$update_annual_paid_holiday_summary_data['days_granted'] += ($objAnnualPaidHoliday['days_granted'] + $objAnnualPaidHoliday['days_added']);
                //$update_annual_paid_holiday_summary_data['usable_days'] += $objAnnualPaidHoliday['usable_days'];
                //				$update_annual_paid_holiday_summary_data['days_granted'] += $objAnnualPaidHoliday['days_granted'];
                //				$update_annual_paid_holiday_summary_data['days_used'] += $objAnnualPaidHoliday['days_used'];
                //				$update_annual_paid_holiday_summary_data['days_added'] += $objAnnualPaidHoliday['days_added'];
                $update_annual_paid_holiday_summary_data['days_granted'] = $this->bcadd($update_annual_paid_holiday_summary_data['days_granted'], $objAnnualPaidHoliday['days_granted']);
                $update_annual_paid_holiday_summary_data['days_used'] = $this->bcadd($update_annual_paid_holiday_summary_data['days_used'], $objAnnualPaidHoliday['days_used']);
                $update_annual_paid_holiday_summary_data['days_added'] = $this->bcadd($update_annual_paid_holiday_summary_data['days_added'], $objAnnualPaidHoliday['days_added']);
            }

            // @baba 2020.11.11 有給付与日数上限・下限の設定。と、付随して取得可能な日数を算出する式を追加。
            //if ( $update_annual_paid_holiday_summary_data['days_granted'] <= 0 ) $update_annual_paid_holiday_summary_data['days_granted'] = 0;
            if ($this->bclte($update_annual_paid_holiday_summary_data['days_granted'], 0)) $update_annual_paid_holiday_summary_data['days_granted'] = 0;
            //else if ( $update_annual_paid_holiday_summary_data['days_granted'] >= 40 ) $update_annual_paid_holiday_summary_data['days_granted'] = 40;
            //else if ( $update_annual_paid_holiday_summary_data['days_granted'] >= $objClientTrcdSetting['paid_holiday_carry_forward_days'] ) $update_annual_paid_holiday_summary_data['days_granted'] = $objClientTrcdSetting['paid_holiday_carry_forward_days'];
            else if ($this->bcgte($update_annual_paid_holiday_summary_data['days_granted'], $objClientTrcdSetting['paid_holiday_carry_forward_days'])) $update_annual_paid_holiday_summary_data['days_granted'] = $objClientTrcdSetting['paid_holiday_carry_forward_days'];

            //$update_annual_paid_holiday_summary_data['usable_days'] = $update_annual_paid_holiday_summary_data['days_granted'] - $update_annual_paid_holiday_summary_data['days_used'];
            $update_annual_paid_holiday_summary_data['usable_days'] = $this->bcsub($this->bcadd($update_annual_paid_holiday_summary_data['days_granted'], $update_annual_paid_holiday_summary_data['days_added']),  $update_annual_paid_holiday_summary_data['days_used']);

            $objAnnualPaidHolidaySummary = $this->AnnualPaidHolidaySummaryRepo->save($update_annual_paid_holiday_summary_data);

            if (empty($objAnnualPaidHolidaySummary)) {
                throw new \Exception("年次有給休概要更新処理失敗。AnnualPaidHolidaySummary.id={$update_annual_paid_holiday_summary_data['id']}, client_employee_id={$client_employee_id}");
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
                社員の年次内の勤務日数算出用関数
                @param ClientEmployee $objClientEmployee 社員モデル
                @param Carbon $base_date
                @return Integer 勤務日数
            */
    private function calculateRangeWorkingDays(ClientEmployee $objClientEmployee, Carbon $base_date)
    {
        // 入社年月日
        $hire_date = Carbon::parse($objClientEmployee['hire_date'])->startOfDay();
        // 初回の基準日
        $first_base_date = $hire_date->copy()->addMonthsNoOverflow(6)->startOfDay();
        // 算出対象となる基準日
        $base_date = $base_date->startOfDay();

        // 基準日の月日と入社から半年後の月日が異なる場合は不正なデータ
        $strFirstBaseDate = $first_base_date->format('m-d');
        $strBaseDate = $base_date->format('m-d');

        if ($strFirstBaseDate !== $strBaseDate)  throw new \LogicException("初回基準日と算出対象基準日が異なる月日です。first={$strFirstBaseDate}, target={$strBaseDate}");

        if ($base_date->gt($first_base_date)) {
            // 入社から1年半経過している場合
            return $this->calculateWorkingDays(
                $objClientEmployee['id'],
                $base_date->copy()->subYearNoOverflow()->startOfDay(),
                $base_date->copy()->subDay()->endOfDay()
            );
        } else {
            // 入社 ~ 半年の場合
            return $this->calculateWorkingDays(
                $objClientEmployee['id'],
                $hire_date->copy()->startOfDay(),
                $base_date->copy()->subDay()->endOfDay()
            ) * 2;
        }
    }

    /*
                期間内の勤務日数と有給休暇の合計を算出する
                @param Int $client_employee_id 対象社員ID
                @param Carbon $worked_period_start Carbonを使って作成した勤務期間の起点
                @param Carbon $worked_period_end Carbonを使って作成した勤務期間の終点
        
                @return int 勤務日数
            */
    private function calculateWorkingDays($client_employee_id, Carbon $worked_period_start, Carbon $worked_period_end)
    {
        // 期間内の社員出勤データ
        $date_for_displays = $this->AttendanceHeaderRepo
            ->where('client_employee_id', $client_employee_id)
            ->whereBetween('date_for_display', [$worked_period_start->format('Y-m-d H:i:s'), $worked_period_end->format('Y-m-d H:i:s')])
            ->where('closing_flag', true)
            ->where('mismatch_flag', false)
            ->pluck('date_for_display');

        // 期間内の社員有給休暇データ
        $paid_holiday_dates = $this->AttendancePaidHolidayRepo
            ->where('client_employee_id', $client_employee_id)
            ->whereBetween('date', [$worked_period_start->format('Y-m-d'), $worked_period_end->format('Y-m-d')])
            ->pluck('date');

        $working_days = [];

        // 期間内の出勤日数を取得する
        foreach ($date_for_displays as $date_for_display) {
            $working_days[Carbon::parse($date_for_display)->format('Y-m-d')] = true;
        }

        // 期間内で取得した有給休暇を取得する
        foreach ($paid_holiday_dates as $paid_holiday_date) {
            $working_days[$paid_holiday_date] = true;
        }

        return count($working_days);
    }

    /*
                年次有給付与日数を算出する
                @param ClientEmployee $objClientEmployee
                @param Integer $working_days
                @param Carbon $base_date
        
                @return Integer 年次有給付与日数
            */
    private function calculateDaysGranted(ClientEmployee $objClientEmployee, $working_days, Carbon $base_date)
    {
        $hire_date = Carbon::parse($objClientEmployee['hire_date'])->startOfDay();
        $first_base_date = $hire_date->copy()->addMonthsNoOverflow(6)->startOfDay();

        // 勤続年数が6か月未満の場合、年次有給付与日数を算出しない。
        if ($base_date->lt($first_base_date))  return 0;

        // 勤務日数が48日未満の場合、年次有給付与日数を算出しない。
        if ($working_days < 48) return 0;

        $diffInMonth = $hire_date->diffInMonths($base_date);
        $days_granted = 0;

        // 内部関数 $num=勤務日数, $items=付与日数配列 @return 付与日数
        $inner_func = function ($num, array $items) {
            if ($num < 48) return 0;
            elseif (48 <= $num  && $num <= 72) return $items[0];
            elseif (73 <= $num  && $num <= 120) return $items[1];
            elseif (121 <= $num  && $num <= 168) return $items[2];
            elseif (169 <= $num  && $num <= 216) return $items[3];
            elseif (217 <= $num) return $items[4];
        };

        switch (true) {
            case ($diffInMonth < 6): // 半年未満
                break;
            case ($diffInMonth >= 6 && $diffInMonth < 18): // 半年 ~ 1年半
                $days_granted = $inner_func($working_days, [1, 3, 5, 7, 10]);
                break;
            case ($diffInMonth >= 18 && $diffInMonth < 30): // 1年半 ~ 2年半
                $days_granted = $inner_func($working_days, [2, 4, 6, 8, 11]);
                break;
            case ($diffInMonth >= 30 && $diffInMonth < 42): // 2年半 ~ 3年半
                $days_granted = $inner_func($working_days, [2, 4, 6, 9, 12]);
                break;
            case ($diffInMonth >= 42 && $diffInMonth < 54): // 3年半 ~ 4年半
                $days_granted = $inner_func($working_days, [2, 5, 8, 10, 14]);
                break;
            case ($diffInMonth >= 54 && $diffInMonth < 66): // 4年半 ~ 5年半
                $days_granted = $inner_func($working_days, [3, 6, 9, 12, 16]);
                break;
            case ($diffInMonth >= 66 && $diffInMonth < 78): // 5年半 ~ 6年半
                $days_granted = $inner_func($working_days, [3, 6, 10, 13, 18]);
                break;
            case ($diffInMonth >= 78): // 6年半 ~
                $days_granted = $inner_func($working_days, [3, 7, 11, 15, 20]);
                break;
            default:
                break;
        }

        return $days_granted;
    }

    /*
                指定期間の有給取得日数を取得
            */
    private function getDaysUsed($client_employee_id, $client_id, $base_date, $next_base_date)
    {
        // 社員の有給算出処理
        $paid_holiday_ids = $this->AttendancePaidHolidayRepo
            ->where('client_employee_id', $client_employee_id)
            ->where('date', '>=', $base_date)
            ->where('date', '<', $next_base_date)
            ->pluck('paid_holiday_id');

        // 論理削除済みの有給種別も取得
        $paid_holiday_rate = $this->PaidHolidayRepo
            ->where('client_id', $client_id)
            ->withTrashed()
            ->pluck('rate', 'id');

        $total_paid_holiday = 0;

        foreach ($paid_holiday_ids as $paid_holiday_id) {
            //$total_paid_holiday += $paid_holiday_rate[$paid_holiday_id];
            $total_paid_holiday = $this->bcadd($total_paid_holiday, $paid_holiday_rate[$paid_holiday_id]);
        }

        //$date['days_used'] = $total_paid_holiday;
        return $total_paid_holiday;
    }

    /*
                年次有給休暇概要の更新データ生成
            */
    private function createAnnualPaidHolidaySummaryDate($paid_holiday_carry_forward_days, $annual_paid_holiday_summary_id, $annual_paid_holiday_summary_last_base_date)
    {
        // 有効期限内のbase_date算出
        $base_date = Carbon::parse($annual_paid_holiday_summary_last_base_date)->subYearNoOverflow()->format('Y-m-d');

        // 算出する年次有給休暇の対象を発見する
        $calc_target_annual_paid_holiday = $this->AnnualPaidHolidayRepo
            ->where('annual_paid_holiday_summary_id', $annual_paid_holiday_summary_id)
            //->where('base_date', '>=', $base_date)
            ->where('expiration_date', '>', Carbon::now()->format('Y-m-d')) // @2020.11.17 有効期限内の年次有給を対象とする
            ->select('days_granted', 'days_used', 'days_added')
            ->get()
            ->toArray();

        $update_data = ['days_granted' => 0, 'days_used' => 0, 'days_added' => 0, 'usable_days' => 0];

        foreach ($calc_target_annual_paid_holiday as $annual_paid_holiday) {
            //			$update_data['days_granted'] += $annual_paid_holiday['days_granted'];
            //			$update_data['days_used'] += $annual_paid_holiday['days_used'];
            //			$update_data['days_added'] += $annual_paid_holiday['days_added'];
            $update_data['days_granted'] = $this->bcadd($update_data['days_granted'], $annual_paid_holiday['days_granted']);
            $update_data['days_used'] = $this->bcadd($update_data['days_used'], $annual_paid_holiday['days_used']);
            $update_data['days_added'] = $this->bcadd($update_data['days_added'], $annual_paid_holiday['days_added']);
        }

        // 付与日数を0～40で制限する。そのあとに、+でdays_addedを加算する。
        if ($this->bclte($update_data['days_granted'], 0)) $update_data['days_granted'] = 0;
        else if ($this->bcgte($update_data['days_granted'], $paid_holiday_carry_forward_days)) $update_data['days_granted'] = $paid_holiday_carry_forward_days;

        // 残日数は (付与日数 + 追加付与日数) - 消化日数
        $update_data['usable_days'] = $this->bcsub($this->bcadd($update_data['days_granted'], $update_data['days_added']), $update_data['days_used']);

        // 更新時にモデルをfindで取得するので、idを付ける
        $update_data = array_merge($update_data, ['id' => $annual_paid_holiday_summary_id]);

        return $update_data;
    }

    /*
                有給取得可能か検証
                @param int $client_employee_id 対象社員ID
                @param string $date 取得したい日付
                @param int $paid_holiday_id 取得したい有給の種別ID
                @param int $attendance_paid_holiday_id 取得済み有給データのID（更新時に渡す）
                @return Array [
                    'result' => bool,
                    'errors' => [],
                ]
    */
    public function ValidateIfCanGetPaidHoliday($client_employee_id, $date, $paid_holiday_id, $attendance_paid_holiday_id = null)
    {
        // 戻り値
        $return_values = [
            'result' => false,
            'errors' => [],
        ];

        // 取得日パース
        try {
            $date = Carbon::parse($date)->startOfDay();
        } catch (\Exception $e) {
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            $return_values['errors'][] = '取得日の値が不正です。';
            return $return_values;
        }

        // 社員
        $objClientEmployee = $this->ClientEmployeeRepo->find($client_employee_id);

        // 社員が存在しない場合
        if (empty($objClientEmployee)) {
            $return_values['errors'][] = '社員が見つかりません。';
            return $return_values;
        }

        // 年次有給概要
        $objAnnualPaidHolidaySummary = $objClientEmployee->annual_paid_holiday_summary;

        // 年次有給概要未設定
        if (empty($objAnnualPaidHolidaySummary)) {
            $return_values['errors'][] = '社員の年次有給概要が設定されていません。';
            return $return_values;
        }

        // 年次有給休暇
        $ColAnnualPaidHolidays = $objAnnualPaidHolidaySummary->annual_paid_holidays;

        // 年次有給が発行されていない場合
        if ($ColAnnualPaidHolidays->isEmpty()) {
            $return_values['errors'][] = '発行されている年次有給がないため取得できません。';
            return $return_values;
        }

        // 取得済み有給
        $oldAttendancePaidHoliday = null;

        // 取得済み有給がある場合のみ処理を行う
        if (!empty($attendance_paid_holiday_id)) {
            // 指定された社員・日付で登録されている取得済み有給か確認
            $oldAttendancePaidHoliday = $this->AttendancePaidHolidayRepo
                ->where('client_employee_id', $objClientEmployee['id'])
                ->where('date', $date->format('Y-m-d'))
                ->find($attendance_paid_holiday_id);

            if (empty($oldAttendancePaidHoliday)) {
                $return_values['errors'][] = '取得済み有給のIDが不正です。';
                return $return_values;
            }
        }

        // 有給種別IDと有給のレート（論理削除済み含む）
        $ColPaidHolidayRates = $this->PaidHolidayRepo
            ->where('client_id', $objClientEmployee['client_id'])
            ->withTrashed()
            ->pluck('rate', 'id');

        // 最古年次有給・最新年次有給抽出
        $oldestAnnualPaidHoliday = $ColAnnualPaidHolidays->sortBy('base_date')->first();
        $latestAnnualPaidHoliday = $ColAnnualPaidHolidays->sortByDesc('next_base_date')->first();

        // 最古年次有給（単体）の基準日よりも過去の日付であれば全てtrue
        if ($date->lt(Carbon::parse($oldestAnnualPaidHoliday['base_date']))) {
            $return_values['result'] = true;
            return $return_values;
        }

        // 最新年次有給（単体）の有効期限以降の日付であれば全てfalse
        if ($date->gte(Carbon::parse($latestAnnualPaidHoliday['expiration_date']))) {
            $return_values['errors'][] = '取得可能範囲を超えた日時が設定されています。';
            return $return_values;
        }


        // 管理範囲内の場合


        // レート取得
        if (!isset($ColPaidHolidayRates[$paid_holiday_id])) {
            $return_values['errors'][] = '有給レートの取得に失敗しました。';
            return $return_values;
        }

        $new_rate = $ColPaidHolidayRates[$paid_holiday_id];
        $old_rate = 0;

        // 既存の有給データがある場合は元のレートを取得
        if (!empty($oldAttendancePaidHoliday)) {
            // メタ情報の紐付けがなされていない場合
            if ($oldAttendancePaidHoliday->annual_paid_holidays->isEmpty()) {
                $return_values['errors'][] = 'メタ情報の取得に失敗しました。';
                return $return_values;
            }

            // 複数年度に跨って消費されている可能性があるため、走査して加算
            foreach ($oldAttendancePaidHoliday->annual_paid_holidays as $oldAnnualPaidHoliday) {
                $old_rate = $this->bcadd($old_rate, $oldAnnualPaidHoliday['meta']['days_used']);
            }
        }

        // レート差分算出（取得後、加算されるレート数）
        $rate = $this->bcsub($old_rate, $new_rate);

        // 更新した結果、有給利用数が増える場合はtrue
        if ($this->bcgte($rate, 0)) {
            $return_values['result'] = true;
            return $return_values;
        }


        // 有給のレートに対して残日数が足りていない場合はfalse
        // 基準日が申請日以前の年次有給（単体）のみ利用して計算。
        // 該当する（複数の）年次有給（単体）で残日数が足りていればtrue
        //$ColUsableDays = $ColAnnualPaidHolidays->pluck('usable_days', 'base_date');
        //$ColUsableDays = $ColAnnualPaidHolidays->keyBy('base_date');
        $total_usable_days = 0;

        /*
                foreach ( $ColUsableDays as $base_date => $usable_days ) {
                    // 基準日 <= 有給申請日 < 有効期限
                    if ( Carbon::parse($base_date)->lte($date) ) $total_usable_days = $this->bcadd($total_usable_days, $usable_days);
                }
        */
        foreach ($ColAnnualPaidHolidays->sortBy('base_date') as $tmpAnnualPaidHoliday) {
            // 基準日 <= 有給申請日 < 有効期限
            if (Carbon::parse($tmpAnnualPaidHoliday['base_date'])->lte($date) && $date->lt(Carbon::parse($tmpAnnualPaidHoliday['expiration_date']))) {
                $total_usable_days = $this->bcadd($total_usable_days, $tmpAnnualPaidHoliday['usable_days']);
            }
        }

        $total_usable_days = $this->bcadd($total_usable_days, $rate);

        if ($this->bcgte($total_usable_days, 0)) {
            // 申請後も残日数がマイナスになっていなければ取得可能
            $return_values['result'] = true;
            return $return_values;
        } else {
            // 申請後に残日数がマイナスになる場合は取得不可
            $return_values['errors'][] = '有給の残日数が足りないため取得できません。';
            return $return_values;
        }
    }
    /*
            public function CanGetPaidHoliday($client_employee_id, $date, $paid_holiday_id, $attendance_paid_holiday_id = null) {
                // 社員
                $clientEmployee = $this->ClientEmployeeRepo->find($client_employee_id);
        
                // 年次有給概要
                $annualPaidHolidaySummary = $clientEmployee->annual_paid_holiday_summary;
        
                // 年次有給休暇
                $annualPaidHolidays = $annualPaidHolidaySummary->annual_paid_holidays;
        
                // 取得済み有給の有給種別ID
                $old_paid_holiday_id = $this->AttendancePaidHolidayRepo->find($attendance_paid_holiday_id)->paid_holiday_id ?? null;
        
                // 有給種別IDと有給のレート
                $paid_holiday_rate = $this->PaidHolidayRepo
                    ->where('client_id', $clientEmployee->client_id)
                    ->withTrashed()
                    ->pluck('rate', 'id');
        
                // 年次有給休暇の最古基準日と最新の次の基準日
                $oldest_base_date = $annualPaidHolidays->pluck('base_date', 'base_date')->sortKeys()->first();
                $latest_next_base_date = $annualPaidHolidays->pluck('next_base_date', 'next_base_date')->sortKeysDesc()->first();
        
                // 最古年次有給（単体）の基準日よりも過去の日付であれば全てtrue
                if ( Carbon::parse($date)->lt(Carbon::parse($oldest_base_date)) ) return true;
        
                // 最新年次有給（単体）の次の基準日以後の日付であれば全てfalse
                if ( Carbon::parse($date)->gte(Carbon::parse($latest_next_base_date)) ) return false;
        // 管理範囲内の場合
        
        // 既存の有給データがある場合は元のレートと新しいレートの差分を計算しておく
        // new - old
        // 0.5 - 1 ... -0.5
        // 差分が0未満であればtrue
                if ( isset($paid_holiday_rate[$old_paid_holiday_id]) ) {
                    $sub_paid_holiday_rate = $paid_holiday_rate[$paid_holiday_id] - $paid_holiday_rate[$old_paid_holiday_id];
        
                    if ( $sub_paid_holiday_rate < 0 ) return true;
                }
        // 有給のレートに対して残日数が足りていない場合はfalse
        // 基準日が申請日以前の年次有給（単体）のみ利用して計算。該当する（複数の）年次有給（単体）で残日数が足りていればtrue
                $col_usable_days = $annualPaidHolidays->pluck('usable_days', 'base_date');
                $total_usable_days = 0;
        
                foreach ( $col_usable_days as $base_date => $usable_days ) {
                    if ( Carbon::parse($date)->lt(Carbon::parse($base_date)) ) $total_usable_days += $usable_days;
                }
        
                if ( $total_usable_days - $paid_holiday_rate[$paid_holiday_id] >= 0 ) return true;
                else return false;
        
                // testのため常にfalse
                return false;
            }
        */

    /*
        勤怠有給IDを元に、
        メタ情報・年次有給・年次有給概要を更新
        @param int $attendance_paid_holiday_id
        @return bool
    */
    public function updateByAttendancePaidHolidayId($attendance_paid_holiday_id)
    {
        $AttendancePaidHoliday = $this->AttendancePaidHolidayRepo->find($attendance_paid_holiday_id);

        // 対象データがない場合
        if (empty($AttendancePaidHoliday)) {
            logger()->error("attendance_paid_holiday_id={$attendance_paid_holiday_id} does not exist. in " . __METHOD__ . ":" . __LINE__);
            return false;
        }

        // 有給取得日
        $date = Carbon::parse($AttendancePaidHoliday['date']);
        // 更新レート値
        $new_rate = $AttendancePaidHoliday->paid_holiday['rate'];
        // 年次有給概要
        $AnnualPaidHolidaySummary = $AttendancePaidHoliday->client_employee->annual_paid_holiday_summary;
        // 年次有給
        $ColAnnualPaidHolidays = $AnnualPaidHolidaySummary->annual_paid_holidays()
            ->select(['id', 'base_date', 'expiration_date', 'days_used', 'usable_days'])
            ->orderBy('base_date')
            ->get()
            ->keyBy('id');


        DB::beginTransaction();

        try {
            // 古いメタ情報がある場合は取得
            $ColOldAnnualPaidHolidays = $AttendancePaidHoliday->annual_paid_holidays;

            if (!$ColOldAnnualPaidHolidays->isEmpty()) {
                // 走査し、消化日数・消化日数を元に戻す
                foreach ($ColOldAnnualPaidHolidays as $objOldAnnualPaidHoliday) {
                    $days_used = $objOldAnnualPaidHoliday['meta']['days_used'];

                    if (!isset($ColAnnualPaidHolidays[$objOldAnnualPaidHoliday['id']])) throw new \Exception("AnnualPaidHoliday.id={$objOldAnnualPaidHoliday} does not exist.");

                    $ColAnnualPaidHolidays[$objOldAnnualPaidHoliday['id']]['days_used'] = $this->bcsub($ColAnnualPaidHolidays[$objOldAnnualPaidHoliday['id']]['days_used'], $days_used);
                    $ColAnnualPaidHolidays[$objOldAnnualPaidHoliday['id']]['usable_days'] = $this->bcadd($ColAnnualPaidHolidays[$objOldAnnualPaidHoliday['id']]['usable_days'], $days_used);
                }
            }

            // 消費日数算出用
            $calcDaysUsed = function ($usable, $rate) {
                $usable = $this->bcsub($usable, $rate);

                if ($this->bcgte($usable, 0)) return $rate;
                else return $this->bcsub($rate, abs($usable));
            };
            // 更新用メタデータ格納配列
            $meta_data = [];
            $hasDone = false;

            // 年次有給更新処理
            foreach ($ColAnnualPaidHolidays as $annual_paid_holiday_id => $objAnnualPaidHoliday) {
                // 基準日 <= 取得日 < 有効期限 ではない場合はスキップ
                if (!($date->gte(Carbon::parse($objAnnualPaidHoliday['base_date'])) && $date->lt(Carbon::parse($objAnnualPaidHoliday['expiration_date'])))) continue;

                // 消費日数算出
                $days_used = $calcDaysUsed($objAnnualPaidHoliday['usable_days'], $new_rate);
                $new_rate = $this->bcsub($new_rate, $days_used);

                $objAnnualPaidHoliday['days_used'] = $this->bcadd($objAnnualPaidHoliday['days_used'], $days_used);
                $objAnnualPaidHoliday['usable_days'] = $this->bcsub($objAnnualPaidHoliday['usable_days'], $days_used);

                // 割り振りが住んでいなければ処理
                if (!$hasDone) $meta_data[$objAnnualPaidHoliday['id']] = ['days_used' => $days_used];

                // 更新されていれば年次有給更新
                if ($objAnnualPaidHoliday->isDirty()) {
                    $update_result = $this->AnnualPaidHolidayRepo->update($objAnnualPaidHoliday['id'], $objAnnualPaidHoliday->toArray());

                    if (empty($update_result)) throw new \Exception("年次有給休暇[ID:{$AttendancePaidHoliday['id']}]の更新に失敗しました。");
                }

                // 割り振り終わったらフラグ立て
                if (!$hasDone && $this->bclte($new_rate, 0)) $hasDone = true;
            }

            // 消費しすぎた場合（発生するのはおかしい）
            if ($this->bclt($new_rate, 0)) throw new \UnexpectedValueException("有給レート値がマイナスになっています。{$new_rate}");

            // メタデータ更新
            try {
                $sync_result = $AttendancePaidHoliday->annual_paid_holidays()->sync($meta_data);
            } catch (\Exception $e) {
                throw new \Exception("勤怠有給メタデータの同期に失敗しました。AttendancePaidHoliday.id={$AttendancePaidHoliday['id']}");
            }

            // 年次有給概要更新処理
            $update_result = $this->AnnualPaidHolidaySummaryRepo->save($this->createAnnualPaidHolidaySummaryDate(
                $AttendancePaidHoliday->client_employee->client->client_trcd_setting['paid_holiday_carry_forward_days'],
                $AnnualPaidHolidaySummary['id'],
                $AnnualPaidHolidaySummary['last_base_date']
            ));

            if (empty($update_result)) throw new \Exception("年次有給概要[ID:{$AnnualPaidHolidaySummary['id']}]の更新に失敗しました。");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return false;
        }

        return true;
    }

    /*
                年次有給と勤怠有給の紐付け
                @param AnnualPaidHoliday $AnnualPaidHoliday
                @return bool
            */
    protected function CreateAttendancePaidHoidayMetasByAnnualPaidHoliday(AnnualPaidHoliday $AnnualPaidHoliday)
    {
        try {
            // 社員の該当年次の取得済み有給（attendance_paid_holiday_metasがないもの）
            $ColAttendancePaidHolidays = $this->AttendancePaidHolidayRepo
                ->where('client_employee_id', $AnnualPaidHoliday->annual_paid_holiday_summary->client_employee->id)
                ->where('date', '>=', $AnnualPaidHoliday['base_date'])
                ->where('date', '<', $AnnualPaidHoliday['next_base_date'])
                ->doesntHave('annual_paid_holidays')
                ->get()
                ->keyBy('id');

            // 有給種別取得（論理削除済み含む）
            $ColPaidHolidayRates = $this->PaidHolidayRepo
                ->whereIn('id', $ColAttendancePaidHolidays->pluck('paid_holiday_id'))
                ->withTrashed()
                ->pluck('rate', 'id');

            $sync_data = [];

            foreach ($ColAttendancePaidHolidays as $attendance_paid_holiday_id => $objAttendancePaidHoliday) {
                $sync_data[$attendance_paid_holiday_id] = [
                    'days_used' => $ColPaidHolidayRates[$objAttendancePaidHoliday['paid_holiday_id']],
                ];
            }

            DB::beginTransaction();

            try {
                $AnnualPaidHoliday->attendance_paid_holidays()->sync($sync_data);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine}");
                throw new \Exception("年次有給[ID:{$AnnualPaidHoliday['id']}]と勤怠有給との紐付けに失敗しました。");
            }
        } catch (\Exception $e) {
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return false;
        }

        return true;
    }

    /*
                勤怠有給削除時の
                メタ情報削除・年次有給・年次有給概要を更新
                @param int $attendance_paid_holiday_id
                @return bool
            */
    public function detachByAttendancePaidHolidayId($attendance_paid_holiday_id)
    {
        $AttendancePaidHoliday = $this->AttendancePaidHolidayRepo->find($attendance_paid_holiday_id);

        // 対象データがない場合
        if (empty($AttendancePaidHoliday)) {
            logger()->error("attendance_paid_holiday_id={$attendance_paid_holiday_id} does not exist. in " . __METHOD__ . ":" . __LINE__);
            return false;
        }

        // 年次有給概要
        $AnnualPaidHolidaySummary = $AttendancePaidHoliday->client_employee->annual_paid_holiday_summary;
        // 勤怠有給に紐づいている年次有給
        $ColAnnualPaidHolidays = $AttendancePaidHoliday->annual_paid_holidays;

        // 紐づいている年次有給がなければ何もしない
        if ($ColAnnualPaidHolidays->isEmpty()) return true;

        DB::beginTransaction();

        try {
            // 走査し、消化日数・消化日数を元に戻す
            foreach ($ColAnnualPaidHolidays as $key => $objAnnualPaidHoliday) {
                $days_used = $objAnnualPaidHoliday['meta']['days_used'];

                $objAnnualPaidHoliday['days_used'] = $this->bcsub($objAnnualPaidHoliday['days_used'], $days_used);
                $objAnnualPaidHoliday['usable_days'] = $this->bcadd($objAnnualPaidHoliday['usable_days'], $days_used);
                $update_result = $this->AnnualPaidHolidayRepo->update($objAnnualPaidHoliday['id'], $objAnnualPaidHoliday->toArray());

                if (empty($update_result)) throw new \Exception("年次有給休暇[ID:{$AttendancePaidHoliday['id']}]の更新に失敗しました。");
            }

            // メタデータ削除
            try {
                $sync_result = $AttendancePaidHoliday->annual_paid_holidays()->detach();
            } catch (\Exception $e) {
                throw new \Exception("勤怠有給メタデータの削除に失敗しました。AttendancePaidHoliday.id={$AttendancePaidHoliday['id']}");
            }

            // 年次有給概要更新処理
            $update_result = $this->AnnualPaidHolidaySummaryRepo->save($this->createAnnualPaidHolidaySummaryDate(
                $AttendancePaidHoliday->client_employee->client->client_trcd_setting['paid_holiday_carry_forward_days'],
                $AnnualPaidHolidaySummary['id'],
                $AnnualPaidHolidaySummary['last_base_date']
            ));

            if (empty($update_result)) throw new \Exception("年次有給概要[ID:{$AnnualPaidHolidaySummary['id']}]の更新に失敗しました。");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            return false;
        }

        return true;
    }
    /*
                任意精度の数値を加算
                @param string $left
                @param stirng $right
                @param int $scale 小数点以下桁数
                @return string
            */
    private function bcadd($left, $right, $scale = 2)
    {
        return bcadd($left, $right, $scale);
    }

    /*
                任意精度の数値を減算
                @param string $left
                @param stirng $right
                @param int $scale 小数点以下桁数
                @return string
            */
    private function bcsub($left, $right, $scale = 2)
    {
        return bcsub($left, $right, $scale);
    }

    /*
                任意精度の数値を比較 $left >= $right
                @param string $left
                @param stirng $right
                @param int $scale 小数点以下桁数
                @return bool
            */
    private function bcgte($left, $right, $scale = 2)
    {
        return bccomp($left, $right, $scale) >= 0;
    }

    /*
                任意精度の数値を比較 $left <= $right
                @param string $left
                @param stirng $right
                @param int $scale 小数点以下桁数
                @return bool
            */
    private function bclte($left, $right, $scale = 2)
    {
        return bccomp($left, $right, $scale) <= 0;
    }

    /*
                任意精度の数値を比較 $left < $right
                @param string $left
                @param stirng $right
                @param int $scale 小数点以下桁数
                @return bool
            */
    private function bclt($left, $right, $scale = 2)
    {
        return bccomp($left, $right, $scale) < 0;
    }

    /*
                クーロンによる新規年次有給発行処理用
                @param Carbon $execDate 実行時刻
                @return void
            */
    public function CreateNewAnnualPaidHoliday(Carbon $execDate)
    {
        logger()->info("Run " . __METHOD__ . " \$execDate=" . $execDate->format('Y-m-d H:i:s'));
        // 実行日 00:00:00に補完しておく
        $execDate->startOfDay();

        // 実行日から半年前（基準日）を算出
        $base_date = $execDate->copy()->subMonthsNoOverflow(6)->format('m-d');
        logger()->info("基準日：{$base_date}");

        // 入社日が設定されていて、
        // 退職していない or 退職日が現在より後に設定され、
        // 実行日が基準日となる社員を検索
        $col_client_employee_ids = $this->ClientEmployeeRepo
            ->whereNotNull('hire_date')
            ->where(function ($query) use ($execDate) {
                $query->whereNull('retirement_date')
                    ->orWhere('retirement_date', '>', $execDate->format('Y-m-d'));
            })
            ->where('hire_date', 'like', "%{$base_date}")
            ->pluck('id');
        logger()->info("該当社員ID:", $col_client_employee_ids->toArray());

        // 検索した社員をフィルタリング
        // 年次有給概要のnext_base_dateが未設定 or next_base_date <= 実行年月日
        $col_client_employee_ids = $this->AnnualPaidHolidaySummaryRepo
            ->whereIn('client_employee_id', $col_client_employee_ids)
            ->where(function ($query) use ($execDate) {
                $query->whereNull('next_base_date')
                    ->orWhere('next_base_date', '<=', $execDate->format('Y-m-d'));
            })
            ->pluck('client_employee_id');
        logger()->info("フィルタリング後該当社員ID:", $col_client_employee_ids->toArray());

        /*
                // next_base_dateと現在の年月日を比較し、同じならその社員IDを取得
                $col_client_employee_ids = $this->AnnualPaidHolidaySummaryRepo
                    ->where('next_base_date', '=', $execDate->format('Y-m-d'))
                    //->where('next_base_date', '=', "2021-11-17")//$execDate->format('Y-m-d'))  テスト時のサンプルデータ
                    ->pluck('client_employee_id');
        
                // 社員に入社日が設定されていて、退職していない社員or退職日が現在より後に設定してある社員IDを取得
                $col_client_employee_ids = $this->ClientEmployeeRepo
                    ->whereIn('id', $col_client_employee_ids)
                    ->whereNotNull('hire_date')
                    ->where(function($query) use($execDate) {
                        $query->whereNull('retirement_date')
                            ->orWhere('retirement_date', '>', $execDate->format('Y-m-d'));
                    })
                    ->pluck('id');
        */

        // 処理開始
        foreach ($col_client_employee_ids as $client_employee_id) {
            DB::beginTransaction();

            try {
                // 年次有給発行処理
                $result = $this->updateByClientEmployeeId($client_employee_id);

                if (empty($result)) throw new \Exception("失敗");

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                logger()->error("新規年次有給発行処理に失敗。client_employee_id={$client_employee_id}");
                logger()->error($e);
            }
        }
    }

    /*
                クーロンによる新規年次有給発行処理用
                @param Carbon $execDate 実行時刻
                @return void
        */
        public function CreateNewAnnualPaidHoliday(Carbon $execDate) {
            logger()->info("Run " . __METHOD__ . " \$execDate=" . $execDate->format('Y-m-d H:i:s'));
            // 実行日 00:00:00に補完しておく
            $execDate->startOfDay();

            // 実行日から半年前（基準日）を算出
            $base_date = $execDate->copy()->subMonthsNoOverflow(6)->format('m-d');
            logger()->info("基準日：{$base_date}");

            // 入社日が設定されていて、
            // 退職していない or 退職日が現在より後に設定され、
            // 実行日が基準日となる社員を検索
            $col_client_employee_ids = $this->ClientEmployeeRepo
                    ->whereNotNull('hire_date')
                    ->where(function($query) use($execDate) {
                            $query->whereNull('retirement_date')
                                    ->orWhere('retirement_date', '>', $execDate->format('Y-m-d'));
                    })
                    ->where('hire_date', 'like', "%{$base_date}")
                    ->pluck('id');
            logger()->info("該当社員ID:", $col_client_employee_ids->toArray());

            // 検索した社員をフィルタリング
            // 年次有給概要のnext_base_dateが未設定 or next_base_date <= 実行年月日
            $col_client_employee_ids = $this->AnnualPaidHolidaySummaryRepo
                    ->whereIn('client_employee_id', $col_client_employee_ids)
                    ->where(function($query) use($execDate) {
                            $query->whereNull('next_base_date')
                                    ->orWhere('next_base_date', '<=', $execDate->format('Y-m-d'));
                    })
                    ->pluck('client_employee_id');
            logger()->info("フィルタリング後該当社員ID:", $col_client_employee_ids->toArray());

            // next_base_dateと現在の年月日を比較し、同じならその社員IDを取得
            $col_client_employee_ids = $this->AnnualPaidHolidaySummaryRepo
                    ->where('next_base_date', '=', $execDate->format('Y-m-d'))
                    //->where('next_base_date', '=', "2021-11-17")//$execDate->format('Y-m-d'))  テスト時のサンプルデータ
                    ->pluck('client_employee_id');
             // 処理開始
                foreach ($col_client_employee_ids as $client_employee_id) {
                        DB::beginTransaction();

                        try {
                                // 年次有給発行処理
                                $result = $this->updateValidAnnualPaidHolidays($client_employee_id);

                                if ( empty($result) ) throw new \Exception("失敗");

                                DB::commit();
                        } catch( \Exception $e ) {
                                DB::rollBack();
                                logger()->error("新規年次有給発行処理に失敗。client_employee_id={$client_employee_id}");
                                logger()->error($e);
                        }
                }
        
            private function updateValidAnnualPaidHolidays($client_employee_id, array $option = []) {
                    $objClientEmployee = $this->ClientEmployeeRepo->find($client_employee_id);
    
                    // 該当社員がいない場合は例外を投げる
                    if (empty($objClientEmployee)) throw new \InvalidArgumentException("社員ID:{$client_employee_id}が存在しません。");
    
                    // 入社日がnullの場合は例外を投げる
                    if (empty($objClientEmployee['hire_date'])) throw new \InvalidArgumentException("社員ID:{$client_employee_id}の入社日がnullです
    。");
    
                    // 社員が所属する企業のTRCD設定情報取得
                    $objClientTrcdSetting = $objClientEmployee->client->client_trcd_setting;
    
                    // 企業TRCD設定が取得できない場合は例外を投げる
                    if (empty($objClientTrcdSetting)) throw new \InvalidArgumentException("社員ID:{$client_employee_id}に紐づく企業TRCD設定がありま
    せん。");
    
                    // 年次有給概要取得
                    $objAnnualPaidHolidaySummary = $objClientEmployee->annual_paid_holiday_summary;
    
                    // 紐づく年次有給概要がない社員の場合は例外を投げる
                    if (empty($objAnnualPaidHolidaySummary)) throw new \InvalidArgumentException("社員ID{$client_employee_id}に紐づく年次有給概要が
    ありません。");
    
                    // 入社日から最終基準日を算出する処理
                    $now = now()->startOfDay();
    
                    // 初回の基準日
                    $first_base_date = Carbon::parse($objClientEmployee['hire_date'])->addMonthsNoOverflow(6)->startOfDay();
    
                    // 最終の基準日を算出
                    $last_base_date = $first_base_date->copy();
                    $last_base_date->year = $now->format('Y');
    
                    // 最終取得日が今日以降か確認(クローン処理では今日が基準日の従業員idしか入ってこないからありえない?)
                    if(!$now->gte($last_base_date)) throw new \InvalidArgumentException("社員ID:{$client_employee_id}は新規有給休暇取得対象者ではあ
    りません");
    
                    // 年次有給休暇に最新区分の有給が追加されていないことを確認
                    $tmpAnnualPaidHoliday = $this->AnnualPaidHolidayRepo
                            ->where('annual_paid_holiday_summary_id', $objAnnualPaidHolidaySummary['id'])
                            ->where('base_date', '<=', $last_base_date->format('Y-m-d'))
                            ->where('next_base_date', '>', $last_base_date->format('Y-m-d'))
                            ->first();
                    if (!empty($tmpAnnualPaidHoliday)) throw new \InvalidArgumentException("社員ID:{$client_employee_id}は既に最新の年次有給を取得>しています。");
    
                    DB::beginTransaction();
    
                    try{

                        // 最新の年次有給の基準日
                        $target_base_date = $last_base_date->format('Y-m-d');

                        // 社員の年次内の勤務日数算出用関数
                        $days_worked = $this->calculateRangeWorkingDays($objClientEmployee, Carbon::parse($target_base_date));

                        // 勤務日数から年次有給付与日数を算出する関数
                        $days_granted = $this->calculateDaysGranted($objClientEmployee, $days_worked, Carbon::parse($target_base_date));

                        $create_new_column_annual_paid_holiday = [
                                'annual_paid_holiday_summary_id' => $objAnnualPaidHolidaySummary['id'],
                                'base_date' => $target_base_date,
                                'next_base_date' => Carbon::parse($target_base_date)->addYear()->format('Y-m-d'), // 次の予定基準日
                                'days_worked' => $days_worked,
                                'days_granted' => $days_granted,
                                'days_used' => 0, // 年次有給新規作成のため使われている有給消費日数は0
                                'expiration_date' => Carbon::parse($target_base_date)->addYears($objClientTrcdSetting['paid_holiday_expiration'])->format('Y-m-d'), // 有効期限
                        ];

                        // 残有給数の算出
                        $create_new_column_annual_paid_holiday['usable_days'] = $this->bcsub($create_new_column_annual_paid_holiday['days_granted'], $create_new_column_annual_paid_holiday['days_used']);

                        // 年次有給休暇を作成する関数
                        $LatestAnnualPaidHoliday = $this->AnnualPaidHolidayRepo->create($create_new_column_annual_paid_holiday);

                        if (!$LatestAnnualPaidHoliday) throw new \Exception("新規年次有給休暇作成処理失敗。client_employee_id={$client_employee_id}, target_base_date={$target_base_date}");

                        // 年次有給概要更新用データ
                        $update_annual_paid_holiday_summary_data = [
                                'id' => $objAnnualPaidHolidaySummary['id'],
                                'last_base_date' => $LatestAnnualPaidHoliday['base_date'],
                                'next_base_date' => $LatestAnnualPaidHoliday['next_base_date'],
                                'days_granted' => 0.00,
                                'days_used' => 0.00,
                                'usable_days' => 0.00,
                                'days_added' => 0.00,
                        ];

                        // 有給概要に関与する年次有給休暇取得(最新の年次有給から持ち越し年数のデータを持ってくる)
                        $ColAnnualPaidHolidays = $this->AnnualPaidHolidayRepo
                                ->where('annual_paid_holiday_summary_id', $objAnnualPaidHolidaySummary['id'])
                                ->orderBy('base_date', 'desc')
                                ->limit($objClientTrcdSetting['paid_holiday_expiration'])
                                ->get();
                         // 概要の各種有給データの更新
                         foreach ($ColAnnualPaidHolidays as $objAnnualPaidHoliday) {
                            $update_annual_paid_holiday_summary_data['days_granted'] = $this->bcadd($update_annual_paid_holiday_summary_data['days_granted'], $objAnnualPaidHoliday['days_granted']);
                            $update_annual_paid_holiday_summary_data['days_used'] = $this->bcadd($update_annual_paid_holiday_summary_data['days_used'], $objAnnualPaidHoliday['days_used']);
                            $update_annual_paid_holiday_summary_data['days_added'] = $this->bcadd($update_annual_paid_holiday_summary_data['days_added'], $objAnnualPaidHoliday['days_added']);
                    }

                    $objAnnualPaidHolidaySummary = $this->AnnualPaidHolidaySummaryRepo->save($update_annual_paid_holiday_summary_data);

                    if (empty($objAnnualPaidHolidaySummary)) {
                            throw new \Exception("年次有給休概要更新処理失敗。AnnualPaidHolidaySummary.id={$update_annual_paid_holiday_summary_data['id']}, client_employee_id={$client_employee_id}");
                    }

                    DB::commit();
            } catch (\Exception $e) {
                    DB::rollBack();
                    logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
                    return false;
            }

            return true;
}
