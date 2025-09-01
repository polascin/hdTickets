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
        localStorage: "readonly",
        sessionStorage: "readonly",
        setTimeout: "readonly",
        clearTimeout: "readonly",
        setInterval: "readonly",
        clearInterval: "readonly",
        fetch: "readonly",
        Notification: "readonly",
        CustomEvent: "readonly",
        IntersectionObserver: "readonly",
        PerformanceObserver: "readonly",
        performance: "readonly",
        screen: "readonly",

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
      "@typescript-eslint/no-unused-vars": ["error", { "argsIgnorePattern": "^_" }],
      "@typescript-eslint/explicit-function-return-type": "off",
      "@typescript-eslint/explicit-module-boundary-types": "off",
      "@typescript-eslint/no-explicit-any": "warn",
      "no-console": ["warn", { "allow": ["warn", "error"] }],
      "prefer-const": "error",
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
      "no-unreachable": "error"
    }
  },
  {
    ignores: ["public/build/**/*", "vendor/**/*", "node_modules/**/*"]
  }
];
