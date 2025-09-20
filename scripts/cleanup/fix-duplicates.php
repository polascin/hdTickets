<?php declare(strict_types=1);

$directories = ['app/Http/Controllers'];

foreach ($directories as $dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());

            // Fix duplicate return types like ): Type: Type
            $content = preg_replace('/\): ([^:]+): \1/', '): $1', $content);

            // Fix specific patterns
            $patterns = [
                '/\): \\\\Illuminate\\\\Contracts\\\\View\\\\View: \\\\Illuminate\\\\Contracts\\\\View\\\\View/' => '): \\Illuminate\\Contracts\\View\\View',
                '/\): \\\\Illuminate\\\\Http\\\\RedirectResponse: \\\\Illuminate\\\\Http\\\\RedirectResponse/'   => '): \\Illuminate\\Http\\RedirectResponse',
                '/\): \\\\Illuminate\\\\Http\\\\JsonResponse: \\\\Illuminate\\\\Http\\\\JsonResponse/'           => '): \\Illuminate\\Http\\JsonResponse',
                '/\): array: array/'                                                                             => '): array',
                '/\): string: string/'                                                                           => '): string',
                '/\): int: int/'                                                                                 => '): int',
                '/\): float: float/'                                                                             => '): float',
                '/\): bool: bool/'                                                                               => '): bool',
            ];

            foreach ($patterns as $pattern => $replacement) {
                $content = preg_replace($pattern, $replacement, $content);
            }

            file_put_contents($file->getPathname(), $content);
            echo "Fixed duplicates in {$file->getPathname()}\n";
        }
    }
}
