<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisteredAccount extends Notification implements ShouldQueue {
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
			->subject('Welcome to Inspicio !')
			->greeting('Hello ' . $this->user->name . ' ! You just created an Inspicio account.')
			->line('Before you can now start creating reviews request and reviewing other people code')
			->line('You will need to confirm your email. Just click the button below !')
			->action('Take me to Inspicio', url(env('APP_URL') . '/confirm/' . $this->user->id . '/' . $this->user->confirm_token))
			->line('Thanks for using Inspicio for your reviews !');
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
