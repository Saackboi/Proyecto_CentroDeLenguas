<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EstudianteCredencialesMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $correo,
        public string $contrasenaTemporal
    ) {
    }

    public function build()
    {
        return $this->subject('Acceso al portal de estudiantes')
            ->view('emails.estudiante_credenciales')
            ->text('emails.estudiante_credenciales_text');
    }
}
