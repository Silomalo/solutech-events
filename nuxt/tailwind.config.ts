import type { Config } from "tailwindcss";

export default <Config>{
  content: [
    "./components/**/*.{js,vue,ts}",
    "./layouts/**/*.vue",
    "./pages/**/*.vue",
    "./plugins/**/*.{js,ts}",
    "./nuxt.config.{js,ts}",
    "./app.vue",
  ],
  theme: {
    extend: {
      colors: {
        // Your custom colors here
        primary: "#3490dc", // Example primary color
        secondary: "#ffed4a", // Example secondary color
        accent: "#f0f8ea", // Example accent color
        "custom-gray": "#8492a6", // Example with a hyphen in the name
      },
      // You can also extend other theme options here, like spacing, typography, etc.
    },
  },
};
