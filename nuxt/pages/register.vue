<script setup lang="ts">
import { useAuth } from '~/composables/useAuth';

const router = useRouter();

// Get auth composable
const { register, error: authError, isLoading } = useAuth();

// Form state
const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  agree_terms: false
});

// Form validation and UI states
const errorMessage = computed(() => authError.value);
const errors = ref<Record<string, string>>({});

// Validate form
function validateForm() {
  const newErrors: Record<string, string> = {};
  
  if (!form.name.trim()) {
    newErrors.name = 'Name is required';
  }
  
  if (!form.email) {
    newErrors.email = 'Email is required';
  } else if (!/^\S+@\S+\.\S+$/.test(form.email)) {
    newErrors.email = 'Please enter a valid email address';
  }
  
  if (!form.password) {
    newErrors.password = 'Password is required';
  } else if (form.password.length < 8) {
    newErrors.password = 'Password must be at least 8 characters';
  }
  
  if (form.password !== form.password_confirmation) {
    newErrors.password_confirmation = 'Passwords do not match';
  }
  
  if (!form.agree_terms) {
    newErrors.agree_terms = 'You must agree to the terms and conditions';
  }
  
  errors.value = newErrors;
  return Object.keys(newErrors).length === 0;
}

// Handle form submission
async function handleRegister() {
  // Validate form
  if (!validateForm()) {
    return;
  }
  
  const userData = {
    name: form.name,
    email: form.email,
    password: form.password,
    password_confirmation: form.password_confirmation
  };
  
  const result = await register(userData);
  
  if (result === true) {
    // Redirect to account page or onboarding
    router.push('/account');
  } else if (typeof result === 'object' && result.validationErrors) {
    // Handle validation errors from Laravel
    Object.keys(result.validationErrors).forEach(key => {
      errors.value[key] = result.validationErrors[key][0];
    });
  }
}
</script>

<template>
  <div class="min-h-screen flex flex-col justify-center items-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div class="text-center">
        <h2 class="mt-6 text-3xl font-extrabold text-[var(--color-primary)]">
          Create your account
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          Or
          <NuxtLink to="/login" class="font-medium text-[var(--color-primary)] hover:text-opacity-90">
            sign in to your account
          </NuxtLink>
        </p>
      </div>

      <div class="mt-8 bg-white py-8 px-4 shadow-lg sm:rounded-lg sm:px-10 border border-gray-200">
        <form @submit.prevent="handleRegister" class="space-y-6">
          <!-- Error alert -->
          <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-red-700">{{ errorMessage }}</p>
              </div>
            </div>
          </div>

          <!-- Name input -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">
              Full name
            </label>
            <div class="mt-1">
              <input 
                id="name" 
                name="name" 
                type="text" 
                autocomplete="name" 
                required 
                v-model="form.name"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] sm:text-sm"
                placeholder="John Doe"
              />
              <p v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name }}</p>
            </div>
          </div>

          <!-- Email input -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email address
            </label>
            <div class="mt-1">
              <input 
                id="email" 
                name="email" 
                type="email" 
                autocomplete="email" 
                required 
                v-model="form.email"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] sm:text-sm"
                placeholder="john.doe@example.com"
              />
              <p v-if="errors.email" class="mt-2 text-sm text-red-600">{{ errors.email }}</p>
            </div>
          </div>

          <!-- Password input -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              Password
            </label>
            <div class="mt-1">
              <input 
                id="password" 
                name="password" 
                type="password" 
                autocomplete="new-password" 
                required 
                v-model="form.password"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] sm:text-sm"
                placeholder="••••••••"
              />
              <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password }}</p>
            </div>
          </div>

          <!-- Confirm Password input -->
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
              Confirm password
            </label>
            <div class="mt-1">
              <input 
                id="password_confirmation" 
                name="password_confirmation" 
                type="password" 
                autocomplete="new-password" 
                required 
                v-model="form.password_confirmation"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] sm:text-sm"
                placeholder="••••••••"
              />
              <p v-if="errors.password_confirmation" class="mt-2 text-sm text-red-600">{{ errors.password_confirmation }}</p>
            </div>
          </div>

          <!-- Terms and Conditions -->
          <div class="flex items-start">
            <div class="flex items-center h-5">
              <input 
                id="agree_terms" 
                name="agree_terms" 
                type="checkbox" 
                v-model="form.agree_terms"
                class="h-4 w-4 text-[var(--color-primary)] focus:ring-[var(--color-primary)] border-gray-300 rounded"
              />
            </div>
            <div class="ml-3 text-sm">
              <label for="agree_terms" class="font-medium text-gray-700">
                I agree to the 
                <a href="#" class="text-[var(--color-primary)] hover:text-opacity-90">Terms and Conditions</a>
                 and 
                <a href="#" class="text-[var(--color-primary)] hover:text-opacity-90">Privacy Policy</a>
              </label>
              <p v-if="errors.agree_terms" class="mt-2 text-sm text-red-600">{{ errors.agree_terms }}</p>
            </div>
          </div>

          <!-- Submit button -->
          <div>
            <button 
              type="submit" 
              :disabled="isLoading"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[var(--color-primary)] hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-primary)]"
            >
              <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ isLoading ? 'Creating account...' : 'Create account' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>



