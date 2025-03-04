<?php

namespace App\Services;

use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    protected $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getAllCategories(int $userId): array
    {
        try {
            $categories = $this->categoryRepository->getAllByUser($userId);
            Log::info('Categories fetched successfully.', ['categories_count' => $categories->count()]);
            return $categories->toArray();
        } catch (\Exception $e) {
            Log::error('Error fetching categories.', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function createCategory(array $data, int $userId): Model
    {
        try {
            DB::beginTransaction();
            
            // Only check for active (non-deleted) categories
            $existingCategory = $this->categoryRepository->findByName($data['name']);
            
            if ($existingCategory) {
                DB::rollBack();
                Log::warning('Category creation attempted with an existing name.', ['name' => $data['name']]);
                return $existingCategory;
            }

            $category = $this->categoryRepository->create([
                'name' => trim($data['name']),
                'created_by' => $userId
            ]);

            DB::commit();
            Log::info('New category created successfully.', ['category_name' => $data['name']]);
            return $category;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create category.', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateCategory(string $id, array $data, int $userId): bool
    {
        try {
            DB::beginTransaction();
            
            $result = $this->categoryRepository->update($id, [
                'name' => trim($data['name']),
                'updated_by' => $userId
            ]);
            
            DB::commit();
            Log::info('Category updated successfully.', ['category_id' => $id, 'new_name' => $data['name']]);
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update category.', ['category_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function deleteCategory(string $id, int $userId): bool
    {
        try {
            DB::beginTransaction();
            
            $category = $this->categoryRepository->findById($id);
            $category->update(['deleted_by' => $userId]);
            $result = $this->categoryRepository->delete($id);
            
            DB::commit();
            Log::info('Category deleted successfully.', ['category_id' => $id]);
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete category.', ['category_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findCategoryById(string $id): Model
    {
        try {
            $category = $this->categoryRepository->findById($id);
            Log::info('Category found successfully.', ['category_id' => $id]);
            return $category;
        } catch (\Exception $e) {
            Log::error('Error finding category.', ['category_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
