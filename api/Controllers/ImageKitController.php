<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\ImageKitService;
use App\Traits\JwtAuth;

class ImageKitController
{
    use JwtAuth;

    private ImageKitService $service;

    private const ALLOWED_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/svg+xml',
    ];
    private const MAX_SIZE = 10 * 1024 * 1024;

    public function __construct()
    {
        $config = $this->getConfig();
        $privateKey = $config['imagekit']['private_key'] ?? '';
        $this->service = new ImageKitService($privateKey);
    }

    public function upload(): never
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonError('Método no permitido', 405);
        }

        if (empty($_FILES['file'])) {
            jsonError('No se recibió ningún archivo');
        }

        $file = $_FILES['file'];
        $folder = $_POST['folder'] ?? 'blog';

        if ($file['error'] !== UPLOAD_ERR_OK) {
            jsonError('Error al subir el archivo: ' . $this->uploadError($file['error']));
        }

        if ($file['size'] > self::MAX_SIZE) {
            jsonError('El archivo excede el límite de 10MB');
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_TYPES, true)) {
            jsonError('Tipo de archivo no permitido: ' . $mime);
        }

        try {
            $result = $this->service->upload($file['tmp_name'], $file['name'], $folder);
        } catch (\RuntimeException $e) {
            jsonError($e->getMessage());
        }

        jsonSuccess([
            'url'    => $result['url'] ?? '',
            'fileId' => $result['fileId'] ?? '',
            'name'   => $result['name'] ?? '',
        ]);
    }

    public function delete(): never
    {
        $this->requireAuth();

        $data = getJsonInput();
        $fileId = $data['fileId'] ?? '';

        if ($fileId === '') {
            jsonError('fileId requerido');
        }

        try {
            $this->service->delete($fileId);
        } catch (\RuntimeException $e) {
            jsonError($e->getMessage());
        }

        jsonSuccess(null, 'Archivo eliminado');
    }

    private function uploadError(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE   => 'Archivo excede upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE  => 'Archivo excede MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL    => 'Upload incompleto',
            UPLOAD_ERR_NO_FILE    => 'No se seleccionó archivo',
            UPLOAD_ERR_NO_TMP_DIR => 'Directorio temporal no disponible',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir en disco',
            default               => 'Error desconocido (' . $code . ')',
        };
    }
}
