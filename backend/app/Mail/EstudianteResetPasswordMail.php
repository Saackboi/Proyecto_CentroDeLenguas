<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EstudianteResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $correo,
        public string $link,
        public string $rol = 'estudiante',
        public string $portal = 'estudiantes'
    ) {
    }

    public function build()
    {
        return $this->subject('Recuperacion de contrasena')
            ->view('emails.estudiante_reset_password')
            ->text('emails.estudiante_reset_password_text');
    }
}
