<script setup>
import { Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps({
    user: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <GuestLayout>
        <div class="p-6">
            <h1 class="text-2xl font-bold mb-4">
                Email Verified Successfully!
            </h1>
            
            <p class="mb-4">
                Thank you {{ user.name }}! Your email has been verified.
            </p>

            <Link
                :href="route('dashboard')"
                class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            >
                Go to Dashboard
            </Link>
        </div>
    </GuestLayout>
</template>
