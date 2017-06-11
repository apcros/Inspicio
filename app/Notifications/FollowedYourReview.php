<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class FollowedYourReview extends Notification
{
    //use Queueable;
    //TODO : Set a worker and let it be queued
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $user;
    private $review;

    public function __construct($user, $review)
    {
        $this->user = $user;
        $this->review = $review;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        //TODO : Update the email template
        return (new MailMessage)
                    ->greeting('Someone just followed your review !')
                    ->line('Hey, '.$this->user->nickname.' just followed your review "'.$this->review->name.'"')
                    ->action('See review', url(env('APP_URL').'/reviews/'.$this->review->id.'/view'))
                    ->line('Thanks for using Inspicio for your reviews !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
