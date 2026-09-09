<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ResetpasswordMaillable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ?User $user = null;
    public ?string $clave = null;
    public ?string $empresa = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, string $clave)
    {
        $this->user = $user;
        $this->clave = $clave;
        // $this->empresa = $empresa;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): Mailable
    {
        return $this->view('emails.resetpassword')
            ->subject('Cambio de clave para ingreso al portal');
    }
}
