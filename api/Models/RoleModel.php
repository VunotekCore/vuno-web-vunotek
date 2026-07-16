<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class RoleModel
{
    public function __construct(private PDO $db) {}

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM roles WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $role = $stmt->fetch();
        if ($role && isset($role['permissions'])) {
            $role['permissions'] = json_decode($role['permissions'], true);
        }
        return $role ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM roles WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $role = $stmt->fetch();
        if ($role && isset($role['permissions'])) {
            $role['permissions'] = json_decode($role['permissions'], true);
        }
        return $role ?: null;
    }

    public function list(): array
    {
        $stmt = $this->db->query('SELECT * FROM roles ORDER BY id ASC');
        $roles = $stmt->fetchAll();
        foreach ($roles as &$role) {
            if (isset($role['permissions'])) {
                $role['permissions'] = json_decode($role['permissions'], true);
            }
        }
        return $roles;
    }
}
