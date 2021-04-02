<?php

namespace App\Console\Commands\Trcd;

use Illuminate\Console\Command;

// Service
use App\Services\Trcd\AnnualPaidHolidayService;

// Carbon
use Carbon\Carbon;

class GrantedAnnualPaidHolidayToTargetEmployee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:granted_annual_paid_holiday_to_target_employee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Annual paid holiday is granted to the target employees.';

    protected $AnnualPaidHolidayService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        AnnualPaidHolidayService $AnnualPaidHolidayService
    ) {
        parent::__construct();

        $this->AnnualPaidHolidayService = $AnnualPaidHolidayService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        logger()->info("年次有給休暇付与のcron処理開始");

        try {
            $this->AnnualPaidHolidayService->CreateNewAnnualPaidHoliday(Carbon::now());
        } catch (\Exception $e) {
            logger()->error("年次有給発行クーロン処理失敗");
            logger()->error("{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
        }

        logger()->info("年次有給休暇付与のcron処理終了");
    }
}
