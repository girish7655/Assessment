<script setup>
import { computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function UseFlashMessages() {
    const page = usePage();
    const successMessage = computed(() => page.props.flash?.success || '');
    const errorMessage = computed(() => page.props.flash?.error || '');

    watch([successMessage, errorMessage], ([newSuccess, newError]) => {
        if (newSuccess || newError) {
            setTimeout(() => {
                page.props.flash.success = '';
                page.props.flash.error = '';
            }, 3000);
        }
    });

    return { successMessage, errorMessage };
}
