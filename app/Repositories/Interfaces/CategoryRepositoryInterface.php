<?php

namespace App\Repositories\Interfaces;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    public function getAllByUser(int $userId): Collection;
    public function findById(string $id): ?Category;
    public function findByName(string $name): ?Category;
    public function create(array $data): Category;
    public function update(string $id, array $data): bool;
    public function delete(string $id): bool;
}