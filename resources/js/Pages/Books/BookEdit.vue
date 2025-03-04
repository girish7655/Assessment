<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, usePage, Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const page = usePage();
const book = computed(() => page.props.book);
const authors = computed(() => page.props.authors);
const publishers = computed(() => page.props.publishers);
const categories = computed(() => page.props.categories);

// Initialize form with existing book data
const form = useForm({
    title: book.value.title || '',
    description: book.value.description || '',
    cover_image: null,
    publication_date: book.value.publication_date || '',
    isbn: book.value.isbn || '',
    page_count: book.value.page_count || '',
    author_id: book.value.author_id || '',
    publisher_id: book.value.publisher_id || '',
    category_id: book.value.category_id || '',
    errors: {} 
});


// Handle Cancel Button Click
const cancelForm = (bookId) => {
    router.get(route('books.show',{ id: bookId }));
};

// Function to delete Image
const deleteImage = (bookId) => {
    if (bookId && confirm("Are you sure you want to remove this Image?")) {
        router.post(route('book_image.destroy', { id: bookId }), {
            preserveScroll: true,
            onSuccess: () => {
                successMessage.value = "Image removed successfully!";
                form.cover_image = null;
                imagePreview.value = null;
            }
        });
    }
};

const successMessage = computed(() => page.props.flash?.success || '');
const errorMessage = computed(() => 
  page.props.errors?.title || 
  page.props.errors?.author_id || 
  page.props.errors?.category_id ||
  page.props.errors?.publisher_id ||
  page.props.errors?.page_count ||
  page.props.errors?.isbn ||
  page.props.errors?.publication_date ||
  page.props.errors?.description || 
  page.props.errors?.cover_image || 
  ''
);
// Auto-dismiss flash messages after 3 seconds
watch([successMessage, errorMessage], ([newSuccess, newError]) => {
  if (newSuccess || newError) {
    setTimeout(() => {
      page.props.flash.success = '';
      page.props.flash.error = '';
    }, 3000);
  }
});

// Image Preview
const imagePreview = ref(book.value.cover_image ? `/storage/book_covers/${book.value.cover_image}` : null);
const fileInput = ref(null);

// Handle Image Upload
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    const maxSize = 2 * 1024 * 1024; // 2MB
    const allowedTypes = ['image/jpeg', 'image/png'];

    if (!file) return;

    if (!allowedTypes.includes(file.type)) {
        form.errors.cover_image = 'Only JPG and PNG files are allowed';
        fileInput.value.value = '';
        return;
    }

    if (file.size > maxSize) {
        form.errors.cover_image = 'Image size should not exceed 2MB';
        fileInput.value.value = '';
        return;
    }

    form.cover_image = file;
    imagePreview.value = URL.createObjectURL(file);
    form.errors.cover_image = null;
};

// Submit Form
const submit = () => {
    form.errors = {};

    Object.keys(rules).forEach(field => {
        validateField(field, form[field]);
    });

    if (Object.values(form.errors).some(error => error !== null && error !== "")) {
        return;
    }

    const formData = new FormData();
    formData.append('_method', 'PUT');

    Object.keys(form).forEach(key => {
        if (key !== 'errors') {
            formData.append(key, form[key]);
        }
    });

    if (form.cover_image instanceof File) {
        formData.append('cover_image', form.cover_image);
    }

    router.post(route('books.update', book.value.id), formData, {
        forceFormData: true,
        preserveScroll: true,
    });
};


const validateField = (fieldName, value) => {
    const rule = rules[fieldName];
    if (!rule) return;

    if (rule.required && !value) {
        form.errors[fieldName] = 'This field is required';
        return;
    }

    if (rule.minLength && value.length < rule.minLength) {
        form.errors[fieldName] = `Minimum ${rule.minLength} characters required`;
        return;
    }

    if (rule.maxLength && value.length > rule.maxLength) {
        form.errors[fieldName] = `Maximum ${rule.maxLength} characters allowed`;
        return;
    }

    if (rule.pattern && !rule.pattern.test(value)) {
        form.errors[fieldName] = `Invalid format`;
        return;
    }

    if (rule.min && parseInt(value) < rule.min) {
        form.errors[fieldName] = `Minimum value is ${rule.min}`;
        return;
    }

    if (rule.validator && !rule.validator(value)) {
        form.errors[fieldName] = `Invalid value`;
        return;
    }

    form.errors[fieldName] = null;
};


const rules = {
    title: {
        required: true,
        minLength: 3,
        maxLength: 255
    },
    isbn: {
        required: true,
        pattern: /^[0-9]+$/
    },
    page_count: {
        required: true,
        min: 1,
        pattern: /^[0-9]+$/
    },
    publication_date: {
        required: true,
        validator: (value) => {
            const date = new Date(value);
            return date <= new Date();
        }
    },
    description: {
        required: true,
        minLength: 10
    },
    cover_image: {
        required: true
    },
    category_id: {
        required: true
    },
    publisher_id: {
        required: true
    },
    author_id: {
        required: true
    }
};

const handleInput = (field) => {
    validateField(field, form[field]);
};

</script>

<template>
    <Head title="Edit Book" />

    <AuthenticatedLayout>
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 mb-4">
                <h1 class="text-xl sm:text-2xl font-bold">Edit Book</h1>
                
                <div class="w-full sm:w-auto sm:ml-8">
                    <p v-if="successMessage" class="text-sm sm:text-lg font-bold text-green-700 transition-opacity duration-300">
                        {{ successMessage }}
                    </p>
                    <p v-else-if="errorMessage" class="text-sm sm:text-lg font-bold text-red-700 transition-opacity duration-300">
                        {{ errorMessage }}
                    </p>
                </div>
            </div>



            <form @submit.prevent="submit" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div class="col-span-1">
                        <label class="block font-medium text-sm text-gray-700">Title</label>
                        <input 
                            type="text" 
                            v-model="form.title"
                            @input="handleInput('title')"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            :class="{ 'border-red-500': form.errors.title }"
                        />
                        <p v-if="form.errors.title" class="text-sm text-red-600 mt-1">
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <div class="col-span-1">
                        <label class="block font-medium text-sm text-gray-700">Page Count</label>
                        <input 
                            type="text" 
                            v-model="form.page_count"
                            @input="handleInput('page_count')"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            :class="{ 'border-red-500': form.errors.page_count }"
                        />
                        <p v-if="form.errors.page_count" class="text-sm text-red-600 mt-1">
                            {{ form.errors.page_count }}
                        </p>
                    </div>

                    <div class="col-span-1">
                        <label class="block font-medium text-sm text-gray-700">ISBN</label>
                        <input 
                            type="text" 
                            v-model="form.isbn"
                            @input="handleInput('isbn')"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            :class="{ 'border-red-500': form.errors.isbn }"
                        />
                        <p v-if="form.errors.isbn" class="text-sm text-red-600 mt-1">
                            {{ form.errors.isbn }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div class="col-span-1">
                        <label class="block font-medium text-sm text-gray-700">Author</label>
                        <select v-model="form.author_id" @input="handleInput('author_id')" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option v-for="author in authors" :key="author.id" :value="author.id">
                                {{ author.name }}
                            </option>
                        </select>
                    </div>

                    <div class="col-span-1">
                        <label class="block font-medium text-sm text-gray-700">Publisher</label>
                        <select v-model="form.publisher_id" @input="handleInput('publisher_id')" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option v-for="publisher in publishers" :key="publisher.id" :value="publisher.id">
                                {{ publisher.name }}
                            </option>
                        </select>
                    </div>

                    <div class="col-span-1">
                        <label class="block font-medium text-sm text-gray-700">Category</label>
                        <select v-model="form.category_id" @input="handleInput('category_id')" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="col-span-1">
                        <label class="block font-medium text-sm text-gray-700">Publication Date</label>
                        <input 
                            type="date" 
                            v-model="form.publication_date"
                            @input="handleInput('publication_date')"
                            :max="new Date().toISOString().split('T')[0]"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        />
                    </div>

                    <div class="col-span-1">
                        <label class="block font-medium text-sm text-gray-700">Description</label>
                        <input 
                            type="text" 
                            v-model="form.description"
                            @input="handleInput('description')"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                    <div class="col-span-1">
                        <label class="block font-medium text-sm text-gray-700 mb-2">Book Cover</label>
                        <input
                            type="file"
                            ref="fileInput"
                            @change="handleImageUpload"
                            accept="image/jpeg,image/png"
                            class="hidden"
                        />
                        <button
                            type="button"
                            @click="$refs.fileInput.click()"
                            class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors text-sm"
                        >
                            Change Image
                        </button>

                        <div v-if="imagePreview" class="relative mt-4">
                            <img
                                :src="imagePreview"
                                class="w-32 h-32 sm:w-48 sm:h-48 object-cover rounded-lg border-2 border-gray-300"
                                alt="Book cover preview"
                            />
                            <button 
                                v-if="book.cover_image && !form.cover_image"
                                @click="deleteImage(book.id)"
                                type="button"
                                class="absolute top-1 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 transition-colors duration-200"
                            >
                                Delete Image
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 sm:space-x-4 mt-4">
                    <button type="submit" class="w-full sm:w-auto bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                        Update Book
                    </button>

                    <button type="button" @click="cancelForm(book.id)" class="w-full sm:w-auto bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Back to Book Details
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
