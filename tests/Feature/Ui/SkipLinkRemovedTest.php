<?php

namespace Tests\Feature\Ui;

use Tests\Feature\Ui\UiNoDbTestCase;

class SkipLinkRemovedTest extends UiNoDbTestCase
{
    /** @test */
    public function legal_index_view_does_not_render_skip_link()
    {
        $html = view('legal.index')->render();
        $this->assertStringNotContainsString('Skip to main content', $html);
    }

    /** @test */
    public function legal_show_view_does_not_render_skip_link_for_terms()
    {
        $doc = (object) [
            'type' => 'terms_of_service',
            'summary' => null,
            'content' => '<p>Sample terms content</p>',
            'effective_date' => now(),
            'version' => '1.0',
        ];
        $html = view('legal.show', ['document' => $doc])->render();
        $this->assertStringNotContainsString('Skip to main content', $html);
    }

    /** @test */
    public function legal_show_view_does_not_render_skip_link_for_privacy()
    {
        $doc = (object) [
            'type' => 'privacy_policy',
            'summary' => null,
            'content' => '<p>Sample privacy content</p>',
            'effective_date' => now(),
            'version' => '1.0',
        ];
        $html = view('legal.show', ['document' => $doc])->render();
        $this->assertStringNotContainsString('Skip to main content', $html);
    }
}
