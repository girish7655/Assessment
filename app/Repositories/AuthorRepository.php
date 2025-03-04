<?php

namespace App\Repositories;

use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AuthorRepository implements AuthorRepositoryInterface
{
    protected $model;

    public function __construct(Author $model)
    {
        $this->model = $model;
    }

    public function getAllByUser(int $userId): Collection
    {
        return $this->model
            ->whereNull('deleted_at')
            ->where('created_by', $userId)
            ->get();
    }

    public function findById(string $id): ?Author
    {
        return $this->model
            ->whereNull('deleted_at')
            ->findOrFail($id);
    }

    public function findByName(string $name): ?Author
    {
        return $this->model
            ->withTrashed()
            ->where('name', $name)
            ->first();
    }

    public function findActiveByName(string $name): ?Author
    {
        return $this->model
            ->whereNull('deleted_at')
            ->where('name', $name)
            ->first();
    }

    public function create(array $data): Author
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        return $this->model->findOrFail($id)->update($data);
    }

    public function delete(string $id): bool
    {
        $author = $this->findById($id);
        return $author->delete();
    }
}
