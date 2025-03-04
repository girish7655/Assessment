<script setup>
import { onErrorCaptured, ref } from 'vue';

const error = ref(null);

onErrorCaptured((err) => {
  error.value = err;
  return false;
});
</script>

<style scoped>
.error-container {
  background-color: #fee2e2;
  border: 1px solid #ef4444;
  color: #991b1b;
  padding: 1rem;
  border-radius: 0.375rem;
  text-align: center;
}
</style>

<template>
  <div>
    <div v-if="error" class="error-container">
      <p>Something went wrong. Please try again later.</p>
    </div>
    <slot v-else></slot>
  </div>
</template>

