<?php

namespace App\Services;

use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class AuthorService
{
    protected $authorRepository;

    public function __construct(AuthorRepositoryInterface $authorRepository)
    {
        $this->authorRepository = $authorRepository;
    }

    public function getAllAuthors(int $userId): Collection
    {
        return $this->authorRepository->getAllByUser($userId);
    }

    public function findAuthorById(string $id): Model
    {
        return $this->authorRepository->findById($id);
    }

    public function createAuthor(array $data, int $userId): Model
    {
        try {
            $author = $this->authorRepository->create([
                'name' => trim($data['name']),
                'created_by' => $userId,
                'updated_by' => $userId
            ]);

            Log::info('Author created successfully', ['author_name' => $data['name']]);
            return $author;
            
        } catch (\Exception $e) {
            Log::error('Failed to create author', ['error' => $e->getMessage()]);
            throw new \Exception('Failed to create author. Please try again.');
        }
    }

    public function updateAuthor(string $id, array $data, int $userId): Model
    {
        try {
            $author = $this->authorRepository->findById($id);
            $data['updated_by'] = $userId;
            $this->authorRepository->update($author, $data);

            Log::info('Author updated successfully', ['author_id' => $id]);
            return $author;
        } catch (\Exception $e) {
            Log::error('Failed to update author', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function deleteAuthor(string $id, int $userId): bool
    {
        try {
            $author = $this->authorRepository->findById($id);
            $author->update(['deleted_by' => $userId]);
            $this->authorRepository->delete($author);

            Log::info('Author deleted successfully', ['author_id' => $id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete author', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}



