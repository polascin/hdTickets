import js from '@eslint/js';
import tseslint from '@typescript-eslint/eslint-plugin';
import tsParser from '@typescript-eslint/parser';

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

        // Browser APIs
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
        location: "readonly",
        history: "readonly",
        URL: "readonly",
        URLSearchParams: "readonly",
        FormData: "readonly",
        FileReader: "readonly",
        Image: "readonly",
        Audio: "readonly",
        Blob: "readonly",
        Event: "readonly",
        EventTarget: "readonly",
        Node: "readonly",
        HTMLElement: "readonly",
        confirm: "readonly",
        alert: "readonly",
        btoa: "readonly",
        atob: "readonly",
        getComputedStyle: "readonly",
        CSS: "readonly",
        caches: "readonly",
        indexedDB: "readonly",
        scrollY: "readonly",

        // Animation and observation APIs
        requestAnimationFrame: "readonly",
        cancelAnimationFrame: "readonly",
        MutationObserver: "readonly",
        ResizeObserver: "readonly",
        IntersectionObserver: "readonly",
        PerformanceObserver: "readonly",
        performance: "readonly",
        screen: "readonly",
        AbortController: "readonly",
        WebSocket: "readonly",
        CSSRule: "readonly",

        // Third-party libraries (loaded via CDN/script tags)
        d3: "readonly",
        React: "readonly",
        ReactDOM: "readonly",
        $: "readonly",
        Cropper: "readonly",
        result: "readonly",

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
      "no-console": ["warn", { "allow": ["log", "warn", "error", "info", "debug"] }],
      "prefer-const": "error",
      "no-var": "error",
      "no-unused-vars": "off",
      "no-prototype-builtins": "warn",
      "no-empty": ["error", { "allowEmptyCatch": true }],

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
    // Test files specific configuration
    files: ["resources/js/**/__tests__/**/*.{js,ts}", "resources/js/**/*.test.{js,ts}", "resources/js/**/*.spec.{js,ts}"],
    languageOptions: {
      globals: {
        // Testing globals
        global: "readonly",
        describe: "readonly",
        test: "readonly",
        it: "readonly",
        expect: "readonly",
        beforeEach: "readonly",
        afterEach: "readonly",
        beforeAll: "readonly",
        afterAll: "readonly",
        jest: "readonly",
        vi: "readonly"
      }
    },
    rules: {
      "no-console": "off"
    }
  },
  {
    ignores: ["public/build/**/*", "vendor/**/*", "node_modules/**/*"]
  }
];
