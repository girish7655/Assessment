<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    protected $model;

    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    public function findByName(string $name): ?Category
    {
        return $this->model
            ->whereNull('deleted_at')
            ->where('name', $name)
            ->first();
    }

    public function create(array $data): Category
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        return $this->model->findOrFail($id)->update($data);
    }

    public function delete(string $id): bool
    {
        $category = $this->findById($id);
        return $category->delete();
    }
}
