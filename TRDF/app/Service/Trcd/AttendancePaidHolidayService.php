<?php

namespace App\Services\Trcd;

use App\Services\ServiceBase;
use App\Repositories\Trcd\AttendancePaidHolidayRepositoryInterface as AttendancePaidHolidayRepository;

class AttendancePaidHolidayService extends ServiceBase
{
    protected $objAttendancePaidHolidayRepository;

    public function __construct(AttendancePaidHolidayRepository $objAttendancePaidHolidayRepository)
    {
        $this->objAttendancePaidHolidayRepository = $objAttendancePaidHolidayRepository;
    }

    /*
		IDから取得
	*/
    public function getById($attendance_paid_holiday_id)
    {
        return $this->objAttendancePaidHolidayRepository->find($attendance_paid_holiday_id);
    }

    /*
		勤怠申請から新規追加
	*/
    public function createByAttendanceRequest($objAttendanceRequest)
    {
        return $this->objAttendancePaidHolidayRepository->createByAttendanceRequest($objAttendanceRequest);
    }

    /*
		社員を指定して取得
	*/
    public function getByClientEmployeeId($client_employee_id, $options = [])
    {
        return $this->objAttendancePaidHolidayRepository->getByClientEmployeeId($client_employee_id, $options);
    }

    /*
		保存
	*/
    public function save(array $data)
    {
        return $this->objAttendancePaidHolidayRepository->save($data);
    }

    /*
		IDを指定して削除
	*/
    public function delete($attendance_paid_holiday_id)
    {
        return $this->objAttendancePaidHolidayRepository->delete($attendance_paid_holiday_id);
    }
}
