<?php

namespace App\Repositories\Interfaces;

use App\Models\Author;
use Illuminate\Database\Eloquent\Collection;

interface AuthorRepositoryInterface
{
    public function getAllByUser(int $userId): Collection;
    public function findById(string $id): ?Author;
    public function findByName(string $name): ?Author;
    public function create(array $data): Author;
    public function update(string $id, array $data): bool;
    public function delete(string $id): bool;
}