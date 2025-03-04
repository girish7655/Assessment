<script setup>
import { ref } from 'vue';
import { useForm, usePage, Head, router} from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, watch } from 'vue';

const page = usePage();
const authors = computed(() => page.props.authors);
const publishers = computed(() => page.props.publishers);
const categories = computed(() => page.props.categories);

//Front end validations

const form = useForm({
    title: '',
    description: '',
    cover_image: '',
    publication_date: '',
    isbn: '',
    page_count: '',
    author_id: '',
    publisher_id: '',
    category_id: '',
});

// Handle Cancel Button Click
const cancelForm = () => {
    router.get(route('books.index'));
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

    if (rule.validator && !rule.validator(value)) {
        form.errors[fieldName] = `Please select a valid option`;
        return;
    }

    form.errors[fieldName] = null;
};

const handleInput = (field) => {
    validateField(field, form[field]);
};


const imagePreview = ref(null);
const fileInput = ref(null);

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

const submit = () => {
    form.errors = {};  // Clear previous errors

    Object.keys(rules).forEach(field => {
        validateField(field, form[field]);
    });

    if (!Object.values(form.errors).some(error => error !== null && error !== "")) {
        form.post(route('books.store'), {
            onSuccess: () => {
                form.reset();
                form.clearErrors();
                imagePreview.value = null;
            }
        });
    }
};

const successMessage = computed(() => page.props.flash?.success || '');
const errorMessage = computed(() => page.props.errors?.title || '');

// Auto-dismiss flash messages after 3 seconds
watch([successMessage, errorMessage], ([newSuccess, newError]) => {
  if (newSuccess || newError) {
    setTimeout(() => {
      page.props.flash.success = '';
      page.props.errors.title = '';
    }, 3000);
  }
});
</script>

<template>
    <Head title="Book Create" />

    <AuthenticatedLayout>

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-6">
                <h1 class="text-2xl sm:text-3xl font-bold">Create a new Book</h1>
                
                <div class="w-full sm:w-auto">
                    <p v-if="successMessage" class="text-sm sm:text-lg font-bold text-green-700 transition-opacity duration-300">
                        {{ successMessage }}
                    </p>
                    <p v-else-if="errorMessage" class="text-sm sm:text-lg font-bold text-red-700 transition-opacity duration-300">
                        {{ errorMessage }}
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit">
                <!-- First Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div class="col-span-1">
                        <label class="block font-medium text-sm text-gray-700">Title</label>
                        <input 
                            type="text" 
                            v-model="form.title"
                            @input="handleInput('title')"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500"
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
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500"
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
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500"
                            :class="{ 'border-red-500': form.errors.isbn }"
                        />
                        <p v-if="form.errors.isbn" class="text-sm text-red-600 mt-1">
                            {{ form.errors.isbn }}
                        </p>
                    </div>
                </div>

                <!-- Second Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <!-- Author Dropdown -->
                    <div class="col-span-1">
                        <label class="block font-medium text-sm text-gray-700">Author</label>
                        <select 
                            v-model="form.author_id" 
                            @change="handleInput('author_id')"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500"
                            :class="{ 'border-red-500': form.errors.author_id }"
                        >
                            <option value="">Select an Author</option>
                            <option v-for="author in authors" :key="author.id" :value="author.id">
                                {{ author.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.author_id" class="text-sm text-red-600 mt-1">
                            {{ form.errors.author_id }}
                        </p>
                    </div>

                    <!-- Publisher Dropdown -->
                    <div class="col-span-1">
                        <label class="block font-medium text-sm text-gray-700">Publisher</label>
                        <select 
                            v-model="form.publisher_id" 
                            @change="handleInput('publisher_id')"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500"
                            :class="{ 'border-red-500': form.errors.publisher_id }"
                        >
                            <option value="">Select a Publisher</option>
                            <option v-for="publisher in publishers" :key="publisher.id" :value="publisher.id">
                                {{ publisher.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.publisher_id" class="text-sm text-red-600 mt-1">
                            {{ form.errors.publisher_id }}
                        </p>
                    </div>

                    <!-- Category Dropdown -->
                    <div class="col-span-1">
                        <label class="block font-medium text-sm text-gray-700">Category</label>
                        <select 
                            v-model="form.category_id" 
                            @change="handleInput('category_id')"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500"
                            :class="{ 'border-red-500': form.errors.category_id }"
                        >
                            <option value="">Select a Category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.category_id" class="text-sm text-red-600 mt-1">
                            {{ form.errors.category_id }}
                        </p>
                    </div>
                </div>

                <!-- Third Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="col-span-1">
                        <label class="block font-medium text-sm text-gray-700">Publication Date</label>
                        <input 
                            type="date" 
                            v-model="form.publication_date"
                            @input="handleInput('publication_date')"
                            :max="new Date().toISOString().split('T')[0]"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500"
                            :class="{ 'border-red-500': form.errors.publication_date }"
                        />
                    </div>

                    <div class="col-span-1">
                        <label class="block font-medium text-sm text-gray-700">Description</label>
                        <textarea 
                            v-model="form.description"
                            @input="handleInput('description')"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-orange-500 focus:border-orange-500"
                            :class="{ 'border-red-500': form.errors.description }"
                        ></textarea>
                        <p v-if="form.errors.description" class="text-sm text-red-600 mt-1">
                            {{ form.errors.description }}
                        </p>
                    </div>
                </div>

                <!-- Book Cover Section -->
                <div class="mb-6">
                    <label class="block font-medium text-sm text-gray-700 mb-2">Book Cover</label>
                    <div class="flex flex-col sm:flex-row items-start gap-4">
                        <div>
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
                                Select Image
                            </button>
                            <p class="text-xs text-gray-500 mt-1">JPG, PNG up to 2MB</p>
                            
                            <p v-if="form.errors.cover_image" class="text-sm text-red-600 mt-1">
                                {{ form.errors.cover_image }}
                            </p>
                        </div>

                        <div v-if="imagePreview" class="mt-4 sm:mt-0">
                            <img
                                :src="imagePreview"
                                class="w-32 h-32 sm:w-48 sm:h-48 object-cover rounded-lg border-2 border-gray-300"
                                alt="Book cover preview"
                            />
                        </div>
                    </div>
                </div>

                <!-- Buttons Section -->
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                    <button 
                        type="submit" 
                        class="w-full sm:w-auto px-6 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 transition-colors"
                    >
                        Create Book
                    </button>

                    <button
                        type="button"
                        @click="cancelForm"
                        class="w-full sm:w-auto px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    
    </AuthenticatedLayout>

</template>
