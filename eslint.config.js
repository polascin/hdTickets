import js from "@eslint/js";
import tseslint from "@typescript-eslint/eslint-plugin";
import tsparser from "@typescript-eslint/parser";

export default [
  js.configs.recommended,
  {
    files: ["resources/js/**/*.{js,ts}"],
    languageOptions: {
      parser: tsparser,
      ecmaVersion: "latest",
      sourceType: "module",
      globals: {
        // Alpine.js & Laravel globals
        Alpine: "readonly",
        axios: "readonly",
        Echo: "readonly",
        Pusher: "readonly",
        Chart: "readonly",

        // Browser globals
        window: "readonly",
        document: "readonly",
        console: "readonly",
        navigator: "readonly",
        location: "readonly",
        history: "readonly",
        URL: "readonly",
        URLSearchParams: "readonly",
        localStorage: "readonly",
        sessionStorage: "readonly",
        setTimeout: "readonly",
        clearTimeout: "readonly",
        setInterval: "readonly",
        clearInterval: "readonly",
        fetch: "readonly",
        FormData: "readonly",
        Blob: "readonly",
        AbortController: "readonly",
        Notification: "readonly",
        CustomEvent: "readonly",
        requestAnimationFrame: "readonly",
        cancelAnimationFrame: "readonly",
        IntersectionObserver: "readonly",
        MutationObserver: "readonly",
        ResizeObserver: "readonly",
        PerformanceObserver: "readonly",
        performance: "readonly",
        screen: "readonly",
        CSS: "readonly",
        Image: "readonly",
        Audio: "readonly",
        Node: "readonly",
        getComputedStyle: "readonly",
        btoa: "readonly",
        atob: "readonly",
        d3: "readonly",

        // Google Analytics
        gtag: "readonly",
        dataLayer: "readonly",

        // Node.js (for build tools)
        process: "readonly",
        module: "readonly",
        require: "readonly",
        exports: "readonly"
      }
    },
    plugins: {
      "@typescript-eslint": tseslint
    },
    rules: {
      "@typescript-eslint/no-unused-vars": ["warn", { "argsIgnorePattern": "^_", "varsIgnorePattern": "^_" }],
      "@typescript-eslint/explicit-function-return-type": "off",
      "@typescript-eslint/explicit-module-boundary-types": "off",
      "@typescript-eslint/no-explicit-any": "warn",
      "no-console": ["warn", { "allow": ["warn", "error"] }],
      "prefer-const": "warn",
      "no-var": "error",
      "no-unused-vars": "off",
      "no-prototype-builtins": "warn",
      "no-empty": "warn",

      // Semicolon consistency and ASI prevention
      "semi": ["error", "always"],
      "semi-style": ["error", "last"],
      "semi-spacing": ["error", { "before": false, "after": true }],
      "no-extra-semi": "error",
      "no-unexpected-multiline": "error",
      "no-unreachable": "error",
      "no-useless-escape": "warn"
    }
  },
  {
    ignores: ["public/build/**/*", "vendor/**/*", "node_modules/**/*"]
  }
];
