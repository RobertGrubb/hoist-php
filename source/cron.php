<?php

defined('CLI_OPTIONS') || define('CLI_OPTIONS', getopt('c:', [
    'id::'
]));

// Validate c flag
if (!isset(CLI_OPTIONS['c'])) {
    die('No cron provided.' . PHP_EOL);
}

// Test

/**
 * Set paths
 */
defined("CORE_DIRECTORY") || define('CORE_DIRECTORY', __DIR__ . '/Core');
defined("APPLICATION_DIRECTORY") || define('APPLICATION_DIRECTORY', __DIR__ . '/Application');
defined("WEB_DIRECTORY") || define("WEB_DIRECTORY", __DIR__ . '/public');
defined("ROOT_DIRECTORY") || define("ROOT_DIRECTORY", __DIR__);

// Load the bootstrap file
require_once CORE_DIRECTORY . "/Bootstrap.php";

$Instance->cron->run(CLI_OPTIONS['c']);
