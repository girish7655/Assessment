<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Auth\Guard;
use Inertia\Inertia;
use App\Models\Author;

class AuthorController extends Controller
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
            $authors = Author::whereNull('deleted_at')->where('created_by', $user->id)->get();

            // Log the successful fetch of authors
            Log::info('Authors list fetched successfully');

            return Inertia::render('Authors/AuthorList', [
                'authors' => $authors,
                'message' => "Authors List Fetched Successfully",
            ]);
        } catch (\Exception $e) {
            // Log the error in case fetching authors fails
            Log::error('Failed to fetch authors', ['error_message' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Unable to fetch authors. Please try again.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            // Log the display of the create author form
            Log::info('Displaying create author form');

            return Inertia::render('Authors/AuthorCreate', [
                'authors' => Author::whereNull('deleted_at')->get(),
            ]);
        } catch (\Exception $e) {
            // Log the error in case there is an issue
            Log::error('Failed to load create author form', ['error_message' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
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
            'name.required' => 'The author name is required.',
            'name.string' => 'The author name must be a valid string.',
            'name.max' => 'The author name must not exceed 25 characters.',
            'name.regex' => 'Only alphabets and spaces are allowed.',
        ]);

        try {
            $user = $this->auth->user();
            $author = Author::firstOrCreate(['name' => $validatedData['name']],['created_by' => $user->id]);

            if (!$author->wasRecentlyCreated) {
                // Log the attempt to create an author that already exists
                Log::warning('Author already exists', ['name' => $validatedData['name']]);

                return redirect()->back()->withErrors(['name' => 'This author name already exists.']);
            }

            // Log the successful creation of a new author
            Log::info('Author created successfully', ['author_name' => $validatedData['name']]);

            return redirect()->route('authors.create')->with('success', 'Author created successfully!');
        } catch (\Exception $e) {
            // Log the error if the creation fails
            Log::error('Failed to create author', ['error_message' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to create Author. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $author = Author::findOrFail($id);

            // Log the attempt to load the edit form for a specific author
            Log::info('Displaying edit form for author', ['author_id' => $id]);

            return Inertia::render('Authors/AuthorEdit', [
                'author' => $author,
            ]);
        } catch (\Exception $e) {
            // Log the error if there is a problem loading the author
            Log::error('Failed to load edit form for author', ['author_id' => $id, 'error_message' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:25|regex:/^[a-zA-Z\s]+$/|unique:authors,name,' . $id,
        ], [
            'name.required' => 'The author name is required.',
            'name.string' => 'The author name must be a valid string.',
            'name.max' => 'The author name must not exceed 25 characters.',
            'name.regex' => 'Only alphabets and spaces are allowed.',
            'name.unique' => 'This author name already exists.',
        ]);

        try {
            $author = Author::findOrFail($id);
            $author->update($validatedData);

            // Log the successful update of the author
            Log::info('Author updated successfully', ['author_id' => $id, 'author_name' => $validatedData['name']]);

            return redirect()->route('authors.edit', ['author' => $id])->with('success', 'Author updated successfully!');
        } catch (\Exception $e) {
            // Log the error if the update fails
            Log::error('Failed to update author', ['author_id' => $id, 'error_message' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to update Author. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $author = Author::findOrFail($id);
            $author->update(['deleted_by' => $this->auth->user()->id]);
            $author->delete();

            // Log the successful deletion of the author
            Log::info('Author deleted successfully', ['author_id' => $id]);

            return redirect()->route('authors.index')->with('success', 'Author deleted successfully!');
        } catch (\Exception $e) {
            // Log the error if the deletion fails
            Log::error('Failed to delete author', ['author_id' => $id, 'error_message' => $e->getMessage()]);

            return redirect()->route('authors.index')->with('error', 'Failed to delete Author.');
        }
    }
}
