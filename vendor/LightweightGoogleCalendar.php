<?php

class LightweightGoogleCalendar
{
    private $config;
    private $access_token;

    const CALENDAR_BASE_URL = 'https://www.googleapis.com/calendar/v3/calendars/';
    const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    public function __construct($config)
    {
        $this->config = $config;
        $this->access_token = null;
    }

    private function refreshToken()
    {
        $data = [
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'refresh_token' => $this->config['refresh_token'],
            'grant_type' => 'refresh_token'
        ];

        $response = $this->makeRequest(self::TOKEN_URL, 'POST', $data, false);

        if (isset($response['access_token'])) {
            $this->access_token = $response['access_token'];
            return $this->access_token;
        }

        throw new Exception('Error al refrescar token: ' . json_encode($response));
    }

    private function makeRequest($url, $method = 'GET', $data = null, $needsAuth = true)
    {
        $ch = curl_init($url);

        $headers = [];
        if ($needsAuth) {
            if (!$this->access_token) {
                $this->refreshToken();
            }
            $headers[] = 'Authorization: Bearer ' . $this->access_token;
        }

        if ($method === 'POST' || $method === 'PUT') {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('Error cURL: ' . $error);
        }

        $decoded = json_decode($response, true);

        if ($httpCode === 401 && $needsAuth) {
            $this->access_token = null;
            return $this->makeRequest($url, $method, $data, $needsAuth);
        }

        if ($httpCode >= 400) {
            throw new Exception('API Error ' . $httpCode . ': ' . $response);
        }

        return $decoded;
    }

    public function createEvent($calendarId, $eventData)
    {
        $url = self::CALENDAR_BASE_URL . $calendarId . '/events';
        return $this->makeRequest($url, 'POST', $eventData);
    }

    public function createEventWithMeet($calendarId, $eventData)
    {
        $url = self::CALENDAR_BASE_URL . $calendarId . '/events?conferenceDataVersion=1';
        return $this->makeRequest($url, 'POST', $eventData);
    }
}