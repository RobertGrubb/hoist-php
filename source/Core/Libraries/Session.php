<?php

/**
 * ===============================================================
 * HOIST FRAMEWORK - SESSION MANAGEMENT
 * ===============================================================
 * 
 * Advanced session handling service with flash data and state management.
 * 
 * The Session class provides a robust session management system that extends
 * PHP's native session handling with specialized features for web application
 * development. It offers two distinct data storage mechanisms optimized for
 * different use cases in modern web applications.
 * 
 * CORE CAPABILITIES:
 * 
 * 1. FLASH DATA MANAGEMENT
 *    - Temporary data that persists for exactly one request cycle
 *    - Perfect for status messages, form validation errors, and notifications
 *    - Automatic cleanup after data is consumed
 *    - Request-to-request communication for redirects
 * 
 * 2. STATE DATA PERSISTENCE
 *    - Long-term session storage for user preferences and application state
 *    - Survives multiple requests until explicitly cleared
 *    - Ideal for user settings, shopping carts, and multi-step forms
 *    - Direct session variable management with framework abstraction
 * 
 * 3. FRAMEWORK INTEGRATION
 *    - Seamless integration with validation system for error display
 *    - Automatic initialization during framework bootstrap
 *    - Service container access for cross-component communication
 *    - Consistent API for all session operations
 * 
 * FLASH DATA WORKFLOW:
 * 
 * Flash data follows a precise lifecycle designed for single-use messaging:
 * 1. Data is set during current request processing
 * 2. Data is stored in session for next request
 * 3. Data is available during next request only
 * 4. Data is automatically cleared after consumption
 * 
 * This pattern is essential for:
 * - Post-redirect-get (PRG) pattern implementation
 * - Form validation error display
 * - Success/failure notifications
 * - Temporary user feedback
 * 
 * STATE DATA PATTERNS:
 * 
 * State data provides persistent storage for:
 * - User authentication status and preferences
 * - Shopping cart contents and session data
 * - Multi-step form progress and temporary saves
 * - Application settings and user customizations
 * 
 * USAGE EXAMPLES:
 * 
 * Flash Data for Notifications:
 * ```php
 * // In controller after form processing
 * $session->setFlashData('success', 'Profile updated successfully!');
 * // Redirect to prevent form resubmission
 * 
 * // In view template
 * if ($flash = $session->getFlashData('success')) {
 *     echo "<div class='alert alert-success'>{$flash}</div>";
 * }
 * ```
 * 
 * State Data for User Preferences:
 * ```php
 * // Store user theme preference
 * $session->setStateData('theme', 'dark');
 * 
 * // Retrieve across multiple requests
 * $theme = $session->getStateData('theme') ?: 'light';
 * ```
 * 
 * SECURITY CONSIDERATIONS:
 * 
 * - Session data should never contain sensitive information like passwords
 * - Use HTTPS to protect session cookies from interception
 * - Implement session regeneration for privilege escalation
 * - Consider session timeout for inactive users
 * - Validate and sanitize all session data before use
 * 
 * @package    Hoist\Core\Libraries
 * @author     Hoist Framework Team
 * @copyright  2024 Hoist Framework
 * @license    MIT License
 * @version    1.0.0
 * @since      Framework 1.0
 * 
 * @see        Validation For flash data integration with form validation
 * @see        Controller For session access in application controllers
 * @see        Instance For framework service container integration
 */
class Session
{
    // ===============================================================
    // CLASS PROPERTIES AND SESSION KEYS
    // ===============================================================

    /**
     * Framework application instance for service access.
     * 
     * Provides access to other framework services and maintains
     * consistency across the application lifecycle. Used for
     * logging, configuration access, and cross-service communication.
     * 
     * @var Instance Framework service container
     */
    private $instance;

    /**
     * Current request's flash data retrieved from session.
     * 
     * Contains flash data that was set in the previous request
     * and is available for consumption in the current request.
     * This data will be automatically cleared after this request.
     * 
     * @var array Flash data from previous request
     */
    private $flashData = [];

    /**
     * New flash data to be saved for the next request.
     * 
     * Accumulates flash data set during the current request
     * that will be available in the next request only. This
     * separation ensures proper flash data lifecycle management.
     * 
     * @var array Flash data for next request
     */
    private $newFlashData = [];

    /**
     * Persistent state data for long-term session storage.
     * 
     * Contains application state data that persists across
     * multiple requests until explicitly removed. Used for
     * user preferences, settings, and long-term session state.
     * 
     * @var array Persistent state data
     */
    private $stateData = [];

    /**
     * Session key for flash data storage.
     * 
     * Defines the $_SESSION array key used to store flash data.
     * Using a framework-specific key prevents conflicts with
     * other session data and provides consistent access patterns.
     * 
     * @var string Session key for flash data
     */
    private $flashDataKey = 'SITE_FLASH_DATA';

    /**
     * Session key for state data storage.
     * 
     * Defines the $_SESSION array key used to store persistent
     * state data. Separated from flash data to maintain clear
     * distinction between temporary and persistent session data.
     * 
     * @var string Session key for state data
     */
    private $stateDataKey = 'SITE_STATE_DATA';

    // ===============================================================
    // CONSTRUCTOR AND INITIALIZATION
    // ===============================================================

    /**
     * Initializes session management with data retrieval.
     * 
     * Sets up the session manager by establishing framework
     * connections and loading existing session data. This
     * constructor ensures that both flash and state data
     * are immediately available for use.
     * 
     * Initialization Process:
     * 1. Stores framework instance for service access
     * 2. Retrieves existing flash data from session
     * 3. Loads persistent state data from session
     * 4. Prepares internal state for session operations
     * 
     * @param Instance $instance Framework service container
     */
    public function __construct($instance)
    {
        $this->instance = $instance;
        $this->retrieveFlashData();
        $this->retrieveStateData();
    }

    // ===============================================================
    // FLASH DATA MANAGEMENT
    // ===============================================================

    /**
     * Commits new flash data to session for next request availability.
     * 
     * Updates the session storage with flash data that was set during
     * the current request, making it available for the next request only.
     * This method is typically called automatically during framework
     * shutdown or can be called manually for immediate persistence.
     * 
     * FLASH DATA LIFECYCLE:
     * 1. Data is set with setFlashData() during current request
     * 2. updateFlashData() commits data to $_SESSION for next request
     * 3. Next request retrieves data via retrieveFlashData()
     * 4. Data is consumed and automatically cleared
     * 
     * AUTOMATIC vs MANUAL CALLING:
     * - Framework typically calls this automatically during shutdown
     * - Manual calling useful for testing or special circumstances
     * - Multiple calls are safe - only latest data is preserved
     * 
     * Usage Example:
     * ```php
     * $session->setFlashData('message', 'Data saved successfully');
     * $session->updateFlashData(); // Usually automatic
     * // Data will be available in next request only
     * ```
     * 
     * @return void
     */
    public function updateFlashData()
    {
        $_SESSION[$this->flashDataKey] = $this->newFlashData;
    }

    /**
     * Sets flash data that will be available in the next request only.
     * 
     * Stores temporary data that persists for exactly one request cycle,
     * perfect for status messages, validation errors, and user notifications
     * that need to survive redirects but shouldn't persist indefinitely.
     * 
     * COMMON USE CASES:
     * 
     * Form Validation Errors:
     * ```php
     * $session->setFlashData('errors', [
     *     'email' => 'Invalid email format',
     *     'password' => 'Password too short'
     * ]);
     * ```
     * 
     * Success/Status Messages:
     * ```php
     * $session->setFlashData('success', 'Profile updated successfully!');
     * $session->setFlashData('info', 'Please check your email for verification');
     * ```
     * 
     * Complex Data Structures:
     * ```php
     * $session->setFlashData('form_data', [
     *     'step' => 2,
     *     'completed_fields' => ['name', 'email'],
     *     'next_action' => 'verify_phone'
     * ]);
     * ```
     * 
     * POST-REDIRECT-GET PATTERN:
     * Essential for implementing PRG pattern to prevent form resubmission:
     * 1. Process POST request and set flash data
     * 2. Redirect to GET endpoint
     * 3. Display flash data in new request
     * 4. Data automatically clears after display
     * 
     * @param string $key Unique identifier for the flash data
     * @param mixed $value Data to store (string, array, object, etc.)
     * @return void
     */
    public function setFlashData($key, $value)
    {
        $this->newFlashData[$key] = $value;
    }

    /**
     * Retrieves flash data from the current request cycle.
     * 
     * Accesses flash data that was set in the previous request and is
     * available for consumption in the current request only. After this
     * request completes, the flash data will be automatically cleared.
     * 
     * RETRIEVAL PATTERNS:
     * 
     * Single Value Retrieval:
     * ```php
     * $message = $session->getFlashData('success');
     * if ($message) {
     *     echo "<div class='alert alert-success'>{$message}</div>";
     * }
     * ```
     * 
     * Error Handling:
     * ```php
     * $errors = $session->getFlashData('errors');
     * if ($errors && is_array($errors)) {
     *     foreach ($errors as $field => $error) {
     *         echo "<span class='error'>{$error}</span>";
     *     }
     * }
     * ```
     * 
     * All Flash Data:
     * ```php
     * $allFlash = $session->getFlashData();
     * foreach ($allFlash as $key => $value) {
     *     // Process each flash data item
     * }
     * ```
     * 
     * Conditional Display:
     * ```php
     * // Safe retrieval with default handling
     * $status = $session->getFlashData('status') ?: 'No status available';
     * ```
     * 
     * TEMPLATE INTEGRATION:
     * Flash data is commonly used in view templates:
     * ```php
     * // In view template
     * <?php if ($successMsg = $session->getFlashData('success')): ?>
     *     <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
     * <?php endif; ?>
     * ```
     * 
     * @param string|null $key Flash data key to retrieve, null for all data
     * @return mixed Single value, all flash data array, or false if not found
     */
    public function getFlashData($key = null)
    {
        if (is_null($key)) {
            return $this->flashData;
        }

        if (!isset($this->flashData[$key])) {
            return false;
        }

        return $this->flashData[$key];
    }

    /**
     * Loads flash data from session storage into current request context.
     * 
     * Retrieves flash data that was stored in the previous request and
     * makes it available for the current request. This is an internal
     * method called during session initialization to establish the
     * flash data context for the current request.
     * 
     * INTERNAL PROCESS:
     * 1. Checks if flash data exists in session
     * 2. Loads data into current request context
     * 3. Initializes empty array if no data exists
     * 4. Prepares data for consumption in current request
     * 
     * Note: This is automatically called during __construct() and
     * should not typically be called manually by application code.
     * 
     * @return void
     * @access private
     */
    private function retrieveFlashData()
    {
        if (!isset($_SESSION[$this->flashDataKey])) {
            $this->flashData = [];
            return;
        }

        $this->flashData = $_SESSION[$this->flashDataKey];
        return;
    }

    // ===============================================================
    // STATE DATA MANAGEMENT
    // ===============================================================

    /**
     * Removes persistent state data by key from session storage.
     * 
     * Permanently deletes specific state data from both the local
     * cache and the underlying session storage. This is useful for
     * cleaning up temporary data, clearing user preferences, or
     * implementing logout functionality.
     * 
     * REMOVAL SCENARIOS:
     * 
     * User Preference Cleanup:
     * ```php
     * // Clear theme preference
     * $session->removeStateData('user_theme');
     * ```
     * 
     * Shopping Cart Management:
     * ```php
     * // Clear cart after successful order
     * $session->removeStateData('shopping_cart');
     * $session->removeStateData('cart_totals');
     * ```
     * 
     * Multi-Step Form Cleanup:
     * ```php
     * // Clear form progress after completion
     * $session->removeStateData('form_step');
     * $session->removeStateData('form_data_backup');
     * ```
     * 
     * Security Cleanup:
     * ```php
     * // Clear sensitive temporary data
     * $session->removeStateData('password_reset_token');
     * $session->removeStateData('two_factor_temp');
     * ```
     * 
     * SAFETY FEATURES:
     * - Safe to call on non-existent keys (no errors thrown)
     * - Immediately removes from both memory and session
     * - Permanent removal - data cannot be recovered
     * 
     * @param string $key State data key to remove from session
     * @return void
     */
    public function removeStateData($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Sets persistent state data that survives multiple requests.
     * 
     * Stores data in the session that persists across multiple requests
     * until explicitly removed. Unlike flash data, state data remains
     * available until the session expires or is manually cleared.
     * 
     * PERSISTENCE PATTERNS:
     * 
     * User Preferences:
     * ```php
     * $session->setStateData('language', 'en');
     * $session->setStateData('timezone', 'America/New_York');
     * $session->setStateData('theme', 'dark');
     * ```
     * 
     * Shopping Cart Data:
     * ```php
     * $session->setStateData('cart_items', [
     *     ['id' => 123, 'quantity' => 2, 'price' => 29.99],
     *     ['id' => 456, 'quantity' => 1, 'price' => 49.99]
     * ]);
     * ```
     * 
     * Multi-Step Form Progress:
     * ```php
     * $session->setStateData('registration_step', 3);
     * $session->setStateData('form_data', [
     *     'personal_info' => [...],
     *     'contact_info' => [...],
     *     'preferences' => [...]
     * ]);
     * ```
     * 
     * Application State:
     * ```php
     * $session->setStateData('last_visited_page', '/dashboard');
     * $session->setStateData('search_filters', [
     *     'category' => 'electronics',
     *     'price_range' => [100, 500]
     * ]);
     * ```
     * 
     * AUTOMATIC PERSISTENCE:
     * State data is immediately written to the session, ensuring
     * persistence across requests without requiring manual commits.
     * 
     * @param string $key Unique identifier for the state data
     * @param mixed $value Data to store persistently (any serializable type)
     * @return void
     */
    public function setStateData($key, $value)
    {
        $this->stateData[$key] = $value;
        $_SESSION[$this->stateDataKey] = $this->stateData;
    }

    /**
     * Retrieves persistent state data from session storage.
     * 
     * Accesses state data that persists across multiple requests,
     * providing reliable access to user preferences, application
     * state, and long-term session data throughout the user's
     * interaction with the application.
     * 
     * RETRIEVAL PATTERNS:
     * 
     * Single Value Access:
     * ```php
     * $userTheme = $session->getStateData('theme');
     * $language = $session->getStateData('language') ?: 'en'; // with default
     * ```
     * 
     * Complex Data Structures:
     * ```php
     * $cartItems = $session->getStateData('cart_items');
     * if ($cartItems && is_array($cartItems)) {
     *     foreach ($cartItems as $item) {
     *         // Process cart item
     *     }
     * }
     * ```
     * 
     * All State Data:
     * ```php
     * $allState = $session->getStateData();
     * // Returns all persistent state data as array
     * ```
     * 
     * Conditional Processing:
     * ```php
     * if ($step = $session->getStateData('registration_step')) {
     *     // Continue multi-step process from saved step
     *     showRegistrationStep($step);
     * } else {
     *     // Start new registration process
     *     showRegistrationStep(1);
     * }
     * ```
     * 
     * User Preference Loading:
     * ```php
     * $preferences = [
     *     'theme' => $session->getStateData('theme') ?: 'default',
     *     'language' => $session->getStateData('language') ?: 'en',
     *     'timezone' => $session->getStateData('timezone') ?: 'UTC'
     * ];
     * ```
     * 
     * @param string|null $key State data key to retrieve, null for all data
     * @return mixed Single value, all state data array, or false if not found
     */
    public function getStateData($key = null)
    {
        if (is_null($key)) {
            return $this->stateData;
        }

        if (!isset($this->stateData[$key])) {
            return false;
        }

        return $this->stateData[$key];
    }

    /**
     * Loads persistent state data from session into current context.
     * 
     * Initializes the state data context by retrieving persistent
     * session data that was stored in previous requests. This internal
     * method ensures that all state data is available immediately
     * upon session manager instantiation.
     * 
     * INITIALIZATION PROCESS:
     * 1. Checks for existing state data in session
     * 2. Loads data into current request context
     * 3. Initializes empty array if no data exists
     * 4. Establishes baseline for state data operations
     * 
     * Note: This is automatically called during __construct() and
     * should not typically be called manually by application code.
     * 
     * @return void
     * @access private
     */
    private function retrieveStateData()
    {
        if (!isset($_SESSION[$this->stateDataKey])) {
            $this->stateData = [];
            return;
        }

        $this->stateData = $_SESSION[$this->stateDataKey];
        return;
    }
}
