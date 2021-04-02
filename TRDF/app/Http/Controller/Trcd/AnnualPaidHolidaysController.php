<?php

namespace App\Http\Controllers\Trcd;

// Requests
use Illuminate\Http\Request;

// Repositories
use App\Repositories\ClientEmployeeRepositoryInterface as ClientEmployeeRepository;
use App\Repositories\Trcd\AnnualPaidHolidaySummaryRepositoryInterface as AnnualPaidHolidaySummaryRepository;
use App\Repositories\Trcd\AnnualPaidHolidayRepositoryInterface as AnnualPaidHolidayRepository;

// Service
use App\Services\Trcd\AnnualPaidHolidayService;

// Model
use App\AnnualPaidHoliday;

// FormBuilder
use Kris\LaravelFormBuilder\FormBuilder;
use Kris\LaravelFormBuilder\FormBuilderTrait;

// Form
use App\Forms\Trcd\AnnualPaidHolidayForm;

class AnnualPaidHolidaysController extends TrcdBaseController
{
    use FormBuilderTrait;

    protected $ClientEmployeeRepo;
    protected $AnnualPaidHolidaySummaryRepo;
    protected $AnnualPaidHolidayRepo;
    protected $AnnualPaidHolidayService;

    public function __construct(
        ClientEmployeeRepository $ClientEmployeeRepo,
        AnnualPaidHolidaySummaryRepository $AnnualPaidHolidaySummaryRepo,
        AnnualPaidHolidayRepository $AnnualPaidHolidayRepo,
        AnnualPaidHolidayService $AnnualPaidHolidayService
    ) {
        $this->ClientEmployeeRepo = $ClientEmployeeRepo;
        $this->AnnualPaidHolidaySummaryRepo = $AnnualPaidHolidaySummaryRepo;
        $this->AnnualPaidHolidayRepo = $AnnualPaidHolidayRepo;
        $this->AnnualPaidHolidayService = $AnnualPaidHolidayService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($paid_holiday_summary_id)
    {
        $client_id = $this->_getClientId();
        $AnnualPaidHolidaySummary = $this->AnnualPaidHolidaySummaryRepo->findOrFail($paid_holiday_summary_id);
        // アクセス対象が自企業社員のデータか判定（異なる場合は404エラー）
        $client_employee = $this->ClientEmployeeRepo->specificClient($client_id)->findOrFail($AnnualPaidHolidaySummary['client_employee_id']);

        $annual_paid_holiday_ids = $AnnualPaidHolidaySummary->annual_paid_holidays->pluck('id');
        $annual_paid_holidays = $this->AnnualPaidHolidayRepo->whereIn('id', $annual_paid_holiday_ids)->orderBy('base_date', 'desc')->paginate(20);

        return view('trcd.annual_paid_holidays.index', compact(
            'client_employee',
            'annual_paid_holidays'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AnnualPaidHoliday  $annualPaidHoliday
     * @return \Illuminate\Http\Response
     */
    public function edit($annual_paid_holiday_id)
    {
        // アクセス対象が自企業社員のデータか判定（異なる場合は404エラー）
        $client_id = $this->_getClientId();
        $annual_paid_holiday_summary = $this->AnnualPaidHolidayRepo->findOrFail($annual_paid_holiday_id)->annual_paid_holiday_summary;
        $client_employee_id = $annual_paid_holiday_summary->client_employee_id;
        $client_employee = $this->ClientEmployeeRepo->specificClient($client_id)->findOrFail($client_employee_id);

        // 入力フォーム生成
        $form = $this->form(AnnualPaidHolidayForm::class, [
            'method' => 'POST',
            'url' => route('trcd.annual_paid_holidays.update', $annual_paid_holiday_id),
            'model' => $this->AnnualPaidHolidayRepo->findOrFail($annual_paid_holiday_id),
        ]);

        return view('trcd.annual_paid_holidays.edit', compact('form'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AnnualPaidHoliday  $annualPaidHoliday
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $annual_paid_holiday_id)
    {
        // ToDo:自企業所属社員のデータしか更新できないようにする

        // 更新対象年次有給取得
        $AnnualPaidHoliday = $this->AnnualPaidHolidayRepo->findOrFail($annual_paid_holiday_id);

        // 年次有給概要取得
        $AnnualPaidHolidaySummary = $AnnualPaidHoliday->annual_paid_holiday_summary;

        // 入力したフォームを取得
        $form = $this->form(AnnualPaidHolidayForm::class, [
            'method' => 'POST',
            'url' => route('trcd.annual_paid_holidays.update', $annual_paid_holiday_id),
            'model' => $AnnualPaidHoliday,
        ]);

        // フォーム内で定義したバリデーションチェック
        if (!$form->isValid()) {
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }

        // 更新データ整形
        $values = array_only($AnnualPaidHoliday->toArray(), [
            'base_date',
            'next_base_date',
            'days_worked',
            'days_granted',
            'expiration_date',
            'days_added',
            'usable_days',
            'days_used',
        ]);

        $values = array_merge($values, array_only($form->getFieldValues(), [
            'days_added',
        ]));

        // モデル更新時のバリデーション違反防止のため、idを追加する。
        $data = array_merge($values, ['annual_paid_holiday_summary_id' => $AnnualPaidHolidaySummary->id]);

        // 年次有給休暇の更新
        $result = $this->AnnualPaidHolidayService->update($annual_paid_holiday_id, $data);

        // 更新処理失敗
        if (empty($result)) {
            $request->session()->flash('error_message', '更新に失敗しました。');
            return redirect()->back();
        }

        $request->session()->flash('success_message', '更新が完了しました。');

        return redirect(route('trcd.annual_paid_holidays.index', $AnnualPaidHolidaySummary->id));
    }
}
