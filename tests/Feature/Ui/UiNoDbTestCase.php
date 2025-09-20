<?php declare(strict_types=1);

namespace Tests\Feature\Ui;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\CreatesApplication;

abstract class UiNoDbTestCase extends BaseTestCase
{
    use CreatesApplication;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Keep environment lightweight; no DB refresh here
        config(['app.debug' => TRUE]);
        config(['queue.default' => 'sync']);
        config(['mail.default' => 'array']);
    }
}
