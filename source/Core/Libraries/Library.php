<?php

/**
 * Base Library Class for Framework Extensions
 * 
 * Provides a foundational base class for creating custom libraries and
 * extensions within the Hoist framework. This class establishes standard
 * patterns for service injection, database access, and initialization
 * workflows that all framework libraries should follow.
 * 
 * CORE CAPABILITIES:
 * - Automatic framework instance injection for service access
 * - Direct database and file database connectivity shortcuts
 * - Standardized initialization lifecycle with instantiate() hook
 * - Base foundation for all custom framework libraries
 * 
 * INHERITANCE PATTERN:
 * Custom libraries should extend this base class to inherit standard
 * framework integration patterns and service access capabilities.
 * 
 * SERVICE ACCESS:
 * Provides quick access to commonly used framework services:
 * - $this->instance: Full framework service container
 * - $this->database: Direct Medoo database connection
 * - $this->fileDatabase: Direct FileDatabase instance
 * 
 * INITIALIZATION LIFECYCLE:
 * 1. Constructor receives framework instance
 * 2. Sets up service shortcuts for easy access
 * 3. Calls instantiate() method if implemented
 * 4. Library is ready for use
 * 
 * USAGE EXAMPLES:
 * 
 * Creating Custom Library:
 * ```php
 * class EmailLibrary extends Library
 * {
 *     private $config;
 *     
 *     public function instantiate()
 *     {
 *         // Custom initialization logic
 *         $this->config = $this->instance->get('settings')->get('email');
 *     }
 *     
 *     public function sendWelcomeEmail($userId)
 *     {
 *         // Access database through inherited shortcut
 *         $user = $this->database->get('users', '*', ['id' => $userId]);
 *         
 *         // Library implementation...
 *     }
 * }
 * ```
 * 
 * Registration and Usage:
 * ```php
 * // In framework initialization
 * $instance->register('email', new EmailLibrary($instance));
 * 
 * // In controllers or other classes
 * $emailLib = $instance->get('email');
 * $emailLib->sendWelcomeEmail(123);
 * ```
 * 
 * DESIGN BENEFITS:
 * - Consistent service access patterns across all libraries
 * - Reduced boilerplate code for common framework integrations
 * - Standardized initialization workflow
 * - Easy access to database services without repetitive injection
 * 
 * EXTENSION GUIDELINES:
 * - Override instantiate() for custom initialization logic
 * - Use inherited service shortcuts for database operations
 * - Follow framework naming and coding standards
 * - Implement proper error handling and validation
 * 
 * @package HoistPHP\Core\Libraries
 * @version 1.0.0
 * @since 1.0.0
 */
class Library
{
    /**
     * Framework instance providing access to all services.
     * 
     * Contains the complete framework service container allowing access
     * to all registered services, configurations, and components. This
     * instance should be used for accessing any framework functionality
     * not directly provided through the convenience shortcuts.
     * 
     * @var object Framework service container instance
     * @access public
     */
    public $instance;

    /**
     * Direct access to Medoo database connection.
     * 
     * Provides immediate access to the main database connection for
     * performing SQL operations without needing to call through the
     * instance container. This is a convenience shortcut for the most
     * commonly used database service.
     * 
     * @var object Medoo database connection instance
     * @access public
     */
    public $database;

    /**
     * Direct access to FileDatabase system.
     * 
     * Provides immediate access to the file-based database system for
     * JSON file operations and development database needs. This shortcut
     * eliminates the need to access through the service container.
     * 
     * @var FileDatabase File-based database system instance
     * @access public
     */
    public $fileDatabase;

    /**
     * Initializes the library with framework integration.
     * 
     * Sets up the base library with framework service injection and
     * creates convenient shortcuts to commonly used services. Calls
     * the instantiate() method if implemented by child classes for
     * custom initialization logic.
     * 
     * INITIALIZATION PROCESS:
     * 1. Stores framework instance for full service access
     * 2. Creates shortcuts to database and fileDatabase services
     * 3. Calls instantiate() hook for custom initialization
     * 4. Library is ready for use with all framework services
     * 
     * SERVICE SHORTCUTS:
     * - database: Direct access to Medoo database connection
     * - fileDatabase: Direct access to FileDatabase system
     * - instance: Full framework service container
     * 
     * @param object $instance Framework service container instance
     */
    public function __construct($instance)
    {
        $this->instance = $instance;

        // Easier access ($database->table())
        $this->database = $instance->database;
        $this->fileDatabase = $instance->fileDatabase;

        if (method_exists($this, 'instantiate')) {
            $this->instantiate();
        }
    }

    /**
     * Custom initialization hook for child classes.
     * 
     * Provides a standardized initialization method that child classes
     * can override to implement custom setup logic. This method is
     * automatically called during construction after all base services
     * have been set up and are available for use.
     * 
     * IMPLEMENTATION GUIDELINES:
     * - Override this method in child classes for custom initialization
     * - All framework services are available through $this->instance
     * - Database shortcuts are ready for immediate use
     * - Perform configuration loading, validation, or setup here
     * 
     * USAGE EXAMPLES:
     * 
     * Custom Configuration Loading:
     * ```php
     * public function instantiate()
     * {
     *     $this->config = $this->instance->get('settings')->get('my_library');
     *     $this->validateConfiguration();
     * }
     * ```
     * 
     * Service Registration:
     * ```php
     * public function instantiate()
     * {
     *     // Register additional services or dependencies
     *     $this->logger = $this->instance->get('logger');
     *     $this->cache = $this->instance->get('cache');
     * }
     * ```
     * 
     * @return void
     */
    public function instantiate()
    {

    }
}
