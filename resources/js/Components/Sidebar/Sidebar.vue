<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const isOpen = ref(window.innerWidth >= 768);
const isMobile = ref(window.innerWidth < 768);
const user = computed(() => usePage().props.auth?.user ?? {});
const role = computed(() => usePage().props.auth?.user?.role ?? '');

const toggleSidebar = () => {
    isOpen.value = !isOpen.value;
};

const handleResize = () => {
    isMobile.value = window.innerWidth < 768;
    if (!isMobile.value) {
        isOpen.value = true;
    } else {
        isOpen.value = false;
    }
};

onMounted(() => {
    window.addEventListener('resize', handleResize);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', handleResize);
});
</script>

<template>
    <div class="relative">
        <!-- Mobile Overlay -->
        <div v-if="isMobile && isOpen" 
             class="fixed inset-0 bg-black bg-opacity-50 z-20"
             @click="toggleSidebar">
        </div>

        <!-- Sidebar -->
        <div :class="[
                isOpen ? 'w-64' : 'w-16',
                isMobile && !isOpen ? '-translate-x-full' : 'translate-x-0',
                isMobile ? 'fixed' : 'relative'
             ]" 
             class="bg-gray-800 text-white transition-all duration-300 flex flex-col h-screen z-30">
            
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between p-4">
                <span v-if="isOpen" class="text-lg font-bold truncate">{{ user.name }}</span>
                <button @click="toggleSidebar" 
                        class="text-gray-300 hover:text-white focus:outline-none p-2">
                    <svg v-if="isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-2 py-4 space-y-2">
                <Link :href="route('dashboard')" 
                      class="flex items-center px-4 py-2 rounded hover:bg-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span v-if="isOpen" class="ml-3">Dashboard</span>
                </Link>

                <!-- Librarian-only links -->
                <template v-if="role === 'librarian'">
                    <Link :href="route('authors.index')" 
                          class="flex items-center px-4 py-2 rounded hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span v-if="isOpen" class="ml-3">Authors</span>
                    </Link>
                    <Link :href="route('publishers.index')" 
                          class="flex items-center px-4 py-2 rounded hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span v-if="isOpen" class="ml-3">Publishers</span>
                    </Link>
                    <Link :href="route('categories.index')" 
                          class="flex items-center px-4 py-2 rounded hover:bg-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <span v-if="isOpen" class="ml-3">Categories</span>
                    </Link>
                </template>

                <!-- Common links for all users -->
                <Link :href="route('books.index')" 
                      class="flex items-center px-4 py-2 rounded hover:bg-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span v-if="isOpen" class="ml-3">Books</span>
                </Link>
            </nav>
        </div>
    </div>
</template>
