<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewFollowedForTooLong extends Notification {
	use Queueable;

	private $reviews;
	private $user;
	/**
	 * Create a new notification instance.
	 *
	 * @return void
	 */
	public function __construct($user, $reviews) {
		$this->user    = $user;
		$this->reviews = $reviews;
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
		$mailmessage = (new MailMessage)
			->subject('Some of the reviews you follow need your attention !')
			->greeting('You have been following the following reviews for more than two weeks');

		foreach ($this->reviews as $review) {
			$mailmessage->line($review->name . ' - Last updated at ' . $review->updated_at)
				->action('Show review', url('/reviews/' . $review->id));
		}

		$mailmessage->line('Feel free to go and approve them')
			->line('You can disable theses notifications in your account settings')
			->line('Thank you for using Inspicio');

		return $mailmessage;
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
