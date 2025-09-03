<?php

require_once 'vendor/autoload.php';

echo "=== MODERNIZATION STATUS ANALYSIS ===\n\n";

// Get all plugin files
$pluginFiles = glob('app/Services/Scraping/Plugins/*Plugin.php');

$modernizedPlugins = [];
$legacyPlugins = [];
$newPlugins = [];
$errors = [];

echo "Analyzing " . count($pluginFiles) . " plugin files:\n\n";

foreach ($pluginFiles as $file) {
    $className = basename($file, '.php');
    $pluginName = strtolower(str_replace('Plugin', '', $className));
    
    $content = file_get_contents($file);
    
    if (empty(trim($content))) {
        $errors[] = "$pluginName: Empty file";
        continue;
    }
    
    // Check if uses BaseScraperPlugin
    if (strpos($content, 'extends BaseScraperPlugin') !== false) {
        $modernizedPlugins[] = $pluginName;
    } elseif (strpos($content, 'implements ScraperPluginInterface') !== false) {
        $legacyPlugins[] = $pluginName;
    } else {
        $errors[] = "$pluginName: Unknown architecture";
    }
}

// Categorize plugins
$footballClubs = [
    'manchesterunited', 'manchester_united', 'liverpoolfc', 'arsenalfc', 'chelseafc', 
    'tottenham', 'manchester_city', 'newcastle_united', 'real_madrid', 'realmadrid', 
    'barcelona', 'atletico_madrid', 'bayern_munich', 'borussia_dortmund', 'juventus', 
    'ac_milan', 'intermilan', 'psg', 'celticfc'
];

$majorPlatforms = [
    'ticketmaster', 'stubhub', 'seatgeek', 'viagogo', 'tickpick', 
    'eventbrite', 'bandsintown', 'axs'
];

$ukVenues = [
    'wimbledon', 'wembleystadium', 'twickenham', 'lordscricket', 
    'englandcricket', 'silverstonef1'
];

$ukPlatforms = [
    'ticketekuk', 'seeticketsuk', 'livenationuk', 'gigantic', 'skiddle', 
    'stargreen', 'ticketswap'
];

$europeanPlatforms = [
    'entradiumspain', 'eventim', 'stadionweltgermany', 'ticketoneitaly', 'ticketone'
];

echo "=== MODERNIZATION STATUS BY CATEGORY ===\n\n";

function analyzeCategory($name, $plugins, $modernized, $legacy) {
    echo "## $name\n";
    $total = count($plugins);
    $modernizedCount = count(array_intersect($plugins, $modernized));
    $legacyCount = count(array_intersect($plugins, $legacy));
    $missingCount = $total - $modernizedCount - $legacyCount;
    
    echo "  Total: $total | Modernized: $modernizedCount | Legacy: $legacyCount | Missing: $missingCount\n";
    
    foreach ($plugins as $plugin) {
        if (in_array($plugin, $modernized)) {
            echo "  ✅ $plugin - MODERNIZED\n";
        } elseif (in_array($plugin, $legacy)) {
            echo "  ❌ $plugin - LEGACY\n";
        } else {
            echo "  ⚠️  $plugin - NOT FOUND\n";
        }
    }
    
    $percentage = $total > 0 ? round(($modernizedCount / $total) * 100, 1) : 0;
    echo "  Coverage: $percentage%\n\n";
    
    return $percentage;
}

$footballCoverage = analyzeCategory("FOOTBALL CLUBS", $footballClubs, $modernizedPlugins, $legacyPlugins);
$majorCoverage = analyzeCategory("MAJOR PLATFORMS", $majorPlatforms, $modernizedPlugins, $legacyPlugins);
$venueCoverage = analyzeCategory("UK SPORTS VENUES", $ukVenues, $modernizedPlugins, $legacyPlugins);
$ukPlatformCoverage = analyzeCategory("UK PLATFORMS", $ukPlatforms, $modernizedPlugins, $legacyPlugins);
$europeanCoverage = analyzeCategory("EUROPEAN PLATFORMS", $europeanPlatforms, $modernizedPlugins, $legacyPlugins);

// Overall analysis
echo "=== OVERALL MODERNIZATION STATUS ===\n\n";
echo "Total Plugins: " . count($pluginFiles) . "\n";
echo "Modernized: " . count($modernizedPlugins) . " (" . round((count($modernizedPlugins) / count($pluginFiles)) * 100, 1) . "%)\n";
echo "Legacy: " . count($legacyPlugins) . " (" . round((count($legacyPlugins) / count($pluginFiles)) * 100, 1) . "%)\n";
echo "Errors: " . count($errors) . "\n\n";

if (!empty($errors)) {
    echo "=== ERRORS/ISSUES ===\n";
    foreach ($errors as $error) {
        echo "⚠️  $error\n";
    }
    echo "\n";
}

echo "=== PRIORITY MODERNIZATION LIST ===\n\n";

// Check specific high-priority plugins that should be modernized
$highPriorityLegacy = array_intersect([
    'wimbledon', 'wembleystadium', 'celticfc', 'ticketmaster', 'stubhub'
], $legacyPlugins);

$mediumPriorityLegacy = array_intersect([
    'twickenham', 'lordscricket', 'silverstonef1', 'englandcricket'
], $legacyPlugins);

$lowPriorityLegacy = array_intersect([
    'ticketekuk', 'seeticketsuk', 'livenationuk', 'gigantic', 'skiddle'
], $legacyPlugins);

if (!empty($highPriorityLegacy)) {
    echo "HIGH PRIORITY (Major venues/platforms):\n";
    foreach ($highPriorityLegacy as $plugin) {
        echo "  🔴 $plugin - Needs immediate modernization\n";
    }
    echo "\n";
}

if (!empty($mediumPriorityLegacy)) {
    echo "MEDIUM PRIORITY (Sports venues):\n";
    foreach ($mediumPriorityLegacy as $plugin) {
        echo "  🟡 $plugin - Should be modernized\n";
    }
    echo "\n";
}

if (!empty($lowPriorityLegacy)) {
    echo "LOW PRIORITY (Generic platforms):\n";
    foreach ($lowPriorityLegacy as $plugin) {
        echo "  🟢 $plugin - Can be modernized later\n";
    }
    echo "\n";
}

// Check for newly created plugins that might need review
$newlyCreated = ['seatgeek', 'viagogo', 'tickpick', 'eventbrite', 'bandsintown'];
$foundNew = array_intersect($newlyCreated, $modernizedPlugins);

if (!empty($foundNew)) {
    echo "=== NEWLY CREATED MODERN PLUGINS ===\n";
    foreach ($foundNew as $plugin) {
        echo "✅ $plugin - Recently created with modern architecture\n";
    }
    echo "\n";
}

echo "=== MODERNIZATION SUMMARY ===\n\n";
echo "🎯 Major Platforms: $majorCoverage% modernized\n";
echo "⚽ Football Clubs: $footballCoverage% modernized\n";
echo "🏟️  UK Sports Venues: $venueCoverage% modernized\n";
echo "🇬🇧 UK Platforms: $ukPlatformCoverage% modernized\n";
echo "🇪🇺 European Platforms: $europeanCoverage% modernized\n\n";

$overallModernization = round((count($modernizedPlugins) / count($pluginFiles)) * 100, 1);
echo "🔢 OVERALL MODERNIZATION: $overallModernization%\n\n";

if ($overallModernization >= 90) {
    echo "🎉 STATUS: EXCELLENT - Nearly all plugins modernized!\n";
} elseif ($overallModernization >= 75) {
    echo "✅ STATUS: GOOD - Most plugins modernized\n";
} elseif ($overallModernization >= 50) {
    echo "⚠️  STATUS: PARTIAL - Significant modernization needed\n";
} else {
    echo "🔴 STATUS: POOR - Major modernization required\n";
}