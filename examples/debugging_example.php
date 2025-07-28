<?php
/**
 * Debugging Alternatives - Using Symfony VarDumper and Laravel Telescope
 * 
 * These tools provide excellent debugging capabilities without requiring
 * the native Xdebug extension, which may not be available for PHP 8.4 yet.
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== DEBUGGING ALTERNATIVES EXAMPLE ===\n";

// 1. Symfony VarDumper - Better than var_dump()
echo "\n1. SYMFONY VAR-DUMPER EXAMPLE:\n";
echo "-----------------------------------\n";

use Symfony\Component\VarDumper\VarDumper;

$sampleData = [
    'user' => [
        'id' => 123,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'roles' => ['admin', 'user'],
        'preferences' => [
            'theme' => 'dark',
            'notifications' => true,
            'language' => 'en'
        ]
    ],
    'timestamp' => new DateTime(),
    'session' => session_id() ?: 'no-session'
];

// Using dump() function (more readable than var_dump)
echo "Sample data structure:\n";
dump($sampleData);

// 2. Custom debugging functions
echo "\n2. CUSTOM DEBUGGING FUNCTIONS:\n";
echo "--------------------------------\n";

function debug_trace($message = '', $data = null) {
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
    $caller = $trace[1] ?? $trace[0];
    
    $output = sprintf(
        "[DEBUG] %s | File: %s:%d | Function: %s\n",
        date('Y-m-d H:i:s'),
        basename($caller['file'] ?? 'unknown'),
        $caller['line'] ?? 0,
        $caller['function'] ?? 'main'
    );
    
    if ($message) {
        $output .= "Message: $message\n";
    }
    
    if ($data !== null) {
        $output .= "Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
    
    echo $output . "\n";
}

// Example usage
debug_trace('User authentication check', ['user_id' => 123, 'authenticated' => true]);

function performance_timer($label = 'Operation') {
    static $timers = [];
    
    if (!isset($timers[$label])) {
        $timers[$label] = microtime(true);
        echo "[TIMER] Started: $label\n";
    } else {
        $elapsed = microtime(true) - $timers[$label];
        echo "[TIMER] Completed: $label in " . number_format($elapsed * 1000, 2) . "ms\n";
        unset($timers[$label]);
    }
}

// Example performance measurement
performance_timer('Database Query');
// Simulate some work
usleep(50000); // 50ms
performance_timer('Database Query');

// 3. Memory usage tracking
echo "\n3. MEMORY USAGE TRACKING:\n";
echo "--------------------------\n";

function memory_usage($label = '') {
    $current = memory_get_usage(true);
    $peak = memory_get_peak_usage(true);
    
    echo sprintf(
        "[MEMORY] %s Current: %s | Peak: %s\n",
        $label ? "$label - " : '',
        formatBytes($current),
        formatBytes($peak)
    );
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

memory_usage('Start');

// Simulate memory usage
$largeArray = array_fill(0, 10000, 'sample data');
memory_usage('After creating large array');

unset($largeArray);
memory_usage('After cleanup');

// 4. Error handling and logging
echo "\n4. ADVANCED ERROR HANDLING:\n";
echo "----------------------------\n";

class DebugLogger {
    private $logFile;
    
    public function __construct($logFile = 'debug.log') {
        $this->logFile = $logFile;
    }
    
    public function log($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = $context ? ' | Context: ' . json_encode($context) : '';
        
        $logEntry = "[$timestamp] $level: $message$contextStr\n";
        
        // Write to file
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Also output to console for this example
        echo $logEntry;
    }
    
    public function debug($message, $context = []) {
        $this->log('DEBUG', $message, $context);
    }
    
    public function info($message, $context = []) {
        $this->log('INFO', $message, $context);
    }
    
    public function warning($message, $context = []) {
        $this->log('WARNING', $message, $context);
    }
    
    public function error($message, $context = []) {
        $this->log('ERROR', $message, $context);
    }
}

$logger = new DebugLogger('examples/debug.log');
$logger->info('Application started');
$logger->debug('User data loaded', ['user_id' => 123]);
$logger->warning('Deprecated function used', ['function' => 'old_function()']);

echo "\n5. STACK TRACE WITHOUT XDEBUG:\n";
echo "-------------------------------\n";

function showStackTrace() {
    $trace = debug_backtrace();
    echo "Stack trace:\n";
    
    foreach ($trace as $i => $frame) {
        $file = isset($frame['file']) ? basename($frame['file']) : 'unknown';
        $line = $frame['line'] ?? 'unknown';
        $function = $frame['function'] ?? 'main';
        $class = isset($frame['class']) ? $frame['class'] . '::' : '';
        
        echo sprintf("#%d %s%s() called at %s:%s\n", $i, $class, $function, $file, $line);
    }
}

function level3Function() {
    showStackTrace();
}

function level2Function() {
    level3Function();
}

function level1Function() {
    level2Function();
}

level1Function();

echo "\n✅ Debugging alternatives demonstration completed!\n";
echo "\n💡 DEBUGGING TOOLS SUMMARY:\n";
echo "- Symfony VarDumper: Better variable inspection\n";
echo "- Custom debug functions: Tailored debugging info\n";
echo "- Performance timers: Measure execution time\n";
echo "- Memory tracking: Monitor memory usage\n";
echo "- Advanced logging: Structured error reporting\n";
echo "- Stack traces: Function call hierarchy\n";
echo "- Laravel Telescope: Web-based debugging dashboard (if installed)\n";
