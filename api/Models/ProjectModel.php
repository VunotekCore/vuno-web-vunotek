<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class ProjectModel
{
    public function __construct(private PDO $db) {}

    public function list(?string $locale = null, ?string $status = null, int $page = 1, int $perPage = 20): array
    {
        $where = [];
        $params = [];

        if ($locale !== null) {
            $where[] = 'p.locale = :locale';
            $params['locale'] = $locale;
        }
        if ($status !== null) {
            $where[] = 'p.status = :status';
            $params['status'] = $status;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $perPage;

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM projects p $whereClause");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT p.*
            FROM projects p
            $whereClause
            ORDER BY p.sort_order ASC, p.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $projects = $stmt->fetchAll();
        foreach ($projects as &$project) {
            $project['tech'] = $project['tech'] ? json_decode($project['tech'], true) : null;
            $project['is_saas'] = (bool) $project['is_saas'];
        }

        return [
            'projects' => $projects,
            'total'    => $total,
            'page'     => $page,
            'pages'    => (int) ceil($total / $perPage),
        ];
    }

    public function listPublic(?string $locale = null): array
    {
        $where = ['p.status = :status'];
        $params = ['status' => 'published'];

        if ($locale !== null) {
            $where[] = 'p.locale = :locale';
            $params['locale'] = $locale;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $stmt = $this->db->prepare("
            SELECT p.*
            FROM projects p
            $whereClause
            ORDER BY p.sort_order ASC, p.created_at DESC
        ");
        $stmt->execute($params);

        $projects = $stmt->fetchAll();
        foreach ($projects as &$project) {
            $project['tech'] = $project['tech'] ? json_decode($project['tech'], true) : null;
            $project['is_saas'] = (bool) $project['is_saas'];
        }

        return $projects;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $project = $stmt->fetch();
        if ($project) {
            $project['tech'] = $project['tech'] ? json_decode($project['tech'], true) : null;
            $project['is_saas'] = (bool) $project['is_saas'];
        }
        return $project ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $project = $stmt->fetch();
        if ($project) {
            $project['tech'] = $project['tech'] ? json_decode($project['tech'], true) : null;
            $project['is_saas'] = (bool) $project['is_saas'];
        }
        return $project ?: null;
    }

    public function create(array $data): int
    {
        $tech = isset($data['tech']) ? json_encode($data['tech']) : null;

        $stmt = $this->db->prepare("
            INSERT INTO projects (name, tag, slug, image, live_url, is_saas, description, tech, locale, status, sort_order)
            VALUES (:name, :tag, :slug, :image, :live_url, :is_saas, :description, :tech, :locale, :status, :sort_order)
        ");
        $stmt->execute([
            'name'        => $data['name'],
            'tag'         => $data['tag'],
            'slug'        => $data['slug'],
            'image'       => $data['image'] ?? null,
            'live_url'    => $data['live_url'] ?? null,
            'is_saas'     => (int) !empty($data['is_saas']),
            'description' => $data['description'] ?? null,
            'tech'        => $tech,
            'locale'      => $data['locale'] ?? 'es',
            'status'      => $data['status'] ?? 'draft',
            'sort_order'  => $data['sort_order'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['name', 'tag', 'slug', 'image', 'live_url', 'is_saas', 'description', 'tech', 'locale', 'status', 'sort_order'];
        $fields = [];
        $params = ['id' => $id];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $value = $data[$field];
                if ($field === 'tech' && is_array($value)) {
                    $value = json_encode($value);
                }
                if ($field === 'is_saas') {
                    $value = (int) !empty($value);
                }
                $fields[] = "$field = :$field";
                $params[$field] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = 'UPDATE projects SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM projects WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
