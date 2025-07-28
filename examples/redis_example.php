<?php
/**
 * Redis Alternative - Using Predis (Pure PHP Redis Client)
 * 
 * Predis is a flexible and feature-complete Redis client for PHP that provides
 * full compatibility with Redis without requiring the native ext-redis extension.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Predis\Client;

try {
    // Create Predis client instance
    $redis = new Client([
        'scheme' => 'tcp',
        'host'   => '127.0.0.1',
        'port'   => 6379,
    ]);

    // Test basic operations
    echo "=== PREDIS REDIS CLIENT EXAMPLE ===\n";
    
    // Set and get string values
    $redis->set('user:1:name', 'John Doe');
    $redis->set('user:1:email', 'john@example.com');
    
    echo "Name: " . $redis->get('user:1:name') . "\n";
    echo "Email: " . $redis->get('user:1:email') . "\n";
    
    // Working with lists
    $redis->lpush('tasks', 'Task 1', 'Task 2', 'Task 3');
    $tasks = $redis->lrange('tasks', 0, -1);
    echo "Tasks: " . implode(', ', $tasks) . "\n";
    
    // Working with hashes
    $redis->hmset('user:2', [
        'name' => 'Jane Smith',
        'age' => 28,
        'city' => 'New York'
    ]);
    
    $user = $redis->hgetall('user:2');
    echo "User 2: " . json_encode($user) . "\n";
    
    // Working with sets
    $redis->sadd('skills', 'PHP', 'JavaScript', 'Python', 'Redis');
    $skills = $redis->smembers('skills');
    echo "Skills: " . implode(', ', $skills) . "\n";
    
    // Set expiration
    $redis->setex('session:abc123', 3600, 'session_data');
    $ttl = $redis->ttl('session:abc123');
    echo "Session TTL: {$ttl} seconds\n";
    
    echo "✅ Predis Redis operations completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Redis connection failed: " . $e->getMessage() . "\n";
    echo "Make sure Redis server is running on localhost:6379\n";
}
