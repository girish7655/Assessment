<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';


defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const page = usePage();
// Page Props and Flash Messages
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
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <form @submit.prevent="submit">
            
            <div class="col-span-1 flex items-center ml-8">
                <!-- Flash and Error Messages -->
                <p v-if="successMessage" class="text-lg font-bold text-green-700 mt-4 transition-opacity duration-300">
                {{ successMessage }}
                </p>
                <p v-else-if="errorMessage" class="text-lg font-bold text-red-700 mt-4 transition-opacity duration-300">
                {{ errorMessage }}
                </p>
                </div>
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-end mt-4 mb-4">
                
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Log in
                </PrimaryButton>
            </div>
            <div class="flex items-center">
                <InputLabel class="bold" value="Don't have an account?" />
                <Link
                    :href="route('register')"
                    class="bold underline bold pl-2 text-sm text-gray-600 hover:text-gray-900 rounded-md"
                >
                    Click here to Register!
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
