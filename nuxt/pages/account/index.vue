<script setup lang="ts">
import { useAuth } from '~/composables/useAuth';
const router = useRouter();

// Get auth composable
const { user, checkAuth, logout, error: authError, isLoading } = useAuth();

const errorMessage = computed(() => authError.value);

// Handle logout
async function handleLogout() {
  const success = await logout();
  if (success) {
    router.push('/login');
  }
}

// Fetch user data on mount
onMounted(() => {
  checkAuth();
});
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header Navigation -->
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <nav class="flex justify-between items-center">
          <h2 class="text-xl font-semibold text-[var(--color-primary)]">Organization Portal</h2>
          <div class="flex space-x-6">
            <button 
              @click="handleLogout"
              class="text-sm font-medium text-gray-700 hover:text-[var(--color-primary)] px-3 py-2 rounded-md"
            >
              Logout
            </button>
          </div>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="border-b border-gray-200 pb-5 mb-5">
        <h1 class="text-3xl font-bold text-[var(--color-primary)]">
          My Account
        </h1>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center py-12">
        <div class="animate-pulse flex space-x-4">
          <div class="h-12 w-12 bg-[var(--color-primary)] opacity-50 rounded-full"></div>
          <div class="flex-1 space-y-4 py-1">
            <div class="h-4 bg-[var(--color-primary)] opacity-30 rounded w-3/4"></div>
            <div class="space-y-2">
              <div class="h-4 bg-[var(--color-primary)] opacity-30 rounded"></div>
              <div class="h-4 bg-[var(--color-primary)] opacity-30 rounded w-5/6"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="rounded-md bg-red-50 p-4 my-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">An error occurred while loading account data</h3>
            <div class="mt-2 text-sm text-red-700">
              <p>{{ errorMessage }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- User Profile -->
      <div v-else-if="user" class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
          <div>
            <h3 class="text-lg leading-6 font-medium text-gray-900">User Profile</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Personal details and account information.</p>
          </div>
          <NuxtLink to="/account/edit" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-primary)]">
            Edit Profile
          </NuxtLink>
        </div>
        <div class="border-t border-gray-200">
          <dl>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">Full name</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ user.name }}</dd>
            </div>
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">Email address</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ user.email }}</dd>
            </div>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt class="text-sm font-medium text-gray-500">Account created</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ new Date(user.created_at).toLocaleDateString() }}</dd>
            </div>
          </dl>
        </div>
      </div>
      
      <!-- Recent Activities -->
      <div class="mt-8">
        <h2 class="text-lg font-medium text-gray-900">Recent Activities</h2>
        <div class="mt-3 bg-white shadow overflow-hidden sm:rounded-lg">
          <!-- Activity list would go here, customize based on your needs -->
          <div class="px-4 py-5 sm:p-6 text-center text-gray-500">
            No recent activities
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
