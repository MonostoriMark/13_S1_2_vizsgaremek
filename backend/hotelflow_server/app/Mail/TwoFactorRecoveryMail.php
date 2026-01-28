<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TwoFactorRecoveryMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $recoveryUrl;

    public function __construct(User $user, string $recoveryUrl)
    {
        $this->user = $user;
        $this->recoveryUrl = $recoveryUrl;
    }

    public function build()
    {
        return $this->subject('HotelFlow – 2FA helyreállítás')
            ->view('emails.two_factor_recovery')
            ->with([
                'user' => $this->user,
                'recoveryUrl' => $this->recoveryUrl,
            ]);
    }
}

