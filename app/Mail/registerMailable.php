<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class registerMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $user = null;
    public $empresa = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $empresa)
    {
        $this->user = $user;
        $this->empresa = $empresa;
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
