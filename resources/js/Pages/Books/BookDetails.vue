<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref, watch} from 'vue';

const page = usePage();
const book = computed(() => page.props.book);
const auth = computed(() => usePage().props.auth ?? { user: {} });

const successMessage = computed(() => page.props.flash?.success || '');
const errorMessage = computed(() => page.props.flash?.error || '');

// Auto-dismiss flash messages after 3 seconds
watch([successMessage, errorMessage], ([newSuccess, newError]) => {
  if (newSuccess || newError) {
    setTimeout(() => {
      page.props.flash.success = '';
      page.props.flash.error = '';
    }, 3000);
  }
});

// Create form instances
const returnForm = useForm({});
const checkoutForm = useForm({});

const markAsReturn = () => {
  returnForm.post(route('books.return', book.value.id), {
    onSuccess: () => {
      window.location.reload();
    }
  });
};

const checkoutBook = () => {
  checkoutForm.post(route('books.checkout', book.value.id), {
    onSuccess: () => {
      window.location.reload();
    }
  });
};

const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const form = useForm({
    rating: 0,
    review_text: '',
    book_id: book.value.id
});
const showModal = ref(false);
const rating = ref(0);
const reviewText = ref('');
const errors = ref({
    rating: '',
    reviewText: ''
});

const openModal = () => {
  showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    rating.value = 0;
    reviewText.value = '';
    errors.value = {};
    form.reset();
};

const validateReview = () => {
    errors.value = {};
    let isValid = true;

    if (rating.value === 0) {
        errors.value.rating = 'Please select a rating';
        isValid = false;
    }

    if (!reviewText.value.trim()) {
        errors.value.reviewText = 'Review text is required';
        isValid = false;
    } else if (reviewText.value.length < 10) {
        errors.value.reviewText = 'Review must be at least 10 characters';
        isValid = false;
    }

    // Auto-dismiss errors after 3 seconds
    if (!isValid) {
        setTimeout(() => {
            errors.value = {};
        }, 2000);
    }

    return isValid;
};

const submitReview = () => {
    form.rating = rating.value;
    form.review_text = reviewText.value;

    if (validateReview()) {
        form.post(route('reviews.create', book.value.id), {
            onSuccess: () => {
                closeModal();
                window.location.reload();
            }
        });
    }
};

const daysRemaining = computed(() => {
    if (!book.value.due_date) return 0;
    const today = new Date();
    const dueDate = new Date(book.value.due_date);
    const diffTime = dueDate - today;
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
});

// Function to delete books
const deleteBook = (id) => {
    if (confirm("Are you sure you want to delete this book?")) {
        router.delete(route('books.destroy', id), {
            onSuccess: () => {
                successMessage.value = page.props.flash?.success || "Book deleted successfully!";
            },
            onError: () => {
                errorMessage.value = page.props.flash?.error || "Failed to delete book. Please try again.";
            }
        });
    }
};
</script>

<template>
  <Head title="Book Details Page" />
  <AuthenticatedLayout>
    <div class="min-h-screen bg-gray-50 py-4 sm:py-6 lg:py-8">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
        <!-- Header with Flash Messages -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
          <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900">Book Details</h1>
          <div class="text-sm sm:text-base font-bold transition-opacity duration-300">
            <p v-if="successMessage" class="text-green-700">{{ successMessage }}</p>
            <p v-else-if="errorMessage" class="text-red-700">{{ errorMessage }}</p>
          </div>
        </div>

        <!-- Book Title & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
          <h2 class="text-xl sm:text-2xl font-bold text-gray-900">{{ book.title }}</h2>
          <div class="flex flex-wrap gap-2">
            <button v-if="auth?.user?.role === 'librarian' && book.availability !== 'available'"
              class="w-full sm:w-auto px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white 
                     text-sm sm:text-base font-bold rounded transition duration-200"
              @click="markAsReturn">
              Mark as Return
            </button>

            <button v-if="book.availability === 'available' && auth?.user?.role === 'customer'"
              class="w-full sm:w-auto px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white 
                     text-sm sm:text-base font-bold rounded transition duration-200"
              @click="checkoutBook">
              Checkout Book
            </button>

            <button v-if="auth?.user?.role === 'librarian'"
              @click="deleteBook(book.id)"
              class="w-full sm:w-auto px-4 py-2 bg-red-500 hover:bg-red-600 text-white 
                     text-sm sm:text-base font-bold rounded transition duration-200 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
              Delete Book
            </button>
          </div>
        </div>

        <!-- Book Cover & Basic Details -->
        <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">
          <div class="w-full lg:w-1/3">
            <div class="aspect-[3/4] w-full relative rounded-lg overflow-hidden shadow-lg">
              <img v-if="book.cover_image" 
                   :src="`/storage/book_covers/${book.cover_image}`" 
                   alt="Cover" 
                   class="absolute inset-0 w-full h-full object-cover">
            </div>
          </div>

          <div class="flex-1 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="space-y-2">
                <p class="text-sm sm:text-base">
                  <span class="font-semibold">Author:</span> 
                  {{ book.author_name || "Not available" }}
                </p>
                <p class="text-sm sm:text-base">
                  <span class="font-semibold">Publisher:</span> 
                  {{ book.publisher_name || "Not available" }}
                </p>
                <p class="text-sm sm:text-base">
                  <span class="font-semibold">Publication Date:</span> 
                  {{ formatDate(book.publication_date) }}
                </p>
              </div>
              <div class="space-y-2">
                <p class="text-sm sm:text-base">
                  <span class="font-semibold">Category:</span> 
                  {{ book.category_name || "Not available" }}
                </p>
                <p class="text-sm sm:text-base">
                  <span class="font-semibold">ISBN:</span> {{ book.isbn }}
                </p>
                <p class="text-sm sm:text-base">
                  <span class="font-semibold">Page Count:</span> {{ book.page_count }}
                </p>
              </div>
            </div>
            <p class="text-yellow-500 text-lg sm:text-xl font-bold">
              <span class="font-semibold">Average Rating:</span> ⭐ {{ book.avg_rating || 0 }}/5
            </p>
          </div>
        </div>

        <!-- Book Availability Section -->
        <div class="bg-gray-100 p-4 sm:p-6 mt-6 rounded-lg shadow-md">
          <div v-if="book.availability === 'checked_out'" class="mb-4">
            <p v-if="daysRemaining > 0" class="text-sm sm:text-base text-green-600 font-semibold">
              {{ daysRemaining }} days remaining to return the book
            </p>
            <p v-else class="text-sm sm:text-base text-red-600 font-semibold">
              {{ Math.abs(daysRemaining) }} days overdue
            </p>
          </div>
          <p class="text-sm sm:text-base">
            <strong>Availability:</strong> 
            <span :class="book.availability === 'checked_out' ? 'text-red-600' : 'text-green-600'">
              {{ book.availability === 'checked_out' ? 'Not Available' : 'Available' }}
            </span>
          </p>
        </div>

        <!-- Review Section -->
        <div class="mt-6 space-y-4">
          <h3 class="text-lg sm:text-xl font-semibold">Reviews</h3>
          <div v-if="book.reviews.length" class="space-y-4">
            <div v-for="review in book.reviews" 
                 :key="review.user_name" 
                 class="p-4 sm:p-6 border rounded-lg shadow-sm">
              <p class="font-semibold text-sm sm:text-base">{{ review.user_name }}</p>
              <p class="text-yellow-500 text-sm sm:text-base">⭐ {{ review.rating }}/5</p>
              <p class="text-gray-600 text-sm sm:text-base mt-2">{{ review.review_text }}</p>
            </div>
          </div>
          <p v-else class="text-sm sm:text-base text-gray-500">No reviews yet.</p>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-between">
          <Link v-if="auth?.user?.role === 'librarian'" 
                :href="route('books.edit', book.id)" 
                class="w-full sm:w-auto px-6 py-2 bg-orange-600 text-white text-center
                       text-sm sm:text-base font-semibold rounded-lg 
                       hover:bg-orange-700 transition duration-200">
            Edit Book
          </Link>

          <Link :href="route('books.index')" 
                class="w-full sm:w-auto px-6 py-2 bg-orange-600 text-white text-center
                       text-sm sm:text-base font-semibold rounded-lg 
                       hover:bg-orange-700 transition duration-200">
            Back to Books
          </Link>

          <button v-if="book.availability === 'checked_out' && auth.user.role === 'customer'" 
                  @click="openModal" 
                  class="w-full sm:w-auto px-6 py-2 bg-orange-600 text-white text-center
                         text-sm sm:text-base font-semibold rounded-lg 
                         hover:bg-orange-700 transition duration-200">
            Add Review
          </button>
        </div>
      </div>
    </div>

    <!-- Review Modal -->
    <div v-if="showModal" 
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg w-full max-w-md p-6 space-y-4">
        <h2 class="text-xl font-bold text-gray-900">Add Your Review</h2>
        <div class="space-y-4">
          <div class="flex items-center justify-center space-x-2">
            <template v-for="star in 5" :key="star">
              <button @click="rating = star" 
                      class="text-2xl focus:outline-none"
                      :class="star <= rating ? 'text-yellow-400' : 'text-gray-300'">
                ★
              </button>
            </template>
          </div>
          <textarea v-model="reviewText" 
                    class="w-full p-3 border rounded-lg text-sm sm:text-base" 
                    rows="4" 
                    placeholder="Write your review..."></textarea>
          <div class="flex justify-end space-x-3">
            <button @click="closeModal" 
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 
                           text-sm sm:text-base font-medium transition duration-200">
              Cancel
            </button>
            <button @click="submitReview" 
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 
                           text-sm sm:text-base font-medium transition duration-200">
              Submit
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
  
