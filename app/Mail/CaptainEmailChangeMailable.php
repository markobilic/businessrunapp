<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CaptainEmailChangeMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $oldEmail;
    public string $newEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(string $oldEmail, string $newEmail)
    {
        $this->oldEmail = $oldEmail;
        $this->newEmail = $newEmail;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject('Captain Email Change Request')
            ->view('emails.captain-email-change');
    }
}
