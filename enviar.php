<?php
// Recibe el formulario de contacto de la landing y lo envía por correo.
// Requiere un hosting con PHP y la función mail() habilitada (cPanel, Hostinger, GoDaddy...).

// --- Configuración -------------------------------------------------------
$DESTINATARIOS = ['gustavo.s@devss.com.mx', 'thousandemotions.lua@gmail.com'];
// Debe ser una cuenta del mismo dominio donde se publica la página; si no, el correo suele caer en spam.
$REMITENTE     = 'no-reply@timeouteducation.com';
$NOMBRE_REMITENTE = 'Landing High School';
$ASUNTO        = 'Nueva solicitud: High School en el extranjero';
// -------------------------------------------------------------------------

header('Content-Type: application/json; charset=utf-8');

function responder(int $codigo, array $datos): void {
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['ok' => false, 'error' => 'Método no permitido.']);
}

// Campo trampa para bots: las personas no lo ven, así que debe llegar vacío.
if (!empty($_POST['sitio_web'])) {
    responder(200, ['ok' => true]);
}

// Quita saltos de línea para evitar inyección de cabeceras y limita la longitud.
function limpiar(string $clave, int $max = 200): string {
    $valor = trim((string)($_POST[$clave] ?? ''));
    $valor = preg_replace('/[\r\n\t]+/', ' ', $valor);
    return mb_substr($valor, 0, $max);
}

$nombre   = limpiar('nombre', 80);
$apellido = limpiar('apellido', 80);
$email    = limpiar('email', 120);
$telefono = limpiar('telefono', 40);
$fecha    = limpiar('fecha_nacimiento', 20);
$destino  = limpiar('destino', 40);
$mensaje  = mb_substr(trim((string)($_POST['mensaje'] ?? '')), 0, 3000);

$faltantes = [];
if ($nombre === '')   $faltantes[] = 'nombre';
if ($apellido === '') $faltantes[] = 'apellido';
if ($telefono === '') $faltantes[] = 'teléfono';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $faltantes[] = 'correo electrónico';
if ($faltantes) {
    responder(422, ['ok' => false, 'error' => 'Revisa: ' . implode(', ', $faltantes) . '.']);
}

$e = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$filas = [
    'Nombre'                            => "$nombre $apellido",
    'Correo'                            => $email,
    'Teléfono / WhatsApp'               => $telefono,
    'Fecha de nacimiento del estudiante' => $fecha !== '' ? $fecha : '—',
    'Destino de interés'                => $destino !== '' ? $destino : 'Aún no lo sé',
];

$html = '<div style="font-family:Arial,sans-serif;color:#13284b;font-size:15px">'
      . '<h2 style="margin:0 0 16px">Nueva solicitud desde la landing de High School</h2>'
      . '<table cellpadding="8" style="border-collapse:collapse">';
foreach ($filas as $etiqueta => $valor) {
    $html .= '<tr><td style="border-bottom:1px solid #d6e0ea;font-weight:bold">' . $e($etiqueta) . '</td>'
           . '<td style="border-bottom:1px solid #d6e0ea">' . $e($valor) . '</td></tr>';
}
$html .= '</table>';
if ($mensaje !== '') {
    $html .= '<p style="margin:20px 0 6px;font-weight:bold">Mensaje</p><p style="margin:0">' . nl2br($e($mensaje)) . '</p>';
}
$html .= '<p style="margin-top:24px;color:#47566e;font-size:13px">Responde a este correo para contestarle directamente a ' . $e($nombre) . '.</p></div>';

$codificar = fn(string $t): string => '=?UTF-8?B?' . base64_encode($t) . '?=';
$cabeceras = [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'From: ' . $codificar($NOMBRE_REMITENTE) . " <$REMITENTE>",
    'Reply-To: ' . $codificar("$nombre $apellido") . " <$email>",
    'X-Mailer: PHP/' . phpversion(),
];

$enviado = mail(
    implode(', ', $DESTINATARIOS),
    $codificar($ASUNTO),
    $html,
    implode("\r\n", $cabeceras),
    '-f' . $REMITENTE
);

if (!$enviado) {
    responder(500, ['ok' => false, 'error' => 'No se pudo enviar el correo.']);
}
responder(200, ['ok' => true]);
