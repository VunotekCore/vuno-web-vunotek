<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\CareerModel;
use App\Config\Database;
use App\Services\ImageKitService;

class CareerController
{
    private CareerModel $careerModel;

    public function __construct()
    {
        $this->careerModel = new CareerModel(Database::getConnection());
    }

    public function list(): never
    {
        $this->requirePermission('careers', 'list');

        $locale = $_GET['locale'] ?? null;
        $status = $_GET['status'] ?? null;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($_GET['per_page'] ?? 20)));

        $result = $this->careerModel->list($locale, $status, $page, $perPage);
        jsonSuccess($result);
    }

    public function listPublic(): never
    {
        $locale = $_GET['locale'] ?? null;
        $positions = $this->careerModel->listPublic($locale);
        jsonSuccess($positions);
    }

    public function get(): never
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $slug = $_GET['slug'] ?? null;
        $isPublic = !empty($_GET['public']);

        if ($isPublic && $id) {
            $position = $this->careerModel->findByIdPublic($id);
        } elseif ($id) {
            $position = $this->careerModel->findById($id);
        } elseif ($slug) {
            $position = $this->careerModel->findBySlug($slug);
            if ($isPublic && $position && is_array($position['questions'])) {
                foreach ($position['questions'] as &$q) {
                    unset($q['correct']);
                }
            }
        } else {
            jsonError('ID o slug requerido');
        }

        if ($position === null) {
            jsonError('Vacante no encontrada', 404);
        }

        jsonSuccess($position);
    }

    public function create(): never
    {
        $this->requirePermission('careers', 'create');

        $data = getJsonInput();

        $required = ['title', 'slug'];
        $errors = [];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "El campo '$field' es requerido";
            }
        }
        if (!empty($errors)) {
            jsonError('Validación fallida', 422, $errors);
        }

        $existing = $this->careerModel->findBySlug($data['slug']);
        if ($existing !== null) {
            jsonError('Ya existe una vacante con ese slug', 409);
        }

        $id = $this->careerModel->create($data);
        $position = $this->careerModel->findById($id);

        jsonSuccess($position, 'Vacante creada exitosamente', 201);
    }

    public function update(): never
    {
        $this->requirePermission('careers', 'edit');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            jsonError('ID requerido');
        }

        $existing = $this->careerModel->findById($id);
        if ($existing === null) {
            jsonError('Vacante no encontrada', 404);
        }

        $data = getJsonInput();

        if (isset($data['slug']) && $data['slug'] !== $existing['slug']) {
            $conflict = $this->careerModel->findBySlug($data['slug']);
            if ($conflict !== null) {
                jsonError('Ya existe una vacante con ese slug', 409);
            }
        }

        $this->careerModel->update($id, $data);
        $position = $this->careerModel->findById($id);

        jsonSuccess($position, 'Vacante actualizada exitosamente');
    }

    public function delete(): never
    {
        $this->requirePermission('careers', 'delete');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            jsonError('ID requerido');
        }

        $existing = $this->careerModel->findById($id);
        if ($existing === null) {
            jsonError('Vacante no encontrada', 404);
        }

        $this->careerModel->delete($id);
        jsonSuccess(null, 'Vacante eliminada exitosamente');
    }

    public function apply(): never
    {
        $positionId = filter_input(INPUT_POST, 'position_id', FILTER_VALIDATE_INT);
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $answersJson = $_POST['answers'] ?? '[]';

        if (!$positionId || !$name || !$email) {
            jsonError('position_id, name y email son requeridos');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonError('Email inválido');
        }

        $position = $this->careerModel->findById($positionId);
        if ($position === null || $position['status'] !== 'published') {
            jsonError('Vacante no disponible', 404);
        }

        $answers = json_decode($answersJson, true);
        if (!is_array($answers)) {
            $answers = [];
        }

        $questions = $position['questions'] ?? [];
        $passingScore = (int) ($position['passing_score'] ?? 70);
        $total = count($questions);
        $correct = 0;

        if ($total > 0) {
            foreach ($questions as $i => $q) {
                $correctIndex = $q['correct'] ?? -1;
                $userAnswer = $answers[$i] ?? -1;
                if ($correctIndex >= 0 && (int) $userAnswer === (int) $correctIndex) {
                    $correct++;
                }
            }
        }

        $score = $total > 0 ? (int) round(($correct / $total) * 100) : 100;
        $passed = $score >= $passingScore;

        $cvUrl = '';
        if (!empty($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['cv'];
            if ($file['size'] > 5 * 1024 * 1024) {
                jsonError('El CV no puede superar los 5 MB');
            }
            $config = \config();
            $privateKey = $config['imagekit']['private_key'] ?? '';
            if ($privateKey !== '') {
                try {
                    $ik = new ImageKitService($privateKey);
                    $result = $ik->upload($_FILES['cv']['tmp_name'], $_FILES['cv']['name'], 'careers/cv');
                    $cvUrl = $result['url'] ?? '';
                } catch (\RuntimeException $e) {
                    apiLog('ERROR', 'CV upload failed: ' . $e->getMessage());
                }
            }
        }

        $this->careerModel->createApplication([
            'position_id'       => $positionId,
            'name'              => $name,
            'email'             => $email,
            'phone'             => $phone,
            'cv_url'            => $cvUrl,
            'assessment_score'  => $score,
            'assessment_passed' => $passed,
        ]);

        if ($passed) {
            $this->sendApplicationEmail($position, $name, $email, $phone, $cvUrl, $score);
        }

        jsonSuccess([
            'passed' => $passed,
            'score'  => $score,
            'total'  => $total,
            'correct' => $correct,
        ], $passed ? '¡Postulación recibida!' : 'Gracias por postularte');
    }

    private function sendApplicationEmail(array $position, string $name, string $email, string $phone, string $cvUrl, int $score): void
    {
        try {
            $config = \config();
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $config['email']['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['email']['smtp_username'];
            $mail->Password = $config['email']['smtp_password'];
            $mail->SMTPSecure = $config['email']['smtp_encryption'];
            $mail->Port = $config['email']['smtp_port'];
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($config['email']['from_email'], $config['email']['from_name']);
            $toEmails = $config['email']['to_email'] ?? [];
            if (is_string($toEmails)) {
                $mail->addAddress($toEmails);
            } elseif (is_array($toEmails)) {
                foreach ($toEmails as $toEmail => $toName) {
                    if (is_numeric($toEmail)) {
                        $mail->addAddress($toName);
                    } else {
                        $mail->addAddress($toEmail, $toName);
                    }
                }
            }

            $title = htmlspecialchars($position['title']);
            $siteName = $config['app']['site_name'] ?? 'Vunotek';

            $mail->Subject = "Nueva Postulación - $title - $name";
            $mail->isHTML(true);

            $cvLink = $cvUrl ? "<p><strong>CV:</strong> <a href=\"$cvUrl\">Descargar CV</a></p>" : '';

            $mail->Body = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background: #eef2f5; }
        .container { max-width: 600px; margin: 20px auto; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; color: #ffffff; }
        .header p { margin: 8px 0 0; color: #d1fae5; font-size: 14px; }
        .content { background: #f9f9f9; padding: 32px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width:480px) { .grid-2 { grid-template-columns: 1fr; } }
        .field { margin-bottom: 0; }
        .label { font-weight: 700; color: #059669; font-size: 13px; margin-bottom: 5px; }
        .value { background: white; padding: 12px; border-radius: 6px; border-left: 3px solid #059669; color: #1e293b; }
        .full { grid-column: 1 / -1; }
        .score-pass { color: #059669; font-weight: 700; }
        .footer { text-align: center; padding: 24px; color: #666; font-size: 12px; background: #f9f9f9; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Nueva Postulación</h1>
            <p>$siteName - Vacante: $title</p>
        </div>
        <div class="content">
            <div class="grid-2">
                <div class="field"><div class="label">Nombre</div><div class="value">$name</div></div>
                <div class="field"><div class="label">Email</div><div class="value"><a href="mailto:$email">$email</a></div></div>
                <div class="field"><div class="label">Teléfono</div><div class="value">$phone</div></div>
                <div class="field"><div class="label">Evaluación</div><div class="value score-pass">$score% (Aprobado)</div></div>
            </div>
            $cvLink
        </div>
        <div class="footer">
            <p>Postulación recibida desde el sitio web de $siteName</p>
        </div>
    </div>
</body>
</html>
HTML;
            $mail->AltBody = "Nueva Postulación - $title\n\nNombre: $name\nEmail: $email\nTeléfono: $phone\nEvaluación: $score% (Aprobado)\nCV: $cvUrl";

            $mail->send();
        } catch (\Throwable $e) {
            apiLog('ERROR', 'Failed to send application email: ' . $e->getMessage());
        }
    }

    private function requirePermission(string $module, string $action): array
    {
        $payload = requireAuth();
        $permissions = $payload['permissions'] ?? [];

        if (!empty($permissions['all'])) {
            return $payload;
        }

        $modulePerms = $permissions[$module] ?? [];
        if (!in_array($action, $modulePerms)) {
            jsonError('No tienes permisos para esta acción', 403);
        }

        return $payload;
    }
}
