<?php

/**
 * ===================================================================
 * HOIST PHP FRAMEWORK - INPUT VALIDATION SERVICE
 * ===================================================================
 * 
 * The Validation class provides a comprehensive and fluent input validation
 * system for the Hoist PHP framework. It supports method chaining for
 * easy validation rule composition and provides detailed error reporting
 * with automatic redirect handling for failed validations.
 * 
 * Key Features:
 * - Fluent Interface: Method chaining for clean validation syntax
 * - Comprehensive Rules: Email, length, numeric, empty checks
 * - Error Aggregation: Collects all validation errors for detailed feedback
 * - Automatic Redirects: Can automatically handle validation failures
 * - Field Naming: Contextual error messages with field names
 * - Framework Integration: Built-in session and redirect support
 * 
 * Usage Pattern:
 * $result = $validation
 *     ->set($email)
 *     ->isNotEmpty()
 *     ->isValidEmail()
 *     ->validate('Email', [
 *         'onFail' => ['redirect' => '/contact']
 *     ]);
 * 
 * @package HoistPHP\Core\Libraries
 * @author  Hoist PHP Framework Team
 * @version 1.0.0
 */
class Validation
{
    // ===============================================================
    // VALIDATION STATE PROPERTIES
    // ===============================================================

    /**
     * Current string value being validated.
     * 
     * Holds the input value that is currently being processed through
     * the validation chain. This value is set using the set() method
     * and is used by all validation rule methods.
     * 
     * @var string|null The string value under validation
     */
    private $string = null;

    /**
     * Collection of validation errors for the current validation chain.
     * 
     * Accumulates error messages as validation rules fail. Each failed
     * rule adds a descriptive error message to this array. The array
     * is reset after each validate() call.
     * 
     * @var array Array of error message strings
     */
    private $errors = [];

    /**
     * Application instance for accessing framework services.
     * 
     * Provides access to session management, redirect functionality,
     * and other framework services needed for validation processing
     * and error handling.
     * 
     * @var Instance Framework application instance
     */
    private $instance;

    // ===============================================================
    // CONSTRUCTOR AND INITIALIZATION
    // ===============================================================

    /**
     * Initializes the validation service with framework dependencies.
     * 
     * Sets up the validation service with access to the main application
     * instance, enabling integration with session management, redirects,
     * and other framework services for comprehensive validation handling.
     * 
     * @param Instance $instance Main application service container
     */
    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    // ===============================================================
    // VALIDATION EXECUTION AND RESULT PROCESSING
    // ===============================================================

    /**
     * Executes validation and returns results with optional error handling.
     * 
     * This method completes the validation chain by:
     * 1. Evaluating all accumulated validation errors
     * 2. Formatting error messages with field context
     * 3. Handling validation failures with optional redirects
     * 4. Resetting validation state for next use
     * 5. Returning structured validation results
     * 
     * The method supports automatic error handling through the events
     * parameter, including redirects with flash messages for failed
     * validations.
     * 
     * Return Structure:
     * {
     *   'valid': boolean,    // True if no errors, false if validation failed
     *   'errors': array      // Array of formatted error messages
     * }
     * 
     * Events Configuration:
     * - onFail.redirect: URL to redirect to on validation failure
     *   When specified, failed validations automatically redirect with
     *   error messages stored in session flash data
     * 
     * Usage Examples:
     * 
     * // Basic validation
     * $result = $validation->set($email)->isValidEmail()->validate('Email');
     * if (!$result['valid']) {
     *     // Handle errors: $result['errors']
     * }
     * 
     * // Validation with automatic redirect
     * $validation->set($password)
     *     ->isNotEmpty()
     *     ->isMinLength(8)
     *     ->validate('Password', [
     *         'onFail' => ['redirect' => '/register']
     *     ]);
     * 
     * @param string|null $fieldName Display name for the field (used in error messages)
     * @param array $events Event configuration for handling validation results
     * @return array Validation result with 'valid' status and 'errors' array
     * @throws Exception If fieldName is not provided
     */
    public function validate($fieldName = null, $events = [])
    {

        if (!$fieldName) {
            throw new \Exception('Validate requires a field name');
        }

        $events = array_merge([
            'onFail' => [
                'redirect' => false
            ]
        ], $events);

        $res = [
            'valid' => count($this->errors) ? false : true,
            'errors' => $this->errors
        ];

        foreach ($res['errors'] as $key => $error) {
            $res['errors'][$key] = $fieldName . ': ' . $error;
        }

        $this->__reset();

        if ($res['valid'] === false) {
            if (isset($events['onFail'])) {
                if (is_array($events['onFail'])) {
                    if (isset($events['onFail']['redirect'])) {
                        if ($events['onFail']['redirect']) {
                            $this->instance->session->setFlashData('error', $res['errors']);
                            $this->instance->redirect($events['onFail']['redirect']);
                        }
                    }
                }
            }
        }

        return $res;
    }

    // ===============================================================
    // VALIDATION CHAIN SETUP
    // ===============================================================

    /**
     * Sets the value to be validated and starts the validation chain.
     * 
     * This method initializes the validation process by setting the
     * value that will be tested against validation rules. It must be
     * called before any validation rule methods.
     * 
     * The method supports method chaining, allowing you to immediately
     * chain validation rules:
     * 
     * $validation->set($email)->isNotEmpty()->isValidEmail()->validate('Email');
     * 
     * @param string|null $string The value to validate
     * @return Validation Returns self for method chaining
     * @throws Exception If no string is provided
     */
    public function set($string = null)
    {
        if (is_null($string)) {
            throw new \Exception('Validation::set requires a string to be passed.');
        }

        $this->string = $string;
        return $this;
    }

    // ===============================================================
    // VALIDATION RULE METHODS
    // ===============================================================

    /**
     * Validates that the value is a properly formatted email address.
     * 
     * Uses PHP's built-in FILTER_VALIDATE_EMAIL filter to ensure the
     * value conforms to standard email format requirements. This filter
     * performs comprehensive email validation including:
     * - Basic format checking (user@domain.tld)
     * - Character validation
     * - Domain format validation
     * 
     * Error Message: "Must be a valid email."
     * 
     * Common Use Cases:
     * - User registration email validation
     * - Contact form email validation
     * - Newsletter subscription validation
     * - Password reset email validation
     * 
     * @return Validation Returns self for method chaining
     */
    public function isValidEmail()
    {
        if (!filter_var($this->string, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Must be a valid email.';
        }

        return $this;
    }

    /**
     * Validates that the value is not empty.
     * 
     * Uses PHP's empty() function to check if the value contains any
     * meaningful content. This catches various "empty" states including:
     * - Empty strings ("")
     * - Null values
     * - Zero values (0, "0")
     * - Boolean false
     * - Empty arrays
     * 
     * Error Message: "Must not be empty."
     * 
     * Common Use Cases:
     * - Required field validation
     * - Ensuring user input is provided
     * - Mandatory form field checking
     * - API parameter validation
     * 
     * @return Validation Returns self for method chaining
     */
    public function isNotEmpty()
    {
        if (empty($this->string)) {
            $this->errors[] = 'Must not be empty.';
        }

        return $this;
    }

    /**
     * Validates that the value is numeric.
     * 
     * Uses PHP's is_numeric() function to check if the value is a number
     * or a numeric string. Accepts various numeric formats including:
     * - Integers (123, -456)
     * - Floats (123.45, -67.89)
     * - Scientific notation (1.23e4)
     * - Hexadecimal (0xFF)
     * 
     * Error Message: "Must be a number."
     * 
     * Common Use Cases:
     * - Price and currency validation
     * - Quantity and count validation
     * - Mathematical input validation
     * - ID and reference number validation
     * 
     * @return Validation Returns self for method chaining
     */
    public function isNumeric()
    {
        if (!is_numeric($this->string)) {
            $this->errors[] = 'Must be a number.';
        }

        return $this;
    }

    /**
     * Validates that the value meets minimum length requirements.
     * 
     * Checks that the string length (character count) is at least the
     * specified minimum. Uses strlen() for accurate byte-level counting.
     * 
     * Error Message: "Must be at least X characters long."
     * 
     * Common Use Cases:
     * - Password strength requirements
     * - Username length validation
     * - Comment minimum length
     * - Security code validation
     * - Description field requirements
     * 
     * @param int $length Minimum required character count
     * @return Validation Returns self for method chaining
     */
    public function isMinLength($length)
    {
        if (strlen($this->string) < $length) {
            $this->errors[] = 'Must be at least ' . $length . ' characters long.';
        }

        return $this;
    }

    /**
     * Validates that the value does not exceed maximum length limits.
     * 
     * Checks that the string length (character count) does not exceed
     * the specified maximum. Uses strlen() for accurate byte-level counting.
     * 
     * Error Message: "Must be a max of X characters long."
     * 
     * Common Use Cases:
     * - Database field size limits
     * - Form input constraints
     * - Social media post limits
     * - SMS message length validation
     * - Title and headline validation
     * 
     * @param int $length Maximum allowed character count
     * @return Validation Returns self for method chaining
     */
    public function isMaxLength($length)
    {
        if (strlen($this->string) > $length) {
            $this->errors[] = 'Must be a max of ' . $length . ' characters long.';
        }

        return $this;
    }

    // ===============================================================
    // INTERNAL STATE MANAGEMENT
    // ===============================================================

    /**
     * Resets validation state for the next validation chain.
     * 
     * Clears the current validation context by resetting:
     * - Current string value being validated
     * - Accumulated error messages
     * 
     * This method is automatically called after each validate() call
     * to ensure clean state for subsequent validations. Should not
     * be called manually during normal usage.
     * 
     * @return void
     */
    private function __reset()
    {
        $this->string = null;
        $this->errors = [];
    }
}
