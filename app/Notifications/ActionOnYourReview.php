<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActionOnYourReview extends Notification implements ShouldQueue {
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $user;
    private $review;
    private $action;

    public function __construct($user, $review, $action) {
        $this->user   = $user;
        $this->review = $review;
        $this->action = $action;
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
        //TODO : Update the email template
        return (new MailMessage)
            ->greeting('Someone just ' . $this->action . ' your review !')
            ->line('Hey, ' . $this->user->nickname . ' just ' . $this->action . ' your review "' . $this->review->name . '"')
            ->action('See review', url(env('APP_URL') . '/reviews/' . $this->review->id . '/view'))
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
