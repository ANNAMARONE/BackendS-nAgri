<?php

namespace App\Notifications;

use Twilio\Rest\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SendSMS extends Notification
{
    public function toTwilio($notifiable)
    {
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
    
        $message = $twilio->messages
                        ->create("+17722910795", // to
                            array(
                                "body" => "Hello from Laravel!",
                                "from" => "784615847"
                            )
                        );
        return $message;
    }
        }
    
    

