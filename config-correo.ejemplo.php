<?php
// Copia este archivo como config-correo.php (en la misma carpeta) y llena los datos de la cuenta que envía.
// config-correo.php no se guarda en el repositorio, para que la contraseña nunca quede publicada.

return [
    // Quién recibe cada solicitud
    'destinatarios' => ['gustavo.s@devss.com.mx', 'thousandemotions.lua@gmail.com'],

    // Cuenta que envía los correos
    // Déjalo vacío para enviar como la cuenta de smtp_usuario (lo recomendado).
    'remitente'        => '',
    'nombre_remitente' => 'Timeout Education',
    'asunto'           => 'Nueva solicitud: High School en el extranjero',

    // Servidor SMTP del proveedor de correo de esa cuenta:
    //   Google Workspace / Gmail: smtp.gmail.com, 587, tls (usa una "contraseña de aplicación")
    //   Microsoft 365 / Outlook:  smtp.office365.com, 587, tls
    //   Correo de Hostinger:      smtp.hostinger.com, 465, ssl
    //   GoDaddy:                  smtpout.secureserver.net, 465, ssl
    //   Zoho:                     smtp.zoho.com, 465, ssl
    'smtp_host'     => 'smtp.gmail.com',
    'smtp_puerto'   => 587,
    'smtp_cifrado'  => 'tls',            // 'tls' para el puerto 587, 'ssl' para el 465
    'smtp_usuario'  => '',
    'smtp_password' => '',
];
