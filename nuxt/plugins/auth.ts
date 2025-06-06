export default defineNuxtPlugin((nuxtApp) => {
  // Add a global HTTP interceptor for authentication
  nuxtApp.hook('app:created', () => {
    const authCookie = useCookie('auth_token');
    
    // Add a global fetch interceptor
    globalThis.$fetch = $fetch.create({
      onRequest({ options }) {
        // Add authorization header if token exists
        if (authCookie.value) {
          // Create a new headers object with the correct type
          const headers = new Headers(options.headers as HeadersInit);
          headers.set("Authorization", `Bearer ${authCookie.value}`);
          // For Laravel Sanctum, ensure Accept header is set to application/json
          // This prevents redirects and forces API responses
          headers.set("Accept", "application/json");
          options.headers = headers;
        }
      },
      onResponseError({ response }) {
        // Handle 401 Unauthorized responses
        // if (response.status === 401) {
        //   // Clear auth cookie
        //   const authCookie = useCookie('auth_token');
        //   authCookie.value = null;
          
        //   // Redirect to login page
        //   navigateTo('/login');
        // }
      }
    });
  });
});
