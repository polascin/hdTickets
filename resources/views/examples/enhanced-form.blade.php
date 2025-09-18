@extends('layouts.app-v2')

@section('title', 'Enhanced Form UX Examples')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Enhanced Form UX Examples</h1>
            <p class="text-gray-600 mt-2">Demonstrating modern form components with validation, masking, and real-time feedback</p>
        </div>

        {{-- Include CSS and JS --}}
        <link rel="stylesheet" href="{{ asset('css/enhanced-forms.css') }}">
        <script src="{{ asset('js/formValidator.js') }}" defer></script>

        {{-- Basic Enhanced Form --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Basic Enhanced Form</h2>
            
            <form id="basic-form" class="form-enhanced" data-autosave="true" novalidate>
                <div class="form-grid form-grid--2col">
                    {{-- Name Field with Floating Label --}}
                    <x-form-field 
                        name="first_name" 
                        label="First Name" 
                        :required="true"
                        :floating="true"
                        help="Enter your legal first name"
                    >
                        <x-text-input 
                            id="first_name"
                            name="first_name"
                            type="text"
                            :floating="true"
                            placeholder="First Name"
                            :required="true"
                            validate="required"
                        />
                    </x-form-field>

                    <x-form-field 
                        name="last_name" 
                        label="Last Name" 
                        :required="true"
                        :floating="true"
                    >
                        <x-text-input 
                            id="last_name"
                            name="last_name"
                            type="text"
                            :floating="true"
                            placeholder="Last Name"
                            :required="true"
                            validate="required"
                        />
                    </x-form-field>
                </div>

                {{-- Email with Icon --}}
                <x-form-field 
                    name="email" 
                    label="Email Address" 
                    :required="true"
                    icon="<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207'></path></svg>"
                    help="We'll never share your email address"
                >
                    <x-text-input 
                        id="email"
                        name="email"
                        type="email"
                        placeholder="Enter your email address"
                        :required="true"
                        validate="required,email"
                        mask="email"
                    />
                </x-form-field>

                {{-- Phone with Masking --}}
                <x-form-field 
                    name="phone" 
                    label="Phone Number" 
                    :required="true"
                    icon="<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'></path></svg>"
                >
                    <x-text-input 
                        id="phone"
                        name="phone"
                        type="tel"
                        placeholder="(555) 123-4567"
                        :required="true"
                        validate="required,phone"
                        mask="phone"
                    />
                </x-form-field>

                {{-- Password with Strength Meter --}}
                <x-form-field 
                    name="password" 
                    label="Password" 
                    :required="true"
                    icon="<svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'></path></svg>"
                    help="Password must be at least 8 characters with mixed case, numbers, and symbols"
                >
                    <x-text-input 
                        id="password"
                        name="password"
                        type="password"
                        placeholder="Create a strong password"
                        :required="true"
                        validate="required,password"
                        minlength="8"
                    />
                </x-form-field>

                <div class="form-actions">
                    <button type="submit" class="form-button form-button--primary form-button--large">
                        <span class="form-button__text">Create Account</span>
                        <svg class="form-button__icon w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        {{-- Multi-Step Form --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Multi-Step Form</h2>
            
            {{-- Progress Indicator --}}
            <div class="form-progress mb-6">
                <div class="form-progress__track">
                    <div class="form-progress__bar" style="width: 33.33%"></div>
                </div>
                <div class="form-progress__steps">
                    <div class="form-step form-step--completed">
                        <span class="form-step__number">1</span>
                        <span class="form-step__label">Personal Info</span>
                    </div>
                    <div class="form-step form-step--active">
                        <span class="form-step__number">2</span>
                        <span class="form-step__label">Contact Details</span>
                    </div>
                    <div class="form-step">
                        <span class="form-step__number">3</span>
                        <span class="form-step__label">Preferences</span>
                    </div>
                </div>
            </div>
            
            <form id="multistep-form" class="form-enhanced" data-multi-step="true" novalidate>
                {{-- Step 1: Personal Info --}}
                <div class="form-step form-step--completed" style="display: none;">
                    <h3 class="form-step__title">Personal Information</h3>
                    
                    <div class="form-grid form-grid--2col">
                        <x-form-field name="ms_first_name" label="First Name" :required="true">
                            <x-text-input 
                                id="ms_first_name"
                                name="ms_first_name"
                                type="text"
                                :required="true"
                                validate="required"
                            />
                        </x-form-field>

                        <x-form-field name="ms_last_name" label="Last Name" :required="true">
                            <x-text-input 
                                id="ms_last_name"
                                name="ms_last_name"
                                type="text"
                                :required="true"
                                validate="required"
                            />
                        </x-form-field>
                    </div>

                    <x-form-field name="ms_birthdate" label="Date of Birth" :required="true">
                        <x-text-input 
                            id="ms_birthdate"
                            name="ms_birthdate"
                            type="date"
                            :required="true"
                            validate="required,date"
                        />
                    </x-form-field>
                </div>

                {{-- Step 2: Contact Details --}}
                <div class="form-step form-step--active">
                    <h3 class="form-step__title">Contact Details</h3>
                    
                    <x-form-field name="ms_email" label="Email Address" :required="true">
                        <x-text-input 
                            id="ms_email"
                            name="ms_email"
                            type="email"
                            :required="true"
                            validate="required,email"
                        />
                    </x-form-field>

                    <x-form-field name="ms_phone" label="Phone Number" :required="true">
                        <x-text-input 
                            id="ms_phone"
                            name="ms_phone"
                            type="tel"
                            :required="true"
                            validate="required,phone"
                            mask="phone"
                        />
                    </x-form-field>

                    <x-form-field name="ms_address" label="Address">
                        <textarea 
                            id="ms_address" 
                            name="ms_address" 
                            class="form-input"
                            rows="3"
                            placeholder="Enter your address"
                        ></textarea>
                    </x-form-field>
                </div>

                {{-- Step 3: Preferences --}}
                <div class="form-step" style="display: none;">
                    <h3 class="form-step__title">Preferences</h3>
                    
                    <x-form-field name="ms_newsletter" label="Newsletter Subscription">
                        <div class="form-checkbox-group">
                            <label class="form-checkbox">
                                <input type="checkbox" name="ms_newsletter" value="1">
                                <span class="form-checkbox__mark"></span>
                                <span class="form-checkbox__label">Subscribe to our newsletter</span>
                            </label>
                        </div>
                    </x-form-field>

                    <x-form-field name="ms_notifications" label="Notification Preferences">
                        <div class="form-radio-group">
                            <label class="form-radio">
                                <input type="radio" name="ms_notifications" value="all">
                                <span class="form-radio__mark"></span>
                                <span class="form-radio__label">All notifications</span>
                            </label>
                            <label class="form-radio">
                                <input type="radio" name="ms_notifications" value="important">
                                <span class="form-radio__mark"></span>
                                <span class="form-radio__label">Important only</span>
                            </label>
                            <label class="form-radio">
                                <input type="radio" name="ms_notifications" value="none">
                                <span class="form-radio__mark"></span>
                                <span class="form-radio__label">No notifications</span>
                            </label>
                        </div>
                    </x-form-field>
                </div>

                {{-- Form Navigation --}}
                <div class="form-actions form-actions--between">
                    <button type="button" class="form-button form-button--secondary" data-form-prev>
                        <svg class="form-button__icon w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
                        </svg>
                        <span class="form-button__text">Previous</span>
                    </button>
                    
                    <button type="button" class="form-button form-button--primary" data-form-next>
                        <span class="form-button__text">Next</span>
                        <svg class="form-button__icon w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </button>
                    
                    <button type="submit" class="form-button form-button--success" style="display: none;">
                        <span class="form-button__text">Complete Registration</span>
                        <svg class="form-button__icon w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        {{-- Input Variations --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Input Variations</h2>
            
            <form class="form-enhanced" novalidate>
                {{-- Size Variations --}}
                <div class="form-section">
                    <h3 class="form-section__title">Size Variations</h3>
                    
                    <div class="space-y-4">
                        <x-form-field name="input_small" label="Small Input" size="small">
                            <x-text-input 
                                id="input_small"
                                name="input_small"
                                size="small"
                                placeholder="Small input"
                            />
                        </x-form-field>

                        <x-form-field name="input_medium" label="Medium Input" size="medium">
                            <x-text-input 
                                id="input_medium"
                                name="input_medium"
                                size="medium"
                                placeholder="Medium input (default)"
                            />
                        </x-form-field>

                        <x-form-field name="input_large" label="Large Input" size="large">
                            <x-text-input 
                                id="input_large"
                                name="input_large"
                                size="large"
                                placeholder="Large input"
                            />
                        </x-form-field>
                    </div>
                </div>

                {{-- Input Masks --}}
                <div class="form-section">
                    <h3 class="form-section__title">Input Masking</h3>
                    
                    <div class="form-grid form-grid--2col">
                        <x-form-field name="credit_card" label="Credit Card">
                            <x-text-input 
                                id="credit_card"
                                name="credit_card"
                                placeholder="1234 5678 9012 3456"
                                mask="creditcard"
                                validate="creditcard"
                            />
                        </x-form-field>

                        <x-form-field name="currency" label="Amount">
                            <x-text-input 
                                id="currency"
                                name="currency"
                                placeholder="$1,234.56"
                                mask="currency"
                            />
                        </x-form-field>

                        <x-form-field name="date_input" label="Date">
                            <x-text-input 
                                id="date_input"
                                name="date_input"
                                placeholder="MM/DD/YYYY"
                                mask="date"
                            />
                        </x-form-field>

                        <x-form-field name="time_input" label="Time">
                            <x-text-input 
                                id="time_input"
                                name="time_input"
                                placeholder="12:34"
                                mask="time"
                            />
                        </x-form-field>
                    </div>
                </div>

                {{-- Input with Addons --}}
                <div class="form-section">
                    <h3 class="form-section__title">Input Addons</h3>
                    
                    <x-form-field 
                        name="website" 
                        label="Website URL"
                        prefix="https://"
                        suffix=".com"
                    >
                        <x-text-input 
                            id="website"
                            name="website"
                            placeholder="example"
                        />
                    </x-form-field>

                    <x-form-field 
                        name="price" 
                        label="Product Price"
                        prefix="$"
                        suffix="USD"
                    >
                        <x-text-input 
                            id="price"
                            name="price"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                        />
                    </x-form-field>
                </div>
            </form>
        </div>

        {{-- Form States --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Form States</h2>
            
            <form class="form-enhanced" novalidate>
                <div class="form-grid form-grid--2col">
                    {{-- Success State --}}
                    <x-form-field 
                        name="success_field" 
                        label="Success Field" 
                        success="Great! This field is valid"
                    >
                        <x-text-input 
                            id="success_field"
                            name="success_field"
                            value="valid@example.com"
                            class="form-input--success"
                        />
                    </x-form-field>

                    {{-- Error State --}}
                    <x-form-field 
                        name="error_field" 
                        label="Error Field" 
                        error="Please enter a valid email address"
                    >
                        <x-text-input 
                            id="error_field"
                            name="error_field"
                            value="invalid-email"
                            class="form-input--error"
                        />
                    </x-form-field>

                    {{-- Warning State --}}
                    <x-form-field 
                        name="warning_field" 
                        label="Warning Field" 
                        warning="This email domain is not commonly used"
                    >
                        <x-text-input 
                            id="warning_field"
                            name="warning_field"
                            value="user@uncommon-domain.xyz"
                            class="form-input--warning"
                        />
                    </x-form-field>

                    {{-- Info State --}}
                    <x-form-field 
                        name="info_field" 
                        label="Info Field" 
                        info="This field is optional but recommended"
                    >
                        <x-text-input 
                            id="info_field"
                            name="info_field"
                            placeholder="Optional field"
                        />
                    </x-form-field>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Alpine.js Integration --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('enhancedForms', () => ({
        init() {
            // Initialize form validator if available
            if (window.formValidator) {
                console.log('Form validator initialized');
            }
        }
    }));
});
</script>
@endsection
