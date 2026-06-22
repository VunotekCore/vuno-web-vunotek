<?php
/**
 * Manejador de Google Calendar
 * Crea eventos en Google Calendar cuando se agenda una reunión
 */

header('Content-Type: application/json; charset=utf-8');

$configFile = __DIR__ . '/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de configuración del servidor.']);
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

require_once __DIR__ . '/../../vendor/LightweightGoogleCalendar.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('Datos inválidos');
    }

    $requiredFields = ['name', 'email', 'preferredDate', 'preferredTime', 'meetingType'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo '$field' es requerido");
        }
    }

    $googleConfig = [
        'client_id' => $config['google_calendar']['client_id'],
        'client_secret' => $config['google_calendar']['client_secret'],
        'refresh_token' => $config['google_calendar']['refresh_token']
    ];

    $calendar = new LightweightGoogleCalendar($googleConfig);
    $eventData = prepareEventData($data, $config);
    $calendarId = $config['google_calendar']['calendar_id'];

    if ($data['meetingType'] === 'virtual') {
        $createdEvent = $calendar->createEventWithMeet($calendarId, $eventData);
    } else {
        $createdEvent = $calendar->createEvent($calendarId, $eventData);
    }

    $additionalCalendars = $config['google_calendar']['additional_calendars'] ?? [];
    $additionalEvents = [];

    foreach ($additionalCalendars as $additionalCalendarId) {
        try {
            if ($data['meetingType'] === 'virtual') {
                $additionalEvent = $calendar->createEventWithMeet($additionalCalendarId, $eventData);
            } else {
                $additionalEvent = $calendar->createEvent($additionalCalendarId, $eventData);
            }
            $additionalEvents[] = [
                'calendar_id' => $additionalCalendarId,
                'event_id' => $additionalEvent['id'],
                'link' => $createdEvent['htmlLink']
            ];
        } catch (Exception $e) {
            error_log("Error al crear evento en calendario $additionalCalendarId: " . $e->getMessage());
        }
    }

    http_response_code(200);
    $response = [
        'success' => true,
        'message' => 'Reunión agendada exitosamente',
        'event' => [
            'id' => $createdEvent['id'],
            'link' => $createdEvent['htmlLink'],
            'start' => $createdEvent['start']['dateTime'],
        ]
    ];

    if (!empty($additionalEvents)) {
        $response['additional_calendars'] = $additionalEvents;
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    $errorMessage = 'Error al agendar la reunión. Por favor intenta nuevamente.';
    if ($config['app']['debug'] ?? false) {
        $errorMessage .= ' Debug: ' . $e->getMessage();
    }
    echo json_encode([
        'success' => false,
        'message' => $errorMessage
    ]);
}

function prepareEventData($data, $config) {
    $timezone = $config['google_calendar']['timezone'];
    $date = $data['preferredDate'];
    $time = convertTimeToHour($data['preferredTime']);
    $startDateTime = $date . 'T' . $time . ':00';
    $startTimestamp = strtotime($startDateTime);
    $endTimestamp = $startTimestamp + 3600;
    $endDateTime = date('Y-m-d\TH:i:s', $endTimestamp);
    $description = prepareEventDescription($data);

    $meetingTypes = [
        'virtual' => '💻 Reunión Virtual',
        'presencial' => '🏢 Reunión Presencial',
        'phone' => '📞 Llamada Telefónica'
    ];
    $meetingTypeLabel = $meetingTypes[$data['meetingType']] ?? 'Reunión';

    $eventData = [
        'summary' => $meetingTypeLabel . ' - ' . $data['name'],
        'description' => $description,
        'start' => [
            'dateTime' => $startDateTime,
            'timeZone' => $timezone,
        ],
        'end' => [
            'dateTime' => $endDateTime,
            'timeZone' => $timezone,
        ],
        'attendees' => prepareAttendees($data, $config),
        'reminders' => [
            'useDefault' => false,
            'overrides' => [
                ['method' => 'email', 'minutes' => 24 * 60],
                ['method' => 'popup', 'minutes' => 30],
            ],
        ],
        'guestsCanModify' => false,
        'guestsCanInviteOthers' => false,
        'guestsCanSeeOtherGuests' => true,
    ];

    if ($data['meetingType'] === 'presencial') {
        $eventData['location'] = 'Por confirmar';
    }

    if ($data['meetingType'] === 'virtual') {
        $eventData['conferenceData'] = [
            'createRequest' => [
                'requestId' => uniqid(),
                'conferenceSolutionKey' => ['type' => 'hangoutsMeet']
            ]
        ];
    }

    return $eventData;
}

function convertTimeToHour($timeString) {
    $timeMap = [
        '9am' => '09:00',
        '10am' => '10:00',
        '11am' => '11:00',
        '2pm' => '14:00',
        '3pm' => '15:00',
        '4pm' => '16:00',
    ];
    return $timeMap[$timeString] ?? '09:00';
}

function prepareEventDescription($data) {
    $name = $data['name'];
    $email = $data['email'];
    $phone = $data['phone'] ?? 'No especificado';
    $company = $data['company'] ?? 'No especificada';
    $message = $data['message'];
    $meetingType = $data['meetingType'];

    $meetingTypeLabels = [
        'virtual' => 'Virtual (Google Meet)',
        'presencial' => 'Presencial',
        'phone' => 'Llamada Telefónica'
    ];
    $meetingTypeLabel = $meetingTypeLabels[$meetingType] ?? $meetingType;

    return <<<DESC
INFORMACIÓN DEL CONTACTO:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
👤 Nombre: $name
📧 Email: $email
📱 Teléfono: $phone
🏢 Empresa: $company

TIPO DE REUNIÓN:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
$meetingTypeLabel

TEMAS A DISCUTIR:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
$message

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Reunión agendada desde: Vunotek Contact Form
DESC;
}

function prepareAttendees($data, $config) {
    $attendees = [];

    $attendees[] = [
        'email' => $data['email'],
        'displayName' => $data['name'],
        'responseStatus' => 'needsAction',
        'organizer' => false
    ];

    $additionalAttendees = $config['google_calendar']['additional_attendees'] ?? [];

    foreach ($additionalAttendees as $email => $name) {
        if (is_numeric($email)) {
            $email = $name;
            $name = '';
        }
        $attendees[] = [
            'email' => $email,
            'displayName' => $name,
            'responseStatus' => 'accepted',
            'organizer' => false
        ];
    }

    return $attendees;
}
