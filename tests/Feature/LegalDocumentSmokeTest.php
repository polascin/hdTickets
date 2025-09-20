<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LegalDocument;
use Tests\TestCase;

class LegalDocumentSmokeTest extends TestCase
{
    /**
     * Test that all legal document routes return 200 OK
     */
    public function test_all_legal_routes_return_200(): void
    {
        $routes = [
            'legal.terms-of-service',
            'legal.disclaimer',
            'legal.privacy-policy',
            'legal.gdpr-compliance',
            'legal.data-processing-agreement',
            'legal.cookie-policy',
            'legal.acceptable-use-policy',
            'legal.legal-notices',
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
            $response->assertSee('HD Tickets');
            $response->assertSee('Legal'); // Should contain legal-related content
        }
    }

    /**
     * Test that the legal index page works
     */
    public function test_legal_index_page_works(): void
    {
        $response = $this->get(route('legal.index'));
        $response->assertStatus(200);
        $response->assertSee('Legal Documents');
        $response->assertSee('Terms of Service');
        $response->assertSee('Privacy Policy');
    }

    /**
     * Test that all legal documents exist in database
     */
    public function test_all_legal_documents_exist_in_database(): void
    {
        $expectedTypes = [
            LegalDocument::TYPE_TERMS_OF_SERVICE,
            LegalDocument::TYPE_DISCLAIMER,
            LegalDocument::TYPE_PRIVACY_POLICY,
            LegalDocument::TYPE_GDPR_COMPLIANCE,
            LegalDocument::TYPE_DATA_PROCESSING_AGREEMENT,
            LegalDocument::TYPE_COOKIE_POLICY,
            LegalDocument::TYPE_ACCEPTABLE_USE_POLICY,
            LegalDocument::TYPE_LEGAL_NOTICES,
        ];

        $this->assertCount(8, $expectedTypes);

        foreach ($expectedTypes as $type) {
            $document = LegalDocument::getActive($type);
            $this->assertNotNull($document, "Legal document of type {$type} should exist");
            $this->assertTrue($document->is_active, "Legal document {$type} should be active");
            $this->assertNotEmpty($document->content, "Legal document {$type} should have content");
        }
    }

    /**
     * Test that legal document content is properly rendered
     */
    public function test_legal_document_content_is_rendered(): void
    {
        // Test terms of service specifically
        $response = $this->get(route('legal.terms-of-service'));
        $response->assertStatus(200);
        $response->assertSee('Terms of Service');
        $response->assertSee('HD Tickets');
        $response->assertSee('Service provided');

        // Test disclaimer specifically
        $response = $this->get(route('legal.disclaimer'));
        $response->assertStatus(200);
        $response->assertSee('Disclaimer');
        $response->assertSee('AS IS');
        $response->assertSee('warranty');
    }
}
