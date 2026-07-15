<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\BlogModel;
use App\Config\Database;

class BlogController
{
    private BlogModel $blogModel;

    public function __construct()
    {
        $this->blogModel = new BlogModel(Database::getConnection());
    }

    public function list(): never
    {
        $locale = $_GET['locale'] ?? null;
        $status = $_GET['status'] ?? null;
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $result = $this->blogModel->list($locale, $status, $page);
        jsonSuccess($result);
    }

    public function get(): never
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $slug = $_GET['slug'] ?? null;

        if ($id) {
            $post = $this->blogModel->findById($id);
        } elseif ($slug) {
            $post = $this->blogModel->findBySlug($slug);
        } else {
            jsonError('ID o slug requerido');
        }

        if ($post === null) {
            jsonError('Post no encontrado', 404);
        }

        jsonSuccess($post);
    }

    public function create(): never
    {
        $data = getJsonInput();

        $required = ['title', 'slug', 'excerpt', 'content', 'category_id'];
        $errors = [];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "El campo '$field' es requerido";
            }
        }
        if (!empty($errors)) {
            jsonError('Validación fallida', 422, $errors);
        }

        $existing = $this->blogModel->findBySlug($data['slug']);
        if ($existing !== null) {
            jsonError('Ya existe un post con ese slug', 409);
        }

        $id = $this->blogModel->create($data);
        $post = $this->blogModel->findById($id);

        jsonSuccess($post, 'Post creado exitosamente', 201);
    }

    public function update(): never
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            jsonError('ID requerido');
        }

        $existing = $this->blogModel->findById($id);
        if ($existing === null) {
            jsonError('Post no encontrado', 404);
        }

        $data = getJsonInput();

        if (isset($data['slug']) && $data['slug'] !== $existing['slug']) {
            $conflict = $this->blogModel->findBySlug($data['slug']);
            if ($conflict !== null) {
                jsonError('Ya existe un post con ese slug', 409);
            }
        }

        $this->blogModel->update($id, $data);
        $post = $this->blogModel->findById($id);

        jsonSuccess($post, 'Post actualizado exitosamente');
    }

    public function delete(): never
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            jsonError('ID requerido');
        }

        $existing = $this->blogModel->findById($id);
        if ($existing === null) {
            jsonError('Post no encontrado', 404);
        }

        $this->blogModel->delete($id);
        jsonSuccess(null, 'Post eliminado exitosamente');
    }
}
