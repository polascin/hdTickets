@extends('layouts.app')

@section('title', 'TailwindCSS Test')

@section('content')
  <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">TailwindCSS Integration Test</h1>
        <p class="text-xl text-gray-600">Testing TailwindCSS v4 utilities and custom components</p>
      </div>

      <!-- Utility Classes Test -->
      <div class="card mb-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Utility Classes</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div class="p-4 bg-blue-100 rounded-lg">
            <h3 class="font-medium text-blue-900">Blue Background</h3>
            <p class="text-blue-700 text-sm">Using bg-blue-100</p>
          </div>
          <div class="p-4 bg-green-100 rounded-lg">
            <h3 class="font-medium text-green-900">Green Background</h3>
            <p class="text-green-700 text-sm">Using bg-green-100</p>
          </div>
          <div class="p-4 bg-yellow-100 rounded-lg">
            <h3 class="font-medium text-yellow-900">Yellow Background</h3>
            <p class="text-yellow-700 text-sm">Using bg-yellow-100</p>
          </div>
        </div>
      </div>

      <!-- Custom Components Test -->
      <div class="card mb-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Custom Components</h2>
        <div class="space-y-4">
          <!-- Buttons -->
          <div class="flex flex-wrap gap-4">
            <button class="btn btn-primary">Primary Button</button>
            <button class="btn btn-secondary">Secondary Button</button>
            <button class="btn btn-outline">Outline Button</button>
          </div>

          <!-- Badges -->
          <div class="flex flex-wrap gap-2">
            <span class="badge badge-primary">Primary Badge</span>
            <span class="badge badge-success">Success Badge</span>
            <span class="badge badge-warning">Warning Badge</span>
            <span class="badge badge-danger">Danger Badge</span>
          </div>

          <!-- Form Input -->
          <div class="max-w-md">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Test Input
            </label>
            <input type="text" class="form-input" placeholder="Enter some text...">
          </div>
        </div>
      </div>

      <!-- Responsive Grid Test -->
      <div class="card">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Responsive Grid</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <div class="bg-gray-100 p-4 rounded text-center">
            <div class="w-8 h-8 bg-gray-400 rounded-full mx-auto mb-2"></div>
            <p class="text-sm text-gray-600">Item 1</p>
          </div>
          <div class="bg-gray-100 p-4 rounded text-center">
            <div class="w-8 h-8 bg-gray-400 rounded-full mx-auto mb-2"></div>
            <p class="text-sm text-gray-600">Item 2</p>
          </div>
          <div class="bg-gray-100 p-4 rounded text-center">
            <div class="w-8 h-8 bg-gray-400 rounded-full mx-auto mb-2"></div>
            <p class="text-sm text-gray-600">Item 3</p>
          </div>
          <div class="bg-gray-100 p-4 rounded text-center">
            <div class="w-8 h-8 bg-gray-400 rounded-full mx-auto mb-2"></div>
            <p class="text-sm text-gray-600">Item 4</p>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('styles')
  @vite('resources/css/tailwind.css')
@endpush
