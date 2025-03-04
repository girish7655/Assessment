<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import ErrorBoundary from '@/Components/ErrorBoundary.vue';
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const name = computed(() => page.props.name);
const role = computed(() => page.props.role);

const isLoading = ref(false);
</script>

<style scoped>
.dashboard-container {
  padding: 1rem;
  max-width: 1200px;
  margin: 0 auto;
}

.loading-state {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 200px;
}
</style>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <div class="dashboard-container">
      <div v-if="$page.props.flash.success" class="text-sm sm:text-base font-medium text-green-600">
        {{ $page.props.flash.success }}
      </div>

      <h1 v-if="role === 'customer'">{{ `Hi ${name}! You are logged in as ${role}! Please have a look through the books menu on the left navigation bar.` }}</h1>
      <h1 v-else-if="role === 'librarian'">{{ `Hi ${name}! You are logged in as ${role}! Welcome to Librarian! Please have a look through the books menu on the left navigation bar to Add/Edit Books.` }}</h1>
      
      <div v-if="isLoading" class="loading-state">
        <span>Loading...</span>
      </div>
      
      <ErrorBoundary v-else>
      </ErrorBoundary>
    </div>
  </AuthenticatedLayout>
</template>

