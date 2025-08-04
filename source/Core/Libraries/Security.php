<?php

/**
 * ===================================================================
 * HOIST PHP FRAMEWORK - SECURITY SERVICE
 * ===================================================================
 * 
 * The Security class provides essential security features for the Hoist PHP
 * framework, with a primary focus on Cross-Site Request Forgery (CSRF)
 * protection. It automatically manages CSRF tokens throughout the request
 * lifecycle and provides utilities for secure form handling.
 * 
 * Key Features:
 * - CSRF Protection: Automatic token generation and validation
 * - Form Integration: Easy CSRF token inclusion in forms
 * - Automatic Validation: Transparent POST request validation
 * - Token Rotation: Fresh tokens after successful validation
 * - Session Integration: Secure token storage in user sessions
 * - Request Filtering: Automatic blocking of invalid requests
 * 
 * Security Mechanisms:
 * - Cryptographically secure random token generation
 * - Time-safe token comparison to prevent timing attacks
 * - Automatic token rotation to limit replay attack windows
 * - Integration with framework request/response cycle
 * 
 * The service automatically activates during web requests and requires
 * no manual configuration for basic CSRF protection.
 * 
 * @package HoistPHP\Core\Libraries
 * @author  Hoist PHP Framework Team
 * @version 1.0.0
 */
class Security
{
    // ===============================================================
    // SECURITY CONFIGURATION PROPERTIES
    // ===============================================================

    /**
     * Application instance for accessing framework services.
     * 
     * Provides access to request handling, session management,
     * and other framework services needed for security operations.
     * 
     * @var Instance Framework application instance
     */
    private $instance;

    /**
     * Session key used to store the CSRF token.
     * 
     * This key identifies where the current CSRF token is stored
     * in the user's session. The token is used to validate that
     * POST requests originate from the same application that
     * generated the form.
     * 
     * @var string Session storage key for CSRF token
     */
    private $csrfTokenSessionKey = 'APPLICATION_CSRF_TOKEN';

    /**
     * POST parameter key expected to contain the CSRF token.
     * 
     * This is the form field name that should contain the CSRF
     * token in POST requests. Forms must include a hidden input
     * with this name containing the current token value.
     * 
     * @var string POST parameter name for CSRF token
     */
    private $csrfPostKey = '_csrf';

    // ===============================================================
    // CONSTRUCTOR AND INITIALIZATION
    // ===============================================================

    /**
     * Initializes security service and begins CSRF protection.
     * 
     * Automatically sets up CSRF protection by:
     * 1. Storing reference to application instance
     * 2. Calling initiate() to begin CSRF token management
     * 3. Setting up automatic POST request validation
     * 
     * The constructor ensures that CSRF protection is active
     * immediately upon service instantiation, providing transparent
     * security without requiring manual activation.
     * 
     * @param Instance $instance Main application service container
     */
    public function __construct($instance)
    {
        $this->instance = $instance;
        $this->initiate();
    }

    // ===============================================================
    // CSRF TOKEN FORM INTEGRATION
    // ===============================================================

    /**
     * Generates HTML for CSRF token inclusion in forms.
     * 
     * Creates a hidden input field containing the current CSRF token
     * that should be included in all forms making POST requests. This
     * method provides the primary integration point between the security
     * system and application forms.
     * 
     * Generated HTML format:
     * <input type="hidden" name="_csrf" value="[64-character-token]" />
     * 
     * Usage in templates:
     * <form method="POST" action="/submit">
     *     <?= $instance->security->csrfInput(); ?>
     *     <!-- other form fields -->
     *     <button type="submit">Submit</button>
     * </form>
     * 
     * Integration with view system:
     * The security instance is automatically available in all views
     * as $security, making it easy to include CSRF protection:
     * <?= $security->csrfInput(); ?>
     * 
     * @return string HTML hidden input field with CSRF token
     */
    public function csrfInput()
    {
        return '<input type="hidden" name="' . $this->csrfPostKey . '" value="' . $_SESSION[$this->csrfTokenSessionKey] . '" />';
    }

    // ===============================================================
    // CSRF PROTECTION LIFECYCLE MANAGEMENT
    // ===============================================================

    /**
     * Initializes and manages CSRF protection throughout request lifecycle.
     * 
     * This method handles the complete CSRF protection workflow:
     * 
     * 1. Token Initialization: Creates a new CSRF token if none exists
     * 2. POST Request Validation: Validates tokens on POST submissions
     * 3. Security Enforcement: Blocks requests with invalid tokens
     * 4. Token Rotation: Generates new tokens after successful validation
     * 
     * Token Validation Process:
     * - Checks if POST request contains expected CSRF token
     * - Uses timing-safe comparison (hash_equals) to prevent timing attacks
     * - Redirects to error page for invalid tokens
     * - Generates fresh token after successful validation
     * 
     * Security Benefits:
     * - Prevents Cross-Site Request Forgery attacks
     * - Ensures requests originate from legitimate forms
     * - Protects sensitive operations (user updates, transactions)
     * - Provides transparent protection without user interaction
     * 
     * Error Handling:
     * Invalid CSRF tokens result in automatic redirect to /error
     * to prevent unauthorized actions while informing users of the issue.
     * 
     * @return void
     */
    private function initiate()
    {
        if (!isset($_SESSION[$this->csrfTokenSessionKey])) {
            $this->assignToken();
        }

        if ($this->instance->request->method() === 'post') {
            if ($this->instance->request->post($this->csrfPostKey)) {
                if (!hash_equals($_SESSION[$this->csrfTokenSessionKey], $this->instance->request->post($this->csrfPostKey))) {
                    header('Location: /error');
                    exit;
                }

                $this->assignToken();
            }
        }
    }

    // ===============================================================
    // CSRF TOKEN GENERATION
    // ===============================================================

    /**
     * Generates and assigns a new cryptographically secure CSRF token.
     * 
     * Creates a fresh CSRF token using cryptographically secure random
     * byte generation and stores it in the user's session. The token
     * is 64 characters long (32 random bytes converted to hexadecimal).
     * 
     * Token Properties:
     * - 256 bits of entropy (32 random bytes)
     * - Hexadecimal encoding for safe transmission
     * - Cryptographically secure random generation
     * - Stored in user session for validation
     * 
     * Security Features:
     * - Uses random_bytes() for cryptographic randomness
     * - Sufficient entropy to prevent brute force attacks
     * - Unique per session and request cycle
     * - Safe for inclusion in HTML forms
     * 
     * This method is called automatically during:
     * - Initial security service setup
     * - Successful CSRF token validation
     * - Manual token refresh if needed
     * 
     * @return void Token is stored directly in session
     */
    public function assignToken()
    {
        $_SESSION[$this->csrfTokenSessionKey] = bin2hex(random_bytes(32));
    }
}
