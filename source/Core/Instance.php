<?php

/**
 * ===================================================================
 * HOIST PHP FRAMEWORK - APPLICATION INSTANCE
 * ===================================================================
 * 
 * The Instance class serves as the main service container and application
 * context for the Hoist PHP framework. It acts as a centralized registry
 * for all framework services, handles application lifecycle management,
 * and provides dependency injection throughout the application.
 * 
 * Key Responsibilities:
 * - Service Container: Manages all framework services and dependencies
 * - Environment Detection: Handles CLI vs web request contexts
 * - URL Management: Base URL and current URL handling
 * - Redirect Processing: URL redirection logic
 * - Security: Environment validation and secure configuration
 * - Lifecycle Management: Application cleanup and shutdown
 * 
 * This class is instantiated once during bootstrap and made available
 * globally throughout the application via the $Instance variable.
 * 
 * @package HoistPHP\Core
 * @author  Hoist PHP Framework Team
 * @version 1.0.0
 */
class Instance
{
    // ===============================================================
    // URL AND ENVIRONMENT PROPERTIES
    // ===============================================================

    /** @var string Base URL of the application (e.g., https://example.com) */
    public $baseUrl = '';

    /** @var string Complete current URL including query parameters */
    public $currentUrl = '';

    // ===============================================================
    // CORE SERVICE PROPERTIES
    // ===============================================================

    /** @var Cleaner Data cleaning and sanitization service */
    public $cleaner;

    /** @var Response HTTP response handling service */
    public $response;

    /** @var Router URL routing and dispatch service */
    public $router;

    /** @var Session Session management service */
    public $session;

    /** @var Auth Authentication and authorization service */
    public $auth;

    /** @var Request HTTP request handling service */
    public $request;

    /** @var Database Database connection and query service */
    public $database;

    /** @var Settings Application settings management service */
    public $settings;

    /** @var FileDatabase File-based database service */
    public $fileDatabase;

    /** @var Uploader File upload handling service */
    public $uploader;

    /** @var Security Security utilities and validation service */
    public $security;

    /** @var View Template rendering and view service */
    public $view;

    /** @var object Container for all registered application models */
    public $models;

    /** @var object Container for all registered application libraries */
    public $libraries;

    /** @var Validation Input validation service */
    public $validation;

    /** @var Cron Cron job management service */
    public $cron;

    /** @var Cache High-performance caching service */
    public $cache;

    // ===============================================================
    // APPLICATION STATE PROPERTIES
    // ===============================================================

    /** @var array URL redirect mappings from /Application/Redirects.php */
    private $redirects = [];

    /** @var object SEO meta data container for page titles, descriptions, etc. */
    public $meta;

    /** @var bool Dark mode preference state */
    public $darkMode;

    // ===============================================================
    // FRAMEWORK DEPENDENCIES
    // ===============================================================

    /**
     * List of core framework classes that are required for the Instance
     * to function correctly. These classes must exist and be loaded
     * before the Instance can be instantiated.
     * 
     * Each class provides essential framework functionality:
     * - Utilities: Helper functions and common operations
     * - Cleaner: Data sanitization and cleaning
     * - Response: HTTP response management
     * - Router: URL routing and controller dispatch
     * - Controller: Base controller class
     * - Model: Base model class with ORM features
     * - Session: Session handling and flash messages
     * - Security: Security utilities and validation
     * - Validation: Input validation and filtering
     * - Database: Database abstraction and ORM
     * - Settings: Application configuration management
     * - FileDatabase: File-based data storage
     * - Uploader: File upload processing
     * - Request: HTTP request parsing and handling
     * - Auth: Authentication and user management
     * - View: Template rendering and view management
     * - Cron: Scheduled task management
     * 
     * @var array List of required core framework class names
     */
    private $bootstrapClasses = [
        "Utilities",
        "Cleaner",
        "Response",
        "Router",
        "Controller",
        "Model",
        "Session",
        "Security",
        "Validation",
        "Database",
        "Settings",
        "FileDatabase",
        "Uploader",
        "Request",
        "Auth",
        "View",
        "Cron",
        "Cache",
    ];

    // ===============================================================
    // CONSTRUCTOR AND INITIALIZATION
    // ===============================================================

    /**
     * Initializes the application instance and all core services.
     * 
     * This constructor performs the complete application setup process:
     * 
     * 1. Service Container Setup: Initializes model/library containers
     * 2. URL Configuration: Sets base URL and current URL for web requests
     * 3. Core Class Validation: Ensures all required framework classes exist
     * 4. Service Instantiation: Creates instances of all core services
     * 5. Redirect Processing: Handles URL redirects if configured
     * 6. Environment Validation: Validates required environment variables
     * 7. Database Setup: Establishes database connection with credentials
     * 8. Service Configuration: Configures services with dependencies
     * 9. Meta Data Setup: Initializes SEO and page meta information
     * 10. CLI Detection: Adjusts behavior for command-line usage
     * 
     * The constructor ensures that all services are properly initialized
     * and configured before the application begins processing requests.
     * 
     * @throws Exception If required classes are missing
     * @throws Exception If required environment variables are not set
     */
    public function __construct()
    {
        // ---------------------------------------------------------------
        // SERVICE CONTAINER INITIALIZATION
        // ---------------------------------------------------------------

        /**
         * Initialize service containers as objects.
         * 
         * These containers will hold all registered models and libraries,
         * making them accessible via $instance->models->modelName and
         * $instance->libraries->libraryName syntax.
         */
        $this->models = (object) [];
        $this->libraries = (object) [];

        // ---------------------------------------------------------------
        // URL CONFIGURATION
        // ---------------------------------------------------------------

        /**
         * Configure application URLs for web requests.
         * 
         * Base URL is used for generating absolute URLs and redirects.
         * Current URL is used for request analysis and logging.
         */
        $this->setBaseUrl();

        if (!$this->isCommandLine()) {
            $this->setCurrentUrl();
        }

        // ---------------------------------------------------------------
        // CORE CLASS VALIDATION
        // ---------------------------------------------------------------

        /**
         * Validate that all required core framework classes exist.
         * 
         * This ensures that the framework bootstrap process completed
         * successfully and all dependencies are available.
         */
        foreach ($this->bootstrapClasses as $c) {
            if (!class_exists($c)) {
                throw new \Exception('Required core class `' . $c . '` not found.');
            }
        }

        // ---------------------------------------------------------------
        // BASIC SERVICE INSTANTIATION
        // ---------------------------------------------------------------

        /**
         * Initialize core services that don't have dependencies.
         * 
         * These services are initialized first as they may be required
         * by other services during their initialization.
         */
        $this->cleaner = new Cleaner();

        // Only initialize web-specific services for HTTP requests
        if (!$this->isCommandLine()) {
            $this->response = new Response();
            $this->request = new Request();
            $this->session = new Session($this);
        }

        // ---------------------------------------------------------------
        // REDIRECT PROCESSING
        // ---------------------------------------------------------------

        /**
         * Process URL redirects if configured.
         * 
         * The framework supports defining redirects in /Application/Redirects.php
         * which maps old URLs to new URLs. This is useful for:
         * - SEO: Maintaining search engine rankings during URL changes
         * - User Experience: Ensuring bookmarks continue to work
         * - Migration: Moving content to new locations
         * 
         * Only process redirects for web requests, not CLI commands.
         */
        if (!$this->isCommandLine()) {
            if (file_exists(__DIR__ . '/Redirects.php')) {
                $redirects = require_once __DIR__ . '/Redirects.php';
                $this->redirects = $redirects;

                // Process redirect if current URL matches a redirect rule
                $this->processRedirect();
            }
        }

        // ---------------------------------------------------------------
        // ENVIRONMENT VALIDATION
        // ---------------------------------------------------------------

        /**
         * Validate that required environment variables are set.
         * 
         * The framework requires database connection parameters to be
         * defined in environment variables (typically via .env file).
         * This ensures secure configuration without hardcoding credentials.
         * 
         * Required variables:
         * - DB_HOST: Database server hostname/IP
         * - DB_USER: Database username
         * - DB_PASSWORD: Database password
         * - DB_NAME: Database name
         */
        if (!isset($_ENV['DB_HOST'])) {
            throw new \Exception('DB_HOST environment variable not found.');
        }

        if (!isset($_ENV['DB_USER'])) {
            throw new \Exception('DB_USER environment variable not found.');
        }

        if (!isset($_ENV['DB_PASSWORD'])) {
            throw new \Exception('DB_PASSWORD environment variable not found.');
        }

        if (!isset($_ENV['DB_NAME'])) {
            throw new \Exception('DB_NAME environment variable not found.');
        }

        // ---------------------------------------------------------------
        // DATABASE AND CORE SERVICE SETUP
        // ---------------------------------------------------------------

        /**
         * Initialize database connection with environment credentials.
         * 
         * Creates a secure database connection using the validated
         * environment variables. The Database class wraps Medoo ORM
         * for secure, convenient database operations.
         */
        $this->database = new Database([
            'host' => $_ENV['DB_HOST'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
            'dbname' => $_ENV['DB_NAME'],
        ]);

        /**
         * Initialize core services with dependencies.
         * 
         * These services require the database or other services to be
         * initialized first, so they're created after basic setup.
         */
        $this->settings = new Settings($this);
        $this->fileDatabase = new FileDatabase('app');
        $this->uploader = new Uploader($this, WEB_DIRECTORY . '/uploads');
        $this->auth = new Auth($this, $this->request, $this->baseUrl);

        // ---------------------------------------------------------------
        // SEO META DATA SETUP
        // ---------------------------------------------------------------

        /**
         * Initialize SEO and meta data configuration.
         * 
         * Sets up default meta information for pages including:
         * - Page title from settings
         * - Meta description for search engines
         * - Keywords for SEO
         * - Default social media image
         * - Base URL for absolute links
         * 
         * This data can be overridden on individual pages as needed.
         */
        $this->meta = (object) [
            'title' => $this->settings->get('seo.title'),
            'extraTitle' => false,
            'image' => $this->baseUrl . '/assets/images/noimage.jpg',
            'description' => $this->settings->get('seo.description'),
            'keywords' => $this->settings->get('seo.keywords'),
            'url' => $this->baseUrl
        ];

        // ---------------------------------------------------------------
        // REMAINING SERVICE INITIALIZATION
        // ---------------------------------------------------------------

        /**
         * Initialize remaining framework services.
         * 
         * These services are initialized last as they may depend on
         * other services being available.
         */
        if (!$this->isCommandLine()) {
            $this->security = new Security($this);
        }

        $this->validation = new Validation($this);
        $this->view = new View($this);
        $this->router = new Router($this);
        $this->cron = new Cron($this);
        $this->cache = new Cache([
            'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
            'path' => APPLICATION_DIRECTORY . '/Cache'
        ]);

        // ---------------------------------------------------------------
        // USER PREFERENCE SETUP
        // ---------------------------------------------------------------

        /**
         * Initialize user interface preferences.
         * 
         * Checks for user preferences stored in cookies, such as
         * dark mode settings. Only applies to web requests.
         */
        if (!$this->isCommandLine()) {
            $this->darkMode = isset($_COOKIE['darkmode']) ? true : false;
        }
    }

    // ===============================================================
    // URL REDIRECT PROCESSING
    // ===============================================================

    /**
     * Processes URL redirects based on configured redirect rules.
     * 
     * Checks if the current request URI matches any configured redirects
     * in the $redirects array (loaded from /Application/Redirects.php).
     * If a match is found, performs an immediate redirect to the target URL.
     * 
     * Redirects are useful for:
     * - SEO: Maintaining search rankings during URL restructuring
     * - User Experience: Handling moved or renamed pages
     * - Migration: Supporting legacy URLs during site updates
     * 
     * Uses permanent (301) redirects to inform search engines and browsers
     * that the URL has permanently moved to the new location.
     * 
     * @return void
     */
    private function processRedirect()
    {
        if (isset($this->redirects[$this->request->uri()])) {
            $url = $this->redirects[$this->request->uri()];
            $this->redirect($url, true);
        }
    }

    // ===============================================================
    // URL CONFIGURATION METHODS
    // ===============================================================

    /**
     * Configures the application's base URL.
     * 
     * The base URL is used throughout the application for:
     * - Generating absolute URLs for links and assets
     * - Handling redirects and form submissions
     * - Creating canonical URLs for SEO
     * - API responses and email links
     * 
     * Priority order for base URL determination:
     * 1. BASE_URL environment variable (preferred method)
     * 2. Falls back to empty string (relative URLs only)
     * 
     * The URL is sanitized to prevent XSS attacks and ensure validity.
     * 
     * @return void
     */
    private function setBaseUrl()
    {
        if (isset($_ENV['BASE_URL'])) {
            $this->baseUrl = $_ENV['BASE_URL'];
        }

        $this->baseUrl = filter_var($this->baseUrl, FILTER_SANITIZE_URL);
    }

    /**
     * Sets the complete current URL for the request.
     * 
     * Constructs the full URL including protocol, host, and path with
     * query parameters. Used for:
     * - Request logging and analytics
     * - Referer tracking
     * - Debug information
     * - Security monitoring
     * 
     * Format: https://example.com/path?query=value
     * 
     * Note: Assumes HTTPS protocol for security. In production environments,
     * this should be enhanced to detect actual protocol.
     * 
     * @return void
     */
    private function setCurrentUrl()
    {
        $this->currentUrl = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    // ===============================================================
    // APPLICATION LIFECYCLE METHODS
    // ===============================================================

    /**
     * Performs HTTP redirect to specified URL.
     * 
     * Handles both temporary (302) and permanent (301) redirects with
     * proper header management and application cleanup.
     * 
     * Features:
     * - Header cleanup to prevent conflicts
     * - Application state cleanup via cleanup() method
     * - Support for permanent redirects (SEO-friendly)
     * - Immediate script termination to prevent further execution
     * 
     * @param string $url Target URL for redirection
     * @param bool $permanent Whether to use 301 (permanent) vs 302 (temporary) redirect
     * @return void This method terminates script execution
     */
    public function redirect($url, $permanent = false)
    {
        // Clear any existing headers to prevent conflicts
        if (!headers_sent()) {
            header_remove();
        }

        // Perform application cleanup before redirect
        $this->cleanup();

        // Set appropriate redirect status code
        if ($permanent) {
            header("HTTP/1.1 301 Moved Permanently");
        }

        // Perform the redirect and terminate execution
        header('Location: ' . $url);
        exit;
    }

    /**
     * Performs application cleanup operations.
     * 
     * This method handles end-of-request cleanup tasks that need to
     * occur before the application terminates or redirects. Currently
     * includes:
     * 
     * - Flash message processing: Updates session flash data so messages
     *   are properly displayed on the next request
     * 
     * Additional cleanup tasks can be added here as needed, such as:
     * - Closing database connections
     * - Flushing caches
     * - Logging request completion
     * - Clearing temporary data
     * 
     * This method is called automatically by:
     * - Controller after() hooks
     * - Redirect operations
     * - Application shutdown
     * 
     * @return void
     */
    public function cleanup()
    {
        $this->session->updateFlashData();
    }

    // ===============================================================
    // SERVICE REGISTRATION METHODS
    // ===============================================================

    /**
     * Registers a model instance in the service container.
     * 
     * Models are business logic classes that handle data operations,
     * database interactions, and domain-specific functionality.
     * This method makes models accessible via $instance->models->modelName.
     * 
     * Features:
     * - Duplicate prevention: Ensures no model key conflicts
     * - Global accessibility: Makes models available throughout application
     * - Service container pattern: Centralized dependency management
     * 
     * @param string $key The property name for accessing the model (e.g., 'user')
     * @param object $instance The model instance to register
     * @throws Exception If a model with the same key already exists
     * @return void
     */
    public function registerModel($key, $instance)
    {
        if (isset($this->models->{$key})) {
            throw new \Exception('Duplicate model `' . $key . '` found.');
        }

        $this->models->{$key} = $instance;
    }

    /**
     * Registers a library instance in the service container.
     * 
     * Libraries are utility classes that provide specialized functionality
     * such as email handling, API integrations, data processing, etc.
     * This method makes libraries accessible via $instance->libraries->libraryName.
     * 
     * Features:
     * - Duplicate prevention: Ensures no library key conflicts
     * - Global accessibility: Makes libraries available throughout application
     * - Service container pattern: Centralized dependency management
     * 
     * @param string $key The property name for accessing the library (e.g., 'email')
     * @param object $instance The library instance to register
     * @throws Exception If a library with the same key already exists
     * @return void
     */
    public function registerLibrary($key, $instance)
    {
        if (isset($this->libraries->{$key})) {
            throw new \Exception('Duplicate library `' . $key . '` found.');
        }

        $this->libraries->{$key} = $instance;
    }

    // ===============================================================
    // ENVIRONMENT DETECTION METHODS
    // ===============================================================

    /**
     * Determines if the application is running in command-line interface mode.
     * 
     * This detection is crucial for:
     * - Conditional service initialization (web vs CLI services)
     * - Different error handling approaches
     * - Output formatting (HTML vs plain text)
     * - Security considerations (CLI vs web security)
     * 
     * CLI usage examples:
     * - Cron jobs and scheduled tasks
     * - Database migrations
     * - Data import/export scripts
     * - Administrative commands
     * 
     * @return bool True if running from command line, false for web requests
     */
    public function isCommandLine()
    {
        return php_sapi_name() === "cli";
    }

    // ===============================================================
    // USER PREFERENCE METHODS
    // ===============================================================

    /**
     * Manages user dark mode preference setting.
     * 
     * Dark mode is a popular UI feature that:
     * - Reduces eye strain in low-light conditions
     * - Saves battery life on OLED displays
     * - Provides modern, professional appearance
     * - Improves accessibility for some users
     * 
     * This method:
     * - Sets persistent cookie for preference storage
     * - Updates current request state immediately
     * - Handles both enabling and disabling dark mode
     * 
     * @param bool $val True to enable dark mode, false to disable
     * @return void
     */
    public function setDarkMode($val)
    {
        if ($val == true) {
            // Set cookie for 30 days
            setcookie('darkmode', "true", time() + (86400 * 30), "/");
            $_COOKIE['darkmode'] = $val;
        } else {
            // Remove dark mode preference
            unset($_COOKIE['darkmode']);
            setcookie('darkmode', '', -1, '/');
        }
    }
}
