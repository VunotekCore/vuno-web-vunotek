<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class UserModel
{
    public function __construct(private PDO $db) {}

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name AS role_name, r.slug AS role_slug, r.permissions
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE u.email = :email LIMIT 1
        ");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if ($user && isset($user['permissions'])) {
            $user['permissions'] = json_decode($user['permissions'], true);
        }
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.email, u.name, u.role_id, r.name AS role_name, r.slug AS role_slug, r.permissions, u.created_at
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE u.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        if ($user && isset($user['permissions'])) {
            $user['permissions'] = json_decode($user['permissions'], true);
        }
        return $user ?: null;
    }

    public function create(string $email, string $password, string $name, int $roleId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (email, password, name, role_id) VALUES (:email, :password, :name, :role_id)'
        );
        $stmt->execute([
            'email'    => $email,
            'password' => $password,
            'name'     => $name,
            'role_id'  => $roleId,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        foreach (['email', 'name', 'role_id'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
