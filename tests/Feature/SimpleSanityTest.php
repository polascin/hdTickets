<?php declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SimpleSanityTest extends TestCase
{
    #[Test]
    public function test_it_runs(): void
    {
        $this->assertTrue(TRUE);
    }
}
