<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Auth\Guard;
use Inertia\Inertia;
use App\Models\Publisher;

class PublisherController extends Controller
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
            $publishers = Publisher::whereNull('deleted_at')->where('created_by',$user->id)->get();

            Log::info('Fetched publishers list.', [
                'publisher_count' => $publishers->count(),
            ]);

            return Inertia::render('Publishers/PublisherList', [
                'publishers' => $publishers,
                'message' => "Publishers List Fetched Successfully",
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching publishers list.', [
                'error_message' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Unable to fetch Publishers. Please try again.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            Log::info('User accessed publisher creation page.');

            return Inertia::render('Publishers/PublisherCreate', [
                'publishers' => Publisher::whereNull('deleted_at')->get(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error accessing publisher creation page.', [
                'error_message' => $e->getMessage(),
            ]);
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
            'name.required' => 'The publisher name is required.',
            'name.string' => 'The publisher name must be a valid string.',
            'name.max' => 'The publisher name must not exceed 25 characters.',
            'name.regex' => 'Only alphabets and spaces are allowed.',
        ]);

        try {
            $user = $this->auth->user();
            $publisher = Publisher::firstOrCreate(['name' => $validatedData['name']],['created_by' => $user->id]);

            if (!$publisher->wasRecentlyCreated) {
                Log::warning('Attempt to create duplicate publisher.', [
                    'publisher_name' => $validatedData['name'],
                ]);
                return redirect()->back()->withErrors(['name' => 'This author name already exists.']);
            }

            Log::info('Publisher created successfully.', [
                'publisher_name' => $validatedData['name'],
                'publisher_id' => $publisher->id,
            ]);

            return redirect()->route('publishers.create')->with('success', 'Publisher created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating publisher.', [
                'error_message' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Failed to create Publisher. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Implement show logic with logging if necessary
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $publisher = Publisher::findOrFail($id);

            Log::info('User accessed publisher edit page.', [
                'publisher_id' => $id,
                'publisher_name' => $publisher->name,
            ]);

            return Inertia::render('Publishers/PublisherEdit', [
                'publisher' => $publisher,
            ]);
        } catch (\Exception $e) {
            Log::error('Error accessing publisher edit page.', [
                'publisher_id' => $id,
                'error_message' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:25|regex:/^[a-zA-Z\s]+$/|unique:publishers,name,' . $id,
        ], [
            'name.required' => 'The publisher name is required.',
            'name.string' => 'The publisher name must be a valid string.',
            'name.max' => 'The publisher name must not exceed 25 characters.',
            'name.regex' => 'Only alphabets and spaces are allowed.',
            'name.unique' => 'This publisher name already exists.',
        ]);

        try {
            $publisher = Publisher::findOrFail($id);
            $publisher->update($validatedData);

            Log::info('Publisher updated successfully.', [
                'publisher_id' => $id,
                'new_name' => $validatedData['name'],
            ]);

            return redirect()->route('publishers.edit', ['publisher' => $id])->with('success', 'Publisher updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating publisher.', [
                'publisher_id' => $id,
                'error_message' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Failed to update Publisher. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $publisher = Publisher::findOrFail($id);
            $publisher->update(['deleted_by' => $this->auth->user()->id]);
            $publisher->forceDelete();

            Log::info('Publisher deleted successfully.', [
                'publisher_id' => $id,
                'publisher_name' => $publisher->name,
            ]);

            return redirect()->route('publishers.index')->with('success', 'Publisher deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting publisher.', [
                'publisher_id' => $id,
                'error_message' => $e->getMessage(),
            ]);
            return redirect()->route('publishers.index')->with('error', 'Failed to delete Publisher.');
        }
    }
}
