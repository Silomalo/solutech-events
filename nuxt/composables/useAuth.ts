import { useRuntimeConfig } from "nuxt/app";
import { ref, computed } from "vue";
import { getAPIUrl } from "~/lib/utils";

export const useAuth = () => {
  const user = ref<any>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const isAuthenticated = computed(() => !!useCookie("auth_token").value);
  const isAdmin = computed(() => user.value?.role === "admin");

  // Check authentication status
  const checkAuth = async () => {
    try {
      isLoading.value = true;
      error.value = null;

      const token = useCookie("auth_token").value;
      if (!token) {
        user.value = null;
        return false;
      }

      user.value = await $fetch(`${getAPIUrl()}/api/user`, {
        method: "GET",
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      return true;
    } catch (err: any) {
      console.error("Auth check error:", err);
      error.value = err?.message || "Authentication error";
      user.value = null;

      // Clear token if invalid
      if (err.response?.status === 401) {
        const authCookie = useCookie("auth_token");
        authCookie.value = null;
      }

      return false;
    } finally {
      isLoading.value = false;
    }
  };

  // Login user
  const login = async (
    email: string,
    password: string,
    remember: boolean = false
  ) => {
    try {
      isLoading.value = true;
      error.value = null;

      const response = await $fetch<{ token: string }>(
        `${getAPIUrl()}/api/login`,
        {
          method: "POST",
          body: { email, password },
          credentials: "include",
        }
      );

      // Set auth token in cookie
      if (response.token) {
        const authCookie = useCookie("auth_token", {
          maxAge: remember ? 60 * 60 * 24 * 30 : 60 * 60 * 24, // 30 days if remember is checked, otherwise 1 day
          path: "/",
        });
        authCookie.value = response.token;

        // Get user data
        await checkAuth();
        return true;
      }

      return false;
    } catch (err: any) {
      console.error("Login error:", err);
      error.value =
        err?.response?._data?.message ||
        "Login failed. Please check your credentials.";
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  // Logout user
  const logout = async () => {
    try {
      isLoading.value = true;

      const token = useCookie("auth_token").value;
      if (token) {
        await $fetch(`${getAPIUrl()}/api/logout`, {
          method: "POST",
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
      }

      // Clear auth cookie
      const authCookie = useCookie("auth_token");
      authCookie.value = null;

      user.value = null;
      return true;
    } catch (err: any) {
      console.error("Logout error:", err);

      // Even if logout API fails, clear cookie
      const authCookie = useCookie("auth_token");
      authCookie.value = null;
      user.value = null;

      return true;
    } finally {
      isLoading.value = false;
    }
  };

  // Register user
  const register = async (userData: any) => {
    try {
      isLoading.value = true;
      error.value = null;

      const response = await $fetch<{ token?: string }>(
        `${getAPIUrl()}/api/register`,
        {
          method: "POST",
          body: userData,
        }
      );

      // Set auth token in cookie if API returns token after registration
      if (response.token) {
        const authCookie = useCookie("auth_token", {
          maxAge: 60 * 60 * 24, // 1 day
          path: "/",
        });
        authCookie.value = response.token;
        // Get user data
        await checkAuth();
        return true;
      }

      return true; // Registration successful but no auto-login
    } catch (err: any) {
      console.error("Registration error:", err);

      if (err.response && err.response.status === 422) {
        error.value = "Validation error. Please check your input.";
        return {
          error: error.value,
          validationErrors: err.response._data.errors,
        };
      }

      error.value =
        err?.response?._data?.message ||
        "Registration failed. Please try again.";
      return { error: error.value };
    } finally {
      isLoading.value = false;
    }
  };

  // Forgot password
  const forgotPassword = async (email: string) => {
    try {
      isLoading.value = true;
      error.value = null;

      await $fetch(`${getAPIUrl()}/api/forgot-password`, {
        method: "POST",
        body: { email },
      });

      return true;
    } catch (err: any) {
      console.error("Forgot password error:", err);
      error.value =
        err?.response?._data?.message ||
        "Failed to send password reset link. Please try again.";
      return false;
    } finally {
      isLoading.value = false;
    }
  };

  // Reset password
  const resetPassword = async (resetData: {
    email: string;
    password: string;
    password_confirmation: string;
    token: string;
  }) => {
    try {
      isLoading.value = true;
      error.value = null;

      await $fetch(`${getAPIUrl()}/api/reset-password`, {
        method: "POST",
        body: resetData,
      });

      return true;
    } catch (err: any) {
      console.error("Reset password error:", err);

      if (err.response && err.response.status === 422) {
        error.value = "Validation error. Please check your input.";
        return {
          error: error.value,
          validationErrors: err.response._data.errors,
        };
      }

      error.value =
        err?.response?._data?.message ||
        "Password reset failed. Please try again.";
      return { error: error.value };
    } finally {
      isLoading.value = false;
    }
  };

  // Initialize auth state
  const initAuth = async () => {
    await checkAuth();
  };

  // Call initAuth on server-side for initial load
  if (process.server) {
    initAuth();
  }

  return {
    user,
    isLoading,
    error,
    isAuthenticated,
    isAdmin,
    checkAuth,
    login,
    logout,
    register,
    forgotPassword,
    resetPassword,
    initAuth,
  };
};
