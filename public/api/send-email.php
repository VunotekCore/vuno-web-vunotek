<?php
/**
 * Endpoint para envío de emails desde el formulario de contacto
 * Maneja tanto cotizaciones como agendamiento de reuniones
 */

header('Content-Type: application/json; charset=utf-8');

$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de configuración del servidor. Por favor contacta al administrador.'
    ]);
    exit;
}

$config = require $configFile;

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $config['app']['allowed_origins'])) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('Datos del formulario inválidos');
    }

    $requiredFields = ['name', 'email', 'message', 'formType'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo '$field' es requerido");
        }
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email inválido');
    }

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $config['email']['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['email']['smtp_username'];
    $mail->Password = $config['email']['smtp_password'];
    $mail->SMTPSecure = $config['email']['smtp_encryption'];
    $mail->Port = $config['email']['smtp_port'];
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($config['email']['from_email'], $config['email']['from_name']);

    addRecipients($mail, $config['email']['to_email'], '');

    if (!empty($config['email']['cc_email'])) {
        addRecipients($mail, $config['email']['cc_email'], '', 'cc');
    }

    $mail->addReplyTo($data['email'], $data['name']);

    $formType = $data['formType'];

    if ($formType === 'cotizar') {
        $mail->Subject = '🎯 Nueva Solicitud de Cotización - ' . $data['name'];
        $mail->isHTML(true);
        $mail->Body = generateQuoteEmailHTML($data, $config);
        $mail->AltBody = generateQuoteEmailText($data);
    } elseif ($formType === 'agendar') {
        $mail->Subject = '📅 Nueva Solicitud de Reunión - ' . $data['name'];
        $mail->isHTML(true);
        $mail->Body = generateMeetingEmailHTML($data, $config);
        $mail->AltBody = generateMeetingEmailText($data);
    } else {
        throw new Exception('Tipo de formulario inválido');
    }

    $mail->send();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Mensaje enviado exitosamente. Nos pondremos en contacto contigo pronto.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    $errorMessage = 'Error al enviar el mensaje. Por favor intenta nuevamente.';
    if ($config['app']['debug'] ?? false) {
        $errorMessage .= ' Debug: ' . $e->getMessage();
    }
    echo json_encode([
        'success' => false,
        'message' => $errorMessage
    ]);
}

function generateQuoteEmailHTML($data, $config) {
    $name = htmlspecialchars($data['name']);
    $email = htmlspecialchars($data['email']);
    $phone = htmlspecialchars($data['phone'] ?? 'No especificado');
    $company = htmlspecialchars($data['company'] ?? 'No especificada');
    $projectType = htmlspecialchars($data['projectType'] ?? 'No especificado');
    $budget = htmlspecialchars($data['budget'] ?? 'No especificado');
    $timeline = htmlspecialchars($data['timeline'] ?? 'No especificado');
    $message = nl2br(htmlspecialchars($data['message']));
    $siteName = $config['app']['site_name'] ?? 'Vunotek';

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #0b1326 0%, #1a2332 100%); color: #42b883; padding: 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; color: #42b883; }
        .header p { margin: 8px 0 0; color: #94a3b8; font-size: 14px; }
        .content { background: #f8fafc; padding: 32px; }
        .field { margin-bottom: 20px; }
        .label { font-weight: 700; color: #42b883; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
        .value { background: white; padding: 12px; border-radius: 6px; border-left: 3px solid #42b883; color: #1e293b; }
        .footer { text-align: center; padding: 24px; color: #94a3b8; font-size: 12px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 Nueva Solicitud de Cotización</h1>
            <p>Formulario de contacto - $siteName</p>
        </div>
        <div class="content">
            <div class="field"><div class="label">👤 Nombre</div><div class="value">$name</div></div>
            <div class="field"><div class="label">📧 Email</div><div class="value"><a href="mailto:$email">$email</a></div></div>
            <div class="field"><div class="label">📱 Teléfono</div><div class="value">$phone</div></div>
            <div class="field"><div class="label">🏢 Empresa / Tipo</div><div class="value">$company</div></div>
            <div class="field"><div class="label">💼 Tipo de Proyecto</div><div class="value">$projectType</div></div>
            <div class="field"><div class="label">💰 Presupuesto</div><div class="value">$budget</div></div>
            <div class="field"><div class="label">⏱️ Tiempo Estimado</div><div class="value">$timeline</div></div>
            <div class="field"><div class="label">💬 Mensaje</div><div class="value">$message</div></div>
        </div>
        <div class="footer">
            <p>Este mensaje fue enviado desde el formulario de contacto de $siteName</p>
        </div>
    </div>
</body>
</html>
HTML;
}

function generateQuoteEmailText($data) {
    $name = $data['name'];
    $email = $data['email'];
    $phone = $data['phone'] ?? 'No especificado';
    $company = $data['company'] ?? 'No especificada';
    $projectType = $data['projectType'] ?? 'No especificado';
    $budget = $data['budget'] ?? 'No especificado';
    $timeline = $data['timeline'] ?? 'No especificado';
    $message = $data['message'];

    return <<<TEXT
NUEVA SOLICITUD DE COTIZACIÓN
================================

Nombre: $name
Email: $email
Teléfono: $phone
Empresa: $company

Tipo de Proyecto: $projectType
Presupuesto: $budget
Tiempo Estimado: $timeline

Mensaje:
$message

================================
Este mensaje fue enviado desde el formulario de contacto de Vunotek
TEXT;
}

function generateMeetingEmailHTML($data, $config) {
    $name = htmlspecialchars($data['name']);
    $email = htmlspecialchars($data['email']);
    $phone = htmlspecialchars($data['phone'] ?? 'No especificado');
    $company = htmlspecialchars($data['company'] ?? 'No especificada');
    $preferredDate = htmlspecialchars($data['preferredDate'] ?? 'No especificada');
    $preferredTime = htmlspecialchars($data['preferredTime'] ?? 'No especificada');
    $meetingType = htmlspecialchars($data['meetingType'] ?? 'No especificado');
    $message = nl2br(htmlspecialchars($data['message']));
    $siteName = $config['app']['site_name'] ?? 'Vunotek';

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #0b1326 0%, #1a2332 100%); color: #42b883; padding: 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; color: #42b883; }
        .header p { margin: 8px 0 0; color: #94a3b8; font-size: 14px; }
        .content { background: #f8fafc; padding: 32px; }
        .highlight { background: #f0fdf4; padding: 16px; border-radius: 8px; border-left: 4px solid #42b883; margin-bottom: 24px; }
        .highlight strong { color: #166534; }
        .field { margin-bottom: 20px; }
        .label { font-weight: 700; color: #42b883; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
        .value { background: white; padding: 12px; border-radius: 6px; border-left: 3px solid #42b883; color: #1e293b; }
        .footer { text-align: center; padding: 24px; color: #94a3b8; font-size: 12px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📅 Nueva Solicitud de Reunión</h1>
            <p>Formulario de contacto - $siteName</p>
        </div>
        <div class="content">
            <div class="highlight">
                <strong>📆 Fecha:</strong> $preferredDate<br>
                <strong>🕐 Hora:</strong> $preferredTime<br>
                <strong>💻 Tipo:</strong> $meetingType
            </div>
            <div class="field"><div class="label">👤 Nombre</div><div class="value">$name</div></div>
            <div class="field"><div class="label">📧 Email</div><div class="value"><a href="mailto:$email">$email</a></div></div>
            <div class="field"><div class="label">📱 Teléfono</div><div class="value">$phone</div></div>
            <div class="field"><div class="label">🏢 Empresa / Tipo</div><div class="value">$company</div></div>
            <div class="field"><div class="label">💬 Temas a Discutir</div><div class="value">$message</div></div>
        </div>
        <div class="footer">
            <p>Este mensaje fue enviado desde el formulario de contacto de $siteName</p>
        </div>
    </div>
</body>
</html>
HTML;
}

function generateMeetingEmailText($data) {
    $name = $data['name'];
    $email = $data['email'];
    $phone = $data['phone'] ?? 'No especificado';
    $company = $data['company'] ?? 'No especificada';
    $preferredDate = $data['preferredDate'] ?? 'No especificada';
    $preferredTime = $data['preferredTime'] ?? 'No especificada';
    $meetingType = $data['meetingType'] ?? 'No especificado';
    $message = $data['message'];

    return <<<TEXT
NUEVA SOLICITUD DE REUNIÓN
================================

DETALLES DE LA REUNIÓN:
Fecha: $preferredDate
Hora: $preferredTime
Tipo: $meetingType

INFORMACIÓN DEL CONTACTO:
Nombre: $name
Email: $email
Teléfono: $phone
Empresa: $company

Temas a Discutir:
$message

================================
Este mensaje fue enviado desde el formulario de contacto de Vunotek
TEXT;
}

function addRecipients($mail, $emails, $defaultName = '', $type = 'to') {
    if (is_string($emails)) {
        switch ($type) {
            case 'cc': $mail->addCC($emails, $defaultName); break;
            case 'bcc': $mail->addBCC($emails, $defaultName); break;
            default: $mail->addAddress($emails, $defaultName);
        }
        return;
    }
    if (is_array($emails)) {
        foreach ($emails as $email => $name) {
            if (is_numeric($email)) {
                $email = $name;
                $name = '';
            }
            switch ($type) {
                case 'cc': $mail->addCC($email, $name); break;
                case 'bcc': $mail->addBCC($email, $name); break;
                default: $mail->addAddress($email, $name);
            }
        }
    }
}
