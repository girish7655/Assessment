<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Auth\Guard;
use Inertia\Inertia;
use App\Models\Category;

class CategoryController extends Controller
{
    protected $user;

    /**
     * Injecting Auth Guard via Dependency Injection.
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = $this->auth->user();
            $categories = Category::whereNull('deleted_at')->where('created_by',$user->id)->get();
            Log::info('Categories fetched successfully.', ['categories_count' => $categories->count()]);

            return Inertia::render('Categories/CategoryList', [
                'categories' => $categories,
                'message' => "Categories retrieved successfully.",
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching categories.', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error fetching categories. Please try again later.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            Log::info('Category creation page loaded.');
            return Inertia::render('Categories/CategoryCreate', [
                'categories' => Category::whereNull('deleted_at')->get(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading the category creation page.', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error loading the category creation page. Please try again.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:25|regex:/^[a-zA-Z\s]+$/',
        ], [
            'name.required' => 'Category name is required.',
            'name.string' => 'Category name must be a valid string.',
            'name.max' => 'Category name cannot exceed 25 characters.',
            'name.regex' => 'Category name can only contain letters and spaces.',
        ]);

        try {
            $user = $this->auth->user();
            $category = Category::firstOrCreate(['name' => $validatedData['name']],['created_by' => $user->id]);

            if (!$category->wasRecentlyCreated) {
                Log::warning('Category creation attempted with an existing name.', ['name' => $validatedData['name']]);
                return redirect()->back()->withErrors(['name' => 'This author name already exists.']);
            }

            Log::info('New category created successfully.', ['category_name' => $validatedData['name']]);
            return redirect()->route('categories.create')->with('success', 'Category successfully created.');
        } catch (\Exception $e) {
            Log::error('Failed to create category.', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to create category. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // No implementation provided in the original code.
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $category = Category::findOrFail($id);
            Log::info('Category edit page loaded.', ['category_id' => $id]);

            return Inertia::render('Categories/CategoryEdit', [
                'category' => $category,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading the category edit page.', ['category_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Error loading the category edit page. Please try again.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:25|regex:/^[a-zA-Z\s]+$/|unique:categories,name,' . $id,
        ], [
            'name.required' => 'Category name is required.',
            'name.string' => 'Category name must be a valid string.',
            'name.max' => 'Category name cannot exceed 25 characters.',
            'name.regex' => 'Category name can only contain letters and spaces.',
            'name.unique' => 'Category name already exists.',
        ]);

        try {
            $category = Category::findOrFail($id);
            $category->update($validatedData);

            Log::info('Category updated successfully.', ['category_id' => $id, 'new_name' => $validatedData['name']]);

            return redirect()->route('categories.edit', ['category' => $id])->with('success', 'Category successfully updated.');
        } catch (\Exception $e) {
            Log::error('Failed to update category.', ['category_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to update category. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->update(['deleted_by' => $this->auth->user()->id]);
            $category->forceDelete();

            Log::info('Category deleted successfully.', ['category_id' => $id]);

            return redirect()->route('categories.index')->with('success', 'Category successfully deleted.');
        } catch (\Exception $e) {
            Log::error('Failed to delete category.', ['category_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->route('categories.index')->with('error', 'Failed to delete category. Please try again.');
        }
    }
}
