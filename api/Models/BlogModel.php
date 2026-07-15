<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class BlogModel
{
    public function __construct(private PDO $db) {}

    public function list(?string $locale = null, ?string $status = null, int $page = 1, int $perPage = 20): array
    {
        $where = [];
        $params = [];

        if ($locale !== null) {
            $where[] = 'b.locale = :locale';
            $params['locale'] = $locale;
        }
        if ($status !== null) {
            $where[] = 'b.status = :status';
            $params['status'] = $status;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $perPage;

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM blog_posts b $whereClause");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT b.*, c.name AS category_name, c.slug AS category_slug
            FROM blog_posts b
            LEFT JOIN categories c ON c.id = b.category_id
            $whereClause
            ORDER BY b.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'posts'  => $stmt->fetchAll(),
            'total'  => $total,
            'page'   => $page,
            'pages'  => (int) ceil($total / $perPage),
        ];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT b.*, c.name AS category_name, c.slug AS category_slug
            FROM blog_posts b
            LEFT JOIN categories c ON c.id = b.category_id
            WHERE b.id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();
        return $post ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("
            SELECT b.*, c.name AS category_name, c.slug AS category_slug
            FROM blog_posts b
            LEFT JOIN categories c ON c.id = b.category_id
            WHERE b.slug = :slug LIMIT 1
        ");
        $stmt->execute(['slug' => $slug]);
        $post = $stmt->fetch();
        return $post ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO blog_posts (title, slug, excerpt, content, category_id, author, locale, image, meta_title, og_image, status)
            VALUES (:title, :slug, :excerpt, :content, :category_id, :author, :locale, :image, :meta_title, :og_image, :status)
        ");
        $stmt->execute([
            'title'       => $data['title'],
            'slug'        => $data['slug'],
            'excerpt'     => $data['excerpt'],
            'content'     => $data['content'],
            'category_id' => $data['category_id'],
            'author'      => $data['author'] ?? 'Daniel Flores',
            'locale'      => $data['locale'] ?? 'es',
            'image'       => $data['image'] ?? null,
            'meta_title'  => $data['meta_title'] ?? null,
            'og_image'    => $data['og_image'] ?? null,
            'status'      => $data['status'] ?? 'draft',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['title', 'slug', 'excerpt', 'content', 'category_id', 'author', 'locale', 'image', 'meta_title', 'og_image', 'status'];
        $fields = [];
        $params = ['id' => $id];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = 'UPDATE blog_posts SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM blog_posts WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countByStatus(): array
    {
        $stmt = $this->db->query("
            SELECT status, COUNT(*) AS count
            FROM blog_posts
            GROUP BY status
        ");
        $result = ['published' => 0, 'draft' => 0];
        while ($row = $stmt->fetch()) {
            $result[$row['status']] = (int) $row['count'];
        }
        return $result;
    }
}
