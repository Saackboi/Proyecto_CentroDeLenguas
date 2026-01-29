<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Recuperacion de contrasena</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        color: #333333;
        padding: 20px;
      }
      .container {
        background-color: #ffffff;
        border-radius: 8px;
        padding: 20px;
        max-width: 600px;
        margin: auto;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      }
      .header-img {
        width: 100%;
        max-height: 150px;
        object-fit: cover;
        border-radius: 8px 8px 0 0;
      }
      h3 {
        color: #2c3e50;
      }
      .cta {
        display: inline-block;
        background-color: #2c7be5;
        color: #ffffff;
        padding: 10px 16px;
        border-radius: 6px;
        text-decoration: none;
        margin: 12px 0;
      }
      a {
        color: #2c7be5;
      }
      .footer {
        font-size: 12px;
        color: #888888;
        margin-top: 20px;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <img
        src="https://cc.utp.ac.pa/sites/default/files/documentos/2024/imagen/logocel640.png"
        alt="Centro Especializado en Lenguas"
        class="header-img"
      />

      <h3>Estimado {{ $rol ?? 'estudiante' }},</h3>
      <p>Hemos recibido una solicitud para restablecer su contrasena.</p>

      <p>Haga clic en el siguiente enlace para continuar:</p>
      <p><a class="cta" href="{{ $link }}">Restablecer contrasena</a></p>

      <p>Si no puede abrir el boton, copie y pegue este enlace en su navegador:</p>
      <p><a href="{{ $link }}">{{ $link }}</a></p>

      <p><em>Este enlace expira en 24 horas.</em></p>

      <div class="footer">
        Atentamente,<br />
        Area de Coordinacion<br />
        Centro Especializado en Lenguas
      </div>
    </div>
  </body>
</html>
