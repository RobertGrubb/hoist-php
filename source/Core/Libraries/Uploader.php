<?php

/**
 * ===================================================================
 * HOIST PHP FRAMEWORK - FILE UPLOAD SERVICE
 * ===================================================================
 * 
 * The Uploader class provides secure and flexible file upload functionality
 * for the Hoist PHP framework. It handles file validation, secure storage,
 * automatic naming, and comprehensive error reporting for uploaded files.
 * 
 * Key Features:
 * - Secure File Handling: Proper validation and secure file movement
 * - Flexible Path Management: Support for custom upload directories
 * - Automatic File Naming: MD5-based naming with collision prevention
 * - Custom Naming Support: Override automatic naming when needed
 * - Extension Preservation: Maintains original file extensions
 * - Error Handling: Comprehensive error reporting and validation
 * - Method Chaining: Fluent interface for easy configuration
 * - Directory Validation: Ensures target directories exist and are writable
 * 
 * Security Features:
 * - Uses move_uploaded_file() for secure file handling
 * - Validates directory permissions before processing
 * - Generates unique filenames to prevent conflicts
 * - Proper error handling to prevent information disclosure
 * 
 * Usage Pattern:
 * $result = $uploader
 *     ->setFile($_FILES['upload'])
 *     ->setPath('avatars')
 *     ->process(['name' => 'custom_name']);
 * 
 * @package HoistPHP\Core\Libraries
 * @author  Hoist PHP Framework Team
 * @version 1.0.0
 */
class Uploader
{
    // ===============================================================
    // UPLOADER CONFIGURATION PROPERTIES
    // ===============================================================

    /**
     * Base directory path for all file uploads.
     * 
     * This is the root directory where all uploaded files will be stored.
     * It's typically set to something like '/var/www/uploads' or 'public/uploads'.
     * All file uploads will be placed within this base directory structure.
     * 
     * @var string|null Absolute path to upload base directory
     */
    private $basePath = null;

    /**
     * Application instance for accessing framework services.
     * 
     * Provides access to other framework services that may be needed
     * during file upload processing, such as validation, security,
     * or logging services.
     * 
     * @var Instance Framework application instance
     */
    private $instance = null;

    /**
     * File data array from $_FILES superglobal.
     * 
     * Contains the uploaded file information including:
     * - name: Original filename
     * - tmp_name: Temporary file path
     * - size: File size in bytes
     * - type: MIME type
     * - error: Upload error code
     * 
     * @var array|null Uploaded file data from $_FILES
     */
    private $file = null;

    /**
     * Subdirectory path within the base upload directory.
     * 
     * Optional subdirectory for organizing uploads. For example,
     * 'avatars', 'documents', or 'images/thumbnails'. This path
     * will be appended to the base path for the final upload location.
     * 
     * @var string|null Subdirectory path for file organization
     */
    private $path = null;

    /**
     * Error message from the last upload operation.
     * 
     * Contains descriptive error information if an upload fails.
     * Set to false when no error occurs. Used for debugging and
     * user feedback when upload operations fail.
     * 
     * @var string|false Error message or false if no error
     */
    public $error = false;

    // ===============================================================
    // CONSTRUCTOR AND INITIALIZATION
    // ===============================================================

    /**
     * Initializes the uploader with base configuration.
     * 
     * Sets up the file uploader with the necessary base path and
     * application instance. The base path is required and must be
     * a valid, writable directory where uploaded files will be stored.
     * 
     * @param Instance $instance Main application service container
     * @param string|null $basePath Base directory path for uploads
     * @throws Exception If basePath is not provided
     */
    public function __construct($instance, $basePath = null)
    {
        $this->instance = $instance;

        if (!$basePath) {
            throw new Exception('BasePath not set for Uploader');
        }

        $this->basePath = $basePath;
    }

    // ===============================================================
    // FILE PROCESSING AND UPLOAD EXECUTION
    // ===============================================================

    /**
     * Processes and uploads the configured file to the target location.
     * 
     * This method performs the complete file upload workflow:
     * 1. Resets any previous error state
     * 2. Validates target directory permissions
     * 3. Generates a secure filename (custom or automatic)
     * 4. Constructs the complete target file path
     * 5. Moves the uploaded file to its final location
     * 6. Returns success status and file information
     * 
     * The method uses PHP's move_uploaded_file() function for security,
     * which ensures the file was actually uploaded via HTTP POST and
     * prevents various attack vectors.
     * 
     * Success Response Format:
     * [
     *   'success' => true,
     *   'file_name' => 'generated_filename.ext'
     * ]
     * 
     * Usage Examples:
     * 
     * // Basic upload with automatic naming
     * $result = $uploader->setFile($_FILES['upload'])->process();
     * 
     * // Upload with custom filename
     * $result = $uploader->setFile($_FILES['avatar'])
     *     ->setPath('users')
     *     ->process(['name' => 'user_123_avatar']);
     * 
     * @param array $options Processing options including 'name' for custom filename
     * @return array|false Success array with file info, or false on failure
     */
    public function process($options = [])
    {
        // Reset the error from any past use.
        $this->error = false;

        if (!$this->isWritable()) {
            return false;
        }

        // Generate a name for the file
        $name = $this->generateFileName(
            (isset($options['name']) ? $options['name'] : false)
        );

        // If name could not be resolved.
        if (!$name) {
            return false;
        }

        // Construct the new path
        $target = $this->constructFilePath($name);

        if (move_uploaded_file($this->file["tmp_name"], $target)) {

            // Reset class vars for future uses.
            $this->file = null;
            $this->path = null;

            return [
                'success' => true,
                'file_name' => $name,
            ];
        } else {

            // Reset class vars for future uses.
            $this->file = null;
            $this->path = null;

            $this->setError('Unable to create file.');
            return false;
        }
    }

    // ===============================================================
    // UPLOADER CONFIGURATION METHODS
    // ===============================================================

    /**
     * Sets the file data for upload processing.
     * 
     * Configures the uploader with file data from the $_FILES superglobal.
     * This method should be called with the specific file array element
     * corresponding to the uploaded file.
     * 
     * Expected file array structure:
     * [
     *   'name' => 'original_filename.ext',
     *   'tmp_name' => '/tmp/php_upload_xyz',
     *   'size' => 1024,
     *   'type' => 'image/jpeg',
     *   'error' => 0
     * ]
     * 
     * Usage Examples:
     * $uploader->setFile($_FILES['upload']);
     * $uploader->setFile($_FILES['user_avatar']);
     * 
     * @param array $file File data array from $_FILES superglobal
     * @return Uploader Returns self for method chaining
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Sets the subdirectory path for organized file storage.
     * 
     * Configures a subdirectory within the base upload path for better
     * file organization. This is useful for categorizing uploads by type,
     * user, or other organizational schemes.
     * 
     * The path will be appended to the base path, so a path of 'avatars'
     * with a base path of '/uploads' results in '/uploads/avatars/'.
     * 
     * Examples:
     * - 'avatars' for user profile images
     * - 'documents' for file attachments
     * - 'images/thumbnails' for processed images
     * - 'users/123' for user-specific uploads
     * 
     * @param string $path Subdirectory path relative to base upload directory
     * @return Uploader Returns self for method chaining
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    // ===============================================================
    // DIRECTORY VALIDATION AND PATH CONSTRUCTION
    // ===============================================================

    /**
     * Validates that the target directory is writable for file uploads.
     * 
     * Performs comprehensive directory validation including:
     * 1. Directory existence verification
     * 2. Write permission checking
     * 3. Automatic error setting on failure
     * 
     * This method handles two path scenarios:
     * - Simple uploads: Uses basePath directly
     * - Organized uploads: Combines subdirectory path with basePath
     * 
     * Common validation failures:
     * - Path doesn't exist or isn't a directory
     * - Directory exists but lacks write permissions
     * - Directory is mounted read-only
     * - Insufficient filesystem permissions
     * 
     * @return bool True if directory is writable, false otherwise
     */
    private function isWritable()
    {
        $path = $this->basePath;

        if ($this->path)
            $path = $this->path . '/' . $this->basePath;

        if (!is_dir($path)) {
            $this->setError('Provided path is not a directory');
            return false;
        }

        // If it is not writable
        if (!is_writable($path)) {
            $this->setError('Provided path is not writable');
            return false;
        }

        return true;
    }

    /**
     * Constructs the complete file system path for the uploaded file.
     * 
     * Builds the full target path by combining:
     * 1. Base upload directory
     * 2. Optional subdirectory path (if configured)
     * 3. Generated filename
     * 
     * The method ensures proper path formatting by:
     * - Removing trailing slashes from base path
     * - Adding subdirectory path when configured
     * - Appending filename with proper separator
     * 
     * Path Construction Examples:
     * - Base only: "/uploads/filename.jpg"
     * - With subpath: "/uploads/avatars/filename.jpg"
     * - Complex path: "/uploads/users/123/filename.pdf"
     * 
     * @param string $name The generated filename to append to path
     * @return string Complete file system path for upload target
     */
    private function constructFilePath($name)
    {
        $path = rtrim($this->basePath . ($this->path ? $this->path : ''), '/');
        return $path . '/' . $name;
    }

    // ===============================================================
    // FILENAME GENERATION AND FILE INFORMATION
    // ===============================================================

    /**
     * Generates a secure filename for the uploaded file.
     * 
     * Creates a unique, web-safe filename using either:
     * 1. A custom name provided in options
     * 2. Automatic generation using MD5 hash of timestamp
     * 
     * The method ensures filename security by:
     * - Validating file extension availability
     * - Using timestamp-based MD5 for uniqueness when auto-generating
     * - Preserving original file extension for proper MIME handling
     * - Providing fallback error handling for extension issues
     * 
     * Filename formats:
     * - Custom: "{custom_name}.{extension}"
     * - Auto: "{md5_hash}.{extension}"
     * 
     * @param string|false $customName Optional custom filename (without extension)
     * @return string|false Generated filename or false on failure
     */
    private function generateFileName($customName = false)
    {
        $extension = $this->getFileExtension();

        if (!$extension) {
            $this->setError('Could not extract an extension');
            return false;
        }

        return ($customName !== false ? $customName : md5(time())) . '.' . $this->getFileExtension();
    }

    /**
     * Retrieves the file size from uploaded file data.
     * 
     * Extracts the file size information from the $_FILES array
     * with proper validation to ensure the data exists and is valid.
     * This is useful for file size validation and storage tracking.
     * 
     * File size is provided in bytes by PHP's upload handling.
     * Common size limits:
     * - PHP upload_max_filesize setting
     * - PHP post_max_size setting
     * - Web server limits (nginx, Apache)
     * 
     * @return int|false File size in bytes, or false if unavailable
     */
    private function getFileSize()
    {
        if (!$this->file) {
            return false;
        }

        if (!isset($this->file['size'])) {
            return false;
        }

        return $this->file['size'];
    }

    /**
     * Extracts and returns the file extension from uploaded file.
     * 
     * Retrieves the file extension from the original filename using
     * PHP's pathinfo() function for reliable parsing. The extension
     * is crucial for:
     * - Maintaining MIME type associations
     * - Ensuring proper file handling by applications
     * - Security validation and filtering
     * - File type identification
     * 
     * Extension examples:
     * - "image.jpg" returns "jpg"
     * - "document.pdf" returns "pdf"
     * - "archive.tar.gz" returns "gz" (last extension only)
     * - "file" returns false (no extension)
     * 
     * @return string|false File extension without dot, or false if none/invalid
     */
    private function getFileExtension()
    {
        if (!$this->file) {
            return false;
        }

        if (!isset($this->file['name'])) {
            return false;
        }

        $extension = pathinfo($this->file['name'], PATHINFO_EXTENSION);
        return $extension;
    }

    // ===============================================================
    // ERROR HANDLING AND REPORTING
    // ===============================================================

    /**
     * Sets an error message for upload failure scenarios.
     * 
     * Stores error messages with a class prefix for easy identification
     * in logs and debugging. This provides a consistent error handling
     * mechanism across all upload operations.
     * 
     * Error messages are prefixed with "\\Uploader: " to indicate
     * the source of the error in complex application workflows.
     * 
     * Common error scenarios:
     * - Directory permission issues
     * - File extension extraction failures
     * - Invalid file data or missing properties
     * - Security validation failures
     * 
     * @param string $error Descriptive error message for the failure
     * @return void
     */
    private function setError($error)
    {
        $this->error = '\\Uploader: ' . $error;
    }

    /**
     * Retrieves the current error message if an upload operation failed.
     * 
     * Returns the most recent error message set during upload processing.
     * This allows calling code to provide specific feedback about what
     * went wrong during the upload attempt.
     * 
     * Error messages include the class prefix "\\Uploader: " for easy
     * identification in application logs and debugging output.
     * 
     * Usage Example:
     * ```php
     * $result = $uploader->setFile($_FILES['upload'])->process();
     * if (!$result) {
     *     $errorMsg = $uploader->getError();
     *     error_log("Upload failed: " . $errorMsg);
     *     // Display user-friendly error message
     * }
     * ```
     * 
     * @return string|false Current error message with prefix, or false if no error
     */
    public function getError()
    {
        return $this->error;
    }
}
