<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use App\Services\AuthService;
use App\Traits\JwtAuth;
use App\Config\Database;

class AuthController
{
    use JwtAuth;

    private UserModel $userModel;
    private AuthService $authService;

    public function __construct()
    {
        $this->userModel = new UserModel(Database::getConnection());
        $this->authService = new AuthService();
    }

    public function login(): never
    {
        $data = getJsonInput();

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if ($email === '' || $password === '') {
            jsonError('Email y contraseña son requeridos');
        }

        $user = $this->userModel->findByEmail($email);

        if ($user === null || !$this->authService->verifyPassword($password, $user['password'])) {
            jsonError('Credenciales incorrectas', 401);
        }

        $token = $this->createToken([
            'sub'         => $user['id'],
            'email'       => $user['email'],
            'role_id'     => $user['role_id'],
            'role_slug'   => $user['role_slug'],
            'permissions' => $user['permissions'],
        ]);

        $config = $this->getConfig();
        $expiresIn = $config['jwt']['expires_in'] ?? 86400;

        setcookie('admin_token', $token, [
            'expires'  => time() + $expiresIn,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly'  => true,
            'samesite' => 'Lax',
        ]);

        jsonSuccess([
            'user' => [
                'id'          => $user['id'],
                'email'       => $user['email'],
                'name'        => $user['name'],
                'role_id'     => $user['role_id'],
                'role_name'   => $user['role_name'],
                'role_slug'   => $user['role_slug'],
                'permissions' => $user['permissions'],
            ],
        ]);
    }

    public function verify(): never
    {
        $payload = $this->requireAuth();

        $user = $this->userModel->findById((int) $payload['sub']);

        if ($user === null) {
            jsonError('Usuario no encontrado', 401);
        }

        jsonSuccess(['user' => $user]);
    }
}
