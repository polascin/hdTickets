<?php

require_once 'vendor/autoload.php';

// Get enabled plugins from config
$enabledPlugins = [
    'ticketmaster', 'stubhub', 'seatgeek', 'viagogo', 'tickpick', 'eventbrite', 
    'bandsintown', 'axs', 'manchester_united', 'wimbledon', 'liverpoolfc', 
    'wembleystadium', 'ticketekuk', 'arsenalfc', 'twickenham', 'lordscricket',
    'seeticketsuk', 'chelseafc', 'tottenham', 'englandcricket', 'silverstonef1',
    'celticfc', 'manchester_city', 'real_madrid', 'barcelona', 'atletico_madrid',
    'bayern_munich', 'borussia_dortmund', 'juventus', 'ac_milan', 'inter_milan',
    'psg', 'newcastle_united', 'eventim', 'fnac_spectacles', 'vivaticket', 'entradas'
];

// Get platforms from config
$platforms = [
    'ticketmaster', 'stubhub', 'viagogo', 'seatgeek', 'tickpick', 'eventbrite',
    'bandsintown', 'manchester_united', 'real_madrid', 'barcelona', 'atletico_madrid',
    'entradium_spain', 'bayern_munich', 'borussia_dortmund', 'stadionwelt_germany',
    'juventus', 'ac_milan', 'ticketone_italy'
];

// Get actual plugin files
$pluginFiles = glob('app/Services/Scraping/Plugins/*Plugin.php');
$implementedPlugins = [];

foreach ($pluginFiles as $file) {
    $className = basename($file, '.php');
    $pluginName = strtolower(str_replace('Plugin', '', $className));
    $implementedPlugins[] = $pluginName;
}

sort($implementedPlugins);

echo "=== PLATFORM IMPLEMENTATION ANALYSIS ===\n\n";

echo "Total Implementations: " . count($implementedPlugins) . "\n";
echo "Total Enabled: " . count($enabledPlugins) . "\n";
echo "Total Platform Config: " . count($platforms) . "\n\n";

echo "=== IMPLEMENTED PLUGINS ===\n";
foreach ($implementedPlugins as $plugin) {
    $status = in_array($plugin, $enabledPlugins) ? "[ENABLED]" : "[DISABLED]";
    $inConfig = in_array($plugin, $platforms) ? "[CONFIGURED]" : "[NOT CONFIGURED]";
    echo sprintf("%-25s %s %s\n", $plugin, $status, $inConfig);
}

echo "\n=== ENABLED BUT NOT IMPLEMENTED ===\n";
$missing = array_diff($enabledPlugins, $implementedPlugins);
foreach ($missing as $plugin) {
    echo "- " . $plugin . "\n";
}

echo "\n=== CONFIGURED BUT NOT IMPLEMENTED ===\n";
$configMissing = array_diff($platforms, $implementedPlugins);
foreach ($configMissing as $plugin) {
    echo "- " . $plugin . "\n";
}

echo "\n=== CATEGORIES ===\n";

// Categorize plugins
$categories = [
    'Major Platforms' => ['ticketmaster', 'stubhub', 'seatgeek', 'viagogo', 'tickpick', 'eventbrite', 'bandsintown', 'axs'],
    'UK Football' => ['manchester_united', 'liverpoolfc', 'arsenalfc', 'chelseafc', 'tottenham', 'manchester_city', 'newcastle_united'],
    'UK Sports/Events' => ['wimbledon', 'wembleystadium', 'twickenham', 'lordscricket', 'englandcricket', 'silverstonef1'],
    'UK Platforms' => ['ticketekuk', 'seeticketsuk', 'livenationuk', 'gigantic', 'skiddle', 'stargreen', 'ticketswap'],
    'Spanish Football' => ['real_madrid', 'realmadrid', 'barcelona', 'atletico_madrid'],
    'German Football' => ['bayern_munich', 'borussia_dortmund'],
    'Italian Football' => ['juventus', 'ac_milan', 'inter_milan'],
    'French Football' => ['psg'],
    'Other Scottish' => ['celticfc'],
    'European Platforms' => ['entradiumspain', 'eventim', 'ticketone', 'ticketoneitaly', 'stadionweltgermany'],
];

foreach ($categories as $category => $plugins) {
    echo "\n" . $category . ":\n";
    foreach ($plugins as $plugin) {
        if (in_array($plugin, $implementedPlugins)) {
            $status = in_array($plugin, $enabledPlugins) ? "[ENABLED]" : "[DISABLED]";
            echo "  ✓ " . $plugin . " " . $status . "\n";
        }
    }
}