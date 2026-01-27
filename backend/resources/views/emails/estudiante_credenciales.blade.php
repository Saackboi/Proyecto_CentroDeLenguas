<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Acceso al portal de estudiantes</title>
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
      .credentials {
        background-color: #ecf0f1;
        padding: 10px;
        border-radius: 5px;
        margin: 15px 0;
      }
      a {
        color: #3498db;
        text-decoration: none;
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

      <h3>Estimado estudiante,</h3>
      <p>Le informamos que su cuenta de estudiante ha sido creada correctamente.</p>

      <p>A continuación, encontrará sus credenciales temporales para acceder al sistema:</p>

      <div class="credentials">
        <p><strong>Usuario:</strong> {{ $correo }}</p>
        <p><strong>Contraseña:</strong> {{ $contrasenaTemporal }}</p>
      </div>

      <p>Puede iniciar sesión en el portal de estudiantes desde el siguiente enlace:</p>
      <p><a href="{{ config('app.url') }}">{{ config('app.url') }}</a></p>

      <p><em>Por seguridad, cambie su contraseña al ingresar.</em></p>

      <p>Si tiene alguna consulta, no dude en comunicarse con el área encargada.</p>

      <div class="footer">
        Atentamente,<br />
        Área de Coordinación<br />
        Centro Especializado en Lenguas
      </div>
    </div>
  </body>
</html>
