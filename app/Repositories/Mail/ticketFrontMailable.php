<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ticketFrontMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket = null;
    public $cliente = null;
    public $origen = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($ticket, $cliente, $origen)
    {
        $this->ticket = $ticket;
        $this->cliente = $cliente;
        $this->origen = $origen;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->origen == 'front'){
            return $this->view('emails.ticket_front');
        }else{
            return $this->view('emails.ticket_back');
        }
    }
}
