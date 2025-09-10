<?php declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class SimpleSanityTest extends TestCase
{
    #[Test]
    public function test_it_runs(): void
    {
        $this->assertTrue(true);
    }
}
