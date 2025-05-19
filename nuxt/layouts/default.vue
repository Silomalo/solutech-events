<script setup lang="ts">
import { getSubdomain } from '~/lib/utils';

const authCookie = useCookie('auth_token');
const isAuthenticated = computed(() => !!authCookie.value);
const router = useRouter();
const sub_domain = getSubdomain();

useHead({
  title: 'Events - Silomalo Joseph',
  meta: [
    { name: 'description', content: 'An event Subscription and Management System'},
    { name: 'viewport', content: 'width=device-width, initial-scale=1' },
    { name: 'keywords', content: 'events, subscription, management, system' },
    { name: 'author', content: 'Silomalo Joseph' }
  ]
});


// Handle logout
async function handleLogout() {
  try {
    const url = useRuntimeConfig().public.apiUrl;
    
    if (authCookie.value) {
      // Call Laravel API to logout
      await $fetch(`${url}/api/logout`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${authCookie.value}`
        }
      });
    }
    
    // Clear auth cookie and redirect to login
    authCookie.value = null;
    router.push('/login');
  } catch (error) {
    console.error('Logout error:', error);
    // Even if logout API fails, clear cookie and redirect
    authCookie.value = null;
    router.push('/login');
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header Navigation -->
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <nav class="flex justify-between items-center">
          <NuxtLink to="/" class="flex items-center">
            <img src="/solutech.png" alt="Solutech Logo" class="h-8 w-auto mr-2">
            
          </NuxtLink>
          <div class="flex space-x-6">
            <template v-if="isAuthenticated">
              <NuxtLink to="/" class="text-sm font-medium text-[var(--color-tertiary)] hover:text-opacity-80 px-3 py-2 rounded-md">
                My Events
              </NuxtLink>
              <NuxtLink to="/account" class="text-sm font-medium text-[var(--color-tertiary)] hover:text-opacity-80 px-3 py-2 rounded-md">
                My Account
              </NuxtLink>
              <button @click="handleLogout" class="text-sm font-medium text-gray-700 hover:text-[var(--color-primary)] px-3 py-2 rounded-md">
                Logout
              </button>
            </template>
            
            <template v-else>
                <div v-if="sub_domain"> 
                    <NuxtLink to="/register" class="text-sm font-medium text-[var(--color-tertiary)] hover:text-opacity-80 px-3 py-2 rounded-md">
                        Register
                    </NuxtLink>
                    <NuxtLink to="/login" class="text-sm font-medium bg-[var(--color-primary)] text-white hover:bg-opacity-90 px-3 py-2 rounded-md">
                        Login
                    </NuxtLink>
                </div>
              <h2 class="text-xl font-semibold text-[var(--color-primary)]">Events</h2>
            </template>
          </div>
        </nav>
      </div>
    </header>

    <!-- Page Content -->
    <slot />

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
      <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="mb-4 md:mb-0">
            <p class="text-sm text-gray-500">© 2025 Organization Portal. All rights reserved.</p>
          </div>
          <div class="flex space-x-6">
            <a href="#" class="text-sm text-gray-500 hover:text-[var(--color-primary)]">Privacy Policy</a>
            <a href="#" class="text-sm text-gray-500 hover:text-[var(--color-primary)]">Terms of Service</a>
            <a href="#" class="text-sm text-gray-500 hover:text-[var(--color-primary)]">Contact Us</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>