@extends('legal.layout')

@section('title', 'Legal Documents')
@section('document_title', 'Legal Documents & Policies')
@section('document_subtitle', 'Comprehensive legal framework for HD Tickets professional sports ticket monitoring platform')

@section('meta_description', 'Complete legal documentation for HD Tickets professional sports ticket monitoring platform. Terms of service, privacy policy, GDPR compliance, disclaimers, and data protection agreements.')
@section('meta_keywords', 'HD Tickets legal documents, terms of service, privacy policy, GDPR compliance, sports ticket monitoring legal, data protection, legal policies')

@section('og_title', 'HD Tickets Legal Documents - Professional Sports Monitoring Platform')
@section('og_description', 'Complete legal documentation including terms, privacy policy, GDPR compliance, and data protection for HD Tickets professional sports ticket monitoring.')

@section('content')
<div class="prose prose-lg max-w-none">
    <p class="lead text-xl text-gray-600 mb-8">
        HD Tickets is committed to transparency, legal compliance, and protecting user rights. 
        Our comprehensive legal framework ensures GDPR compliance, data protection, and clear service terms.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 not-prose">
        <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <a href="{{ route('legal.terms-of-service') }}" class="hover:text-blue-600">
                            Terms of Service
                        </a>
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Comprehensive terms governing the use of HD Tickets professional sports monitoring platform, including subscription terms and service limitations.
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('legal.terms-of-service') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Read Terms →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <a href="{{ route('legal.privacy-policy') }}" class="hover:text-green-600">
                            Privacy Policy
                        </a>
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Detailed privacy policy explaining how we collect, use, and protect your personal data with full GDPR compliance and user rights.
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('legal.privacy-policy') }}" class="text-sm text-green-600 hover:text-green-800 font-medium">
                            Read Privacy Policy →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <a href="{{ route('legal.disclaimer') }}" class="hover:text-yellow-600">
                            Service Disclaimer
                        </a>
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Important disclaimers about service provision "as-is", warranty limitations, and no money-back guarantee policy.
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('legal.disclaimer') }}" class="text-sm text-yellow-600 hover:text-yellow-800 font-medium">
                            Read Disclaimer →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <a href="{{ route('legal.gdpr-compliance') }}" class="hover:text-purple-600">
                            GDPR Compliance
                        </a>
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Comprehensive GDPR compliance documentation detailing data protection measures, user rights, and legal basis for processing.
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('legal.gdpr-compliance') }}" class="text-sm text-purple-600 hover:text-purple-800 font-medium">
                            Read GDPR Info →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <a href="{{ route('legal.data-processing-agreement') }}" class="hover:text-red-600">
                            Data Processing Agreement
                        </a>
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Detailed agreement outlining how personal data is processed, stored, and protected within our sports monitoring platform.
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('legal.data-processing-agreement') }}" class="text-sm text-red-600 hover:text-red-800 font-medium">
                            Read Agreement →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 110 2h-1v11a3 3 0 01-3 3H6a3 3 0 01-3-3V6H2a1 1 0 110-2h4z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <a href="{{ route('legal.cookie-policy') }}" class="hover:text-indigo-600">
                            Cookie Policy
                        </a>
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Information about cookies and tracking technologies used to enhance your experience on our sports monitoring platform.
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('legal.cookie-policy') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            Read Cookie Policy →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-12 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">
                    Legal Framework Overview
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>
                        HD Tickets operates under a comprehensive legal framework designed to:
                    </p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li><strong>Ensure GDPR Compliance:</strong> Full compliance with EU data protection regulations</li>
                        <li><strong>Protect User Rights:</strong> Clear terms and transparent data processing</li>
                        <li><strong>Professional Standards:</strong> Industry-standard legal documentation</li>
                        <li><strong>Service Clarity:</strong> "As-is" service provision with clear limitations</li>
                        <li><strong>Data Security:</strong> Comprehensive data protection and user privacy</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 text-center">
        <p class="text-gray-600 mb-4">
            Questions about our legal documents or need clarification on any policies?
        </p>
        <div class="space-x-4">
            <a href="{{ route('register.public') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Register for HD Tickets
            </a>
            <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-md hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m0 0h4m0 0h3a1 1 0 001-1V10M9 21h6"/>
                </svg>
                Back to Homepage
            </a>
        </div>
    </div>
</div>
@endsection
