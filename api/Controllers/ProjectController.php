<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\ProjectModel;

class ProjectController
{
    private ProjectModel $projectModel;

    public function __construct()
    {
        $this->projectModel = new ProjectModel(\App\Config\Database::getConnection());
    }

    public function list(): never
    {
        $this->requirePermission('projects', 'list');

        $locale = $_GET['locale'] ?? null;
        $status = $_GET['status'] ?? null;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($_GET['per_page'] ?? 20)));

        $result = $this->projectModel->list($locale, $status, $page, $perPage);
        jsonSuccess($result);
    }

    public function listPublic(): never
    {
        $locale = $_GET['locale'] ?? null;
        $projects = $this->projectModel->listPublic($locale);
        jsonSuccess($projects);
    }

    public function get(): never
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $slug = $_GET['slug'] ?? null;

        if ($id) {
            $project = $this->projectModel->findById($id);
        } elseif ($slug) {
            $project = $this->projectModel->findBySlug($slug);
        } else {
            jsonError('ID o slug requerido');
        }

        if ($project === null) {
            jsonError('Proyecto no encontrado', 404);
        }

        jsonSuccess($project);
    }

    public function create(): never
    {
        $this->requirePermission('projects', 'create');

        $data = getJsonInput();

        $required = ['name', 'tag', 'slug'];
        $errors = [];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "El campo '$field' es requerido";
            }
        }
        if (!empty($errors)) {
            jsonError('Validación fallida', 422, $errors);
        }

        $existing = $this->projectModel->findBySlug($data['slug']);
        if ($existing !== null) {
            jsonError('Ya existe un proyecto con ese slug', 409);
        }

        $id = $this->projectModel->create($data);
        $project = $this->projectModel->findById($id);

        jsonSuccess($project, 'Proyecto creado exitosamente', 201);
    }

    public function update(): never
    {
        $this->requirePermission('projects', 'edit');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            jsonError('ID requerido');
        }

        $existing = $this->projectModel->findById($id);
        if ($existing === null) {
            jsonError('Proyecto no encontrado', 404);
        }

        $data = getJsonInput();

        if (isset($data['slug']) && $data['slug'] !== $existing['slug']) {
            $conflict = $this->projectModel->findBySlug($data['slug']);
            if ($conflict !== null) {
                jsonError('Ya existe un proyecto con ese slug', 409);
            }
        }

        $this->projectModel->update($id, $data);
        $project = $this->projectModel->findById($id);

        jsonSuccess($project, 'Proyecto actualizado exitosamente');
    }

    public function delete(): never
    {
        $this->requirePermission('projects', 'delete');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            jsonError('ID requerido');
        }

        $existing = $this->projectModel->findById($id);
        if ($existing === null) {
            jsonError('Proyecto no encontrado', 404);
        }

        $this->projectModel->delete($id);
        jsonSuccess(null, 'Proyecto eliminado exitosamente');
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
