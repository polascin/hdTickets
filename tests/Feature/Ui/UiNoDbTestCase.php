<?php

namespace Tests\Feature\Ui;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;
use Illuminate\Foundation\Testing\WithFaker;

abstract class UiNoDbTestCase extends BaseTestCase
{
    use CreatesApplication;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Keep environment lightweight; no DB refresh here
        config(['app.debug' => true]);
        config(['queue.default' => 'sync']);
        config(['mail.default' => 'array']);
    }
}
