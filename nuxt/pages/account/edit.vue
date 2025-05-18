<script setup lang="ts">
import { useAuth } from '~/composables/useAuth';

// Define the user data interface
interface UserData {
  id: number;
  name: string;
  email: string;
  created_at: string;
  updated_at: string;
}

const url = useRuntimeConfig().public.apiUrl;
const router = useRouter();

// Form state
const form = reactive({
  name: '',
  email: '',
  current_password: '',
  password: '',
  password_confirmation: ''
});

// Form validation and UI states
const isLoading = ref(true);
const isSaving = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const errors = ref<Record<string, string>>({});

// Fetch user data
async function fetchUserData() {
  try {
    isLoading.value = true;
    errorMessage.value = '';
    
    // Get auth token from cookie
    const authCookie = useCookie('auth_token');
    
    if (!authCookie.value) {
      router.push('/login');
      return;
    }
    
    // Call Laravel API to get user data
    const userData = await $fetch<UserData>(`${url}/api/user`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${authCookie.value}`
      }
    });
    
    // Populate form with user data
    form.name = userData.name;
    form.email = userData.email;
  } catch (error: any) {
    console.error('Error fetching user data:', error);
    errorMessage.value = 'Failed to load user data. Please try again.';
    
    // If unauthorized, redirect to login
    if (error.response && error.response.status === 401) {
      router.push('/login');
    }
  } finally {
    isLoading.value = false;
  }
}

// Validate form
function validateForm(isPasswordUpdate = false) {
  const newErrors: Record<string, string> = {};
  
  if (!form.name.trim()) {
    newErrors.name = 'Name is required';
  }
  
  if (!form.email) {
    newErrors.email = 'Email is required';
  } else if (!/^\S+@\S+\.\S+$/.test(form.email)) {
    newErrors.email = 'Please enter a valid email address';
  }
  
  // Only validate password fields if the user is trying to update the password
  if (isPasswordUpdate) {
    if (!form.current_password) {
      newErrors.current_password = 'Current password is required';
    }
    
    if (form.password && form.password.length < 8) {
      newErrors.password = 'Password must be at least 8 characters';
    }
    
    if (form.password !== form.password_confirmation) {
      newErrors.password_confirmation = 'Passwords do not match';
    }
  }
  
  errors.value = newErrors;
  return Object.keys(newErrors).length === 0;
}

// Update profile information
async function updateProfile() {
  // Validate profile form
  if (!validateForm(false)) {
    return;
  }
  
  try {
    isSaving.value = true;
    errorMessage.value = '';
    successMessage.value = '';
    
    // Call Laravel API to update profile
    await $fetch(`${url}/api/user/profile-information`, {
      method: 'PUT',
      body: {
        name: form.name,
        email: form.email
      }
    });
    
    successMessage.value = 'Profile updated successfully';
  } catch (error: any) {
    console.error('Profile update error:', error);
    
    // Handle validation errors from Laravel
    if (error.response && error.response.status === 422) {
      const validationErrors = error.response._data.errors;
      if (validationErrors) {
        Object.keys(validationErrors).forEach(key => {
          errors.value[key] = validationErrors[key][0];
        });
      }
      errorMessage.value = 'Please correct the errors below.';
    } else {
      errorMessage.value = error?.response?._data?.message || 'Failed to update profile. Please try again.';
    }
  } finally {
    isSaving.value = false;
  }
}

// Update password
async function updatePassword() {
  // Validate password form
  if (!validateForm(true)) {
    return;
  }
  
  try {
    isSaving.value = true;
    errorMessage.value = '';
    successMessage.value = '';
    
    // Call Laravel API to update password
    await $fetch(`${url}/api/user/password`, {
      method: 'PUT',
      body: {
        current_password: form.current_password,
        password: form.password,
        password_confirmation: form.password_confirmation
      }
    });
    
    // Clear password fields
    form.current_password = '';
    form.password = '';
    form.password_confirmation = '';
    
    successMessage.value = 'Password updated successfully';
  } catch (error: any) {
    console.error('Password update error:', error);
    
    // Handle validation errors from Laravel
    if (error.response && error.response.status === 422) {
      const validationErrors = error.response._data.errors;
      if (validationErrors) {
        Object.keys(validationErrors).forEach(key => {
          errors.value[key] = validationErrors[key][0];
        });
      }
      errorMessage.value = 'Please correct the errors below.';
    } else {
      errorMessage.value = error?.response?._data?.message || 'Failed to update password. Please try again.';
    }
  } finally {
    isSaving.value = false;
  }
}

// Cancel and go back to account page
function cancelEdit() {
  router.push('/account');
}

// Fetch user data on mount
onMounted(fetchUserData);
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header Navigation -->
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
        <nav class="flex justify-between items-center">
          <h2 class="text-xl font-semibold text-[var(--color-primary)]">Organization Portal</h2>
          <div class="flex space-x-6">
            <NuxtLink to="/account" class="text-sm font-medium text-gray-700 hover:text-[var(--color-primary)] px-3 py-2 rounded-md">
              Back to Account
            </NuxtLink>
          </div>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="border-b border-gray-200 pb-5 mb-5">
        <h1 class="text-3xl font-bold text-[var(--color-primary)]">
          Edit Profile
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

      <!-- Form Content -->
      <div v-else class="space-y-8">
        <!-- Success alert -->
        <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-md p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-green-700">{{ successMessage }}</p>
            </div>
          </div>
        </div>

        <!-- Error alert -->
        <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-md p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-700">{{ errorMessage }}</p>
            </div>
          </div>
        </div>

        <!-- Profile Information Form -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Profile Information</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Update your account's profile information.</p>
          </div>
          <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <form @submit.prevent="updateProfile" class="space-y-6">
              <!-- Name input -->
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                  Name
                </label>
                <div class="mt-1">
                  <input 
                    id="name" 
                    v-model="form.name"
                    type="text" 
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] sm:text-sm"
                  />
                  <p v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name }}</p>
                </div>
              </div>

              <!-- Email input -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                  Email
                </label>
                <div class="mt-1">
                  <input 
                    id="email" 
                    v-model="form.email"
                    type="email" 
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] sm:text-sm"
                  />
                  <p v-if="errors.email" class="mt-2 text-sm text-red-600">{{ errors.email }}</p>
                </div>
              </div>

              <!-- Save button -->
              <div class="flex justify-end">
                <button 
                  type="button" 
                  @click="cancelEdit"
                  class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 mr-3"
                >
                  Cancel
                </button>
                <button 
                  type="submit" 
                  :disabled="isSaving"
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[var(--color-primary)] hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-primary)]"
                >
                  <svg v-if="isSaving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ isSaving ? 'Saving...' : 'Save' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Update Password Form -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Update Password</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Ensure your account is using a secure password.</p>
          </div>
          <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <form @submit.prevent="updatePassword" class="space-y-6">
              <!-- Current Password input -->
              <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700">
                  Current Password
                </label>
                <div class="mt-1">
                  <input 
                    id="current_password" 
                    v-model="form.current_password"
                    type="password"
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] sm:text-sm"
                  />
                  <p v-if="errors.current_password" class="mt-2 text-sm text-red-600">{{ errors.current_password }}</p>
                </div>
              </div>

              <!-- New Password input -->
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                  New Password
                </label>
                <div class="mt-1">
                  <input 
                    id="password" 
                    v-model="form.password"
                    type="password" 
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] sm:text-sm"
                  />
                  <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password }}</p>
                </div>
              </div>

              <!-- Confirm Password input -->
              <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                  Confirm Password
                </label>
                <div class="mt-1">
                  <input 
                    id="password_confirmation" 
                    v-model="form.password_confirmation"
                    type="password" 
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] sm:text-sm"
                  />
                  <p v-if="errors.password_confirmation" class="mt-2 text-sm text-red-600">{{ errors.password_confirmation }}</p>
                </div>
              </div>

              <!-- Save button -->
              <div class="flex justify-end">
                <button 
                  type="submit" 
                  :disabled="isSaving"
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[var(--color-primary)] hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-primary)]"
                >
                  <svg v-if="isSaving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ isSaving ? 'Updating...' : 'Update Password' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
