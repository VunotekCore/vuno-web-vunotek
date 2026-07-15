<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Config\Database;

class CategoryController
{
    private CategoryModel $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel(Database::getConnection());
    }

    public function list(): never
    {
        $categories = $this->categoryModel->list();
        jsonSuccess($categories);
    }

    public function get(): never
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $slug = $_GET['slug'] ?? null;

        if ($id) {
            $category = $this->categoryModel->findById($id);
        } elseif ($slug) {
            $category = $this->categoryModel->findBySlug($slug);
        } else {
            jsonError('ID o slug requerido');
        }

        if ($category === null) {
            jsonError('Categoría no encontrada', 404);
        }

        jsonSuccess($category);
    }

    public function create(): never
    {
        $data = getJsonInput();

        if (empty($data['name']) || empty($data['slug'])) {
            jsonError('Nombre y slug son requeridos');
        }

        $existing = $this->categoryModel->findBySlug($data['slug']);
        if ($existing !== null) {
            jsonError('Ya existe una categoría con ese slug', 409);
        }

        $id = $this->categoryModel->create($data);
        $category = $this->categoryModel->findById($id);

        jsonSuccess($category, 'Categoría creada exitosamente', 201);
    }

    public function update(): never
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            jsonError('ID requerido');
        }

        $existing = $this->categoryModel->findById($id);
        if ($existing === null) {
            jsonError('Categoría no encontrada', 404);
        }

        $data = getJsonInput();

        if (isset($data['slug']) && $data['slug'] !== $existing['slug']) {
            $conflict = $this->categoryModel->findBySlug($data['slug']);
            if ($conflict !== null) {
                jsonError('Ya existe una categoría con ese slug', 409);
            }
        }

        $this->categoryModel->update($id, $data);
        $category = $this->categoryModel->findById($id);

        jsonSuccess($category, 'Categoría actualizada exitosamente');
    }

    public function delete(): never
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            jsonError('ID requerido');
        }

        $existing = $this->categoryModel->findById($id);
        if ($existing === null) {
            jsonError('Categoría no encontrada', 404);
        }

        $deleted = $this->categoryModel->delete($id);
        if (!$deleted) {
            jsonError('No se puede eliminar: la categoría tiene posts asociados', 409);
        }

        jsonSuccess(null, 'Categoría eliminada exitosamente');
    }
}
