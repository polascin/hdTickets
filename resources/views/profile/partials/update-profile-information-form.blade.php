<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" data-validate-form data-ajax-submit>
        <div class="form-errors"></div>
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)"
                required autofocus autocomplete="name" placeholder="Enter first name" />
            <x-input-error class="mt-2" :messages="isset($errors) ? $errors->get('name') : []" />
        </div>

        <div>
            <x-input-label for="surname" :value="__('Surname')" />
            <x-text-input id="surname" name="surname" type="text" class="mt-1 block w-full" :value="old('surname', $user->surname)"
                autocomplete="family-name" placeholder="Enter last name" />
            <x-input-error class="mt-2" :messages="isset($errors) ? $errors->get('surname') : []" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)"
                required autocomplete="username" placeholder="Enter email address" />
            <x-input-error class="mt-2" :messages="isset($errors) ? $errors->get('email') : []" />
        </div>

        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $user->username)"
                autocomplete="username" placeholder="Enter username" />
            <x-input-error class="mt-2" :messages="isset($errors) ? $errors->get('username') : []" />
        </div>

        <div>
            <x-input-label for="phone" :value="__('Phone Number')" />
            <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone', $user->phone)"
                autocomplete="tel" placeholder="Enter phone number" />
            <x-input-error class="mt-2" :messages="isset($errors) ? $errors->get('phone') : []" />
        </div>

        <div>
            <x-input-label for="bio" :value="__('Bio')" />
            <textarea id="bio" name="bio"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                rows="3" placeholder="Tell us about yourself..." maxlength="1000">{{ old('bio', $user->bio) }}</textarea>
            <x-input-error class="mt-2" :messages="isset($errors) ? $errors->get('bio') : []" />
            <p class="mt-1 text-xs text-gray-500">Maximum 1000 characters</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="timezone" :value="__('Timezone')" />
                <select id="timezone" name="timezone"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Select Timezone</option>
                    @foreach (timezone_identifiers_list() as $timezone)
                        <option value="{{ $timezone }}"
                            {{ old('timezone', $user->timezone) === $timezone ? 'selected' : '' }}>
                            {{ $timezone }}
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="isset($errors) ? $errors->get('timezone') : []" />
            </div>

            <div>
                <x-input-label for="language" :value="__('Language')" />
                <select id="language" name="language"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Select Language</option>
                    <option value="en" {{ old('language', $user->language) === 'en' ? 'selected' : '' }}>English
                    </option>
                    <option value="es" {{ old('language', $user->language) === 'es' ? 'selected' : '' }}>Spanish
                    </option>
                    <option value="fr" {{ old('language', $user->language) === 'fr' ? 'selected' : '' }}>French
                    </option>
                    <option value="de" {{ old('language', $user->language) === 'de' ? 'selected' : '' }}>German
                    </option>
                    <option value="it" {{ old('language', $user->language) === 'it' ? 'selected' : '' }}>Italian
                    </option>
                    <option value="pt" {{ old('language', $user->language) === 'pt' ? 'selected' : '' }}>Portuguese
                    </option>
                </select>
                <x-input-error class="mt-2" :messages="isset($errors) ? $errors->get('language') : []" />
            </div>
        </div>

        <div>
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
