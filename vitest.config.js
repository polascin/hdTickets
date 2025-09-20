/// <reference types="vitest" />
import { defineConfig } from 'vite'

export default defineConfig({
  test: {
    environment: 'jsdom',
    exclude: [
      'tests/e2e/**',
      'tests/Feature/**',
      'tests/Unit/**',
      '**/node_modules/**',
      '**/dist/**',
      '**/public/**',
      '**/.{idea,git,cache,output,temp}/**',
      '**/{karma,rollup,webpack,vite,vitest,jest,ava,babel,nyc,cypress,tsup,build}.config.*'
    ],
    include: [
      'resources/**/*.{test,spec}.{js,ts}',
      'resources/js/**/__tests__/**/*.{js,ts}'
    ]
  }
})