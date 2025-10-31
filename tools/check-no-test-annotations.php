<?php declare(strict_types=1);

/**
 * Check for forbidden PHPUnit annotations in test files
 *
 * This script ensures that new test code uses attributes instead of
 * deprecated docblock annotations.
 */
function checkForForbiddenAnnotations(string $testsDir): int
{
    $forbiddenPatterns = [
        '@test'                        => 'Use #[Test] attribute instead',
        '@dataProvider'                => 'Use #[DataProvider(\'methodName\')] attribute instead',
        '@depends'                     => 'Use #[Depends(\'testMethod\')] attribute instead',
        '@group'                       => 'Use #[Group(\'groupName\')] attribute instead',
        '@covers'                      => 'Use #[CoversClass(Class::class)] attribute instead',
        '@uses'                        => 'Use #[UsesClass(Class::class)] attribute instead',
        '@requires'                    => 'Use #[Requires*] attributes instead',
        '@runInSeparateProcess'        => 'Use #[RunInSeparateProcess] attribute instead',
        '@runTestsInSeparateProcesses' => 'Use #[RunTestsInSeparateProcesses] attribute instead',
        '@backupGlobals'               => 'Use #[BackupGlobals] attribute instead',
        '@backupStaticAttributes'      => 'Use #[BackupStaticProperties] attribute instead',
        '@preserveGlobalState'         => 'Use #[PreserveGlobalState] attribute instead',
        '@testdox'                     => 'Use #[TestDox(\'text\')] attribute instead',
        '@small'                       => 'Use #[Small] attribute instead',
        '@medium'                      => 'Use #[Medium] attribute instead',
        '@large'                       => 'Use #[Large] attribute instead',
        '@doesNotPerformAssertions'    => 'Use #[DoesNotPerformAssertions] attribute instead',
    ];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($testsDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $violations = 0;
    $violationFiles = [];

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $filePath = $file->getRealPath();
        $content = file_get_contents($filePath);

        foreach ($forbiddenPatterns as $pattern => $message) {
            if (strpos($content, $pattern) !== FALSE) {
                // Exclude false positives like email addresses
                if ($pattern === '@test' && strpos($content, 'email') !== FALSE) {
                    // Check if it's actually an email address
                    $lines = explode("\n", $content);
                    $isEmail = FALSE;

                    foreach ($lines as $lineNum => $line) {
                        if (strpos($line, $pattern) !== FALSE && strpos($line, '@test.com') !== FALSE) {
                            $isEmail = TRUE;

                            break;
                        }
                    }

                    if ($isEmail) {
                        continue;
                    }
                }

                $violations++;
                if (!in_array($filePath, $violationFiles)) {
                    $violationFiles[] = $filePath;
                }

                // Find line numbers
                $lines = explode("\n", $content);
                foreach ($lines as $lineNum => $line) {
                    if (strpos($line, $pattern) !== FALSE) {
                        echo "VIOLATION: {$filePath}:" . ($lineNum + 1) . " - Found {$pattern}. {$message}\n";
                    }
                }
            }
        }
    }

    if ($violations > 0) {
        echo "\n❌ Found {$violations} annotation violations in " . count($violationFiles) . " files.\n";
        echo "Please migrate these annotations to attributes.\n";

        return 1;
    } else {
        echo "✅ No forbidden annotations found. All tests are using attributes correctly!\n";

        return 0;
    }
}

// Run the check
$testsDir = __DIR__ . '/../tests';
exit(checkForForbiddenAnnotations($testsDir));
