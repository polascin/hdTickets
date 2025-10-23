<?php declare(strict_types=1);

/**
 * Migrate @test annotations to #[Test] attributes
 * 
 * This script finds all files with @test annotations and replaces them
 * with our custom #[Test] attributes from Hdtickets\Test\Attributes namespace.
 */

function migrateTestAnnotations(string $testsDir): void 
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($testsDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $migratedFiles = 0;
    $migratedMethods = 0;

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $filePath = $file->getRealPath();
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // Skip if already has Test import or no @test annotations
        if (strpos($content, '@test') === false) {
            continue;
        }

        // Add use statement if not present
        if (strpos($content, 'use Hdtickets\\Test\\Attributes\\Test;') === false) {
            // Find the namespace line and add the import after existing use statements
            if (preg_match('/^namespace\s+[^;]+;\s*$/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
                $namespaceEnd = $matches[0][1] + strlen($matches[0][0]);
                
                // Find where to insert the use statement
                $insertPos = $namespaceEnd;
                if (preg_match('/^use\s+[^;]+;\s*$/m', $content, $useMatches, PREG_OFFSET_CAPTURE, $namespaceEnd)) {
                    // Find the last use statement
                    $allMatches = [];
                    preg_match_all('/^use\s+[^;]+;\s*$/m', $content, $allMatches, PREG_OFFSET_CAPTURE, $namespaceEnd);
                    if (!empty($allMatches[0])) {
                        $lastUse = end($allMatches[0]);
                        $insertPos = $lastUse[1] + strlen($lastUse[0]);
                    }
                } else {
                    // No existing use statements, add after namespace
                    $insertPos = $namespaceEnd;
                }
                
                $content = substr_replace(
                    $content, 
                    "\nuse Hdtickets\\Test\\Attributes\\Test;", 
                    $insertPos, 
                    0
                );
            }
        }

        // Replace /** @test */ with #[Test]
        $content = preg_replace(
            '/\s*\/\*\*\s*@test\s*\*\/\s*\n/m',
            "\n    #[Test]\n",
            $content,
            -1,
            $count1
        );

        // Replace * @test in multi-line doc blocks
        $content = preg_replace(
            '/(\s*)\/\*\*\s*\n\s*\*\s*@test\s*\n\s*\*\/\s*\n/m',
            "$1#[Test]\n",
            $content,
            -1,
            $count2
        );
        
        // Handle more complex docblocks with @test
        $content = preg_replace(
            '/(\s*)\/\*\*\s*\n(\s*\*[^\n]*\n)*?\s*\*\s*@test\s*\n(\s*\*[^\n]*\n)*?\s*\*\/\s*\n/m',
            "$1#[Test]\n",
            $content,
            -1,
            $count3
        );

        $totalCount = $count1 + $count2 + $count3;
        
        if ($totalCount > 0) {
            file_put_contents($filePath, $content);
            $migratedFiles++;
            $migratedMethods += $totalCount;
            echo "Migrated {$filePath}: {$totalCount} @test annotations\n";
        }
    }

    echo "\nMigration complete!\n";
    echo "Files processed: {$migratedFiles}\n";
    echo "Test methods migrated: {$migratedMethods}\n";
}

// Run the migration
$testsDir = __DIR__ . '/../tests';
migrateTestAnnotations($testsDir);