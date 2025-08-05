<?php

/**
 * =================================================================
 * HOIST PHP FRAMEWORK - CONSTANTS
 * =================================================================
 * 
 * Application constants configuration file. Define any constants
 * that should be globally accessible throughout the application.
 * 
 * Example: define("CONSTANT_NAME", "value");
 */

defined("APPLICATION_VERSION") || define("APPLICATION_VERSION", "0.0.1");

/**
 * ===================================
 * Application Stylesheets
 * ===================================
 */
defined("APPLICATION_STYLESHEETS") || define("APPLICATION_STYLESHEETS", [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.1/css/all.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css',
    '/assets/styles/app.css',
]);

/**
 * ===================================
 * Application Scripts
 * ===================================
 */

/**
 * Application scripts that should be included
 * in the header of the template.
 */
defined("APPLICATION_HEADER_SCRIPTS") || define("APPLICATION_HEADER_SCRIPTS", [
    'https://cdn.tailwindcss.com',
    'https://code.jquery.com/jquery-3.7.1.min.js',
]);

/**
 * Application scripts that should be included
 * on template
 */
defined("APPLICATION_SCRIPTS") || define("APPLICATION_SCRIPTS", [
    '/assets/scripts/app.js',
]);

defined("APPLICATION_MODULE_SCRIPTS") || define("APPLICATION_MODULE_SCRIPTS", []);
