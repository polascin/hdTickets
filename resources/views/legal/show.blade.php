@extends('legal.layout')

@php
    $documentTitles = [
        'terms_of_service' => 'Terms of Service',
        'privacy_policy' => 'Privacy Policy', 
        'disclaimer' => 'Service Disclaimer',
        'gdpr_compliance' => 'GDPR Compliance',
        'data_processing_agreement' => 'Data Processing Agreement',
        'cookie_policy' => 'Cookie Policy',
        'acceptable_use_policy' => 'Acceptable Use Policy',
        'legal_notices' => 'Legal Notices'
    ];
    
    $documentDescriptions = [
        'terms_of_service' => 'Comprehensive terms of service for HD Tickets professional sports ticket monitoring platform, including subscription terms, user responsibilities, and service limitations.',
        'privacy_policy' => 'Detailed privacy policy explaining how HD Tickets collects, processes, and protects personal data with full GDPR compliance and user rights protection.',
        'disclaimer' => 'Important service disclaimers for HD Tickets including "as-is" service provision, warranty limitations, and no money-back guarantee policy.',
        'gdpr_compliance' => 'Complete GDPR compliance documentation detailing data protection measures, user rights, legal basis for processing, and EU data protection compliance.',
        'data_processing_agreement' => 'Comprehensive data processing agreement outlining how personal data is collected, stored, processed, and protected within the HD Tickets platform.',
        'cookie_policy' => 'Information about cookies and tracking technologies used by HD Tickets to enhance user experience on our sports monitoring platform.',
        'acceptable_use_policy' => 'Guidelines for acceptable use of HD Tickets services including prohibited activities and user conduct expectations.',
        'legal_notices' => 'Important legal notices, copyright information, and additional legal requirements for HD Tickets professional sports monitoring platform.'
    ];
    
    $title = $documentTitles[$document->type] ?? 'Legal Document';
    $description = $documentDescriptions[$document->type] ?? 'Legal document for HD Tickets professional sports ticket monitoring platform.';
@endphp

@section('title', $title)
@section('document_title', $title)
@section('document_subtitle', 'HD Tickets - Professional Sports Ticket Monitoring Platform')

@section('meta_description', $description)
@section('meta_keywords', 'HD Tickets, ' . strtolower($title) . ', sports ticket monitoring, legal compliance, GDPR, data protection, professional sports platform')

@section('og_title', $title . ' - HD Tickets Legal')
@section('og_description', $description)
@section('twitter_title', $title . ' - HD Tickets')
@section('twitter_description', $description)

@if($document->effective_date)
    @section('modified_time', $document->effective_date->toISOString())
@endif

@section('content')
<div class="prose prose-lg max-w-none">
    @if($document->summary)
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-8 not-prose">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">
                        Document Summary
                    </h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>{{ $document->summary }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="legal-document-content">
        {!! $document->content !!}
    </div>

    @if($document->type === 'disclaimer')
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-8 not-prose">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Important Notice
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p><strong>Service provided "as-is" with no warranty or money-back guarantee.</strong> By using HD Tickets, you acknowledge and agree to these terms and limitations.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($document->type === 'gdpr_compliance')
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mt-8 not-prose">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">
                        GDPR Rights
                    </h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p>You have the right to access, rectify, port, erase your personal data, and object to processing. Contact us to exercise these rights.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Document Actions -->
    <div class="mt-8 pt-6 border-t border-gray-200 not-prose">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500">
                @if($document->effective_date)
                    Last updated: <time datetime="{{ $document->effective_date->toISOString() }}">{{ $document->effective_date->format('F j, Y') }}</time>
                @endif
                @if($document->version)
                    | Version {{ $document->version }}
                @endif
            </div>
            <div class="flex space-x-4">
                <button onclick="window.print()" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print
                </button>
                <a href="?download=pdf" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Contact Information for Legal Queries -->
    @if(in_array($document->type, ['terms_of_service', 'privacy_policy', 'gdpr_compliance']))
        <div class="mt-8 bg-gray-50 rounded-lg p-6 not-prose">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Questions About This Document?</h3>
            <p class="text-sm text-gray-600 mb-4">
                If you have questions about this {{ strtolower($title) }} or need clarification on any provisions, please contact us.
            </p>
            <div class="flex space-x-4">
                <a href="{{ route('register.public') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                    Register for HD Tickets
                </a>
                <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 transition-colors">
                    Back to Homepage
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Schema.org structured data for legal documents -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "{{ $title }}",
    "description": "{{ $description }}",
    "author": {
        "@type": "Organization",
        "name": "HD Tickets"
    },
    "publisher": {
        "@type": "Organization",
        "name": "HD Tickets",
        "logo": {
            "@type": "ImageObject",
            "url": "{{ asset('assets/images/hdTicketsLogo.png') }}"
        }
    },
    "datePublished": "{{ $document->effective_date ? $document->effective_date->toISOString() : now()->toISOString() }}",
    "dateModified": "{{ $document->effective_date ? $document->effective_date->toISOString() : now()->toISOString() }}",
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{ url()->current() }}"
    },
    "articleSection": "Legal Documents",
    "keywords": "{{ 'HD Tickets, ' . strtolower($title) . ', sports ticket monitoring, legal compliance' }}",
    "inLanguage": "en-US",
    "isPartOf": {
        "@type": "WebSite",
        "name": "HD Tickets",
        "url": "{{ url('/') }}"
    }
}
</script>
@endsection
