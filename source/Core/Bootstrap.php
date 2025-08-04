<?php

/**
 * ===================================================================
 * HOIST PHP FRAMEWORK - BOOTSTRAP
 * ===================================================================
 * 
 * This file is responsible for initializing the Hoist PHP framework.
 * It performs the following critical setup tasks:
 * 
 * 1. Composer Autoloader Setup
 * 2. Environment Configuration (.env loading)
 * 3. Application Constants Loading
 * 4. Core Framework Library Loading
 * 5. Application Instance Creation
 * 6. Model Auto-registration
 * 7. Library Auto-registration
 * 
 * This bootstrap process ensures that all framework dependencies,
 * configurations, and components are properly loaded and available
 * before the application begins processing requests.
 * 
 * @package HoistPHP\Core
 * @author  Hoist PHP Framework Team
 * @version 1.0.0
 */

// ===================================================================
// COMPOSER AUTOLOADER SETUP
// ===================================================================

/**
 * Load composer autoloader file.
 * 
 * Composer is required for dependency management and PSR-4 autoloading.
 * This ensures all third-party libraries (Medoo, Dotenv, etc.) are
 * available throughout the application.
 * 
 * @throws Exception If composer autoloader is not found
 */
if (!file_exists(ROOT_DIRECTORY . "/vendor/autoload.php")) {
    throw new \Exception('Composer is not installed. Please run "composer install" to install dependencies.');
}
require_once ROOT_DIRECTORY . "/vendor/autoload.php";

// ===================================================================
// ENVIRONMENT CONFIGURATION
// ===================================================================

/**
 * Load environment variables from .env file if it exists.
 * 
 * Environment variables are used for configuration that varies
 * between different environments (development, staging, production).
 * This includes database credentials, API keys, debug settings, etc.
 * 
 * The .env file should never be committed to version control as it
 * contains sensitive configuration data.
 */
if (file_exists(ROOT_DIRECTORY . "/.env")) {
    $dotenv = \Dotenv\Dotenv::createImmutable(ROOT_DIRECTORY);
    $dotenv->load();
}

// ===================================================================
// APPLICATION CONSTANTS LOADING
// ===================================================================

/**
 * Validate and load application constants.
 * 
 * The Constants.php file contains application-wide constants such as:
 * - Application version
 * - Site branding information
 * - Asset paths and URLs
 * - Default configurations
 * 
 * These constants provide a centralized configuration system that
 * can be accessed throughout the entire application.
 * 
 * @throws Exception If Constants.php file is missing
 */
if (!file_exists(APPLICATION_DIRECTORY . "/Constants.php")) {
    throw new \Exception('Application constants file does not appear to exist.');
}

// Require the constants file
require_once APPLICATION_DIRECTORY . "/Constants.php";

// ===================================================================
// CORE FRAMEWORK LIBRARY LOADING
// ===================================================================

/**
 * Load all core framework library files.
 * 
 * This recursively includes all PHP files in the Core/Libraries directory.
 * These files contain the fundamental framework classes including:
 * - Router (URL routing and dispatch)
 * - Database (database abstraction layer)
 * - Auth (authentication and authorization)
 * - Session (session management)
 * - View (template rendering)
 * - Model (base model class with ORM features)
 * - Controller (base controller class)
 * - Request/Response (HTTP handling)
 * - And many other core services
 * 
 * Loading these files makes all core framework functionality available
 * for use throughout the application.
 */
foreach (glob(CORE_DIRECTORY . '/**/*.php') as $filename) {
    require_once $filename;
}

// ===================================================================
// APPLICATION INSTANCE SETUP
// ===================================================================

/**
 * Validate that the Instance class file exists.
 * 
 * The Instance class acts as the main service container for the framework,
 * providing access to all core services and managing the application lifecycle.
 * 
 * @throws Exception If Instance.php file is missing
 */
if (!file_exists(CORE_DIRECTORY . "/Instance.php")) {
    throw new \Exception('Application instance file does not appear to exist.');
}

// Require the instance file
require_once CORE_DIRECTORY . "/Instance.php";

/**
 * Instantiate the main application instance.
 * 
 * This creates the primary service container that will be used throughout
 * the application lifecycle. The Instance constructor automatically:
 * - Initializes all core services
 * - Sets up database connections
 * - Configures session handling
 * - Prepares authentication systems
 * - Sets up routing and request handling
 */
$Instance = new Instance();

// ===================================================================
// MODEL AUTO-REGISTRATION
// ===================================================================

/**
 * Auto-load and register all application models.
 * 
 * This process scans the Application/Models directory for files ending
 * in "Model.php" and automatically:
 * 1. Includes the model file
 * 2. Extracts the class name from the filename
 * 3. Creates a camelCase property name (e.g., UserModel -> user)
 * 4. Instantiates the model with the application instance
 * 5. Registers it in the $Instance->models object
 * 
 * This allows models to be accessed via: $Instance->models->user
 * 
 * @throws Exception If a model class doesn't exist after inclusion
 */
foreach (glob(APPLICATION_DIRECTORY . "/Models/*Model.php") as $model) {
    require_once $model;

    // Establish the class name from the filename
    $className = basename($model);
    $className = str_replace('.php', '', $className);

    // Establish the model property name (UserModel -> user)
    $modelName = lcfirst(str_replace('Model', '', $className));

    // Validate that the class exists after inclusion
    if (!class_exists($className)) {
        throw new \Exception('Model class `' . $className . '` does not exist.');
    }

    // Register the model instance for global access
    $Instance->registerModel($modelName, new $className($Instance));
}

// ===================================================================
// LIBRARY AUTO-REGISTRATION
// ===================================================================

/**
 * Auto-load and register all application libraries.
 * 
 * This process scans the Application/Libraries directory and automatically:
 * 1. Includes each library file
 * 2. Extracts the class name from the filename
 * 3. Creates a camelCase property name
 * 4. Instantiates the library with the application instance
 * 5. Registers it in the $Instance->libraries object
 * 
 * This allows libraries to be accessed via: $Instance->libraries->myLibrary
 * 
 * @throws Exception If a library class doesn't exist after inclusion
 */
foreach (glob(APPLICATION_DIRECTORY . "/Libraries/*.php") as $library) {
    require_once $library;

    // Establish the class name from the filename
    $className = basename($library);
    $className = str_replace('.php', '', $className);

    // Establish the library property name
    $libraryName = lcfirst($className);

    // Validate that the class exists after inclusion
    if (!class_exists($className)) {
        throw new \Exception('Library class `' . $className . '` does not exist.');
    }

    // Register the library instance for global access
    $Instance->registerLibrary($libraryName, new $className($Instance));
}

// ===================================================================
// AUTHENTICATION SESSION INITIALIZATION
// ===================================================================

/**
 * Initialize user authentication session after models are available.
 * 
 * Now that all models have been registered and are available via
 * $Instance->models, we can safely initialize the authentication
 * session which may need to access the UserModel.
 */
if (!$Instance->isCommandLine()) {
    $Instance->auth->initializeSession();
}
