<script setup>
import { ref, computed, watch } from 'vue';
import { usePage, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Form State
const form = useForm({
    name: '',
});

// Page Props and Flash Messages
const page = usePage();
const successMessage = computed(() => page.props.flash?.success || '');
const errorMessage = computed(() => page.props.errors?.name || '');

// Auto-dismiss flash messages after 3 seconds
watch([successMessage, errorMessage], ([newSuccess, newError]) => {
    if (newSuccess || newError) {
        setTimeout(() => {
            page.props.flash.success = '';
            page.props.errors.name = '';
        }, 3000);
    }
});

// Error State
const nameError = ref('');

// Validate Name Before Submission
const validateForm = () => {
    nameError.value = '';

    if (!form.name.trim()) {
        nameError.value = 'Author name cannot be empty.';
    } else if (!/^[a-zA-Z\s]+$/.test(form.name)) {
        nameError.value = 'Only alphabets are allowed.';
    } else if (form.name.length < 3) {
        nameError.value = 'Name must be at least 3 characters long.';
    }

    if (nameError.value) {
        startErrorTimer();
        return false;
    }
    return true;
};

// Handle Form Submission
const submitForm = () => {
    if (validateForm()) {
        form.post(route('authors.store'));
    }
};

// Error Message Timer
const startErrorTimer = () => {
    setTimeout(() => {
        nameError.value = '';
    }, 3000);
};

// Handle Cancel Button Click
const cancelForm = () => {
    router.get(route('authors.index'));
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
                <!-- Header Section -->
                <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4 sm:mb-0">
                        Create Author
                    </h1>
                </div>

                <!-- Form Section -->
                <div class="bg-white rounded-lg shadow-sm">
                    <form @submit.prevent="submitForm" class="p-4 sm:p-6 lg:p-8">
                        <div class="space-y-6">
                            <!-- Name Input and Messages Section -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Name Input -->
                                <div>
                                    <label 
                                        for="name" 
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Author Name
                                    </label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg 
                                               focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                        placeholder="Enter author name"
                                    />
                                </div>

                                <!-- Messages -->
                                <div class="flex items-center">
                                    <p v-if="successMessage" 
                                       class="text-sm sm:text-base font-medium text-green-600">
                                        {{ successMessage }}
                                    </p>
                                    <p v-else-if="errorMessage" 
                                       class="text-sm sm:text-base font-medium text-red-600">
                                        {{ errorMessage }}
                                    </p>
                                    <p v-else-if="nameError" 
                                       class="text-sm sm:text-base font-medium text-red-600">
                                        {{ nameError }}
                                    </p>
                                </div>
                            </div>

                            <!-- Buttons Section -->
                            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                                <button 
                                    type="submit"
                                    class="w-full sm:w-auto px-6 py-2.5 bg-orange-600 text-white font-medium 
                                           rounded-lg hover:bg-orange-700 focus:ring-4 focus:ring-orange-300 
                                           transition-colors duration-200"
                                >
                                    Save Author
                                </button>
                                <button
                                    type="button"
                                    @click="cancelForm"
                                    class="w-full sm:w-auto px-6 py-2.5 bg-gray-500 text-white font-medium 
                                           rounded-lg hover:bg-gray-600 focus:ring-4 focus:ring-gray-300 
                                           transition-colors duration-200"
                                >
                                    Back to Authors
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


