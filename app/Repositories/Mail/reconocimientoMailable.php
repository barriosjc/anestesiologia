<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class reconocimientoMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $params = null;
    public $empresa = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($param, $empresa)
    {
        $this->params = $param;
        $this->empresa = $empresa;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (isset($this->params['name_voto'])) {
            return $this->view('emails.reconocimiento_jefe');
        }else{
            return $this->view('emails.reconocimiento');
        }
    }
}
