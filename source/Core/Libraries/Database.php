<?php

use Medoo\Medoo;

/**
 * Optional Database Connection and Management Library
 * 
 * Provides an optional database abstraction layer built on top of the
 * Medoo ORM library with FileDatabase as the primary storage system.
 * This class handles optional MySQL connections, configuration management,
 * query logging, and provides graceful fallback to FileDatabase-only mode
 * when MySQL environment variables are not configured.
 * 
 * CORE CAPABILITIES:
 * - Optional MySQL/MariaDB connection management with PDO
 * - FileDatabase-first architecture with MySQL enhancement
 * - Medoo ORM integration for secure query building (when connected)
 * - Configurable query logging and debugging
 * - Graceful degradation when MySQL is unavailable
 * - UTF8MB4 charset support for full Unicode compatibility
 * 
 * OPERATING MODES:
 * - FileDatabase-Only: Default mode when MySQL parameters missing/empty
 * - MySQL Enhanced: Optional mode when complete MySQL configuration provided
 * 
 * SECURITY FEATURES:
 * - PDO prepared statements prevent SQL injection (MySQL mode)
 * - Optional connection parameter validation
 * - Secure default configurations
 * - Error handling without exposing sensitive data
 * - No exceptions for missing MySQL configuration
 * 
 * MEDOO INTEGRATION (MySQL Mode):
 * Leverages the Medoo ORM for:
 * - Simplified query syntax
 * - Automatic parameter binding
 * - Cross-database compatibility
 * - Built-in security features
 * 
 * CONNECTION MANAGEMENT:
 * - Optional connection establishment
 * - Configuration validation without exceptions
 * - Error handling and logging
 * - Resource cleanup and management
 * - Status tracking via isConnected property
 * 
 * USAGE EXAMPLES:
 * 
 * FileDatabase-Only Mode (Default):
 * ```php
 * $db = new Database(); // No MySQL connection attempted
 * // Use FileDatabase for all operations
 * ```
 * 
 * Optional MySQL Enhancement:
 * ```php
 * $db = new Database([
 *     'host' => $_ENV['DB_HOST'] ?? '',
 *     'user' => $_ENV['DB_USER'] ?? '',
 *     'password' => $_ENV['DB_PASSWORD'] ?? '',
 *     'dbname' => $_ENV['DB_NAME'] ?? ''
 * ]);
 * 
 * if ($db->isConnected) {
 *     // MySQL operations available
 *     $users = $db->client->select('users', '*', ['status' => 'active']);
 * } else {
 *     // FileDatabase operations only
 * }
 * ```
 * 
 * Environment-Based Configuration:
 * ```php
 * // Will only connect if all environment variables are non-empty
 * $db = new Database([
 *     'host' => getenv('DB_HOST'),
 *     'user' => getenv('DB_USER'),
 *     'password' => getenv('DB_PASSWORD'),
 *     'dbname' => getenv('DB_NAME')
 * ]);
 * ```
 * 
 * CONFIGURATION PATTERNS:
 * - Supports standard MySQL connection parameters
 * - Configurable port and charset settings
 * - Environment-based configuration support
 * - Optional parameter validation (no exceptions)
 * - Graceful handling of missing configuration
 * 
 * @package HoistPHP\Core\Libraries
 * @version 2.0.0
 * @since 1.0.0
 */
class Database
{
    /**
     * Active Medoo database client instance.
     * 
     * Contains the initialized Medoo ORM client that provides the actual
     * database query interface. This client handles all SQL operations
     * through Medoo's secure query building and execution methods.
     * 
     * @var Medoo|null Active database client for query operations, null if not connected
     * @access public
     */
    public $client = null;

    /**
     * Database connection status flag.
     * 
     * Indicates whether a MySQL database connection has been established.
     * When false, the database is operating in FileDatabase-only mode.
     * 
     * @var bool True if MySQL connection is active, false otherwise
     * @access public
     */
    public $isConnected = false;

    /**
     * Query logging configuration flag.
     * 
     * Controls whether database queries should be logged for debugging
     * and performance monitoring purposes. When enabled, queries and
     * their execution details are recorded for analysis.
     * 
     * @var bool True to enable query logging, false to disable
     * @access private
     */
    private $logging = false;

    /**
     * Database connection configuration parameters.
     * 
     * Stores all necessary connection parameters for establishing a
     * database connection. These parameters are validated and used
     * to create the PDO connection through Medoo.
     * 
     * PARAMETER DETAILS:
     * - host: Database server hostname or IP address
     * - user: Database username for authentication
     * - password: Database password (defaults to empty string)
     * - dbname: Name of the database to connect to
     * - port: Database server port (defaults to 3306 for MySQL)
     * - charset: Character encoding (defaults to utf8mb4)
     * 
     * @var array Connection configuration with default values
     * @access private
     */
    private $_connectionVariables = [
        'host' => false,
        'user' => false,
        'password' => '',
        'dbname' => false,
        'port' => '3306',
        'charset' => 'utf8mb4',
    ];

    /**
     * Initializes the database connection with optional MySQL support.
     * 
     * Sets up the database connection by validating the provided configuration
     * parameters and optionally establishing a MySQL connection through the Medoo ORM.
     * If required MySQL parameters are missing or empty, the database will operate
     * in FileDatabase-only mode without throwing exceptions.
     * 
     * INITIALIZATION PROCESS:
     * 1. Validates and processes connection parameters
     * 2. Sets optional parameters with defaults
     * 3. Attempts MySQL connection if parameters are complete
     * 4. Creates accessible client for queries (if connection successful)
     * 
     * MYSQL CONNECTION REQUIREMENTS:
     * - host: Database server address (must be non-empty)
     * - user: Database username (must be non-empty)
     * - dbname: Database name to connect to (must be non-empty)
     * 
     * OPTIONAL PARAMETERS:
     * - password: Database password (defaults to empty)
     * - port: Database port (defaults to 3306)
     * - charset: Character encoding (defaults to utf8mb4)
     * - logs: Enable query logging (defaults to false)
     * 
     * OPERATING MODES:
     * - MySQL Mode: When all required parameters provided and connection successful
     * - FileDatabase Mode: When MySQL parameters missing/empty or connection fails
     * 
     * USAGE EXAMPLES:
     * 
     * MySQL Connection:
     * ```php
     * $db = new Database([
     *     'host' => 'localhost',
     *     'user' => 'dbuser',
     *     'password' => 'password',
     *     'dbname' => 'my_app'
     * ]);
     * 
     * if ($db->isConnected) {
     *     // MySQL operations available
     *     $users = $db->client->select('users', '*');
     * }
     * ```
     * 
     * FileDatabase-Only Mode:
     * ```php
     * $db = new Database(); // No parameters or empty parameters
     * // FileDatabase operations only, $db->client will be null
     * ```
     * 
     * @param array $data Database connection configuration parameters
     */
    public function __construct($data = [])
    {
        $this->_validateData($data);
        $this->_connect();
    }

    /**
     * Establishes the actual database connection through Medoo if parameters are complete.
     * 
     * Creates a new Medoo instance with the validated connection parameters
     * and configures it for MySQL/MariaDB connectivity. This method only
     * attempts connection if all required parameters are available and valid.
     * If parameters are missing or connection fails, the database operates
     * in FileDatabase-only mode.
     * 
     * CONNECTION REQUIREMENTS:
     * - All required parameters must be present and non-empty
     * - Connection attempt must succeed without exceptions
     * 
     * CONNECTION CONFIGURATION:
     * - Type: MySQL (compatible with MariaDB)
     * - Character encoding: UTF8MB4 for full Unicode support
     * - Logging: Configurable for development and debugging
     * - Security: PDO prepared statements enabled
     * 
     * @return void
     * @access private
     */
    private function _connect()
    {
        // Only attempt connection if all required parameters are available
        if (!$this->_hasRequiredParameters()) {
            return;
        }

        try {
            $this->client = new Medoo([
                'type' => 'mysql',
                'host' => $this->_connectionVariables['host'],
                'database' => $this->_connectionVariables['dbname'],
                'username' => $this->_connectionVariables['user'],
                'password' => $this->_connectionVariables['password'],
                'charset' => $this->_connectionVariables['charset'],
                'port' => $this->_connectionVariables['port'],
                'logging' => $this->logging,
            ]);

            $this->isConnected = true;
        } catch (Exception $e) {
            // Connection failed, continue in FileDatabase-only mode
            $this->client = null;
            $this->isConnected = false;
        }
    }

    /**
     * Checks if all required MySQL connection parameters are available and valid.
     * 
     * Validates that the essential connection parameters (host, user, dbname)
     * are present and contain non-empty values. This determines whether a
     * MySQL connection attempt should be made.
     * 
     * VALIDATION CRITERIA:
     * - host: Must be set and non-empty string
     * - user: Must be set and non-empty string  
     * - dbname: Must be set and non-empty string
     * 
     * @return bool True if all required parameters are valid, false otherwise
     * @access private
     */
    private function _hasRequiredParameters()
    {
        return !empty($this->_connectionVariables['host']) &&
            !empty($this->_connectionVariables['user']) &&
            !empty($this->_connectionVariables['dbname']);
    }

    /**
     * Checks if MySQL database connection is available and active.
     * 
     * Provides a convenient method to determine if MySQL database
     * operations are available or if the system should rely on
     * FileDatabase exclusively.
     * 
     * @return bool True if MySQL connection is active, false for FileDatabase-only mode
     * @access public
     */
    public function hasMySQL()
    {
        return $this->isConnected && $this->client !== null;
    }

    /**
     * Validates and processes database connection configuration.
     * 
     * Processes the connection parameters provided during initialization
     * without throwing exceptions for missing values. Sets available parameters
     * and allows the connection attempt to proceed only if all required
     * parameters are present and valid.
     * 
     * PROCESSING APPROACH:
     * 1. Validates input is an array
     * 2. Sets provided parameters in connection variables
     * 3. Applies optional parameters with defaults
     * 4. Configures logging if specified
     * 5. No exceptions for missing MySQL parameters
     * 
     * PARAMETER HANDLING:
     * - host: Set if provided and non-empty
     * - user: Set if provided and non-empty
     * - dbname: Set if provided and non-empty
     * - password: Set if provided, defaults to empty string
     * - charset: Set if provided, defaults to utf8mb4
     * - port: Set if provided, defaults to 3306
     * - logs: Set if provided, defaults to false
     * 
     * OPTIONAL BEHAVIOR:
     * - Missing required parameters result in FileDatabase-only mode
     * - No exceptions thrown for incomplete MySQL configuration
     * - Graceful fallback to file-based operations
     * 
     * @param array $data Connection configuration parameters
     * @return Database Returns self for method chaining
     * @throws Exception Only if data is not an array
     * @access private
     */
    private function _validateData($data = [])
    {
        if (!is_array($data)) {
            throw new \Exception('Data passed in constructor must be of type array.');
        }

        // Set MySQL connection parameters if provided and non-empty
        if (isset($data['host']) && !empty($data['host'])) {
            $this->_connectionVariables['host'] = $data['host'];
        }

        if (isset($data['user']) && !empty($data['user'])) {
            $this->_connectionVariables['user'] = $data['user'];
        }

        if (isset($data['dbname']) && !empty($data['dbname'])) {
            $this->_connectionVariables['dbname'] = $data['dbname'];
        }

        // Password can be empty, so just check if it's set
        if (isset($data['password'])) {
            $this->_connectionVariables['password'] = $data['password'];
        }

        // Optional parameter, override connection variables if passed
        if (isset($data['charset']) && !empty($data['charset'])) {
            $this->_connectionVariables['charset'] = $data['charset'];
        }

        // Optional parameter, override connection variables if passed
        if (isset($data['port']) && !empty($data['port'])) {
            $this->_connectionVariables['port'] = $data['port'];
        }

        // If logging option is provided, set it
        if (isset($data['logs'])) {
            $this->logging = $data['logs'];
        }

        return $this;
    }
}
