<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Add New Ticket Source') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Create a new ticket source to monitor
                </p>
            </div>
            <div>
                <a href="{{ route('ticket-sources.index') }}" 
                   class="btn-secondary inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <form method="POST" action="{{ route('ticket-sources.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Event Name -->
                            <div>
                                <label for="event_name" class="block text-sm font-medium text-gray-700 mb-1">Event Name *</label>
                                <input type="text" 
                                       name="event_name" 
                                       id="event_name" 
                                       value="{{ old('event_name') }}"
                                       required
                                       class="form-input block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-colors @error('event_name') border-red-500 @enderror"
                                       placeholder="Manchester United vs Arsenal">
                                @error('event_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Source Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Source Name *</label>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       value="{{ old('name') }}"
                                       required
                                       class="form-input block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-colors @error('name') border-red-500 @enderror"
                                       placeholder="Official Source, StubHub, etc.">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Platform -->
                            <div>
                                <label for="platform" class="block text-sm font-medium text-gray-700 mb-1">Platform *</label>
                                <select name="platform" 
                                        id="platform" 
                                        required
                                        class="form-select block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-colors @error('platform') border-red-500 @enderror">
                                    <option value="">Select Platform</option>
                                    @foreach($platforms as $key => $name)
                                        <option value="{{ $key }}" {{ old('platform') === $key ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('platform')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Venue -->
                            <div>
                                <label for="venue" class="block text-sm font-medium text-gray-700 mb-1">Venue *</label>
                                <input type="text" 
                                       name="venue" 
                                       id="venue" 
                                       value="{{ old('venue') }}"
                                       required
                                       class="form-input block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-colors @error('venue') border-red-500 @enderror"
                                       placeholder="Old Trafford, Wembley Stadium, etc.">
                                @error('venue')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Event Date -->
                            <div>
                                <label for="event_date" class="block text-sm font-medium text-gray-700 mb-1">Event Date *</label>
                                <input type="datetime-local" 
                                       name="event_date" 
                                       id="event_date" 
                                       value="{{ old('event_date') }}"
                                       required
                                       min="{{ now()->format('Y-m-d\TH:i') }}"
                                       class="form-input block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-colors @error('event_date') border-red-500 @enderror">
                                @error('event_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Availability Status -->
                            <div>
                                <label for="availability_status" class="block text-sm font-medium text-gray-700 mb-1">Availability Status *</label>
                                <select name="availability_status" 
                                        id="availability_status" 
                                        required
                                        class="form-select block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-colors @error('availability_status') border-red-500 @enderror">
                                    <option value="">Select Status</option>
                                    @foreach($statuses as $key => $name)
                                        <option value="{{ $key }}" {{ old('availability_status') === $key ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('availability_status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Price Min -->
                            <div>
                                <label for="price_min" class="block text-sm font-medium text-gray-700 mb-1">Minimum Price</label>
                                <input type="number" 
                                       name="price_min" 
                                       id="price_min" 
                                       value="{{ old('price_min') }}"
                                       step="0.01"
                                       min="0"
                                       class="form-input block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-colors @error('price_min') border-red-500 @enderror"
                                       placeholder="0.00">
                                @error('price_min')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Price Max -->
                            <div>
                                <label for="price_max" class="block text-sm font-medium text-gray-700 mb-1">Maximum Price</label>
                                <input type="number" 
                                       name="price_max" 
                                       id="price_max" 
                                       value="{{ old('price_max') }}"
                                       step="0.01"
                                       min="0"
                                       class="form-input block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-colors @error('price_max') border-red-500 @enderror"
                                       placeholder="999.99">
                                @error('price_max')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Currency -->
                            <div>
                                <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                                <select name="currency" 
                                        id="currency" 
                                        class="form-select block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-colors @error('currency') border-red-500 @enderror">
                                    <option value="GBP" {{ old('currency') === 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                    <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                    <option value="CAD" {{ old('currency') === 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                                    <option value="AUD" {{ old('currency') === 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                                    <option value="JPY" {{ old('currency') === 'JPY' ? 'selected' : '' }}>JPY - Japanese Yen</option>
                                </select>
                                @error('currency')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Country -->
                            <div>
                                <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                <select name="country" 
                                        id="country" 
                                        class="form-select block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-colors @error('country') border-red-500 @enderror">
                                    <option value="GB" {{ old('country') === 'GB' ? 'selected' : '' }}>United Kingdom</option>
                                    <option value="US" {{ old('country') === 'US' ? 'selected' : '' }}>United States</option>
                                    <option value="DE" {{ old('country') === 'DE' ? 'selected' : '' }}>Germany</option>
                                    <option value="FR" {{ old('country') === 'FR' ? 'selected' : '' }}>France</option>
                                    <option value="ES" {{ old('country') === 'ES' ? 'selected' : '' }}>Spain</option>
                                    <option value="IT" {{ old('country') === 'IT' ? 'selected' : '' }}>Italy</option>
                                    <option value="CA" {{ old('country') === 'CA' ? 'selected' : '' }}>Canada</option>
                                    <option value="AU" {{ old('country') === 'AU' ? 'selected' : '' }}>Australia</option>
                                    <option value="JP" {{ old('country') === 'JP' ? 'selected' : '' }}>Japan</option>
                                </select>
                                @error('country')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- URL -->
                        <div>
                            <label for="url" class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                            <input type="url" 
                                   name="url" 
                                   id="url" 
                                   value="{{ old('url') }}"
                                   class="form-input block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-colors @error('url') border-red-500 @enderror"
                                   placeholder="https://example.com/tickets">
                            @error('url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- External ID -->
                        <div>
                            <label for="external_id" class="block text-sm font-medium text-gray-700 mb-1">External ID</label>
                            <input type="text" 
                                   name="external_id" 
                                   id="external_id" 
                                   value="{{ old('external_id') }}"
                                   class="form-input block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-colors @error('external_id') border-red-500 @enderror"
                                   placeholder="External system identifier">
                            @error('external_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="4"
                                      class="form-input block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 sm:text-sm transition-colors @error('description') border-red-500 @enderror"
                                      placeholder="Additional notes about this ticket source...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('ticket-sources.index') }}" 
                               class="btn-secondary px-6 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="btn-primary px-6 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                Create Ticket Source
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
