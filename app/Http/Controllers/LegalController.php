<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use Illuminate\View\View;

class LegalController extends Controller
{
    /**
     * Display list of all legal documents
     */
    public function index(): View
    {
        $documents = LegalDocument::where('is_active', TRUE)
            ->where('effective_date', '<=', now())
            ->orderBy('type')
            ->get()
            ->groupBy('type')
            ->map(fn ($docs) => $docs->first()); // Get latest version of each type

        return view('legal.index', ['documents' => $documents]);
    }

    /**
     * Display specific legal document
     */
    public function show(string $type): View
    {
        $document = LegalDocument::getActive($type);

        if (!$document instanceof LegalDocument) {
            abort(404, 'Legal document not found');
        }

        return view('legal.show', ['document' => $document]);
    }

    /**
     * Display Terms of Service
     */
    public function termsOfService(): View
    {
        return $this->show(LegalDocument::TYPE_TERMS_OF_SERVICE);
    }

    /**
     * Display Privacy Policy
     */
    public function privacyPolicy(): View
    {
        return $this->show(LegalDocument::TYPE_PRIVACY_POLICY);
    }

    /**
     * Display Disclaimer
     */
    public function disclaimer(): View
    {
        return $this->show(LegalDocument::TYPE_DISCLAIMER);
    }

    /**
     * Display GDPR Compliance
     */
    public function gdprCompliance(): View
    {
        return $this->show(LegalDocument::TYPE_GDPR_COMPLIANCE);
    }

    /**
     * Display Data Processing Agreement
     */
    public function dataProcessingAgreement(): View
    {
        return $this->show(LegalDocument::TYPE_DATA_PROCESSING_AGREEMENT);
    }

    /**
     * Display Cookie Policy
     */
    public function cookiePolicy(): View
    {
        return $this->show(LegalDocument::TYPE_COOKIE_POLICY);
    }

    /**
     * Display Acceptable Use Policy
     */
    public function acceptableUsePolicy(): View
    {
        return $this->show(LegalDocument::TYPE_ACCEPTABLE_USE_POLICY);
    }

    /**
     * Display Legal Notices
     */
    public function legalNotices(): View
    {
        return $this->show(LegalDocument::TYPE_LEGAL_NOTICES);
    }
}
