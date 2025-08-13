<?php
/**
 * Systematic Return Type Fixer
 * Addresses missing return types across the entire codebase
 */

class SystematicReturnTypeFixer {
    
    private array $returnTypePatterns = [
        // Controller methods
        'index' => 'Illuminate\Contracts\View\View',
        'create' => 'Illuminate\Contracts\View\View',
        'store' => 'Illuminate\Http\RedirectResponse',
        'show' => 'Illuminate\Contracts\View\View',
        'edit' => 'Illuminate\Contracts\View\View',
        'update' => 'Illuminate\Http\RedirectResponse',
        'destroy' => 'Illuminate\Http\RedirectResponse',
        
        // API Controller methods
        'getStats' => 'Illuminate\Http\JsonResponse',
        'getData' => 'Illuminate\Http\JsonResponse',
        'getMetrics' => 'Illuminate\Http\JsonResponse',
        'getAnalytics' => 'Illuminate\Http\JsonResponse',
        'getChart' => 'Illuminate\Http\JsonResponse',
        'search' => 'Illuminate\Http\JsonResponse',
        
        // Boolean methods
        'canAccess' => 'bool',
        'isActive' => 'bool',
        'isEnabled' => 'bool',
        'hasPermission' => 'bool',
        'exists' => 'bool',
        'isEmpty' => 'bool',
        'isValid' => 'bool',
        
        // String methods
        'getName' => 'string',
        'getTitle' => 'string',
        'getDescription' => 'string',
        'getStatus' => 'string',
        'getType' => 'string',
        'toString' => 'string',
        '__toString' => 'string',
        
        // Numeric methods
        'getCount' => 'int',
        'getTotal' => 'int',
        'getId' => 'int',
        'getPrice' => 'float',
        'getAmount' => 'float',
        'getRate' => 'float',
        'getAverage' => 'float',
        
        // Void methods
        'handle' => 'int', // Console commands
        'boot' => 'void',
        'register' => 'void',
        'setUp' => 'void',
        'tearDown' => 'void',
    ];
    
    private array $methodContextPatterns = [
        // Controller context
        '/Controllers\/.*\.php$/' => [
            'index' => 'Illuminate\Contracts\View\View',
            'store' => 'Illuminate\Http\RedirectResponse',
            'update' => 'Illuminate\Http\RedirectResponse',
        ],
        
        // API Controller context
        '/Controllers\/Api\/.*\.php$/' => [
            'index' => 'Illuminate\Http\JsonResponse',
            'store' => 'Illuminate\Http\JsonResponse',
            'update' => 'Illuminate\Http\JsonResponse',
            'destroy' => 'Illuminate\Http\JsonResponse',
        ],
        
        // Service context
        '/Services\/.*\.php$/' => [
            'process' => 'mixed',
            'execute' => 'mixed',
            'handle' => 'mixed',
        ],
        
        // Model context
        '/Models\/.*\.php$/' => [
            'scopeActive' => 'Illuminate\Database\Eloquent\Builder',
            'scopeInactive' => 'Illuminate\Database\Eloquent\Builder',
        ],
    ];
    
    public function fixFile(string $filePath): bool {
        if (!file_exists($filePath) || !str_ends_with($filePath, '.php')) {
            return false;
        }
        
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // Fix missing return types
        $content = $this->addMissingReturnTypes($content, $filePath);
        
        // Fix array return types
        $content = $this->fixArrayReturnTypes($content);
        
        // Fix PHPDoc issues
        $content = $this->fixPhpDocIssues($content);
        
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            return true;
        }
        
        return false;
    }
    
    private function addMissingReturnTypes(string $content, string $filePath): string {
        // Pattern to match methods without return types
        $pattern = '/^(\s*)((?:public|private|protected)\s+)(?:static\s+)?function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(([^)]*)\)\s*(?!:\s*\w)(\s*{)/m';
        
        return preg_replace_callback($pattern, function($matches) use ($filePath) {
            $indent = $matches[1];
            $visibility = $matches[2];
            $methodName = $matches[3];
            $parameters = $matches[4];
            $openBrace = $matches[5];
            
            // Skip magic methods and constructors
            if (in_array($methodName, ['__construct', '__destruct'])) {
                return $matches[0];
            }
            
            // Get return type based on method name and context
            $returnType = $this->inferReturnType($methodName, $filePath, $parameters);
            
            if ($returnType) {
                return $indent . $visibility . "function {$methodName}({$parameters}): {$returnType}" . $openBrace;
            }
            
            return $matches[0];
        }, $content);
    }
    
    private function inferReturnType(string $methodName, string $filePath, string $parameters): ?string {
        // Check context-specific patterns first
        foreach ($this->methodContextPatterns as $pathPattern => $methods) {
            if (preg_match($pathPattern, $filePath)) {
                if (isset($methods[$methodName])) {
                    return $methods[$methodName];
                }
            }
        }
        
        // Check general patterns
        if (isset($this->returnTypePatterns[$methodName])) {
            return $this->returnTypePatterns[$methodName];
        }
        
        // Pattern-based inference
        if (str_starts_with($methodName, 'get')) {
            if (str_contains($methodName, 'Count') || str_contains($methodName, 'Total') || str_contains($methodName, 'Id')) {
                return 'int';
            }
            if (str_contains($methodName, 'Price') || str_contains($methodName, 'Rate') || str_contains($methodName, 'Average')) {
                return 'float';
            }
            if (str_contains($methodName, 'Name') || str_contains($methodName, 'Title') || str_contains($methodName, 'Status')) {
                return 'string';
            }
            if (str_contains($methodName, 'Data') || str_contains($methodName, 'Stats') || str_contains($methodName, 'Chart')) {
                return str_contains($filePath, '/Api/') ? 'Illuminate\Http\JsonResponse' : 'array<string, mixed>';
            }
        }
        
        if (str_starts_with($methodName, 'is') || str_starts_with($methodName, 'has') || str_starts_with($methodName, 'can')) {
            return 'bool';
        }
        
        if (str_starts_with($methodName, 'set') || str_starts_with($methodName, 'update') || str_starts_with($methodName, 'delete')) {
            return 'void';
        }
        
        // Check if method has Request parameter (likely controller action)
        if (str_contains($parameters, 'Request') && str_contains($filePath, 'Controllers/')) {
            if (str_contains($filePath, '/Api/')) {
                return 'Illuminate\Http\JsonResponse';
            }
            return 'Illuminate\Http\RedirectResponse';
        }
        
        return null;
    }
    
    private function fixArrayReturnTypes(string $content): string {
        // Fix generic array return types in PHPDoc
        $content = preg_replace(
            '/(\*\s*@return\s+)array(\s*$)/m',
            '$1array<string, mixed>$2',
            $content
        );
        
        // Fix array parameters in PHPDoc
        $content = preg_replace(
            '/(\*\s*@param\s+)array(\s+\$[a-zA-Z0-9_]+)/m',
            '$1array<string, mixed>$2',
            $content
        );
        
        return $content;
    }
    
    private function fixPhpDocIssues(string $content): string {
        // Add missing PHPDoc blocks for methods without them
        $pattern = '/^(\s*)((?:public|private|protected)\s+)(?:static\s+)?function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(([^)]*)\)\s*:\s*([^{]+)(\s*{)/m';
        
        return preg_replace_callback($pattern, function($matches) {
            $indent = $matches[1];
            $visibility = $matches[2];
            $methodName = $matches[3];
            $parameters = $matches[4];
            $returnType = trim($matches[5]);
            $openBrace = $matches[6];
            
            // Check if PHPDoc already exists (look backwards)
            $beforeMethod = substr($matches[0], 0, strpos($matches[0], $visibility . 'function'));
            if (str_contains($beforeMethod, '/**') || str_contains($beforeMethod, '*/')) {
                return $matches[0]; // PHPDoc already exists
            }
            
            // Generate PHPDoc
            $phpDoc = $this->generatePhpDoc($methodName, $parameters, $returnType, $indent);
            
            return $phpDoc . $matches[0];
        }, $content);
    }
    
    private function generatePhpDoc(string $methodName, string $parameters, string $returnType, string $indent): string {
        $doc = $indent . "/**\n";
        $doc .= $indent . " * " . $this->generateMethodDescription($methodName) . "\n";
        
        // Add parameter documentation
        if (!empty(trim($parameters))) {
            $params = $this->parseParameters($parameters);
            foreach ($params as $param) {
                $doc .= $indent . " * @param {$param['type']} {$param['name']}\n";
            }
        }
        
        // Add return documentation
        if ($returnType !== 'void') {
            $doc .= $indent . " * @return {$returnType}\n";
        }
        
        $doc .= $indent . " */\n";
        
        return $doc;
    }
    
    private function generateMethodDescription(string $methodName): string {
        if (str_starts_with($methodName, 'get')) {
            return 'Get ' . strtolower(preg_replace('/([A-Z])/', ' $1', substr($methodName, 3)));
        }
        if (str_starts_with($methodName, 'set')) {
            return 'Set ' . strtolower(preg_replace('/([A-Z])/', ' $1', substr($methodName, 3)));
        }
        if (str_starts_with($methodName, 'is')) {
            return 'Check if ' . strtolower(preg_replace('/([A-Z])/', ' $1', substr($methodName, 2)));
        }
        if (str_starts_with($methodName, 'has')) {
            return 'Check if has ' . strtolower(preg_replace('/([A-Z])/', ' $1', substr($methodName, 3)));
        }
        if (str_starts_with($methodName, 'can')) {
            return 'Check if can ' . strtolower(preg_replace('/([A-Z])/', ' $1', substr($methodName, 3)));
        }
        
        return ucfirst($methodName);
    }
    
    private function parseParameters(string $parameters): array {
        $params = [];
        $parts = array_map('trim', explode(',', $parameters));
        
        foreach ($parts as $part) {
            if (empty($part)) continue;
            
            // Extract type and name
            if (preg_match('/(?:([a-zA-Z\\\\]+)\s+)?(\$[a-zA-Z0-9_]+)/', $part, $matches)) {
                $params[] = [
                    'type' => $matches[1] ?: 'mixed',
                    'name' => $matches[2]
                ];
            }
        }
        
        return $params;
    }
}

// Execute the fixer
$fixer = new SystematicReturnTypeFixer();

// Get all PHP files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__ . '/app'),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$fixedFiles = 0;
$processedFiles = 0;

echo "🔧 Starting systematic return type fixes...\n\n";

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }
    
    $filePath = $file->getPathname();
    $processedFiles++;
    
    if ($fixer->fixFile($filePath)) {
        $relativePath = str_replace(__DIR__ . '/', '', $filePath);
        echo "✅ Fixed: {$relativePath}\n";
        $fixedFiles++;
    }
}

echo "\n🎉 Return type fixes completed!\n";
echo "📊 Files processed: {$processedFiles}\n";
echo "✨ Files fixed: {$fixedFiles}\n\n";
