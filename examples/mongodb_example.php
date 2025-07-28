<?php
/**
 * MongoDB Alternative - Using MongoDB PHP Library
 * 
 * While this still requires the native ext-mongodb extension for full functionality,
 * we can demonstrate how to use MongoDB with PHP in a way that's ready when
 * the extension becomes available for PHP 8.4.
 * 
 * For now, this will show the structure and methods you'll use.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// This example shows the MongoDB usage pattern
// Note: This requires ext-mongodb extension which is not yet available for PHP 8.4

echo "=== MONGODB LIBRARY EXAMPLE ===\n";

try {
    // Example of how you would use MongoDB when the extension is available
    /*
    use MongoDB\Client;
    
    $client = new Client("mongodb://localhost:27017");
    $database = $client->selectDatabase('tickets_db');
    $collection = $database->selectCollection('events');
    
    // Insert a document
    $insertResult = $collection->insertOne([
        'title' => 'Sports Event',
        'date' => new MongoDB\BSON\UTCDateTime(),
        'venue' => 'Stadium',
        'capacity' => 50000,
        'tickets_sold' => 0
    ]);
    
    echo "Inserted document with ID: " . $insertResult->getInsertedId() . "\n";
    
    // Find documents
    $events = $collection->find(['capacity' => ['$gte' => 10000]]);
    foreach ($events as $event) {
        echo "Event: " . $event['title'] . " at " . $event['venue'] . "\n";
    }
    
    // Update a document
    $updateResult = $collection->updateOne(
        ['title' => 'Sports Event'],
        ['$inc' => ['tickets_sold' => 100]]
    );
    
    echo "Modified " . $updateResult->getModifiedCount() . " document(s)\n";
    */
    
    echo "📝 MongoDB usage pattern shown above.\n";
    echo "⚠️  MongoDB PHP library is installed but requires ext-mongodb extension.\n";
    echo "💡 For PHP 8.4, you'll need to wait for the extension to be compiled or use alternatives.\n";
    
    // Alternative: Use a JSON file-based approach for development
    echo "\n=== ALTERNATIVE: JSON FILE-BASED STORAGE ===\n";
    
    $dataFile = 'mongodb_alternative_data.json';
    
    // Load existing data or create empty array
    $data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
    
    // Add new event
    $newEvent = [
        'id' => uniqid(),
        'title' => 'Basketball Championship',
        'date' => date('Y-m-d H:i:s'),
        'venue' => 'Arena',
        'capacity' => 20000,
        'tickets_sold' => 150
    ];
    
    $data[] = $newEvent;
    
    // Save data
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
    
    echo "✅ Event added to JSON storage\n";
    echo "📁 Data stored in: {$dataFile}\n";
    
    // Display current events
    echo "\nCurrent events:\n";
    foreach ($data as $event) {
        echo "- {$event['title']} at {$event['venue']} (Capacity: {$event['capacity']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n💡 RECOMMENDATION: For production MongoDB usage with PHP 8.4:\n";
echo "   1. Wait for official ext-mongodb support for PHP 8.4\n";
echo "   2. Use PHP 8.3 where the extension is available\n";
echo "   3. Use alternative databases like MySQL/PostgreSQL for now\n";
echo "   4. Consider using MongoDB via REST API calls\n";
