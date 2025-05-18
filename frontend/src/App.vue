<script setup>
import { RouterView } from 'vue-router'
import { computed } from 'vue'
import { useAuthStore } from './stores/auth'

const authStore = useAuthStore()
const isAuthenticated = computed(() => authStore.isAuthenticated)
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <RouterView />
  </div>
</template>

<style>
@import './assets/main.css';

/* Add any global styles here */
</style>
