<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Category;
use App\Models\BookCopy;
use App\Models\Review;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log; // Add this line to use the Log facade

class BookController extends Controller
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
        $role_name = DB::table('roles')->where('id',$user->role)->pluck('name')->first();
        $booksQuery = Book::with('author')
            ->leftJoin('reviews', 'books.id', '=', 'reviews.book_id')
            ->leftJoin('authors', 'books.author_id', '=', 'authors.id')
            ->select(
                'books.id',
                'books.title',
                'books.description',
                'books.cover_image',
                DB::raw('COALESCE(authors.name, "") as author_name'),
                'books.availability',
                DB::raw('ROUND(COALESCE(AVG(reviews.rating * 1.0), 0), 1) as avg_rating')
            )
            ->whereNull('books.deleted_at') 
            ->groupBy('books.id', 'books.title', 'books.description', 'books.cover_image', 'authors.name', 'books.availability');

        // If the logged-in user is a librarian, filter books created by them
        if ($role_name === 'librarian') {
            $booksQuery->where('books.created_by', $user->id);
        }

        $books = $booksQuery->get();


        Log::info('Fetched books successfully.');

        return Inertia::render('Books/BookList', [
            'books' => $books,
            'message' => "Books List Fetched Successfully",
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching books: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Unable to fetch books. Please try again.');
    }
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $user = $this->auth->user();
            Log::info('Rendering book creation form.');
            return Inertia::render('Books/BookCreate', [
                'authors' => Author::whereNull('deleted_at')->where('created_by',$user->id)->get(),
                'publishers' => Publisher::whereNull('deleted_at')->where('created_by',$user->id)->get(),
                'categories' => Category::whereNull('deleted_at')->where('created_by',$user->id)->get(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error rendering book creation form: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $this->auth->user();
        $validatedData = $request->validate([
            'title' => 'required|string|max:25|unique:books,title,NULL,id,deleted_at,NULL',
            'description' => 'required|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'publication_date' => 'required|date|before_or_equal:today',
            'isbn' => 'required|string|unique:books,isbn|max:20',
            'page_count' => 'required|integer|min:1|max:25000',
            'author_id' => 'required|exists:authors,id,deleted_at,NULL',
            'publisher_id' => 'required|exists:publishers,id,deleted_at,NULL',
            'category_id' => 'required|exists:categories,id,deleted_at,NULL',
        ], [
            'title.required' => 'The book title is required.',
            'title.string' => 'The book title must be a string.',
            'title.max' => 'The book title must not exceed 25 characters.',
            'title.unique' => 'A book with this title already exists.',
        
            'description.required' => 'The description field is required.',
        
            'isbn.required' => 'The ISBN is required.',
            'isbn.string' => 'The ISBN must be a string.',
            'isbn.unique' => 'This ISBN is already registered.',
            'isbn.max' => 'The ISBN must not exceed 20 characters.',
        
            'page_count.required' => 'The page count is required.',
            'page_count.integer' => 'The page count must be a number.',
            'page_count.min' => 'The book must have at least 1 page.',
            'page_count.max' => 'The book cannot have more than 25,000 pages.',
        
            'publication_date.required' => 'The publication date is required.',
            'publication_date.date' => 'The publication date must be a valid date.',
            'publication_date.before_or_equal' => 'The publication date cannot be in the future.',
        
            'author_id.required' => 'Please select an author.',
            'author_id.exists' => 'The selected author does not exist.',
        
            'publisher_id.required' => 'Please select a publisher.',
            'publisher_id.exists' => 'The selected publisher does not exist.',
        
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category does not exist.',
        
            'cover_image.image' => 'The cover image must be an image file.',
            'cover_image.mimes' => 'Only JPEG, PNG, and JPG images are allowed.',
            'cover_image.max' => 'The image size must not exceed 2MB.',
        ]);
        

        try {
            if ($request->hasFile('cover_image')) {
                $imagePath = $request->file('cover_image')->store('book_covers', 'public');
                $validatedData['cover_image'] = basename($imagePath);
                Log::info('Cover image uploaded for book creation.');
            }
            $validatedData['created_by'] = $user->id;
            $validatedData['updated_by'] = $user->id;
            Book::create($validatedData);

            Log::info('Book created successfully: ' . $validatedData['title']);

            return redirect()->route('books.create')->with('success', 'Book created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating book: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create the book. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $book = Book::whereNull('books.deleted_at')
                ->leftJoin('authors', function ($join) {
                    $join->on('books.author_id', '=', 'authors.id')
                        ->whereNull('authors.deleted_at');
                })
                ->leftJoin('publishers', function ($join) {
                    $join->on('books.publisher_id', '=', 'publishers.id')
                        ->whereNull('publishers.deleted_at');
                })
                ->leftJoin('categories', function ($join) {
                    $join->on('books.category_id', '=', 'categories.id')
                        ->whereNull('categories.deleted_at');
                })
                ->leftJoin('reviews', 'books.id', '=', 'reviews.book_id')
                ->leftJoin('users', 'reviews.user_id', '=', 'users.id')
                ->leftJoin('checkouts', 'books.id', '=', 'checkouts.book_id')
                ->select(
                    'books.id',
                    'books.title',
                    'books.description',
                    'books.cover_image',
                    'books.publication_date',
                    'books.isbn',
                    'books.page_count',
                    'authors.name as author_name',
                    'publishers.name as publisher_name',
                    'books.availability',
                    'categories.name as category_name',
                    'checkouts.checkout_date',
                    'checkouts.due_date',
                    'checkouts.status as checkout_status',
                    DB::raw('COALESCE(AVG(reviews.rating), 0) as avg_rating'),
                    DB::raw('GROUP_CONCAT(users.name, ":", reviews.rating, ":", reviews.review_text SEPARATOR ";") as reviews_data')
                )
                ->where('books.id', $id)
                ->groupBy(
                    'books.id', 'books.title', 'books.description', 'books.cover_image',
                    'books.publication_date', 'books.isbn', 'books.page_count',
                    'authors.name', 'books.availability', 'publishers.name',
                    'categories.name', 'checkouts.checkout_date', 'checkouts.due_date',
                    'checkouts.status'
                )->firstOrFail();

            Log::info('Fetched book details for book ID: ' . $id);

            $reviews = [];
            if (!empty($book->reviews_data)) {
                $reviews = collect(explode(";", $book->reviews_data))->map(function ($review) {
                    $parts = explode(":", $review);
                    return [
                        'user_name' => $parts[0],
                        'rating' => $parts[1],
                        'review_text' => $parts[2] ?? '',
                    ];
                });
            }

            $user_commented_reviews = Review::where('book_id', $book->id)
                ->with('user')
                ->select('reviews.*', 'users.name as user_name')
                ->join('users', 'users.id', '=', 'reviews.user_id')
                ->orderBy('created_at', 'desc')
                ->get(['rating', 'review_text', 'user_name']);

            return Inertia::render('Books/BookDetails', [
                'book' => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'description' => $book->description,
                    'cover_image' => $book->cover_image,
                    'publication_date' => $book->publication_date,
                    'isbn' => $book->isbn,
                    'page_count' => $book->page_count,
                    'author_name' => $book->author_name,
                    'availability' => $book->availability,
                    'checkout_date' => $book->checkout_date,
                    'due_date' => $book->due_date,
                    'publisher_name' => $book->publisher_name,
                    'category_name' => $book->category_name,
                    'avg_rating' => round($book->avg_rating, 1),
                    'reviews' => $reviews,
                    'user_commented_reviews' => $user_commented_reviews,
                ],
                'message' => "Book details fetched successfully."
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching book details for book ID: ' . $id . '. Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to fetch books details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $user = $this->auth->user();
            $book = Book::findOrFail($id);

            Log::info('Rendering book edit form for book ID: ' . $id);

            return Inertia::render('Books/BookEdit', [
                'book' => $book,
                'authors' => Author::whereNull('deleted_at')->where('created_by',$user->id)->get(),
                'publishers' => Publisher::whereNull('deleted_at')->where('created_by',$user->id)->get(),
                'categories' => Category::whereNull('deleted_at')->where('created_by',$user->id)->get(),
            ]);
        } catch (ModelNotFoundException $e) {
            Log::error('Book not found with ID: ' . $id);
            return redirect()->route('books.index')->with('error', 'Book not found.');
        } catch (\Exception $e) {
            Log::error('Error rendering book edit form for book ID: ' . $id . '. Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:25|unique:books,title,' . $id . ',id,deleted_at,NULL',
            'description' => 'required|string|min:10',
            'isbn' => 'required|regex:/^[0-9]+$/|max:13|unique:books,isbn,' . $id . ',id,deleted_at,NULL',
            'page_count' => 'required|integer|min:1',
            'publication_date' => 'required|date|before_or_equal:today',
            'author_id' => 'required|exists:authors,id,deleted_at,NULL',
            'publisher_id' => 'required|exists:publishers,id,deleted_at,NULL',
            'category_id' => 'required|exists:categories,id,deleted_at,NULL',
            'cover_image' => 'nullable|image|mimes:jpeg,png|max:2048'
        ], [
            'title.required' => 'The book title is required.',
            'title.max' => 'The book title must not exceed 25 characters.',
            'title.unique' => 'A book with this title already exists.',
            
            'description.required' => 'The description field is required.',
            'description.min' => 'The description must be at least 10 characters.',
        
            'isbn.required' => 'The ISBN is required.',
            'isbn.regex' => 'The ISBN must contain only numbers.',
            'isbn.max' => 'The ISBN must not exceed 13 characters.',
            'isbn.unique' => 'This ISBN already exists in the system.',
        
            'page_count.required' => 'The page count is required.',
            'page_count.integer' => 'The page count must be a number.',
            'page_count.min' => 'The book must have at least 1 page.',
        
            'publication_date.required' => 'The publication date is required.',
            'publication_date.date' => 'The publication date must be a valid date.',
            'publication_date.before_or_equal' => 'The publication date cannot be in the future.',
        
            'author_id.required' => 'Please select an author.',
            'author_id.exists' => 'The selected author does not exist.',
        
            'publisher_id.required' => 'Please select a publisher.',
            'publisher_id.exists' => 'The selected publisher does not exist.',
        
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category does not exist.',
        
            'cover_image.image' => 'The cover image must be an image file.',
            'cover_image.mimes' => 'Only JPEG and PNG images are allowed.',
            'cover_image.max' => 'The image size must not exceed 2MB.',
        ]);

        try {
            $user = $this->auth->user();
            $book = Book::findOrFail($id);
            if ($request->hasFile('cover_image')) {
                // Delete old image if exists
                if ($book->cover_image) {
                    Storage::delete("public/book_covers/{$book->cover_image}");
                }

                // Store new image
                $imagePath = $request->file('cover_image')->store('book_covers', 'public');
                $validatedData['cover_image'] = basename($imagePath);
                $validatedData['updated_by'] = $user->id;
                Log::info('Cover image updated for book ID: ' . $id);
            }

            $book->update($validatedData);

            Log::info('Book details updated for book ID: ' . $id);

            return redirect()->route('books.edit', ['book' => $id])->with('success', 'Book Details updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating book details for book ID: ' . $id . '. Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update the book. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $book = Book::findOrFail($id);
            $book->delete();
            Log::info('Book deleted successfully with ID: ' . $id);
            return redirect()->route('books.index')->with('success', 'Book deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting book with ID: ' . $id . '. Error: ' . $e->getMessage());
            return redirect()->route('books.index')->with('error', 'Failed to delete Book.');
        }
    }

    /**
     * Checkout a book.
     */
    public function checkout(Book $book)
    {
        try {
            if ($book->availability === 'available') {
                // Create checkout record
                BookCopy::create([
                    'user_id' => auth()->id(),
                    'book_id' => $book->id,
                    'checkout_date' => now(),
                    'due_date' => now()->addDays(5),
                    'status' => 'checked_out'
                ]);

                // Update book status
                Book::where('id', $book->id)->whereNull('deleted_at')->update(['availability' => 'checked_out']);

                Log::info('Book checked out successfully with ID: ' . $book->id);

                return redirect()->route('books.show', ['book' => $book->id])->with('success', 'Book checked out successfully!');
            }
        } catch (\Exception $e) {
            Log::error('Error checking out book with ID: ' . $book->id . '. Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Book is not available for checkout.');
        }
    }

    /**
     * Return a book.
     */
    public function return(Book $book)
    {
        try {
            // Update checkout record
            BookCopy::where('book_id', $book->id)
                ->where('status', 'checked_out')
                ->update([
                    'status' => 'returned',
                    'returned_date' => now()
                ]);

            // Update book availability
            Book::where('id', $book->id)
                ->whereNull('deleted_at')
                ->update(['availability' => 'available']);

            Log::info('Book returned successfully with ID: ' . $book->id);

            return redirect()->route('books.show', ['book' => $book->id])->with('success', 'Book returned successfully!');
        } catch (\Exception $e) {
            Log::error('Error returning book with ID: ' . $book->id . '. Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Book is not available for checkout.');
        }
    }

    /**
     * Create a review for a book.
     */
    public function review_create(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|min:5|max:100'
        ]);

        try {
            Review::create([
                'user_id' => auth()->id(),
                'book_id' => $validated['book_id'],
                'rating' => $validated['rating'],
                'review_text' => $validated['review_text']
            ]);

            Log::info('Review submitted successfully for book ID: ' . $validated['book_id']);
            
            return redirect()->back()->with('success', 'Review submitted successfully!');
        } catch (\Exception $e) {
            Log::error('Error submitting review for book ID: ' . $validated['book_id'] . '. Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to submit review. Please try again.');
        }
    }

    /**
     * Delete Book Image
     */
    public function bookimage_delete($id)
    {
        try {
            // Remove book Image
            Book::where('id', $id)->whereNull('deleted_at')->update(['cover_image' => null]);

            Log::info('Book image removed successfully for book ID: ' . $id);

            return redirect()->route('books.show', ['book' => $id])->with('success', 'Book removed successfully!');
        } catch (\Exception $e) {
            Log::error('Error removing book image for book ID: ' . $id . '. Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to remove book image.');
        }
    }
}
