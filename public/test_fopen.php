<?php declare(strict_types=1);

namespace Test;

// Test if fopen works with namespace
try {
    $handle = fopen('php://memory', 'r+');
    echo "fopen works without backslash\n";
    fclose($handle);
} catch (Error $e) {
    echo 'fopen without backslash failed: ' . $e->getMessage() . "\n";
}

try {
    $handle = fopen('php://memory', 'r+');
    echo "\\fopen works with backslash\n";
    fclose($handle);
} catch (Error $e) {
    echo '\\fopen with backslash failed: ' . $e->getMessage() . "\n";
}
