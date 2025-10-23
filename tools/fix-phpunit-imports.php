<?php declare(strict_types=1);

/**
 * Fix imports to use PHPUnit\Framework\Attributes instead of custom attributes
 * 
 * Since PHPUnit 11.x already supports attributes natively and the project
 * was already using them, we'll continue with PHPUnit's standard attributes.
 */

function fixPhpunitImports(string $testsDir): void 
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($testsDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $fixedFiles = 0;

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $filePath = $file->getRealPath();
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // Replace custom imports with PHPUnit imports
        $content = str_replace(
            'use Hdtickets\\Test\\Attributes\\Test;',
            'use PHPUnit\\Framework\\Attributes\\Test;',
            $content
        );
        
        $content = str_replace(
            'use Hdtickets\\Test\\Attributes\\DataProvider;',
            'use PHPUnit\\Framework\\Attributes\\DataProvider;',
            $content
        );

        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $fixedFiles++;
            echo "Fixed imports in {$filePath}\n";
        }
    }

    echo "\nImport fix complete!\n";
    echo "Files processed: {$fixedFiles}\n";
}

// Run the fix
$testsDir = __DIR__ . '/../tests';
fixPhpunitImports($testsDir);