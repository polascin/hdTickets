import js from '@eslint/js';
import vue from 'eslint-plugin-vue';
import typescript from '@typescript-eslint/eslint-plugin';
import typescriptParser from '@typescript-eslint/parser';
import vueParser from 'vue-eslint-parser';
import accessibility from 'eslint-plugin-vuejs-accessibility';
import globals from 'globals';

export default [
  js.configs.recommended,
  ...vue.configs['flat/essential'],
  {
    files: ['**/*.{js,ts,vue}'],
    languageOptions: {
      parser: vueParser,
      parserOptions: {
        parser: typescriptParser,
        ecmaVersion: 2022,
        sourceType: 'module',
        extraFileExtensions: ['.vue']
      },
      globals: {
        ...globals.browser,
        ...globals.node,
        __VUE_OPTIONS_API__: 'readonly',
        __VUE_PROD_DEVTOOLS__: 'readonly',
        defineProps: 'readonly',
        defineEmits: 'readonly',
        defineExpose: 'readonly',
        withDefaults: 'readonly',
        // Third-party library globals
        axios: 'readonly',
        Swal: 'readonly',
        $: 'readonly',
        jQuery: 'readonly',
        Echo: 'readonly',
        mobileUtils: 'readonly'
      }
    },
    plugins: {
      '@typescript-eslint': typescript,
      'vuejs-accessibility': accessibility
    },
    rules: {
      // Vue rules
      'vue/multi-word-component-names': 'off',
      'vue/no-v-html': 'warn',
      'vue/require-default-prop': 'off',
      'vue/require-explicit-emits': 'warn',
      
      // TypeScript rules
      '@typescript-eslint/no-unused-vars': ['error', { 
        argsIgnorePattern: '^_',
        varsIgnorePattern: '^_' 
      }],
      '@typescript-eslint/no-explicit-any': 'warn',
      
      // General rules
      'no-console': 'warn',
      'no-debugger': 'error',
      'no-unused-vars': 'off' // Use TypeScript version instead
    }
  },
  {
    files: ['**/*.js'],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'module',
      globals: {
        ...globals.browser,
        ...globals.node,
        // Third-party library globals
        axios: 'readonly',
        Swal: 'readonly',
        $: 'readonly',
        jQuery: 'readonly',
        Echo: 'readonly',
        mobileUtils: 'readonly'
      }
    }
  },
  {
    ignores: [
      'node_modules/**',
      'public/**',
      'dist/**',
      '*.config.js'
    ]
  }
];
