<?php

ini_set('session.cookie_lifetime', 60 * 60 * 24 * 365);
ini_set('session.gc-maxlifetime', 60 * 60 * 24 * 365);

date_default_timezone_set('America/New_York');

/**
 * Set paths
 */
defined("CORE_DIRECTORY") || define('CORE_DIRECTORY', __DIR__ . '/../Core');
defined("APPLICATION_DIRECTORY") || define('APPLICATION_DIRECTORY', __DIR__ . '/../Application');
defined("WEB_DIRECTORY") || define("WEB_DIRECTORY", __DIR__);
defined("ROOT_DIRECTORY") || define("ROOT_DIRECTORY", __DIR__ . '/..');

/**
 * Starts session if disabled (Prevents duplicate starts)
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Load the bootstrap file
require_once CORE_DIRECTORY . "/Bootstrap.php";

// Register routes
$routes = require_once APPLICATION_DIRECTORY . "/Routes.php";
$Instance->router->registerRoutes($routes);

// Run the router
$Instance->router->run();
