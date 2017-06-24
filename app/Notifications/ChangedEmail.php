<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChangedEmail extends Notification implements ShouldQueue {
	use Queueable;

	/**
	 * Create a new notification instance.
	 *
	 * @return void
	 */
	private $user;
	public function __construct($user) {
		$this->user = $user;
	}

	/**
	 * Get the notification's delivery channels.
	 *
	 * @param  mixed  $notifiable
	 * @return array
	 */
	public function via() {
		return ['mail'];
	}

	/**
	 * Get the mail representation of the notification.
	 *
	 * @param  mixed  $notifiable
	 * @return \Illuminate\Notifications\Messages\MailMessage
	 */
	public function toMail() {
		return (new MailMessage)
			->subject('Inspicio : Email updated !')
			->greeting('Hello ' . $this->user->name . ' ! You just updated your email')
			->line('Your account has been switched to limited access until you confirm your new email')
			->line('Just click the button below !')
			->action('Confirm my email', url(env('APP_URL') . '/confirm/' . $this->user->id . '/' . $this->user->confirm_token))
			->line('Thanks for using Inspicio for your reviews !');
	}

	/**
	 * Get the array representation of the notification.
	 *
	 * @param  mixed  $notifiable
	 * @return array
	 */
	public function toArray() {
		return [
			//
		];
	}
}
