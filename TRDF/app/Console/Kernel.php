<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		// 立ったままの編集済みフラグをおろす
		\App\Console\Commands\RevertToEditable::class,
		// TRCD 1日一回の締め処理（顧客は月１で締め処理を行う）
		\App\Console\Commands\Trcd\ResetWithdrawAmount::class,
		// メールを送信する
		\App\Console\Commands\SendMail::class,
		// Nextcloudの経費用レシート画像を取り込む
		\App\Console\Commands\TakeInNextcloudExpenseImages::class,
		// 経費申請通知
		\App\Console\Commands\Trcd\NotifyBeingRequestedExpense::class,
		//端末残高通知
		\App\Console\Commands\Trcd\NotifyTrcdTerminalBalanceShortage::class,
		// @baba 2020.11.17追加
		// 年次有給休暇付与
		\App\Console\Commands\Trcd\GrantedAnnualPaidHolidayToTargetEmployee::class,
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule	$schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		// 5分毎に実行
		$schedule->command('command:revert_to_editable')->everyFiveMinutes();

		// ========== TRCD ==========
		// 1日一回。夜12時に実施
		$schedule->command('command:reset_withdraw_amount')->daily();

		// メール送信のテスト 5分毎
		$schedule->command('command:send_mail')->everyFiveMinutes();

		// Nextcloudの経費用レシート画像を取り込む
		$schedule->command('command:take_in_nextcloud_expense_images')->everyFiveMinutes()->withoutOverlapping();

		// 経費申請通知 毎分実行
		$schedule->command('command:notify_being_requested_expense')->everyMinute();

		// 残高不足通知 毎分実行
		$schedule->command('command:notify_trcd_terminal_balathreshold_shortage')->everyMinute();

		// @baba 2020.11.17 追加
		// 年次有給付与 1日に1回。夜12時に実施
		$schedule->command('command:granted_annual_paid_holiday_to_target_employee')->daily();
	}

	/**
	 * Register the commands for the application.
	 *
	 * @return void
	 */
	protected function commands()
	{
		$this->load(__DIR__ . '/Commands');

		require base_path('routes/console.php');
	}
}
