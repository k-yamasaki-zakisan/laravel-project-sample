<?php

/***
 * 社員サービス
 *
 * @author T.Ando
 */

namespace App\Services;

use DB;
use Illuminate\Http\Request;

// Model
use App\ClientEmployee;

// Services
use App\Services\ServiceBase;
use App\Services\Trcd\TrcdService;
use App\Services\Trcd\AnnualPaidHolidayService;

// Repositories
use App\Repositories\ClientEmployeeRepositoryInterface as ClientEmployeeRepository;
use App\Repositories\Trcd\ClientEmployeeTrcdSettingRepositoryInterface as ClientEmployeeTrcdSettingRepository;
use App\Repositories\Trcd\ClientTrcdSettingRepositoryInterface as ClientTrcdSettingRepository;
use App\Repositories\AttendancePatternRepositoryInterface as AttendancePatternRepository;
use App\Repositories\Trcd\AnnualPaidHolidaySummaryRepositoryInterface as AnnualPaidHolidaySummaryRepository; // @baba 2020.10.19 年次有給休暇概要テーブル

// Supports
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ClientEmployeeService extends ServiceBase
{
    protected $objClientEmployeeRepository;
    protected $objAnnualPaidHolidayService;

    public function __construct(
        ClientEmployeeRepository $objClientEmployeeRepository,
        ClientEmployeeTrcdSettingRepository $objClientEmployeeTrcdSettingRepository,
        ClientTrcdSettingRepository $objClientTrcdSettingRepository,
        AttendancePatternRepository $objAttendancePatternRepository,
        AnnualPaidHolidaySummaryRepository $objAnnualPaidHolidaySummaryRepository,
        AnnualPaidHolidayService $objAnnualPaidHolidayService
    ) {
        $this->objClientEmployeeRepository = $objClientEmployeeRepository;
        $this->objClientEmployeeTrcdSettingRepository = $objClientEmployeeTrcdSettingRepository;
        $this->objClientTrcdSettingRepository = $objClientTrcdSettingRepository;
        $this->objAttendancePatternRepository = $objAttendancePatternRepository;
        $this->objAnnualPaidHolidaySummaryRepository = $objAnnualPaidHolidaySummaryRepository;
        $this->objAnnualPaidHolidayService = $objAnnualPaidHolidayService;
    }

    /*
		クエリビルダ生成
	*/
    public function getQuery()
    {
        return $this->objClientEmployeeRepository->query();
    }

    /*
		SuperAdmin判定
	*/
    protected function isSuperAdmin()
    {
        $loginUser = auth()->user();
        return Str::endsWith(get_class($loginUser), 'Superadmin');
    }

    /**
     * クライアントIDを条件に全てのデータを取得する
     */
    public function getByClientId($client_id, $options = array())
    {
        return $this->objClientEmployeeRepository->getByClientId($client_id, $options);
    }

    /*
		IDを指定して取得
	*/
    public function getById($client_employee_id, $options = array())
    {
        return $this->objClientEmployeeRepository->getById($client_employee_id, $options);
    }

    /*
	 * auth_keyを指定して取得
	 */
    public function getByAuthKey($auth_key, $options = array())
    {
        if (empty($auth_key)) {
            Log::error(__METHOD__ . "(): 空のclient_employees.auth_keyが渡されました。 in " . __FILE__ . " on line " . __LINE__);
            return null;
        }
        return $this->objClientEmployeeRepository->getByAuthKey($auth_key, $options);
    }




    /**
     * 前払い携帯を取得する。
     * client_employee_trcd_settings.additional_amount_type_id が設定されていなければ
     * client_trcd_settings.additional_amount_type_id を取得し、返す。
     */
    public function getAdditionalConstAmountTypeId($client_employee_id)
    {
        // 個人設定があればそれを取得して返す。
        $setting = $this->objClientEmployeeTrcdSettingRepository->getById($client_employee_id);
        if ($setting != null && is_numeric($setting->additional_amount_type_id)) {
            return $setting->additional_amount_type_id;
        }

        // 個人設定がなければクライアントごとの設定を取得する
        //$setting = $this->objClientTrcdSettingRepository->getByClientId($client_employee_id);
        // @YuKaneko 2019/07/25 ↑ 企業IDでなく社員IDがわたされていたため下記に修正
        $objClientEmployee = $this->getById($client_employee_id);
        if (is_null($objClientEmployee)) return null;

        $setting = $this->objClientTrcdSettingRepository->getByClientId($objClientEmployee->client_id);
        if ($setting != null && $setting->additional_amount_type_id) {
            return $setting->additional_amount_type_id;
        }

        return null;
    }

    /**
     * １日働いた時の前払い残高加算額(固定)を取得する。
     * client_employee_trcd_settings.additional_const_ammount_on_one_dayが設定されていなければ
     * client_trcd_settings.additional_const_ammount_on_one_day を取得し、返す。
     */
    public function getAdditionalConstAmountOnOneDay($client_employee_id)
    {
        // 個人設定があればそれを取得して返す。
        $setting = $this->objClientEmployeeTrcdSettingRepository->getById($client_employee_id);
        if ($setting != null && is_numeric($setting->additional_const_ammount_on_one_day)) {
            return $setting->additional_const_ammount_on_one_day;
        }

        // 個人設定がなければクライアントごとの設定を取得する
        //$setting = $this->objClientTrcdSettingRepository->getByClientId($client_employee_id);
        // @YuKaneko 2019/07/25 ↑ 企業IDでなく社員IDがわたされていたため下記に修正
        $objClientEmployee = $this->getById($client_employee_id);
        if (is_null($objClientEmployee)) return null;

        $setting = $this->objClientTrcdSettingRepository->getByClientId($objClientEmployee->client_id);
        if ($setting != null && $setting->additional_const_ammount_on_one_day) {
            return $setting->additional_const_ammount_on_one_day;
        }

        return null;
    }

    /**
     * １時間 働いた時の前払い残高加算額(固定)を取得する。
     * client_employee_trcd_settings.additional_const_ammount_on_one_hourが設定されていなければ
     * client_trcd_settings.additional_const_ammount_on_one_hour を取得し、返す。
     */
    public function getAdditionalConstAmountOnOneHour($client_employee_id)
    {
        // 個人設定があればそれを取得して返す。
        $setting = $this->objClientEmployeeTrcdSettingRepository->getById($client_employee_id);
        if ($setting != null && is_numeric($setting->additional_const_amount_on_one_hour)) {
            return $setting->additional_const_amount_on_one_hour;
        }

        // 個人設定がなければクライアントごとの設定を取得する
        //$setting = $this->objClientTrcdSettingRepository->getByClientId($client_employee_id);
        // @YuKaneko 2019/07/25 ↑ 企業IDでなく社員IDがわたされていたため下記に修正
        $objClientEmployee = $this->getById($client_employee_id);
        if (is_null($objClientEmployee)) return null;

        $setting = $this->objClientTrcdSettingRepository->getByClientId($objClientEmployee->client_id);
        if ($setting != null && $setting->additional_const_amount_on_one_hour) {
            return $setting->additional_const_amount_on_one_hour;
        }

        return null;
    }


    /*
		クライアントID指定でページネート
	*/
    /*
	public function paginateByClientId($client_id, $limit, $options=[]) {
		return $this->objClientEmployeeRepository->paginateByClientId($client_id, $limit, $options=[]);
	}
*/
    /*
		指定されたクライアントIDに属するを取得
		@throws ModelNotFoundException 検索結果がない場合
	*/
    /*
	public function findOrFailWithinClientId($client_id, $id) {
		return $this->objClientEmployeeRepository->findOrFailWithinClientId($client_id, $id);
	}
*/

    /*
		新規作成
		@param $data
		@return 成功:結果配列 失敗:false
	*/
    public function create($data)
    {
        return $this->objClientEmployeeRepository->create($data);
    }

    /*
		新規作成（関連テーブルも同時に作成）
	*/
    public function createAssociationsAtTheSameTime(
        $client_employee_data,
        $client_employee_trcd_setting_data = [],
        $readable_client_group_ids = [],
        $role_ids = [],
        $readable_expense_group_ids = []
    ) {
        Arr::forget($client_employee_data, ['id']);
        Arr::forget($client_employee_trcd_setting_data, ['id', 'client_employee_id']);
        // 管理者権限でない場合は経費グループIDは更新不可
        if (!($this->isSuperAdmin() || auth()->user()->hasRole(['ADMIN']))) Arr::forget($client_employee_data, ['expense_group_id']);


        // 関連情報抽出
        // 社員属性抽出
        $employee_attributes = collect(array_unique(Arr::pull($client_employee_data, 'employee_attributes') ?? []));


        $err_msg = '[社員情報登録処理]'; // エラーメッセージ用変数
        DB::beginTransaction();

        try {
            // 社員情報更新処理 ------------------------------
            $client_employee = $this->create($client_employee_data);

            if (empty($client_employee)) {
                $err_msg .= '社員情報の登録に失敗しました。' . ' in ' . __FILE__ . ' on line ' . __LINE__;
                throw new \Exception($err_msg);
            }

            $err_msg .= "client_employees.id:{$client_employee['id']} ";

            // 社員TRCD設定情報更新 ------------------------------
            $result = $this->objClientEmployeeTrcdSettingRepository->createInitialData(
                $client_employee['id'],
                $client_employee_trcd_setting_data
            );

            if (empty($result)) {
                $err_msg .= '社員TRCD設定情報の登録に失敗しました。' . ' in ' . __FILE__ . ' on line ' . __LINE__;
                throw new \Exception($err_msg);
            }


            // 年次有給概要テーブル作成
            $result = $this->objAnnualPaidHolidaySummaryRepository->create(['client_employee_id' => $client_employee['id']]);

            if (empty($result)) {
                $err_msg = "年次有給概要の登録に失敗しました。 in " . __FILE__ . ':' . __LINE__;
                throw new \Exception($err_msg);
            }


            // 新規作成時は値がある時のみ、勤怠閲覧可能グループ挿入
            if (!empty($readable_client_group_ids)) $this->updateReadableClientGroups($client_employee['id'], $readable_client_group_ids);

            // 新規作成時は値がある時のみ、経費閲覧可能グループ挿入 ※管理者権限のみ
            if (!empty($readable_expense_group_ids) && auth()->user()->hasRole(['ADMIN'])) $this->updateReadableExpenseGroups($client_employee['id'], $readable_expense_group_ids);

            // 権限更新処理 ------------------------------
            // 権限を更新できるのは管理者と社員管理者のみ
            // 2019/08/21 SuperAdmin側からアクセスしてくる場合も通す
            $objClientEmployee = $this->objClientEmployeeRepository->find($client_employee['id']);
            $ROLE_CONSTANTS = config('database.trcd.roles.CONST');

            if ($this->isSuperAdmin() || auth()->user()->hasRole(['ADMIN', 'EMPLOYEE_MANAGER'])) {
                $new_roles = [];

                if (!empty($role_ids)) {
                    // 渡されてきたrole_idsの中に含まれているroleを取得
                    foreach ($ROLE_CONSTANTS as $role_name => $role_id) {
                        if (in_array($role_id, $role_ids)) $new_roles[$role_id] = $role_name;
                    }
                }

                // 権限を登録
                $result = $objClientEmployee->syncRoles($new_roles);

                if (!$result) {
                    $err_msg .= '権限の登録に失敗しました。' . ' in ' . __FILE__ . ' on line ' . __LINE__;
                    throw new \Exception($err_msg);
                }


                // 社員属性更新
                $objClientEmployee->employee_attributes()->sync($employee_attributes);
                $current_employee_attributes = $objClientEmployee->employee_attributes->pluck('id');

                // リクエスト値と更新後の値が異なる場合
                if (
                    $employee_attributes->count() !== $current_employee_attributes->count()
                    || $employee_attributes->diff($current_employee_attributes)->count() !== 0
                ) {
                    $err_msg .= '社員属性の登録に失敗しました。in ' . __FILE__ . ' on line ' . __LINE__;
                    throw new \Exception($err_msg);
                }
            }


            /*
			// @2020.06.16 YuKaneko 特定権限の自動付与処理（規定値）
			$default_roles = [
				'ATTENDANCE_REQUEST_ONLY', // 勤怠申請のみ可能
				'ATTENDANCE_READING_ONLY', // 勤怠参照のみ可能
			];
			// 経費契約を締結している企業の場合は「経費登録」権限を追加
			if ( $objClientEmployee->client->hasAnyContracts(['EXPENSE']) ) $default_roles[] = 'EXPENSE_REGISTRATION';
			// 特定権限を付与
			$objClientEmployee->assignRole($default_roles);
*/


            // 払出系の値が更新されることもあるため払出額更新処理を行う(登録の場合は必要ない？)
            $objTrcdService = app()->make(TrcdService::class);
            $settings = [
                'force_rounding' => true,
            ];
            $update_withdraw_result = $objTrcdService->UpdateWithdrawAmountAtThisMountByClientEmployeeId(
                $client_employee['id'],
                true,
                $settings
            );

            if (empty($update_withdraw_result)) {
                $err_msg .= '前払額更新処理に失敗しました。[client_employee_id:' . $client_employee['id'] . '] in ' . __FILE__ . ' on line ' . __LINE__;
                throw new \Exception($err_msg);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e);
            return false;
        }

        DB::commit();

        return true;
    }

    /*
		保存
		@param $data
		@return 成功:結果配列 失敗:false
	*/
    public function save($data)
    {
        //IDがない場合新規作成
        if (empty($data['id'])) return $this->create($data);

        try {
            //保存処理
            $save_result = $this->objClientEmployeeRepository->save($data);
        } catch (\Exception $e) {
            throw $e;
        }

        return $save_result;
    }

    /*
		保存（関連テーブルも更新）
	*/
    public function saveAssociationsAtTheSameTime(
        $client_employee_data,
        $client_employee_trcd_setting_data,
        $readable_client_group_ids = [],
        $role_ids = [],
        $readable_expense_group_ids = []
    ) {
        // 管理者権限でない場合は経費グループIDは更新不可
        if (!auth()->user()->hasRole(['ADMIN'])) Arr::forget($client_employee_data, ['expense_group_id']);

        // ID指定されていない場合は新規作成処理（関連テーブルも作成）
        if (empty($client_employee_data['id'])) {
            return $this->createAssociationsAtTheSameTime($client_employee_data, $client_employee_trcd_setting_data);
        }


        // 関連情報抽出
        // 社員属性抽出
        $employee_attributes = collect(array_unique(Arr::pull($client_employee_data, 'employee_attributes') ?? []));


        $err_msg = '[社員情報更新処理]'; // エラーメッセージ用変数
        DB::beginTransaction();

        try {
            // @baba 2020.10.30 社員情報更新前レコード取得
            $old_client_employee_data = $this->objClientEmployeeRepository->find($client_employee_data['id']);

            // 社員情報更新処理 ------------------------------
            $client_employee = $this->save($client_employee_data);

            if (empty($client_employee)) {
                $err_msg .= '社員情報の更新に失敗しました。' . ' in ' . __FILE__ . ' on line ' . __LINE__;
                throw new \Exception($err_msg);
            }

            $err_msg .= "client_employees.id:{$client_employee['id']} ";

            // 社員TRCD設定情報更新 ------------------------------
            $result = $this->objClientEmployeeTrcdSettingRepository->save(
                $client_employee['id'],
                $client_employee_trcd_setting_data
            );

            if (!$result) {
                $err_msg .= '社員TRCD情報の更新に失敗しました。' . ' in ' . __FILE__ . ' on line ' . __LINE__;
                throw new \Exception($err_msg);
            }

            // 勤怠閲覧可能グループ更新
            $this->updateReadableClientGroups($client_employee['id'], $readable_client_group_ids);

            // 経費閲覧可能グループ更新 ※管理者権限のみ
            if (auth()->user()->hasRole(['ADMIN'])) $this->updateReadableExpenseGroups($client_employee['id'], $readable_expense_group_ids);

            // 権限更新処理 ------------------------------
            // 権限を更新できるのは管理者のみ
            // 他者の情報を更新している場合にのみ権限更新処理を行う(自分の権限は自分では変更できない仕様)
            // 2019/08/21 SuperAdmin側からアクセスしてくる場合も通す

            $objClientEmployee = $this->objClientEmployeeRepository->find($client_employee['id']);

            if (
                $this->isSuperAdmin()
                || (auth()->user()->hasRole(['ADMIN', 'EMPLOYEE_MANAGER']) && auth()->user()->id != $client_employee['id'])
            ) {
                $new_roles = [];

                $ROLES_CONST = config('database.trcd.roles.CONST');

                // @terada 2020/01/17 管理者権限を持たない場合は、特定の権限を変更不可にする
                if (!$this->isSuperAdmin() && empty(auth()->user()->hasRole('ADMIN'))) {
                    // 社員が既に持っている権限を$role_idsに追加

                    $client_employee_role_names = $objClientEmployee->getRoleNames()->toArray();

                    if (in_array('ADMIN', $client_employee_role_names, true)) {
                        $role_ids[] = $ROLES_CONST['ADMIN'];
                    }

                    if (in_array('VEIN_INFORMATION_MANAGER', $client_employee_role_names, true)) {
                        $role_ids[] = $ROLES_CONST['VEIN_INFORMATION_MANAGER'];
                    }

                    if (in_array('PAYMENT_MANAGER', $client_employee_role_names, true)) {
                        $role_ids[] = $ROLES_CONST['PAYMENT_MANAGER'];
                    }

                    if (in_array('EXPENSE_APPROVAL', $client_employee_role_names, true)) {
                        $role_ids[] = $ROLES_CONST['EXPENSE_APPROVAL'];
                    }

                    if (in_array('EXPENSE_MANAGER', $client_employee_role_names, true)) {
                        $role_ids[] = $ROLES_CONST['EXPENSE_MANAGER'];
                    }

                    if (in_array('TEMPORARY_PAYMENT_MANAGER', $client_employee_role_names, true)) {
                        $role_ids[] = $ROLES_CONST['TEMPORARY_PAYMENT_MANAGER'];
                    }
                }

                $role_ids = array_unique($role_ids);

                if (!empty($role_ids)) {
                    // 渡されてきたrole_idsの中に含まれているroleを取得
                    foreach (config('database.trcd.roles.CONST') as $role_name => $role_id) {
                        if (in_array($role_id, $role_ids)) $new_roles[$role_id] = $role_name;
                    }
                }

                // 新しい権限に更新
                $result = $objClientEmployee->syncRoles($new_roles);
                if (!$result) {
                    $err_msg .= '権限の更新に失敗しました。' . ' in ' . __FILE__ . ' on line ' . __LINE__;
                    throw new \Exception($err_msg);
                }
            }


            // @baba? 2020.11.05 社員の入社日が設定され、変更された場合、年次有給休暇情報を更新する
            if (!empty($client_employee['hire_date']) && $old_client_employee_data['hire_date'] !== $client_employee['hire_date']) {
                $result = $this->objAnnualPaidHolidayService->updateByClientEmployeeId($client_employee['id']);

                if (empty($result)) {
                    $err_msg .= '年次有給休暇情報の更新に失敗しました。' . ' in ' . __FILE__ . ' on line ' . __LINE__;
                    throw new \Exception($err_msg);
                }
            }


            // 社員属性更新
            $objClientEmployee->employee_attributes()->sync($employee_attributes);
            $current_employee_attributes = $objClientEmployee->employee_attributes->pluck('id');

            // リクエスト値と更新後の値が異なる場合
            if (
                $employee_attributes->count() !== $current_employee_attributes->count()
                || $employee_attributes->diff($current_employee_attributes)->count() !== 0
            ) {
                $err_msg .= '社員属性の更新に失敗しました。in ' . __FILE__ . ' on line ' . __LINE__;
                throw new \Exception($err_msg);
            }


            // 払出系の値が更新されることもあるため払出額更新処理を行う ------------------------------
            $objTrcdService = app()->make(TrcdService::class);
            $settings = [
                'force_rounding' => true,
            ];
            $update_withdraw_result = $objTrcdService->UpdateWithdrawAmountAtThisMountByClientEmployeeId(
                $client_employee['id'],
                true,
                $settings
            );

            if (empty($update_withdraw_result)) {
                $err_msg .= '前払額更新処理に失敗しました。' . ' in ' . __FILE__ . ' on line ' . __LINE__;
                throw new \Exception($err_msg);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            logger()->error($e);

            return false;
        }

        DB::commit();

        return true;
    }

    /*
		IDを指定して削除
	*/
    public function delete($client_employee_id)
    {
        //return $this->objClientEmployeeRepository->delete($client_employee_id);
        // @baba 2020.10.19 年次有給休暇概要テーブルのレコードを削除する処理を追加

        DB::beginTransaction();

        try {
            // 年次有給概要テーブルも論理削除
            $result = $this->objAnnualPaidHolidaySummaryRepository->deleteByClientEmployeeId($client_employee_id);

            if (empty($result)) throw new \Exception("年次有給概要テーブルの削除に失敗");

            // 社員を論理削除
            $result = $this->objClientEmployeeRepository->delete($client_employee_id);

            if (empty($result)) throw new \Exception("社員の削除に失敗");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage()} client_employee_id={$client_employee_id} in " . __FILE__ . ':' . __LINE__);
            return false;
        }

        return true;
    }

    public function updatePassword($client_employee_id, $password)
    {
        $data = ['id' => $client_employee_id, 'password' => $password];

        return $this->save($data);
    }

    /*
		勤務予定日時取得
		@param int $client_employee_id
		@param string $date
	*/
    public function getExpectedWorkDatetimeOf($client_employee_id, $date = null)
    {
        // 勤務予定時刻取得
        $expected_work_time = $this->getExpectedWorkTimeOf($client_employee_id);

        // 両方ともnullの場合 即リターン
        if (empty($expected_work_time['start']) && empty($expected_work_time['end'])) return $expected_work_time;

        // 企業TRCD設定から動的に開始・終了日時を算出
        $objClientEmployee = $this->objClientEmployeeRepository->getById($client_employee_id);
        $objClientTrcdSetting = $this->objClientTrcdSettingRepository->getByClientId($objClientEmployee->client_id);

        return $this->objClientTrcdSettingRepository->CalcRangeOfWorkDatetime(
            $objClientTrcdSetting,
            $date,
            $expected_work_time['start'],
            $expected_work_time['end']
        );
    }

    /*
		勤務予定時刻取得
		@param int $client_employee_id
	*/
    public function getExpectedWorkTimeOf($client_employee_id)
    {
        $expected_work_time = [
            'start' => null,
            'end' => null,
        ];
        $objClientEmployee = $this->objClientEmployeeRepository->getById($client_employee_id);

        // 社員が設定されていない場合 即リターン
        if (empty($objClientEmployee)) return $expected_work_time;


        // 社員情報参照
        $expected_work_time['start'] = $objClientEmployee->work_start_time;
        $expected_work_time['end'] = $objClientEmployee->work_end_time;

        // 両方設定されている場合 リターン
        if (isset($expected_work_time['start']) && isset($expected_work_time['end'])) return $expected_work_time;


        // 社員TRCD設定取得->勤務タイプ取得->勤務タイプ参照
        $objClientEmployeeTrcdSetting = $this->objClientEmployeeTrcdSettingRepository
            ->where('client_employee_id', $objClientEmployee->id)
            ->whereNotNull('attendance_pattern_id')
            ->first();
        $objAttendancePattern = $objClientEmployeeTrcdSetting
            ? $this->objAttendancePatternRepository->getById($objClientEmployeeTrcdSetting->attendance_pattern_id)
            : null;

        if (!empty($objAttendancePattern)) {
            // 勤務タイプ参照
            if (!isset($expected_work_time['start'])) $expected_work_time['start'] = $objAttendancePattern->start_time;
            if (!isset($expected_work_time['end'])) $expected_work_time['end'] = $objAttendancePattern->end_time;
        }

        // 両方設定されている場合 リターン
        if (isset($expected_work_time['start']) && isset($expected_work_time['end'])) return $expected_work_time;


        // 企業TRCD設定取得
        $objClientTrcdSetting = $this->objClientTrcdSettingRepository->getByClientId($objClientEmployee->client_id);

        if (!empty($objClientTrcdSetting)) {
            // 企業TRCD設定参照
            if (!isset($expected_work_time['start'])) $expected_work_time['start'] = $objClientTrcdSetting->fixed_work_start_time;
            if (!isset($expected_work_time['end'])) $expected_work_time['end'] = $objClientTrcdSetting->fixed_work_end_time;
        }

        return $expected_work_time;
    }

    /*
		IDを指定して復元
	*/
    public function restore($client_employee_id)
    {
        //return $this->objClientEmployeeRepository->restore($client_employee_id);
        // @baba 2020.10.21 年次有給休暇概要テーブルのレコードを復活する処理を追加

        DB::beginTransaction();

        try {
            // 年次有給概要テーブルを論理削除から復元する
            $result = $this->objAnnualPaidHolidaySummaryRepository->restoreByClientEmployeeId($client_employee_id);

            if (empty($result)) throw new \Exception("年次有給概要テーブルの復活に失敗");

            // 社員を論理削除から復元する
            $result = $this->objClientEmployeeRepository->restore($client_employee_id);

            if (empty($result)) throw new \Exception("年次有給概要テーブルの復活に失敗");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error("{$e->getMessage} client_employee_id={$client_employee_id} in " . __FILE__ . ":" . __LINE__);
            return false;
        }

        return true;
    }

    /*
		ルートと権限の組み合わせごとにアクセス可能な社員IDを取得
		@param Request $request
		@param ClientEmployee $objClientEmployee
		@param Array $options
		@return Query $objQuery or $objNoneQuery
	*/
    public function BuildQueryAccessibleClientEmployees(Request $request, ClientEmployee $objClientEmployee, array $options = [])
    {
        $objQuery = $this->objClientEmployeeRepository->query();
        $objNoneQuery = $this->objClientEmployeeRepository->whereRaw('1 = 0');

        if (is_null($objClientEmployee)) return $objNoneQuery;

        // 前提：所属企業が同じ社員のみ
        $objQuery->where('client_id', $objClientEmployee->client_id);

        // 管理者の場合：所属企業が同じ全社員
        if ($objClientEmployee->hasRole('ADMIN')) return $objQuery;


        // 勤怠閲覧可能グループID
        $readable_client_group_ids = $objClientEmployee->readable_client_groups()->pluck('client_groups.id', 'client_groups.id');
        // 経費閲覧可能グループID
        $readable_expense_group_ids = $objClientEmployee->readable_expense_groups->pluck('id', 'id');

        //ルート名
        $route_name = $request->route()->getName();
        $options = [];

        switch ($route_name) {
                // ==================== 日毎一覧ページ ====================
            case ('trcd.attendances.daily'):
                // アクセス権限のない社員は即リターン
                if (!$objClientEmployee->hasAnyRole([
                    'PAYMENT_MANAGER',
                    'EMPLOYEE_MANAGER',
                    'ATTENDANCE_MANAGER',
                    'ATTENDANCE_REQUEST_MANAGER',
                    'MESSAGE_MANAGER',
                    'ATTENDANCE_REQUEST_ONLY',
                    'ATTENDANCE_READING_ONLY',
                ])) return $objNoneQuery;

                // 自分 + 勤怠閲覧可能グループ
                $options['include_myself'] = true;
                $options['readable_client_group_ids'] = $readable_client_group_ids;

                // 勤怠管理者には無所属社員を追加
                if ($objClientEmployee->hasRole('ATTENDANCE_MANAGER')) $options['include_unaffiliated'] = true;
                break;
                // ==================== 勤怠申請一覧ページ ====================
            case ('trcd.attendances.requests.index'):
                // アクセス権限のない社員は即リターン
                if (!$objClientEmployee->hasAnyRole([
                    'PAYMENT_MANAGER',
                    'EMPLOYEE_MANAGER',
                    'ATTENDANCE_MANAGER',
                    'ATTENDANCE_REQUEST_MANAGER',
                    'MESSAGE_MANAGER',
                    'ATTENDANCE_REQUEST_ONLY',
                    'ATTENDANCE_READING_ONLY',
                ])) return $objNoneQuery;

                // 自分のみ
                $options['include_myself'] = true;

                // 勤怠申請管理者には勤怠閲覧可能グループを追加
                if ($objClientEmployee->hasRole('ATTENDANCE_REQUEST_MANAGER')) {
                    $options['readable_client_group_ids'] = $readable_client_group_ids;
                }

                // 勤怠管理者には勤怠閲覧可能グループ + 無所属社員を追加
                if ($objClientEmployee->hasRole('ATTENDANCE_MANAGER')) {
                    $options['include_unaffiliated'] = true;
                    $options['readable_client_group_ids'] = $readable_client_group_ids;
                }
                break;
                // ==================== 簡易打刻ページ ====================
            case ('trcd.attendances.simplified'):
                // アクセス権限のない社員は即リターン
                if (!$objClientEmployee->hasAnyRole([
                    'ATTENDANCE_MANAGER',
                ])) return $objNoneQuery;

                // 自分 + 勤怠閲覧可能グループ + 無所属社員
                $options['include_myself'] = true;
                $options['readable_client_group_ids'] = $readable_client_group_ids;
                $options['include_unaffiliated'] = true;
                break;
                // ==================== 勤怠集計ページ ====================
            case ('trcd.aggregates.attendance'):
            case ('trcd.aggregates.attendance.download'):
                //case('trcd.aggregates.attendance.download_by_auth_key'): 未ログイン状態のため権限取得不可
                // アクセス権限のない社員は即リターン
                if (!$objClientEmployee->hasAnyRole([
                    'EMPLOYEE_MANAGER',
                    'ATTENDANCE_MANAGER',
                ])) return $objNoneQuery;

                // 自分 + 勤怠閲覧可能グループ + 無所属社員
                $options['include_myself'] = true;
                $options['readable_client_group_ids'] = $readable_client_group_ids;

                // 勤怠管理者には無所属社員を追加
                if ($objClientEmployee->hasRole('ATTENDANCE_MANAGER')) $options['include_unaffiliated'] = true;
                break;
                // ==================== 経費集計ページ ====================
            case ('trcd.aggregates.expense'):
            case ('trcd.aggregates.expense.download'):
                //case('trcd.aggregates.expense.download_by_auth_key'): 未ログイン状態のため権限取得不可
                // アクセス権限のない社員は即リターン
                if (!$objClientEmployee->hasAnyRole([
                    'EXPENSE_MANAGER',
                    'EXPENSE_APPROVAL',
                ])) return $objNoneQuery;

                // 自分 + 経費閲覧可能グループ + 無所属社員
                $options['include_myself'] = true;
                $options['readable_expense_group_ids'] = $readable_expense_group_ids;
                $options['include_expense_unaffiliated'] = true;

                break;
                // ==================== 仮払い集計ページ ====================
            case ('trcd.aggregates.temporary_payment'):
            case ('trcd.aggregates.temporary_payment.download'):
                //case('trcd.aggregates.temporary_payment.download_by_auth_key'): 未ログイン状態のため権限取得不可
                // アクセス権限のない社員は即リターン
                if (!$objClientEmployee->hasAnyRole([
                    'TEMPORARY_PAYMENT_MANAGER',
                ])) return $objNoneQuery;

                // 自分 + 経費閲覧可能グループ + 無所属社員
                $options['include_myself'] = true;
                $options['readable_expense_group_ids'] = $readable_expense_group_ids;
                $options['include_expense_unaffiliated'] = true;

                break;
            default:
                return $objNoneQuery;
        }

        $objQuery = $this->DecorateAccessibleClientEmployeeQuery($objQuery, $objClientEmployee, $options);

        return $objQuery;
    }

    /*
		ルートと権限の組み合わせごとにアクセス可能な社員IDを取得するためのクエリに
		設定値により、条件を付与したものを返す
		@param QueryBuilder $query
		@param ClientEmployee $objClientEmployee
		@param Array $options
		@return QueryBuilder $query
	*/
    private function DecorateAccessibleClientEmployeeQuery($query, ClientEmployee $objClientEmployee, array $options = [])
    {
        $query->where(function ($query) use ($objClientEmployee, $options) {
            // 自分を含める
            if (!empty($options['include_myself'])) {
                $query->orWhere('id', $objClientEmployee->id);
            }

            // 勤怠所属グループを含める
            if (!empty($options['include_client_group']) && isset($objClientEmployee->client_group_id)) {
                $query->orWhere('client_group_id', $objClientEmployee->client_group_id);
            }

            // 勤怠閲覧可能グループを含める
            if (!empty($options['readable_client_group_ids'])) {
                $query->orWhere(function ($query) use ($options) {
                    $query->whereIn('client_group_id', $options['readable_client_group_ids']);
                });
            }

            // 勤怠所属グループ無所属を含める
            if (!empty($options['include_unaffiliated'])) {
                $query->orWhere(function ($query) {
                    $query->whereNull('client_group_id');
                });
            }

            // 経費閲覧可能グループを含める
            if (!empty($options['readable_expense_group_ids'])) {
                $query->orWhere(function ($query) use ($options) {
                    $query->whereIn('expense_group_id', $options['readable_expense_group_ids']);
                });
            }

            // 経費所属グループ無所属を含める
            if (!empty($options['include_expense_unaffiliated'])) {
                $query->orWhere(function ($query) {
                    $query->whereNull('expense_group_id');
                });
            }
        });

        return $query;
    }

    /*
		勤怠閲覧可能グループ更新
		@throws Exception
		@return bool
	*/
    public function updateReadableClientGroups($client_employee_id, $readable_client_group_ids)
    {
        $result = $this->objClientEmployeeRepository->updateReadableClientGroups($client_employee_id, $readable_client_group_ids);
        if (!$result) throw new \Exception('勤怠閲覧可能グループの更新に失敗しました。');
        return $result;
    }

    /*
		経費閲覧可能グループ更新
		@throws Exception
		@return bool
	*/
    public function updateReadableExpenseGroups($client_employee_id, $readable_expense_group_ids)
    {
        $result = $this->objClientEmployeeRepository->updateReadableExpenseGroups($client_employee_id, $readable_expense_group_ids);
        if (!$result) throw new \Exception('経費閲覧可能グループの更新に失敗しました。');
        return $result;
    }
}
