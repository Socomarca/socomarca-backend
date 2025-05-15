<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contraseña Temporal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2d3748;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background: #f8f9fa;
        }
        .password-box {
            background: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Socomarca</h1>
        </div>
        <div class="content">
            <h2>Hola, {{ $user->name }}</h2>
            <p>Hemos recibido una solicitud para restablecer tu contraseña. Te hemos generado una contraseña temporal que podrás utilizar para acceder a tu cuenta:</p>
            
            <div class="password-box">
                {{ $temporaryPassword }}
            </div>
            
            <p><strong>IMPORTANTE:</strong> Por seguridad, te recomendamos cambiar esta contraseña temporal inmediatamente después de iniciar sesión. El sistema te solicitará cambiarla en tu próximo inicio de sesión.</p>
            
            <p>Si no solicitaste el restablecimiento de tu contraseña, por favor contacta a nuestro equipo de soporte inmediatamente.</p>
            
            <p>Saludos cordiales,<br>
            Equipo de Socomarca</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Socomarca. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>