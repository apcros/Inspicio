<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UseOfReferralLink extends Notification implements ShouldQueue {
	use Queueable;

	/**
	 * Create a new notification instance.
	 *
	 * @return void
	 */

	private $referee;
	private $user;

	public function __construct($referee, $user) {
		$this->referee = $referee;
		$this->user    = $user;
	}

	/**
	 * Get the notification's delivery channels.
	 *
	 * @param  mixed  $notifiable
	 * @return array
	 */
	public function via($notifiable) {
		return ['mail'];
	}

	/**
	 * Get the mail representation of the notification.
	 *
	 * @param  mixed  $notifiable
	 * @return \Illuminate\Notifications\Messages\MailMessage
	 */
	public function toMail($notifiable) {
		return (new MailMessage)
			->subject($this->referee->nickname . ' used your referral link')
			->greeting('Hello ' . $this->user->nickname . ' , We have 5 more points for you !')
			->line($this->referee->nickname . 'used your referral link, that means you both get 5 additional Inspicio points.')
			->line('Happy reviews :)')
			->action('Take me to Inspicio ', url(env('APP_URL')))
			->line('Thanks for using Inspicio for your reviews ');
	}

	/**
	 * Get the array representation of the notification.
	 *
	 * @param  mixed  $notifiable
	 * @return array
	 */
	public function toArray($notifiable) {
		return [
			//
		];
	}
}
