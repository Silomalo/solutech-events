// https://nuxt.com/docs/api/configuration/nuxt-config

import tailwindcss from "@tailwindcss/vite";

export default defineNuxtConfig({
  compatibilityDate: "2025-05-15",
  devtools: { enabled: true },
  modules: ["@nuxt/fonts", "@nuxt/icon", "@nuxt/image"],
  css: ["~/assets/css/main.css"],
  vite: {
    plugins: [tailwindcss()],
  },
  runtimeConfig: {
    public: {
      apiUrl: process.env.NUXT_PUBLIC_API_URL || "http://localhost:3000",
    },
  },
  // Register global middleware
  // routeRules: {
  //   '/account/**': { middleware: ['auth'] },
  //   '/login': { middleware: ['auth'] },
  //   '/register': { middleware: ['auth'] },
  //   '/forgot-password': { middleware: ['auth'] },
  //   '/reset-password': { middleware: ['auth'] },
  // },
  // Register plugins
  plugins: [
    '~/plugins/auth.ts'
  ],
});