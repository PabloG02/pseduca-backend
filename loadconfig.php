<?php
// Load the .ini file and store it in a global variable
function loadConfig(string $filePath): void
{
    // Parse the .ini file
    $config = parse_ini_file($filePath, true);

    // Merge the configuration into $GLOBALS
    foreach ($config as $section => $settings) {
        $GLOBALS[$section] = $settings;
    }
}

// Call the function to load the configuration
loadConfig(__DIR__ . '/config.ini');
