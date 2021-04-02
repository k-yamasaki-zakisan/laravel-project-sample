<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BeingRequestedExpense extends Mailable
{
    use Queueable, SerializesModels;

	protected $settings;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Array $settings) {
		$this->settings = $settings;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
		// 送信元
		if ( !empty($this->settings['from']) ) $this->from($this->settings['from']);
		// 返信先
		if ( empty($this->settings['reply_to']) ) $this->replyTo(preg_replace('/^.*@/', 'noreply@', config('mail.from.address')));
		else $this->replyTo($this->settings['reply_to']);
		// 件名
		if ( empty($this->settings['subject']) ) $this->subject('[TRCD管理画面]経費申請通知');
		else $this->subject('[TRCD管理画面]' . $this->settings['subject']);

        return $this->view('emails.notifications.being_requested_expense')
			->with([
				'ExpenseSummaries' => $this->settings['ExpenseSummaries'] ?? collect(),
				'ClientEmployees' => $this->settings['ClientEmployees'] ?? collect(),
			]);
    }
}
