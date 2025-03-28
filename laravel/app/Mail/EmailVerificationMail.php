<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function build()
    {
        $verificationUrl = url('/api/verify-email?token=' . $this->token);

        return $this->subject('Підтвердження email')
            ->view('emails.verify')
            ->with(['url' => $verificationUrl]);
    }
}
