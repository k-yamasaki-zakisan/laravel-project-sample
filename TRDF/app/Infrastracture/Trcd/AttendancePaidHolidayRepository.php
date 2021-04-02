<?php

/**
 * 有給ヘッダ用リポジトリ
 *
 * @author YuKaneko
 */

namespace App\Infrastracture\Repositories\Trcd;

use App\Repositories\Trcd\AttendancePaidHolidayRepositoryInterface;
use App\Infrastracture\Repositories\Trcd\TrcdBaseRepository;

use App\AttendancePaidHoliday;

class AttendancePaidHolidayRepository extends TrcdBaseRepository implements AttendancePaidHolidayRepositoryInterface
{

    //利用するモデルのクラス指定
    protected static $modelClass = \App\AttendancePaidHoliday::class;

    /*
		社員を指定して取得
	*/
    public function getByClientEmployeeId($client_employee_id, $options = [])
    {
        if (is_array($client_employee_id)) {
            $objQuery = $this->whereIn('client_employee_id', $client_employee_id);
        } else {
            $objQuery = $this->where('client_employee_id', $client_employee_id);
        }

        // 範囲指定
        if (isset($options['from']) && isset($options['to'])) {
            $objQuery->whereBetween('date', [$options['from'], $options['to']]);
        } else if (isset($options['from'])) {
            $objQuery->where('date', '>=', $options['from']);
        } else if (isset($options['to'])) {
            $objQuery->where('date', '<=', $options['to']);
        } else if (isset($options['date'])) {
            $objQuery->whereDate('date', $options['date']);
        }

        return $objQuery->get();
    }

    /*
		新規作成
		@param Array $data
		@return Array or false
	*/
    public function create(array $data)
    {
        $objAttendancePaidHoliday = new AttendancePaidHoliday();

        $data = array_only($data, $objAttendancePaidHoliday->getFillableAttributes());

        $validationRules = $objAttendancePaidHoliday->buildValidationRulesForInsert($data);
        $validator = $this->Validator($data, $validationRules);

        if ($validator->fails()) {
            logger()->error($validator->errors()->toArray());
            return false;
        }

        foreach ($data as $key => $value) {
            $objAttendancePaidHoliday->$key = $value;
        }

        // SQL実行
        try {
            return $objAttendancePaidHoliday->save() ? $objAttendancePaidHoliday->toArray() : false;
        } catch (\Exception $e) {
            Log::Error($e->getMessage());
            return false;
        }
    }

    /*
		保存
		@param Array $data
		@return Array or false
	*/
    public function save(array $data)
    {
        if (!isset($data['id'])) return $this->create($data);

        $objAttendancePaidHoliday = $this->find($data['id']);
        $data = array_only($data, $objAttendancePaidHoliday->getFillableAttributes());

        foreach ($data as $key => $value) {
            $objAttendancePaidHoliday->$key = $value;
        }

        $data = $objAttendancePaidHoliday->toArray();
        $validationRules = $objAttendancePaidHoliday->buildValidationRulesForUpdate($data);
        $validator = $this->Validator($data, $validationRules);

        if ($validator->fails()) {
            logger()->error($validator->errors()->toArray());
            return false;
        }

        // SQL実行
        try {
            return $objAttendancePaidHoliday->save() ? $objAttendancePaidHoliday->toArray() : false;
        } catch (\Exception $e) {
            Log::Error($e->getMessage());
            return false;
        }
    }

    /*
		勤怠申請から新規作成
	*/
    public function createByAttendanceRequest($objAttendanceRequest)
    {
        $data = [
            'client_employee_id' => $objAttendanceRequest->client_employee_id,
            'paid_holiday_id' => $objAttendanceRequest->paid_holiday_id,
            'date' => $objAttendanceRequest->paid_holiday_date,
        ];

        return $this->create($data);
    }

    /*
		有給申請から有給ヘッダを作成
		@param Array $attendance_request
		@param Array $options
		@return Mixed
			Success: Array
			Failure: false
	*/
    public function saveByAttendanceRequest($attendance_request, $options = [])
    {
        // 有給ヘッダIDが指定されていれば取得、なければ新規
        $objAttendancePaidHoliday = isset($attendance_request['attendance_paid_holiday_id'])
            ? $this->find($attendance_request['attendance_paid_holiday_id'])
            : new AttendancePaidHoliday();

        if (isset($attendance_request['client_employee_id'])) $objAttendancePaidHoliday->client_employee_id = $attendance_request['client_employee_id'];

        // null値が渡された場合にissetだと検知してくれないため、array_key_existsに変更
        if (array_key_exists('paid_holiday_date', $attendance_request)) $objAttendancePaidHoliday->date = $attendance_request['paid_holiday_date'];
        if (array_key_exists('paid_holiday_id', $attendance_request)) $objAttendancePaidHoliday->paid_holiday_id = $attendance_request['paid_holiday_id'];
        if (array_key_exists('note', $attendance_request)) $objAttendancePaidHoliday->note = $attendance_request['note'];

        return $this->save($objAttendancePaidHoliday->toArray());
    }

    /*
		削除
	*/
    public function delete($id)
    {
        return AttendancePaidHoliday::where('id', $id)->delete();
    }
}
