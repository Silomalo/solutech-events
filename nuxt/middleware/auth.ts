export default defineNuxtRouteMiddleware((to, from) => {
  const authCookie = useCookie('auth_token');
  // console.log('Auth Middleware:', authCookie.value);
  // console.log("Navigating to:", to.path.startsWith("/account"));
  // If user is not authenticated and trying to access a protected route
  if (!authCookie.value && to.path.startsWith('/account')) {
    return navigateTo('/login');
  }
  
  // If user is already authenticated and trying to access auth pages
  if (authCookie.value && (to.path === '/login' || to.path === '/register' || to.path === '/forgot-password' || to.path === '/reset-password')) {
    return navigateTo('/account');
  }
});
