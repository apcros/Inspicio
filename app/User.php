<?php

namespace App;

use Illuminate\Notifications\Notifiable;

class User
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email',
    ];

    
    public function routeNotificationForMail()
    {
        return $this->email;
    }

    public function getKey()
    {
        return $this->email;
    }
}
