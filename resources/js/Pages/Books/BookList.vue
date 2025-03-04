<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, Transition } from 'vue';

const page = usePage();
const books = computed(() => page.props.books);
const auth = computed(() => usePage().props.auth ?? { user: {} });

const successMessage = ref("");
const errorMessage = ref("");

watch(() => page.props.flash, (flash) => {
    successMessage.value = flash?.success || "";
    errorMessage.value = flash?.error || "";

    if (successMessage.value || errorMessage.value) {
        setTimeout(() => {
            successMessage.value = "";
            errorMessage.value = "";
        }, 3000);
    }
}, { deep: true, immediate: true });

const searchQuery = ref('');
const availabilityFilter = ref('');
const sortOption = ref('title');

const filteredBooks = computed(() => {
    let filtered = books.value;

    if (searchQuery.value) {
        filtered = filtered.filter(book =>
            book.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            book.author_name.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    if (availabilityFilter.value) {
        filtered = filtered.filter(book => book.availability === availabilityFilter.value);
    }

    return filtered.sort((a, b) => {
        if (sortOption.value === 'title') {
            return a.title.localeCompare(b.title);
        } else if (sortOption.value === 'rating') {
            return b.avg_rating - a.avg_rating;
        }
        return 0;
    });
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

@media (max-width: 640px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}
</style>

<template>
    <Head title="Books" />
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 py-4 sm:py-6">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
                <!-- Header Section -->
                <div class="flex flex-col space-y-4 mb-6">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">Books List</h2>
                            
                            <!-- Flash Messages -->
                            <div class="flex items-center">
                                <Transition name="fade">
                                    <p v-if="successMessage" 
                                       class="text-sm sm:text-base font-semibold text-green-700 bg-green-50 
                                              px-3 py-2 rounded-md">
                                        {{ successMessage }}
                                    </p>
                                    <p v-else-if="errorMessage" 
                                       class="text-sm sm:text-base font-semibold text-red-700 bg-red-50 
                                              px-3 py-2 rounded-md">
                                        {{ errorMessage }}
                                    </p>
                                </Transition>
                            </div>
                        </div>

                        <!-- Add Book Button -->
                        <div v-if="auth?.user?.role === 'librarian'" class="mt-4 sm:mt-0">
                            <Link :href="route('books.create')"
                                  class="inline-flex items-center px-6 py-2.5 bg-orange-600 text-white 
                                         rounded-lg hover:bg-orange-700 transition-colors duration-200 
                                         text-sm sm:text-base font-bold w-full sm:w-auto justify-center 
                                         shadow-sm hover:shadow-md">
                                Add New Book
                            </Link>
                        </div>
                    </div>

                    <!-- Filters Section -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input type="text" 
                               v-model="searchQuery" 
                               placeholder="Search by title or author" 
                               class="w-full sm:w-1/3 px-4 py-2.5 border border-gray-300 rounded-lg 
                                      focus:ring-2 focus:ring-orange-500 focus:border-orange-500 
                                      text-sm sm:text-base font-medium placeholder:text-gray-400"/>

                        <select v-model="availabilityFilter" 
                                class="w-full sm:w-1/4 px-4 py-2.5 border border-gray-300 rounded-lg 
                                       focus:ring-2 focus:ring-orange-500 focus:border-orange-500 
                                       text-sm sm:text-base font-medium">
                            <option value="">All Books</option>
                            <option value="available">Available</option>
                            <option value="checked_out">Checked Out</option>
                        </select>

                        <select v-model="sortOption" 
                                class="w-full sm:w-1/4 px-4 py-2.5 border border-gray-300 rounded-lg 
                                       focus:ring-2 focus:ring-orange-500 focus:border-orange-500 
                                       text-sm sm:text-base font-medium">
                            <option value="title">Sort by Title</option>
                            <option value="rating">Sort by Rating</option>
                        </select>
                    </div>
                </div>

                <!-- Books Grid - Adjusted for 25% smaller cards -->
                <div v-if="filteredBooks.length > 0" 
                     class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                    <Link v-for="book in filteredBooks"
                          :key="book.id"
                          :href="route('books.show', book.id)"
                          class="group flex flex-col bg-white rounded-lg shadow-sm hover:shadow-xl 
                                 transition-all duration-200 border border-gray-200 overflow-hidden">
                        <!-- Book Cover - Decreased height by 25% -->
                        <div class="relative w-full pt-[75%] overflow-hidden">
                            <img :src="book.cover_image 
                                     ? `/storage/book_covers/${book.cover_image}` 
                                     : `/storage/book_covers/default-book.png`"
                                 :alt="book.cover_image ? 'Book Cover' : 'Default Cover'"
                                 class="absolute top-0 left-0 w-full h-full object-cover 
                                        transform group-hover:scale-105 transition-transform duration-200"/>
                        </div>

                        <!-- Book Details - Reduced padding and spacing -->
                        <div class="p-4 sm:p-6 flex flex-col flex-grow">
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 group-hover:text-orange-600 
                                       line-clamp-2 mb-2 transition-colors duration-200">
                                {{ book.title }}
                            </h3>
                            <p class="text-sm sm:text-base font-bold text-gray-600 mb-2">
                                by <span class="text-gray-800">{{ book?.author_name || 'Unknown Author' }}</span>
                            </p>
                            <p class="text-xs sm:text-sm font-medium text-gray-700 line-clamp-3 mb-4 flex-grow">
                                {{ book.description }}
                            </p>
                            <div class="flex items-center justify-between mt-auto pt-3 border-t border-gray-100">
                                <span class="text-yellow-500 text-base font-bold flex items-center gap-1">
                                    ⭐ {{ book.avg_rating || 0 }}/5
                                </span>
                                <span class="text-sm font-bold px-3 py-1.5 rounded-full" 
                                      :class="book.availability === 'available' 
                                             ? 'text-green-700 bg-green-100' 
                                             : 'text-red-700 bg-red-100'">
                                    {{ book.availability === 'available' ? 'Available' : 'Checked Out' }}
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- No Books Found -->
                <div v-else class="flex flex-col items-center justify-center py-12">
                    <p class="text-red-500 text-xl font-bold mb-2">No books available.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
