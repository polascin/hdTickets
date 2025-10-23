<?php declare(strict_types=1);

namespace Tests\Feature\Ui;

use PHPUnit\Framework\Attributes\Test;

class SkipLinkRemovedTest extends UiNoDbTestCase
{
    #[Test]
    public function legal_index_view_does_not_render_skip_link(): void
    {
        $html = view('legal.index')->render();
        $this->assertStringNotContainsString('Skip to main content', $html);
    }

    #[Test]
    public function legal_show_view_does_not_render_skip_link_for_terms(): void
    {
        $doc = (object) [
            'type'           => 'terms_of_service',
            'summary'        => NULL,
            'content'        => '<p>Sample terms content</p>',
            'effective_date' => now(),
            'version'        => '1.0',
        ];
        $html = view('legal.show', ['document' => $doc])->render();
        $this->assertStringNotContainsString('Skip to main content', $html);
    }

    #[Test]
    public function legal_show_view_does_not_render_skip_link_for_privacy(): void
    {
        $doc = (object) [
            'type'           => 'privacy_policy',
            'summary'        => NULL,
            'content'        => '<p>Sample privacy content</p>',
            'effective_date' => now(),
            'version'        => '1.0',
        ];
        $html = view('legal.show', ['document' => $doc])->render();
        $this->assertStringNotContainsString('Skip to main content', $html);
    }
}
