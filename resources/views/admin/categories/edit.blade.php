<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Category') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" 
                                              :value="old('name', $category->name)" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <!-- Slug -->
                            <div>
                                <x-input-label for="slug" :value="__('Slug')" />
                                <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" 
                                              :value="old('slug', $category->slug)" />
                                <p class="mt-1 text-sm text-gray-500">Leave blank to auto-generate from name</p>
                                <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                            </div>

                            <!-- Parent Category -->
                            <div>
                                <x-input-label for="parent_id" :value="__('Parent Category')" />
                                <select id="parent_id" name="parent_id" 
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">None (Root Category)</option>
                                    @foreach($parentCategories as $parent)
                                        <option value="{{ $parent->id }}" 
                                            {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('parent_id')" />
                            </div>

                            <!-- Sort Order -->
                            <div>
                                <x-input-label for="sort_order" :value="__('Sort Order')" />
                                <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 block w-full" 
                                              :value="old('sort_order', $category->sort_order)" min="0" />
                                <p class="mt-1 text-sm text-gray-500">Leave blank to auto-assign</p>
                                <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
                            </div>

                            <!-- Color -->
                            <div>
                                <x-input-label for="color" :value="__('Color')" />
                                <div class="mt-1 flex">
                                    <x-text-input id="color" name="color" type="color" class="block w-16 h-10" 
                                                  :value="old('color', $category->color ?? '#3B82F6')" />
                                    <x-text-input id="color_text" name="color_text" type="text" class="ml-2 block flex-1" 
                                                  :value="old('color', $category->color ?? '#3B82F6')" placeholder="#3B82F6" />
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('color')" />
                            </div>

                            <!-- Icon -->
                            <div>
                                <x-input-label for="icon" :value="__('Icon')" />
                                <x-text-input id="icon" name="icon" type="text" class="mt-1 block w-full" 
                                              :value="old('icon', $category->icon)" placeholder="fas fa-tag" />
                                <p class="mt-1 text-sm text-gray-500">FontAwesome icon class (e.g., fas fa-tag)</p>
                                <x-input-error class="mt-2" :messages="$errors->get('icon')" />
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-6">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="4" 
                                      class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                      placeholder="Brief description of this category...">{{ old('description', $category->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <!-- Status -->
                        <div class="mt-6">
                            <label class="flex items-center">
                                <input type="checkbox" id="is_active" name="is_active" value="1" 
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                                       {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-900">Active</span>
                            </label>
                            <p class="mt-1 text-sm text-gray-500">Inactive categories won't be available for new tickets</p>
                            <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end mt-6 space-x-3">
                            <a href="{{ route('admin.categories.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Save Changes') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sync color picker with text input
        document.getElementById('color').addEventListener('input', function() {
            document.getElementById('color_text').value = this.value;
        });
        
        document.getElementById('color_text').addEventListener('input', function() {
            if (this.value.match(/^#[A-Fa-f0-9]{6}$/)) {
                document.getElementById('color').value = this.value;
            }
        });

        // Auto-generate slug from name
        document.getElementById('name').addEventListener('input', function() {
            const slug = this.value.toLowerCase()
                .replace(/[^a-z0-9 -]/g, '') // Remove invalid chars
                .replace(/\s+/g, '-') // Replace spaces with -
                .replace(/-+/g, '-') // Replace multiple - with single -
                .trim('-'); // Remove leading/trailing -
            
            if (!document.getElementById('slug').value || document.getElementById('slug').dataset.auto !== 'false') {
                document.getElementById('slug').value = slug;
            }
        });

        document.getElementById('slug').addEventListener('input', function() {
            this.dataset.auto = 'false'; // Mark as manually edited
        });
    </script>
</x-app-layout>

