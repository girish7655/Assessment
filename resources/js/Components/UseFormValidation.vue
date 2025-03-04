<script setup>
import { ref } from 'vue';

export function UseFormValidation() {
    const nameError = ref('');

    const validateForm = (name) => {
        nameError.value = '';

        if (!name.trim()) {
            nameError.value = 'Name cannot be empty.';
        } else if (!/^[a-zA-Z\s]+$/.test(name)) {
            nameError.value = 'Only alphabets are allowed.';
        } else if (name.length < 3) {
            nameError.value = 'Name must be at least 3 characters long.';
        }

        if (nameError.value) {
            startErrorTimer();
            return false;
        }
        return true;
    };

    const startErrorTimer = () => {
        setTimeout(() => {
            nameError.value = '';
        }, 3000);
    };

    return { nameError, validateForm };
}
