<?php

/**
 * Simple script to publish assets from resources to public directory
 * This replaces the Vite asset compilation
 */

// Define source and destination directories
$cssSource = __DIR__ . '/resources/css';
$jsSource = __DIR__ . '/resources/js';
$cssDestination = __DIR__ . '/public/css';
$jsDestination = __DIR__ . '/public/js';

// Create destination directories if they don't exist
if (!is_dir($cssDestination)) {
    mkdir($cssDestination, 0755, true);
    echo "Created directory: $cssDestination\n";
}

if (!is_dir($jsDestination)) {
    mkdir($jsDestination, 0755, true);
    echo "Created directory: $jsDestination\n";
}

// Copy CSS files
$cssFiles = glob($cssSource . '/*.css');
foreach ($cssFiles as $file) {
    $filename = basename($file);
    $destination = $cssDestination . '/' . $filename;
    
    if (copy($file, $destination)) {
        echo "Copied: $filename to public/css/\n";
    } else {
        echo "Failed to copy: $filename\n";
    }
}

// Copy JS files
$jsFiles = glob($jsSource . '/*.js');
foreach ($jsFiles as $file) {
    $filename = basename($file);
    $destination = $jsDestination . '/' . $filename;
    
    if (copy($file, $destination)) {
        echo "Copied: $filename to public/js/\n";
    } else {
        echo "Failed to copy: $filename\n";
    }
}

echo "Asset publishing complete!\n";
