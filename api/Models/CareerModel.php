<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class CareerModel
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

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM job_positions p $whereClause");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT p.*
            FROM job_positions p
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

        $positions = $stmt->fetchAll();
        foreach ($positions as &$pos) {
            $pos['questions'] = $pos['questions'] ? json_decode($pos['questions'], true) : null;
        }

        return [
            'positions' => $positions,
            'total'     => $total,
            'page'      => $page,
            'pages'     => (int) ceil($total / $perPage),
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
            SELECT p.id, p.title, p.slug, p.short_description, p.location, p.type, p.category, p.locale, p.created_at
            FROM job_positions p
            $whereClause
            ORDER BY p.sort_order ASC, p.created_at DESC
            LIMIT 50
        ");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function findByIdPublic(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, title, questions, passing_score, locale, type
            FROM job_positions
            WHERE id = :id AND status = 'published'
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $pos = $stmt->fetch();
        if ($pos) {
            $pos['questions'] = $pos['questions'] ? json_decode($pos['questions'], true) : null;
        }
        return $pos ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM job_positions WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $pos = $stmt->fetch();
        if ($pos) {
            $pos['questions'] = $pos['questions'] ? json_decode($pos['questions'], true) : null;
        }
        return $pos ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM job_positions WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $pos = $stmt->fetch();
        if ($pos) {
            $pos['questions'] = $pos['questions'] ? json_decode($pos['questions'], true) : null;
        }
        return $pos ?: null;
    }

    public function create(array $data): int
    {
        $questions = isset($data['questions']) ? json_encode($data['questions']) : null;

        $stmt = $this->db->prepare("
            INSERT INTO job_positions (title, slug, short_description, full_description, requirements, responsibilities, location, type, category, questions, passing_score, locale, status, sort_order)
            VALUES (:title, :slug, :short_description, :full_description, :requirements, :responsibilities, :location, :type, :category, :questions, :passing_score, :locale, :status, :sort_order)
        ");
        $stmt->execute([
            'title'              => $data['title'],
            'slug'               => $data['slug'],
            'short_description'  => $data['short_description'] ?? null,
            'full_description'   => $data['full_description'] ?? null,
            'requirements'       => $data['requirements'] ?? null,
            'responsibilities'   => $data['responsibilities'] ?? null,
            'location'           => $data['location'] ?? 'Remote',
            'type'               => $data['type'] ?? 'remote',
            'category'           => $data['category'] ?? null,
            'questions'          => $questions,
            'passing_score'      => $data['passing_score'] ?? 70,
            'locale'             => $data['locale'] ?? 'es',
            'status'             => $data['status'] ?? 'draft',
            'sort_order'         => $data['sort_order'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['title', 'slug', 'short_description', 'full_description', 'requirements', 'responsibilities', 'location', 'type', 'category', 'questions', 'passing_score', 'locale', 'status', 'sort_order'];
        $fields = [];
        $params = ['id' => $id];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $value = $data[$field];
                if ($field === 'questions' && is_array($value)) {
                    $value = json_encode($value);
                }
                $fields[] = "$field = :$field";
                $params[$field] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = 'UPDATE job_positions SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM job_positions WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function createApplication(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO job_applications (position_id, name, email, phone, cv_url, assessment_score, assessment_passed, status)
            VALUES (:position_id, :name, :email, :phone, :cv_url, :assessment_score, :assessment_passed, :status)
        ");
        $stmt->execute([
            'position_id'       => $data['position_id'],
            'name'              => $data['name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'] ?? null,
            'cv_url'            => $data['cv_url'] ?? null,
            'assessment_score'  => $data['assessment_score'] ?? 0,
            'assessment_passed' => !empty($data['assessment_passed']) ? 1 : 0,
            'status'            => $data['assessment_passed'] ? 'pending' : 'rejected',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getApplications(int $positionId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM job_applications WHERE position_id = :position_id ORDER BY created_at DESC
        ");
        $stmt->execute(['position_id' => $positionId]);
        return $stmt->fetchAll();
    }
}
