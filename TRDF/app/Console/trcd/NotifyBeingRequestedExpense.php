<?php

namespace App\Console\Commands\Trcd;

use Illuminate\Console\Command;
// Service
use App\Services\Notifications\ExpenseNotificationService;

class NotifyBeingRequestedExpense extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'command:notify_being_requested_expense';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send mails to notify that some expenses are pending approval.';

	protected $ExpenseNotificationService;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(ExpenseNotificationService $ExpenseNotificationService) {
		parent::__construct();

		$this->ExpenseNotificationService = $ExpenseNotificationService;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		try {
			$this->ExpenseNotificationService->notifyBeingRequestedList(now());
		} catch ( \Exception $e ) {
			$this->error($e);
		}
	}
}
