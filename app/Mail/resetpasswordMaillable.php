<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class resetpasswordMaillable extends Mailable
{
    use Queueable, SerializesModels;

    public $user = null;
    public $clave = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $clave)
    {
        $this->user = $user;
        $this->clave = $clave;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.register');
    }
}
