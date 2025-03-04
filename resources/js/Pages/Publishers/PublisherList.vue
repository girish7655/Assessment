<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage();
const publishers = computed(() => page.props.publishers);
const searchQuery = ref("");

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

// Computed property to filter publishers based on search input
const filteredPublishers = computed(() => {
    if (!searchQuery.value.trim()) {
        return publishers.value;
    }
    return publishers.value.filter(publisher =>
        publisher.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

// Function to delete publisher
const deletePublisher = (id) => {
    if (confirm("Are you sure you want to delete this publisher?")) {
        router.delete(route('publishers.destroy', id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="min-h-screen bg-gray-50 py-6">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
                <!-- Header Section -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-6">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Publishers List</h1>
                        
                        <div class="flex items-center">
                            <p v-if="successMessage" 
                               class="text-sm sm:text-base font-semibold text-green-700 bg-green-50 px-3 py-2 rounded-md">
                                {{ successMessage }}
                            </p>
                            <p v-else-if="errorMessage" 
                               class="text-sm sm:text-base font-semibold text-red-700 bg-red-50 px-3 py-2 rounded-md">
                                {{ errorMessage }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 mt-4 sm:mt-0">
                        <input type="text"
                               v-model="searchQuery"
                               placeholder="Search publisher..."
                               class="w-full sm:w-64 px-4 py-2 border border-gray-300 rounded-lg 
                                      focus:ring-2 focus:ring-orange-500 focus:border-orange-500"/>

                        <Link :href="route('publishers.create')"
                              class="inline-flex justify-center items-center px-4 py-2 bg-orange-600 
                                     text-white rounded-lg hover:bg-orange-700 transition-colors duration-200">
                            Add New Publisher
                        </Link>
                    </div>
                </div>

                <!-- Publishers Grid -->
                <div v-if="filteredPublishers && filteredPublishers.length > 0" 
                     class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <div v-for="publisher in filteredPublishers" 
                         :key="publisher.id"
                         class="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow duration-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ publisher.name }}</h3>
                            </div>
                            <div class="flex space-x-2">
                                <Link :href="route('publishers.edit', publisher.id)"
                                      class="text-blue-600 hover:text-blue-800">
                                    <span class="sr-only">Edit</span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </Link>
                                <button @click="deletePublisher(publisher.id)"
                                        class="text-red-600 hover:text-red-800">
                                    <span class="sr-only">Delete</span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Publishers Found -->
                <div v-else class="text-center py-12">
                    <p class="text-red-500 text-lg font-medium">No publishers available.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
