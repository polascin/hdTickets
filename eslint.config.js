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
        mobileUtils: 'readonly',
        // Development globals
        process: 'readonly'
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
      'vue/require-explicit-emits': 'off', // Allow implicit emits for rapid development
      
      // TypeScript rules
      '@typescript-eslint/no-unused-vars': ['error', { 
        argsIgnorePattern: '^_',
        varsIgnorePattern: '^_',
        ignoreRestSiblings: true,
        destructuredArrayIgnorePattern: '^_'
      }],
      
      // Console rules - more lenient for development
      'no-console': process.env.NODE_ENV === 'production' ? 'error' : 'off',
      
      // General rules
      'no-case-declarations': 'error',
      'no-dupe-class-members': 'error',
      'no-empty': 'warn',
      'no-undef': 'error',
      'getter-return': 'error',
      'no-prototype-builtins': 'warn',
      'no-debugger': process.env.NODE_ENV === 'production' ? 'error' : 'warn',
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
