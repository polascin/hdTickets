<?php

namespace Tests\Feature\Ui;

use Tests\TestCase;

class SkipLinkRemovedTest extends TestCase
{
    /** @test */
    public function home_page_does_not_render_skip_link()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('Skip to main content', false);
    }

    /** @test */
    public function terms_of_service_does_not_render_skip_link()
    {
        $response = $this->get(route('legal.terms-of-service'));
        $response->assertStatus(200);
        $response->assertDontSee('Skip to main content', false);
    }

    /** @test */
    public function privacy_policy_does_not_render_skip_link()
    {
        $response = $this->get(route('legal.privacy-policy'));
        $response->assertStatus(200);
        $response->assertDontSee('Skip to main content', false);
    }
}
