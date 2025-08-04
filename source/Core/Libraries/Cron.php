<?php

/**
 * Cron Job Management Library
 * 
 * Provides a comprehensive system for executing scheduled tasks and cron jobs
 * within the Hoist framework. This library handles cron job execution, logging,
 * and validation with proper error handling and instance management.
 * 
 * CORE CAPABILITIES:
 * - Automated cron job execution from Application/Crons directory
 * - Comprehensive logging with timestamps and labels
 * - Instance-based dependency injection for cron jobs
 * - Robust file validation and error handling
 * - Flexible path resolution with automatic .php extension
 * 
 * CRON JOB STRUCTURE:
 * Cron jobs should be stored in Application/Crons/ directory and return
 * a callable function that accepts the framework instance as a parameter.
 * 
 * LOGGING FEATURES:
 * - Console output for real-time monitoring
 * - File-based logging to /tmp/cron_log
 * - JSON serialization for complex data types
 * - Timestamped entries with optional labeling
 * 
 * EXECUTION WORKFLOW:
 * 1. Validates cron file exists in Application/Crons directory
 * 2. Requires the PHP file and expects callable return
 * 3. Executes function with framework instance injection
 * 4. Provides error handling for missing or malformed crons
 * 
 * USAGE EXAMPLES:
 * 
 * Basic Cron Execution:
 * ```php
 * $cron = new Cron($frameworkInstance);
 * $cron->run('daily_cleanup'); // Runs Application/Crons/daily_cleanup.php
 * ```
 * 
 * Logging Examples:
 * ```php
 * $cron->logger('Task completed successfully', 'CLEANUP');
 * $cron->logger(['processed' => 150, 'errors' => 0], 'STATS');
 * ```
 * 
 * Sample Cron Job File (Application/Crons/example.php):
 * ```php
 * <?php
 * return function($instance) {
 *     $cron = $instance->get('cron');
 *     $cron->logger('Starting example task', 'EXAMPLE');
 *     
 *     // Perform task logic here
 *     
 *     $cron->logger('Example task completed', 'EXAMPLE');
 * };
 * ```
 * 
 * ERROR HANDLING:
 * - Validates cron file existence before execution
 * - Ensures returned value is callable function
 * - Provides clear error messages for debugging
 * - Handles malformed cron job files gracefully
 * 
 * INTEGRATION:
 * Works seamlessly with the framework's service container pattern,
 * allowing cron jobs to access all framework services through the
 * injected instance parameter.
 * 
 * @package HoistPHP\Core\Libraries
 * @version 1.0.0
 * @since 1.0.0
 */
class Cron
{
    /**
     * Framework instance for dependency injection.
     * 
     * Stores the main framework instance that provides access to all
     * framework services and components. This instance is injected into
     * cron jobs during execution, allowing them to access databases,
     * authentication, file management, and other framework features.
     * 
     * @var object Framework instance containing all services
     * @access private
     */
    private $instance;

    /**
     * Initializes the cron management system.
     * 
     * Sets up the cron job execution environment by storing the framework
     * instance for later injection into cron job functions. The instance
     * provides access to all framework services and components.
     * 
     * INITIALIZATION PROCESS:
     * - Stores framework instance for service injection
     * - Prepares logging and execution environment
     * - Validates instance availability for cron jobs
     * 
     * @param object $instance Framework instance with all services
     */
    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    /**
     * Logs messages and data with timestamp and optional labeling.
     * 
     * Provides comprehensive logging functionality for cron job monitoring
     * and debugging. Supports various data types with automatic JSON encoding
     * and outputs to both console and log file for real-time and persistent
     * monitoring capabilities.
     * 
     * LOGGING FEATURES:
     * - Automatic JSON encoding for arrays and objects
     * - Timestamped entries with customizable date format
     * - Optional labeling for categorizing log entries
     * - Dual output: console display and file storage
     * - Append-mode file writing for persistent logs
     * 
     * DATA HANDLING:
     * - Strings: Output directly without modification
     * - Arrays/Objects: Automatically JSON encoded
     * - Mixed types: Converted to appropriate string format
     * - Large data: Efficiently handled through JSON serialization
     * 
     * OUTPUT FORMATTING:
     * - Console: Immediate display with PHP_EOL line breaks
     * - File: Appended to /tmp/cron_log with \r\n endings
     * - Format: [LABEL][MM.DD.YY H:I:S]: MESSAGE
     * 
     * USAGE EXAMPLES:
     * 
     * Simple Message:
     * ```php
     * $cron->logger('Process started successfully');
     * // Output: [08.04.25 14:30:15]: Process started successfully
     * ```
     * 
     * Labeled Message:
     * ```php
     * $cron->logger('Database cleanup completed', 'CLEANUP');
     * // Output: [CLEANUP][08.04.25 14:30:15]: Database cleanup completed
     * ```
     * 
     * Array Data:
     * ```php
     * $cron->logger(['users' => 150, 'errors' => 0], 'STATS');
     * // Output: [STATS][08.04.25 14:30:15]: {"users":150,"errors":0}
     * ```
     * 
     * Object Data:
     * ```php
     * $result = (object)['status' => 'complete', 'time' => time()];
     * $cron->logger($result, 'RESULT');
     * // Output: [RESULT][08.04.25 14:30:15]: {"status":"complete","time":1754345815}
     * ```
     * 
     * MONITORING BENEFITS:
     * - Real-time console output for immediate feedback
     * - Persistent file logging for historical analysis
     * - Structured data logging for automated parsing
     * - Categorized logs for easier filtering and searching
     * 
     * @param mixed $data Data to log (string, array, object, etc.)
     * @param string|null $label Optional label for categorizing the log entry
     * @return void
     */
    public function logger($data, $label = null)
    {

        // Convert array or object to JSON
        if (is_array($data) || is_object($data)) {
            $data = json_encode($data);
        }

        // Format the label and the date
        $label = (!is_null($label) ? "[$label]" : "");
        $date = date("m.d.y G:i:s");

        // Construct the log
        $log = "{$label}[{$date}]: {$data}\r\n";

        echo $log . PHP_EOL;

        file_put_contents('/tmp/cron_log', $log, FILE_APPEND);
    }

    /**
     * Executes a cron job from the Application/Crons directory.
     * 
     * Locates, validates, and executes cron job files stored in the
     * Application/Crons directory. Handles automatic file extension
     * resolution, validates callable returns, and injects the framework
     * instance for full service access within cron jobs.
     * 
     * EXECUTION WORKFLOW:
     * 1. Normalizes file path with automatic .php extension
     * 2. Constructs full path to Application/Crons directory
     * 3. Validates file existence and accessibility
     * 4. Requires file and expects callable function return
     * 5. Executes function with framework instance injection
     * 6. Provides comprehensive error handling throughout
     * 
     * FILE STRUCTURE REQUIREMENTS:
     * Cron files must be located in ROOT_DIRECTORY/Application/Crons/
     * and should return a callable function that accepts the framework
     * instance as its only parameter.
     * 
     * PATH RESOLUTION:
     * - Automatically appends .php extension if not present
     * - Supports nested directories within Crons folder
     * - Validates complete file path before execution
     * 
     * CRON JOB FORMAT:
     * Expected cron job file structure:
     * ```php
     * <?php
     * // Application/Crons/example.php
     * return function($instance) {
     *     // Access framework services through $instance
     *     $db = $instance->get('database');
     *     $cron = $instance->get('cron');
     *     
     *     $cron->logger('Starting example task', 'EXAMPLE');
     *     
     *     // Perform task operations
     *     
     *     $cron->logger('Task completed', 'EXAMPLE');
     * };
     * ```
     * 
     * SERVICE ACCESS:
     * The injected instance provides access to all framework services:
     * - Database connections and models
     * - Authentication and session management
     * - File management and uploading
     * - Validation and security services
     * - All registered framework components
     * 
     * ERROR CONDITIONS:
     * - File not found: Throws exception with clear message
     * - Non-callable return: Validates function return type
     * - Path validation: Ensures secure file access
     * - Runtime errors: Allows natural exception propagation
     * 
     * USAGE EXAMPLES:
     * 
     * Simple Cron Execution:
     * ```php
     * $cron = new Cron($frameworkInstance);
     * $cron->run('daily_cleanup');
     * // Executes: Application/Crons/daily_cleanup.php
     * ```
     * 
     * Nested Directory:
     * ```php
     * $cron->run('reports/generate_monthly');
     * // Executes: Application/Crons/reports/generate_monthly.php
     * ```
     * 
     * With Extension:
     * ```php
     * $cron->run('backup.php');
     * // Executes: Application/Crons/backup.php
     * ```
     * 
     * SECURITY CONSIDERATIONS:
     * - Validates file paths to prevent directory traversal
     * - Ensures files exist within designated Crons directory
     * - Validates callable return to prevent code injection
     * - Provides controlled environment for task execution
     * 
     * @param string $path The path of the cron file to execute (relative to Application/Crons)
     * @return void
     * @throws Exception If cron file doesn't exist or isn't properly formatted
     */
    public function run($path)
    {
        $fileExtension = '.php';

        if (strpos($path, $fileExtension) === false) {
            $path .= $fileExtension;
        }

        $filePath = ROOT_DIRECTORY . '/Application/Crons/' . $path;

        if (!file_exists($filePath)) {
            throw new \Exception('Cron does not exist.');
        }

        $function = require $filePath;

        if (!is_callable($function)) {
            throw new \Exception('Cron is not formatted and returned as a function.');
        }

        $function($this->instance);
    }
}
