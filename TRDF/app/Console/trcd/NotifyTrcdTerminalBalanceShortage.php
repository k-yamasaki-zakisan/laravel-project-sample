<?php

namespace App\Console\Commands\Trcd;

use Illuminate\Console\Command;
// Service
use App\Services\Notifications\TrcdTerminalBalanceNotificationService;

class NotifyTrcdTerminalBalanceShortage extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'command:notify_trcd_terminal_balathreshold_shortage';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send mails to notify that the bill in the terminal is below the threshold.';

	protected $TrcdTerminalBalanceNotificationService;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(TrcdTerminalBalanceNotificationService $TrcdTerminalBalanceNotificationService) {
		parent::__construct();

		$this->TrcdTerminalBalanceNotificationService = $TrcdTerminalBalanceNotificationService;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		try {
			$this->TrcdTerminalBalanceNotificationService->notifyBelowThresholdList(now());
		} catch ( \Exception $e ) {
			$this->error($e);
		}
	}
}
