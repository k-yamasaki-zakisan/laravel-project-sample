<?php

namespace App\Http\Controllers\Trcd;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

//Repository
use App\Infrastracture\Repositories\ClientEmployeeRepository;
use App\Infrastracture\Repositories\Trcd\AttendanceRequestRepository;
use App\Infrastracture\Repositories\Trcd\AttendanceHeaderRepository;
use App\Infrastracture\Repositories\Trcd\AttendancePaidHolidayRepository;

//Service
use App\Services\ClientEmployeeService;
use App\Services\Trcd\AttendanceService;
use App\Services\Trcd\AttendanceRequestService;
use App\Services\Trcd\AnnualPaidHolidayService;

//Forms
use Kris\LaravelFormBuilder\FormBuilderTrait;
use App\Forms\Trcd\AttendanceRequestForm;

// Requests
use App\Http\Requests\Trcd\AttendanceRequestGetFormRequest;
use App\Http\Requests\Trcd\AttendanceRequestPostRequest;
use App\Http\Requests\Trcd\AttendanceRequestApprovalPostRequest;

class AttendanceRequestsController extends TrcdBaseController
{

    use FormBuilderTrait;

    /*
		一覧
	*/
    public function index(
        Request $request,
        AttendanceService $objAttendanceService,
        ClientEmployeeRepository $objClientEmployeeRepo,
        AttendanceRequestRepository $objAttendanceRequestRepo,
        ClientEmployeeService $objClientEmployeeService
    ) {
        $loginUser = $this->_getLoginUser();
        $login_user_id = $loginUser->id;

        // 自企業に所属する全社員リスト
        $client_employee_list = $objClientEmployeeRepo->where('client_id', $loginUser->client_id)->pluck('name', 'id')->toArray();
        // 表示する勤怠申請者リスト
        $target_client_employee_ids = $objClientEmployeeService->BuildQueryAccessibleClientEmployees($request, $loginUser)
            ->pluck('id')
            ->toArray();

        $query = $objAttendanceRequestRepo->whereIn('client_employee_id', $target_client_employee_ids);

        // 検索フォームの初期値とページネーションのリンクに追加するパラメータ用の配列
        $params = array();

        $processing_status = isset($request->processing_status) ? (int)$request->processing_status : null;

        if ($processing_status === 0) {
            // 未処理の勤怠申請を取得
            $query->where('processed_flag', false)
                ->whereNull('request_canceled_at');
            $params['processing_status'] = $processing_status;
        } elseif ($processing_status === 10) {
            // 処理済みの勤怠申請を取得
            $query->where(function ($query) {
                $query->where('processed_flag', true)
                    ->orWhereNotNull('request_canceled_at');
            });
            $params['processing_status'] = $processing_status;
        } elseif ($processing_status === 20) {
            // すべての勤怠申請を取得
            $params['processing_status'] = $processing_status;
        } else {
            // 検索条件の処理状態が指定されていない場合や、
            // 検索条件の処理状態に異常な値が設定されていた場合は、
            // 未処理の勤怠申請を取得
            $query->where('processed_flag', false)
                ->whereNull('request_canceled_at');
        }

        // 申請格納配列
        $attendance_requests = $query->orderBy('id', 'desc')
            ->paginate(20);

        // 申請がない場合はnullにしておく
        if ($attendance_requests->count() < 1) $attendance_requests = null;


        $paid_holiday_list = $objAttendanceService->getPaidHolidayList($loginUser->client_id, ['withTrashed' => true]);
        $attendance_special_type_list = $objAttendanceService->getAttendanceSpecialTypeList($loginUser->client_id);
        $attendance_request_results = $objAttendanceService->getAttendanceRequestResults();

        $view_name = \Agent::isMobile() ? 'trcd.attendances.requests.index_sp' : 'trcd.attendances.requests.index';
        return view($view_name, compact(
            'client_employee_list',
            'attendance_requests',
            'paid_holiday_list',
            'attendance_special_type_list',
            'attendance_request_results',
            'login_user_id',
            'params'
        ));
    }

    /*
		勤怠申請処理
	*/
    public function request(
        AttendanceRequestPostRequest $request,
        AttendanceRequestService $objAttendanceRequestService,
        AttendanceHeaderRepository $objAttendanceHeaderRepo,
        AttendancePaidHolidayRepository $objAttendancePaidHolidayRepo,
        ClientEmployeeRepository $objClientEmployeeRepo,
        AnnualPaidHolidayService $objAnnualPaidHolidayService
    ) {
        $loginUser = $this->_getLoginUser();
        $data = $request->getOnlyAllowedFields();
        $options = [];

        // 申請対象となる社員は自企業に属する社員でなくてはいけない
        $objClientEmployee = $objClientEmployeeRepo->where('client_id', $loginUser->client_id)->findOrFail($data['client_employee_id']);

        // 申請をした社員のIDを追加
        $data['request_client_employee_id'] = $loginUser->id;

        // 勤怠ヘッダ
        if (!empty($data['attendance_header_id'])) {
            $attendanceHeader = $objAttendanceHeaderRepo->where('client_employee_id', $objClientEmployee->id)->findOrFail($data['attendance_header_id']);
            $data['attendance_header_id'] = $attendanceHeader->id;
        }

        // 有給ヘッダ
        if (!empty($data['attendance_paid_holiday_id'])) {
            $attendancePaidHoliday = $objAttendancePaidHolidayRepo->where('client_employee_id', $objClientEmployee->id)->findOrFail($data['attendance_paid_holiday_id']);
            $data['attendance_paid_holiday_id'] = $attendancePaidHoliday->id;
        }

        // ログインユーザーが承認可能であれば同時に承認処理も行えるように設定
        if ($loginUser->can('update attendances', 'trcd')) {
            $options['with_approval'] = true;
        }


        // 申請が有給申請の場合で、
        // 有給申請可能なデータか判定
        // もし申請不可なデータであればエラーレスポンスを返して終了
        // 事前にその社員がその日付で有給を取得していないか検索。取得している場合は、$attendance_paid_holiday_idにセット

        // @2020.11.12 有給取得可能判定
        if (isset($data['paid_holiday_date']) && isset($data['paid_holiday_id'])) {
            $validate_result = $objAnnualPaidHolidayService->ValidateIfCanGetPaidHoliday(
                $objClientEmployee['id'],
                $data['paid_holiday_date'],
                $data['paid_holiday_id'],
                $data['attendance_paid_holiday_id'] ?? null
            );

            // 検証失敗時
            if (empty($validate_result['result'])) {
                $errors = !empty($validate_result['errors']) ? $validate_result['errors'] : ['有給取得可能判定に失敗しました。'];
                return response()->json(['errors' => [
                    'paid_holiday_id' => $errors,
                ]]);
            }
        }


        $result = $objAttendanceRequestService->add($objClientEmployee->id, $data, $options);

        if (empty($result)) {
            $validator = $objAttendanceRequestService->getLastValidator();
            $errors = empty($validator) ? [] : $validator->errors()->toArray();
            logger()->error($errors);

            return response()->json(['errors' => $errors]);
        }

        return response()->json($result);
    }

    /*
		申請承認処理
	*/
    public function approve(
        AttendanceRequestApprovalPostRequest $request,
        ClientEmployeeRepository $objClientEmployeeRepo,
        AttendanceRequestRepository $objAttendanceRequestRepo,
        AttendanceRequestService $objAttendanceRequestService,
        AttendancePaidHolidayRepository $objAttendancePaidHolidayRepo,
        AnnualPaidHolidayService $objAnnualPaidHolidayService,
        $request_id
    ) {
        $loginUser = $this->_getLoginUser();
        $attendance_request = $objAttendanceRequestRepo->findOrFail($request_id);
        $client_employee = $objClientEmployeeRepo->where('client_id', $loginUser->client_id)->findOrFail($attendance_request->client_employee_id);
        $data = $request->getOnlyAllowedFields();

        // 承認が有給申請の場合で、
        // 有給承認可能なデータか判定
        // もし承認不可なデータであればエラーレスポンスを返して終了
        // @2020.11.12 有給取得可能判定
        if (isset($data['paid_holiday_date']) && isset($data['paid_holiday_id'])) {
            // 既に取得している有給を変更する場合は、既存データを取得
            $AttendancePaidHoliday = $objAttendancePaidHolidayRepo->where('client_employee_id', $client_employee['id'])
                ->where('date', $data['paid_holiday_date'])
                ->first();
            $validate_result = $objAnnualPaidHolidayService->ValidateIfCanGetPaidHoliday(
                $objClientEmployee['id'],
                $data['paid_holiday_date'],
                $data['paid_holiday_id'],
                $AttendancePaidHoliday['id'] ?? null
            );

            // 検証失敗時
            if (empty($validate_result['result'])) {
                logger($validate_result);
                $errors = !empty($validate_result['errors']) ? $validate_result['errors'] : ['有給取得可能判定に失敗しました。'];
                return response()->json(['errors' => [
                    'paid_holiday_id' => $errors,
                ]]);
            }
        }


        if ($request->isAttendanceRequest()) {
            // 勤怠申請として承認処理
            $result = $objAttendanceRequestService->approveAsAttendance($attendance_request->id, $data);
        } else if ($request->isPaidHolidayRequest()) {
            // 有給申請として承認処理
            $result = $objAttendanceRequestService->approveAsPaidHoliday($attendance_request->id, $data);
        }

        if (empty($result)) {
            $validator = $objAttendanceRequestService->getLastValidator();
            $errors = isset($validator) ? $validator->errors()->toArray() : [];
            logger()->error($errors);

            if (empty($errors)) $errors['db'] = ["申請の承認処理に失敗しました。"];

            return response()->json(['errors' => $errors]);
        }

        return response()->json($result);
    }

    /*
		申請否認処理
	*/
    public function deny(
        Request $request,
        ClientEmployeeRepository $objClientEmployeeRepo,
        AttendanceRequestRepository $objAttendanceRequestRepo,
        AttendanceService $objAttendanceService,
        $request_id
    ) {
        $loginUser = $this->_getLoginUser();
        $attendance_request = $objAttendanceRequestRepo->findOrFail($request_id);
        $client_employee = $objClientEmployeeRepo->where('client_id', $loginUser->client_id)->findOrFail($attendance_request->client_employee_id);

        $result = $objAttendanceService->denyAttendanceRequest($attendance_request->id);

        if (empty($result)) {
            $validator = $objAttendanceService->getLastValidator();
            $errors = isset($validator) ? $validator->errors()->toArray() : [];
            if (empty($errors)) $errors['db'] = ["申請の否認処理に失敗しました。"];

            return response()->json(['errors' => $errors]);
        }

        return response()->json($result);
    }

    /*
		申請取り消し処理
	*/
    public function cancel(
        Request $request,
        AttendanceRequestRepository $objAttendanceRequestRepo,
        AttendanceService $objAttendanceService,
        $attendance_request_id
    ) {
        $loginUser = $this->_getLoginUser();

        // 申請した本人のみ取消可能
        //$attendance_request = $objAttendanceRequestRepo->where('client_employee_id', $loginUser->id)->findOrFail($attendance_request_id);
        $attendance_request = $objAttendanceRequestRepo->where('request_client_employee_id', $loginUser->id)->findOrFail($attendance_request_id);

        $result = $objAttendanceService->cancelAttendanceRequest($attendance_request->id);

        if (empty($result)) {
            $validator = $objAttendanceService->getLastValidator();
            $errors = isset($validator) ? $validator->errors()->toArray() : [];
            if (empty($errors)) $errors['db'] = ["申請の否認処理に失敗しました。"];

            logger()->error($errors);
            $request->session()->flash('error_message', '勤怠申請の取り消しに失敗しました。');
            return redirect()->back()->withErrors($errors)->withInput();
        }

        $request->session()->flash('success_message', '勤怠申請を取り消しました。');
        return redirect()->back();
    }

    /*
		申請・承認フォーム取得
	*/
    public function fetch_form(
        AttendanceRequestGetFormRequest $request,
        AttendanceService $objAttendanceService,
        ClientEmployeeService $objClientEmployeeService,
        AttendanceRequestRepository $objAttendanceRequestRepo,
        ClientEmployeeRepository $objClientEmployeeRepo,
        AttendanceHeaderRepository $objAttendanceHeaderRepo,
        AttendancePaidHolidayRepository $objAttendancePaidHolidayRepo
    ) {
        $loginUser = $this->_getLoginUser();
        $data = $request->getOnlyAllowedFields();
        $form = null;

        // 有給種別・特殊勤怠リスト取得
        $paid_holiday_list = $objAttendanceService->getPaidHolidayList($loginUser->client_id)->toArray();
        $attendance_special_type_list = $objAttendanceService->getAttendanceSpecialTypeList($loginUser->client_id)->toArray();

        if ($request->isApprovalRequest()) {
            // 承認フォームの要請は、自企業の社員の申請のみ対象
            $objAttendanceRequest = $objAttendanceRequestRepo->findOrFail($data['id']);
            $objClientEmployee = $objClientEmployeeRepo->where('client_id', $loginUser->client_id)->findOrFail($objAttendanceRequest->client_employee_id);

            $form = $this->_generateApprovalForm(
                $objAttendanceRequest,
                $paid_holiday_list,
                $attendance_special_type_list
            );
        } else if ($request->isRequestRequest()) {
            // 申請フォーム要請であれば申請元ヘッダの情報を取得
            $objAttendanceHeader = null;
            $objAttendancePaidHoliday = null;
            $objClientEmployee = null;

            if ($request->isFromAttendanceHeader()) {
                // 自企業の社員の勤怠ヘッダのみ対象
                $objAttendanceHeader = $objAttendanceHeaderRepo->findOrFail($data['id']);
                $objClientEmployee = $objClientEmployeeRepo->where('client_id', $loginUser->client_id)->findOrFail($objAttendanceHeader->client_employee_id);
            } else if ($request->isFromAttendancePaidHoliday()) {
                // 自企業の社員の有給ヘッダのみ対象
                $objAttendancePaidHoliday = $objAttendancePaidHolidayRepo->findOrFail($data['id']);
                $objClientEmployee = $objClientEmployeeRepo->where('client_id', $loginUser->client_id)->findOrFail($objAttendancePaidHoliday->client_employee_id);
            } else {
                /*
					@YuKaneko 2019/09/05
					他者の勤怠への申請も可能にする
				*/
                $objClientEmployee = $objClientEmployeeRepo->where('client_id', $loginUser->client_id)->findOrFail($data['client_employee_id']);
            }

            // 日付・勤務予定日時既定値設定
            $specific_date = null;
            $expected_work_datetime = null;

            if (isset($data['date'])) {
                $specific_date = Carbon::parse("{$data['date']}");
                $expected_work_datetime = $objClientEmployeeService->getExpectedWorkDatetimeOf($objClientEmployee->id, $specific_date->format('Y-m-d'));
            }

            $form = $this->_generateRequestForm(
                $loginUser,
                $objClientEmployee,
                $objAttendanceHeader,
                $objAttendancePaidHoliday,
                $specific_date,
                $expected_work_datetime,
                $paid_holiday_list,
                $attendance_special_type_list
            );
        }

        if (is_null($form)) {
            logger()->error($data);
            return 'フォームが取得できませんでした。';
        }

        return view('trcd.attendances.requests.form', compact('form'));
    }

    /*
		勤怠申請フォーム生成
	*/
    protected function _generateRequestForm(
        $loginUser,
        $objClientEmployee,
        $objAttendanceHeader,
        $objAttendancePaidHoliday,
        $specific_date = null,
        $expected_work_datetime = null,
        $paid_holiday_list,
        $attendance_special_type_list
    ) {
        return $this->form(AttendanceRequestForm::class, [
            'method' => 'POST',
            'url' => route('trcd.attendances.request'),
            'data' => [
                'loginUser' => $loginUser,
                'ClientEmployee' => $objClientEmployee,
                'objAttendanceHeader' => $objAttendanceHeader,
                'objAttendancePaidHoliday' => $objAttendancePaidHoliday,
                'specific_date' => $specific_date,
                'expected_work_datetime' => $expected_work_datetime,
                'paid_holiday_list' => $paid_holiday_list,
                'attendance_special_type_list' => $attendance_special_type_list,
            ],
        ]);
    }

    /*
		勤怠承認フォーム生成
	*/
    protected function _generateApprovalForm(
        $attendance_request,
        $paid_holiday_list,
        $attendance_special_type_list
    ) {
        return $this->form(AttendanceRequestForm::class, [
            'method' => 'POST',
            'url' => route('trcd.attendances.requests.approve', $attendance_request->id),
            'model' => $attendance_request,
            'data' => [
                'paid_holiday_list' => $paid_holiday_list,
                'attendance_special_type_list' => $attendance_special_type_list,
            ],
        ]);
    }
}
