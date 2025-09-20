<?php
declare(strict_types=1);

// Automated conversion of legacy @test docblocks to PHPUnit 10/11/12 #[Test] attributes.
// Safe patterns handled:
// 1. Pure annotation blocks: /**\n * @test\n */ -> replaced fully by attribute
// 2. Mixed docblocks: retains docblock (minus @test line) and inserts attribute above method.

$root = dirname(__DIR__, 2) . '/tests';
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

$changed = 0;
$filesProcessed = 0;

foreach ($rii as $file) {
    if ($file->isDir()) {
        continue;
    }
    if ($file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getPathname();
    $code = file_get_contents($path);
    $original = $code;

    // Skip if already using attributes widely and no @test present
    if (strpos($code, '@test') === FALSE) {
        continue;
    }

    // Ensure attribute import exists (only if we'll modify)
    $needsImport = !str_contains($code, 'use PHPUnit\\Framework\\Attributes\\Test;');

    // 1. Pure annotation block replacement
    $code = preg_replace_callback(
        '/(?P<indent>^[ \t]*)\/\*\*\s*\n(?P=indent)\*\s*@test\s*\n(?P=indent)\*\/\s*\n(?P=indent)public function /m',
        function ($m) {
            return $m['indent'] . '#[Test]' . "\n" . $m['indent'] . 'public function ';
        },
        $code
    );

    // 2. Mixed docblocks containing @test somewhere: remove the line and insert attribute if not already present before method
    // Pattern: docblock with @test followed by public function ... (no existing attribute directly above)
    $code = preg_replace_callback(
        '/(?P<indent>^[ \t]*)\/\*\*(?:[^*]|\*(?!\/))*@test[^*]*\*\/(?:(?:\n|\r\n)(?P=indent)#[^\n]+)?\s*\n(?P=indent)public function /m',
        function ($m) {
            $block = $m[0];
            // If attribute already inserted, skip
            if (str_contains($block, '#[Test]')) {
                return $block;
            } // already done
            // Remove @test line(s)
            $block = preg_replace('/^([ \t]*\*[^\n]*@test[^\n]*\n)/m', '', $block);
            // Insert attribute after closing */ if not present
            $block = preg_replace('/(\*\/[ \t]*\n)/', '\\1' . $m['indent'] . "#[Test]\n", $block, 1);

            return $block;
        },
        $code
    );

    if ($needsImport && $code !== $original) {
        // Insert import after namespace declaration
        $code = preg_replace_callback('/^namespace[^;]+;\s*\n/m', function ($m) {
            return $m[0] . "use PHPUnit\\\\Framework\\\\Attributes\\\\Test;\n";
        }, $code, 1, $count);
        if ($count === 0) {
            // No namespace (rare) - add at top after <?php
            $code = preg_replace('/^<\?php\s*/', "<?php\nuse PHPUnit\\\\Framework\\\\Attributes\\\\Test;\n", $code, 1);
        }
    }

    if ($code !== $original) {
        file_put_contents($path, $code);
        $changed++;
    }
    $filesProcessed++;
}

echo "Processed: {$filesProcessed} files\nChanged: {$changed} files\n";
exit(0);
