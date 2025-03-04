<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const page = usePage();
const successMessage = ref(page.props.flash.success || '');
const errorMessage = ref(page.props.flash.error || '');

// Form data
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '1',
});

// Error messages
const nameError = ref('');
const emailError = ref('');
const passwordError = ref('');
const confirmPasswordError = ref('');

// Validate Name (Min 3 characters, only letters & spaces)
const validateName = () => {
    const regex = /^[A-Za-z][A-Za-z\s]{2,}$/;
    nameError.value = regex.test(form.name.trim()) ? '' : 'Name must be at least 3 characters and contain only letters and spaces.';
};

// Validate Email Format
const validateEmail = () => {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    emailError.value = regex.test(form.email.trim()) ? '' : 'Please enter a valid email address.';
};

// Validate Password Length
const validatePassword = () => {
    passwordError.value = form.password.length >= 6 ? '' : 'Password must be at least 6 characters.';
};

// Validate Confirm Password Match
const validateConfirmPassword = () => {
    confirmPasswordError.value = form.password === form.password_confirmation ? '' : 'Passwords do not match.';
};

const validateBeforeSubmit = () => {
    validateName();
    validateEmail();
    validatePassword();
    validateConfirmPassword();

    return !(nameError.value || emailError.value || passwordError.value || confirmPasswordError.value);
};

// Submit Form
const submit = () => {
    if (!validateBeforeSubmit()) return;

    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div v-if="successMessage" class="p-4 mb-4 text-green-700 bg-green-100 border border-green-500 rounded">
            {{ successMessage }}
        </div>

        <div v-if="errorMessage" class="p-4 mb-4 text-red-700 bg-red-100 border border-red-500 rounded">
            {{ errorMessage }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Name" />
                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" autofocus autocomplete="name" @input="validateName"  />
                <InputError class="mt-2" :message="nameError || form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" autocomplete="username" @input="validateEmail"  />
                <InputError class="mt-2" :message="emailError || form.errors.email" />
            </div>

            <!-- Password Field -->
            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    autocomplete="new-password"
                    @input="validatePassword"
                />
                <InputError class="mt-2" :message="passwordError || form.errors.password" />
            </div>

            <!-- Confirm Password Field -->
            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    autocomplete="new-password"
                    @input="validateConfirmPassword"
                />
                <InputError class="mt-2" :message="confirmPasswordError || form.errors.password_confirmation" />
            </div>

            <div class="mt-4">
                <InputLabel for="role" value="Role" />
                <select id="role" class="mt-1 block w-full border-gray-300" v-model="form.role" required>
                    <option value="1">Customer</option>
                    <option value="2">Librarian</option>
                </select>
                <InputError class="mt-2" :message="form.errors.role" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    :href="route('login')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Already registered?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
