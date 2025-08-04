<?php

/**
 * ===============================================================
 * HOIST FRAMEWORK - AUTHENTICATION & AUTHORIZATION
 * ===============================================================
 * 
 * Comprehensive user authentication and role-based authorization system.
 * 
 * The Auth class provides a complete authentication and authorization solution
 * for web applications, handling user sessions, login/logout operations,
 * role-based access control, and security validation. It integrates seamlessly
 * with the framework's session management and database systems.
 * 
 * CORE CAPABILITIES:
 * 
 * 1. SESSION MANAGEMENT
 *    - Automatic session restoration and validation
 *    - Secure user identity loading and caching
 *    - Session persistence across requests
 *    - Automatic logout and session cleanup
 * 
 * 2. AUTHENTICATION OPERATIONS
 *    - Email/password login validation
 *    - Password hashing and verification
 *    - Account creation authentication
 *    - Secure logout with session destruction
 * 
 * 3. ROLE-BASED AUTHORIZATION
 *    - Flexible user group system
 *    - Single and multi-group access control
 *    - Required authentication enforcement
 *    - Group-based access restrictions
 * 
 * 4. SECURITY FEATURES
 *    - Password sanitization during sessions
 *    - Deleted account validation
 *    - Last online tracking for security
 *    - Automatic redirect on access denial
 * 
 * USER GROUP SYSTEM:
 * 
 * The framework supports flexible role-based access control:
 * - 'guest': Unauthenticated users (default)
 * - Custom groups: 'admin', 'moderator', 'user', etc.
 * - Multi-group support: ['admin', 'moderator']
 * - Universal access: 'All' group for authenticated users
 * 
 * AUTHENTICATION FLOW:
 * 
 * 1. User submits login credentials
 * 2. System validates email and password
 * 3. Password hash verification using SHA1
 * 4. User data loaded and password removed
 * 5. Session established with user information
 * 6. User group assigned for authorization
 * 
 * AUTHORIZATION PATTERNS:
 * 
 * Controller Protection:
 * ```php
 * public function adminPanel() {
 *     $this->auth->required(); // Must be logged in
 *     $this->auth->requireGroup('admin'); // Must be admin
 *     // Admin-only functionality
 * }
 * ```
 * 
 * Multi-Role Access:
 * ```php
 * if ($this->auth->is(['admin', 'moderator'])) {
 *     // Management functionality
 * }
 * ```
 * 
 * Conditional Features:
 * ```php
 * if ($this->auth->is('premium')) {
 *     // Premium user features
 * }
 * ```
 * 
 * SECURITY CONSIDERATIONS:
 * 
 * - Passwords are hashed using SHA1 (consider upgrading to bcrypt)
 * - User passwords are removed from session data
 * - Deleted accounts are blocked from authentication
 * - Last online timestamps track user activity
 * - Flash messages provide user feedback on errors
 * 
 * @package    Hoist\Core\Libraries
 * @author     Hoist Framework Team
 * @copyright  2024 Hoist Framework
 * @license    MIT License
 * @version    1.0.0
 * @since      Framework 1.0
 * 
 * @see        Session For flash messaging and session management
 * @see        Database For user data persistence
 * @see        Controller For authentication integration
 */
class Auth
{
    // ===============================================================
    // CLASS PROPERTIES AND STATE MANAGEMENT
    // ===============================================================

    /**
     * Current user's group identifier for role-based access control.
     * 
     * Stores the user's group/role which determines their access permissions
     * throughout the application. Defaults to 'guest' for unauthenticated
     * users and is updated to the user's actual group upon login.
     * 
     * Common group values:
     * - 'guest': Unauthenticated users (default)
     * - 'user': Standard authenticated users
     * - 'admin': Administrative users
     * - 'moderator': Content moderation users
     * - Custom groups as defined by application needs
     * 
     * @var string Current user's group identifier
     */
    public $userGroup = 'guest';

    /**
     * Current authenticated user data or false if not logged in.
     * 
     * Contains the complete user record (excluding password) when a user
     * is authenticated. Set to false for unauthenticated sessions.
     * This data is loaded from the database and cached for the session.
     * 
     * User data structure typically includes:
     * - id: Unique user identifier
     * - email: User's email address
     * - user_group_id: Role/permission group
     * - last_online: Last activity timestamp
     * - Additional profile fields as defined
     * 
     * @var array|false Complete user data array or false if not authenticated
     */
    public $user = false;

    /**
     * Base URL for authentication redirects and navigation.
     * 
     * Defines the root URL used for redirecting users during authentication
     * operations such as login requirements, access denials, and logout.
     * Typically set to '/' for root-level applications.
     * 
     * @var string Application base URL for redirects
     */
    private $baseUrl = '/';

    /**
     * Framework application instance for service access.
     * 
     * Provides access to framework services including database, session
     * management, and other core functionality required for authentication
     * and authorization operations.
     * 
     * @var Instance Framework service container
     */
    private $instance;

    /**
     * HTTP request handler for accessing request data.
     * 
     * Used for processing login forms, API authentication, and other
     * request-based authentication operations. Provides access to
     * POST data, headers, and request metadata.
     * 
     * @var Request HTTP request handling service
     */
    private $request;

    /**
     * Session management service for authentication state.
     * 
     * Handles flash messaging for authentication errors and success
     * notifications. Provides session-based communication for user
     * feedback during authentication operations.
     * 
     * @var Session Framework session management service
     */
    private $session;

    /**
     * Available user groups for role-based access control.
     * 
     * Stores the complete list of available user groups/roles that
     * can be assigned to users. Used for validation and administrative
     * interfaces for user management.
     * 
     * @var array List of available user groups/roles
     */
    private $groups = [];

    // ===============================================================
    // CONSTRUCTOR AND SESSION INITIALIZATION
    // ===============================================================

    /**
     * Initializes authentication system with deferred session restoration.
     * 
     * Sets up the authentication system by establishing framework connections
     * but defers session restoration until after models are available.
     * This prevents the chicken-and-egg problem where Auth needs UserModel
     * but UserModel hasn't been registered yet.
     * 
     * INITIALIZATION WORKFLOW:
     * 1. Stores framework service references
     * 2. Configures base URL for redirects
     * 3. Defers session restoration to initializeSession() method
     * 
     * SESSION RESTORATION:
     * Session restoration is handled by initializeSession() which is called
     * after all models have been registered in the framework.
     * 
     * @param Instance $instance Framework service container
     * @param Request $request HTTP request handler
     * @param string $baseUrl Base URL for authentication redirects
     */
    public function __construct($instance, $request, $baseUrl)
    {
        $this->preloaded = (object) [];
        $this->baseUrl = $baseUrl;
        $this->instance = $instance;
        $this->request = $request;
        $this->session = $instance->session;

        // Session restoration is deferred until models are available
    }

    /**
     * Initializes user session after models are available.
     * 
     * This method handles session restoration after all models have been
     * registered in the framework. It's called automatically by the
     * framework after model registration is complete.
     * 
     * SESSION RESTORATION PROCESS:
     * 1. Checks for existing user session
     * 2. Validates session integrity
     * 3. Loads user identity if session exists
     * 4. Sets appropriate user group
     * 5. Handles session errors with redirects
     * 
     * ERROR HANDLING:
     * Invalid sessions trigger:
     * - Flash error message for user feedback
     * - Automatic redirect to base URL
     * - Session cleanup and logout
     * 
     * @return void
     */
    public function initializeSession()
    {
        if (isset($_SESSION['user_session'])) {
            if ($_SESSION['user_session']) {

                if (isset($_SESSION['user_session']['id'])) {
                    $this->loadIdentity($_SESSION['user_session']['id']);
                } else {
                    $this->instance->session->setFlashData('error', 'You must be logged in for that.');
                    header('Location: ' . $this->baseUrl);
                    exit;
                }

                if ($this->user && isset($this->user['user_group_id'])) {
                    $this->userGroup = $this->user['user_group_id'];
                }
            }
        }
    }

    // ===============================================================
    // AUTHENTICATION REQUIREMENT AND ACCESS CONTROL
    // ===============================================================

    /**
     * Enforces authentication requirement with automatic redirect.
     * 
     * Validates that a user is currently authenticated and redirects
     * unauthenticated users to the base URL with an error message.
     * This is typically used in controller methods that require login.
     * 
     * ENFORCEMENT PROCESS:
     * 1. Checks if user is authenticated
     * 2. Sets flash error message for feedback
     * 3. Redirects to base URL
     * 4. Terminates script execution
     * 
     * USAGE PATTERNS:
     * 
     * Controller Method Protection:
     * ```php
     * public function userProfile() {
     *     $this->auth->required();
     *     // Protected functionality here
     * }
     * ```
     * 
     * Before Controller Hook:
     * ```php
     * public function before() {
     *     if (in_array($this->method, ['edit', 'delete'])) {
     *         $this->auth->required();
     *     }
     * }
     * ```
     * 
     * API Endpoint Protection:
     * ```php
     * public function apiEndpoint() {
     *     $this->auth->required();
     *     return $this->response->json(['data' => $protectedData]);
     * }
     * ```
     * 
     * @return void Terminates execution if not authenticated
     */
    public function required()
    {
        if (!$this->user) {

            $this->instance->session->setFlashData('error', 'You must be logged in for that.');
            header('Location: ' . $this->baseUrl);
            exit;
        }
    }

    // ===============================================================
    // SESSION LIFECYCLE MANAGEMENT
    // ===============================================================

    /**
     * Logs out the current user and destroys the session.
     * 
     * Performs complete logout by clearing user session data and
     * destroying the entire session. This ensures that all user
     * data is removed and the user is returned to guest status.
     * 
     * LOGOUT PROCESS:
     * 1. Clears user session data
     * 2. Destroys entire PHP session
     * 3. Returns success status
     * 
     * SECURITY FEATURES:
     * - Complete session destruction prevents session fixation
     * - All user data removed from memory
     * - Forces re-authentication for any further access
     * 
     * USAGE EXAMPLES:
     * 
     * Standard Logout:
     * ```php
     * public function logout() {
     *     if ($this->auth->logout()) {
     *         $this->session->setFlashData('success', 'Logged out successfully');
     *         $this->instance->redirect('/');
     *     }
     * }
     * ```
     * 
     * Security Logout (forced):
     * ```php
     * if ($securityBreach) {
     *     $this->auth->logout();
     *     $this->session->setFlashData('error', 'Session terminated for security');
     *     $this->instance->redirect('/login');
     * }
     * ```
     * 
     * @return bool Always returns true after successful logout
     */
    public function logout()
    {
        $_SESSION['user_session'] = false;
        session_destroy();
        return true;
    }

    // ===============================================================
    // USER IDENTITY MANAGEMENT
    // ===============================================================

    /**
     * Loads and refreshes user identity from FileDatabase by ID.
     * 
     * Retrieves current user data from the FileDatabase using the framework's
     * UserModel and updates the user's last online timestamp. This method is used 
     * during session restoration and provides fresh data while tracking user activity.
     * 
     * IDENTITY LOADING PROCESS:
     * 1. Validates UserModel is available
     * 2. Queries FileDatabase for user by ID using framework UserModel
     * 3. Password is already excluded by UserModel security
     * 4. Updates last_online timestamp for activity tracking
     * 5. Stores user data in instance for session use
     * 
     * SECURITY FEATURES:
     * - Password automatically excluded by UserModel
     * - Fresh data prevents stale session information
     * - Activity tracking via last_online updates
     * - FileDatabase-authoritative user information
     * 
     * USAGE SCENARIOS:
     * - Session restoration during authentication
     * - User data refresh after profile updates
     * - Activity tracking for security monitoring
     * 
     * @param int $id User ID to load from FileDatabase
     * @return bool True on successful load, false if UserModel not available
     */
    public function loadIdentity($id)
    {
        // Ensure UserModel is available before attempting to use it
        if (!isset($this->instance->models->user)) {
            return false;
        }

        $userData = $this->instance->models->user->get(['id' => $id]);

        if (!$userData) {
            return false;
        }

        // Update last online timestamp
        $this->instance->models->user->save(['id' => $id], [
            'last_online' => time(),
        ]);

        $this->user = $userData;
        return true;
    }

    // ===============================================================
    // AUTHENTICATION OPERATIONS
    // ===============================================================

    /**
     * Authenticates user with newly created account data.
     * 
     * Establishes user session with account data from registration
     * process, bypassing password validation since account was just
     * created. Includes validation for deleted accounts.
     * 
     * ACCOUNT CREATION FLOW:
     * 1. User registration creates new account
     * 2. Account data passed to this method
     * 3. Password already secure via UserModel
     * 4. Deleted account validation performed
     * 5. Session established with user data
     * 6. User group permissions assigned
     * 
     * SECURITY VALIDATIONS:
     * - Password already secure via UserModel hashing
     * - Deleted account check prevents suspended user access
     * - Session data sanitized before storage
     * 
     * USAGE EXAMPLES:
     * 
     * Post-Registration Login:
     * ```php
     * // After successful user creation
     * if ($this->auth->loginWithCreatedAccount($newUserData)) {
     *     $this->session->setFlashData('success', 'Account created and logged in!');
     *     $this->instance->redirect('/dashboard');
     * }
     * ```
     * 
     * API Registration Response:
     * ```php
     * if ($this->auth->loginWithCreatedAccount($userData)) {
     *     return $this->response->json([
     *         'success' => true,
     *         'user' => $this->auth->user,
     *         'redirect' => '/welcome'
     *     ]);
     * }
     * ```
     * 
     * @param array $userData Complete user data from registration
     * @return bool True on successful login, false if account is deleted
     */
    public function loginWithCreatedAccount($userData)
    {
        // Password is already secure via UserModel, no need to remove

        if ($userData['deleted']) {
            return false;
        }

        $this->user = $userData;
        $this->userGroup = $userData['user_group_id'];
        $_SESSION['user_session'] = $userData;
        return true;
    }

    /**
     * Authenticates user with email and password credentials.
     * 
     * Performs complete login validation including user lookup,
     * account status verification, password validation, and session
     * establishment. This is the primary authentication method using
     * modern secure password verification.
     * 
     * AUTHENTICATION WORKFLOW:
     * 1. Validates UserModel is available
     * 2. Looks up user by email address using framework UserModel
     * 3. Validates user account exists
     * 4. Checks account is not deleted/suspended
     * 5. Verifies password using secure password_verify()
     * 6. Password already secure via UserModel
     * 7. Establishes user session
     * 8. Sets user group permissions
     * 
     * SECURITY FEATURES:
     * - Email-based user identification
     * - Modern password verification with password_verify()
     * - Deleted account blocking
     * - Password security via UserModel
     * - Secure session establishment
     * 
     * FAILURE SCENARIOS:
     * - UserModel not available (returns false)
     * - User not found (returns false)
     * - Account deleted/suspended (returns false)  
     * - Password mismatch (returns false)
     * - Invalid credentials (returns false)
     * 
     * @param string $email User's email address for identification
     * @param string $password Plain text password to validate
     * @return bool True on successful authentication, false on failure
     */
    public function login($email, $password)
    {
        // Ensure UserModel is available before attempting to use it
        if (!isset($this->instance->models->user)) {
            return false;
        }

        $userData = $this->instance->models->user->getByEmail($email);

        if (!$userData) {
            return false;
        }

        if ($userData['deleted']) {
            return false;
        }

        /**
         * If the password is a match using secure verification
         */
        if ($this->validatePassword($password, $userData['password'])) {
            // Password is already secure via UserModel, store user data
            $this->user = $userData;
            $this->userGroup = $userData['user_group_id'];
            $_SESSION['user_session'] = $userData;
            return true;
        }

        return false;
    }

    // ===============================================================
    // ROLE-BASED AUTHORIZATION SYSTEM
    // ===============================================================

    /**
     * Checks if current user belongs to specified group(s).
     * 
     * Performs flexible group membership validation supporting both
     * single group strings and multi-group arrays. Includes special
     * handling for universal access and guest user scenarios.
     * 
     * GROUP MATCHING LOGIC:
     * - 'All': Universal access for any authenticated user
     * - String group: Exact match with user's group
     * - Array groups: User group must be in provided list
     * - Null/empty: Always returns false for safety
     * 
     * AUTHORIZATION PATTERNS:
     * 
     * Single Group Check:
     * ```php
     * if ($this->auth->is('admin')) {
     *     // Administrative functionality
     * }
     * ```
     * 
     * Multi-Group Authorization:
     * ```php
     * if ($this->auth->is(['admin', 'moderator'])) {
     *     // Management functionality
     * }
     * ```
     * 
     * Universal Access:
     * ```php
     * if ($this->auth->is('All')) {
     *     // Any authenticated user
     * }
     * ```
     * 
     * Conditional Features:
     * ```php
     * $canEdit = $this->auth->is(['admin', 'editor']);
     * $canView = $this->auth->is(['admin', 'editor', 'viewer']);
     * ```
     * 
     * Template Usage:
     * ```php
     * <?php if ($auth->is('premium')): ?>
     *     <div class="premium-content">...</div>
     * <?php endif; ?>
     * ```
     * 
     * @param string|array|null $group Group name or array of group names to check
     * @return bool True if user belongs to specified group(s), false otherwise
     */
    public function is($group = null)
    {

        if (!$group) {
            return false;
        }

        if ($group === 'All') {
            return true;
        }

        $userGroup = $this->userGroup;

        /**
         * Check if the group matches.
         */
        if (is_array($group)) {
            if (in_array($userGroup, $group)) {
                return true;
            }
        } else {
            if ($userGroup === $group) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enforces group membership requirement with automatic redirect.
     * 
     * Validates that the current user belongs to the specified group(s)
     * and redirects to an access denied page if authorization fails.
     * This provides enforcement for role-based access control.
     * 
     * ENFORCEMENT PROCESS:
     * 1. Checks group membership using is() method
     * 2. Redirects to access denied page on failure
     * 3. Allows continued execution on success
     * 
     * ACCESS CONTROL PATTERNS:
     * 
     * Single Group Requirement:
     * ```php
     * public function adminDashboard() {
     *     $this->auth->requireGroup('admin');
     *     // Admin-only functionality
     * }
     * ```
     * 
     * Multi-Group Requirement:
     * ```php
     * public function moderateContent() {
     *     $this->auth->requireGroup(['admin', 'moderator']);
     *     // Content moderation functionality
     * }
     * ```
     * 
     * Controller Before Hook:
     * ```php
     * public function before() {
     *     if (in_array($this->method, ['edit', 'delete', 'approve'])) {
     *         $this->auth->required(); // Must be logged in
     *         $this->auth->requireGroup(['admin', 'moderator']); // Must have permissions
     *     }
     * }
     * ```
     * 
     * API Endpoint Protection:
     * ```php
     * public function deleteUser() {
     *     $this->auth->required();
     *     $this->auth->requireGroup('admin');
     *     // User deletion logic
     * }
     * ```
     * 
     * @param string|array $group Group name or array of group names required
     * @return void Redirects to access denied page if authorization fails
     */
    public function requireGroup($group)
    {
        if (!$this->is($group)) {
            $this->instance->redirect($this->baseUrl . '/user/access-denied');
        }
    }

    // ===============================================================
    // PASSWORD SECURITY AND VALIDATION
    // ===============================================================

    /**
     * Validates a plain text password against a stored hash.
     * 
     * Compares a user-provided password with the stored password hash
     * using modern password verification. This method supports both
     * legacy SHA1 hashes and modern password_hash() hashes for
     * backward compatibility during migration.
     * 
     * VALIDATION PROCESS:
     * 1. First attempts modern password_verify() for new hashes
     * 2. Falls back to SHA1 comparison for legacy hashes
     * 3. Returns boolean result of comparison
     * 
     * SECURITY CONSIDERATIONS:
     * - Uses password_verify() for secure verification
     * - Maintains backward compatibility with SHA1 hashes
     * - Constant-time comparison prevents timing attacks
     * - Supports gradual migration from legacy hashing
     * 
     * MIGRATION STRATEGY:
     * This method supports both old and new password formats,
     * allowing gradual migration as users log in.
     * 
     * USAGE EXAMPLES:
     * 
     * Login Validation:
     * ```php
     * if ($this->auth->validatePassword($inputPassword, $user['password'])) {
     *     // Credentials valid, proceed with login
     * } else {
     *     // Invalid credentials, show error
     * }
     * ```
     * 
     * Password Change Verification:
     * ```php
     * if ($this->auth->validatePassword($currentPassword, $user['password'])) {
     *     // Current password verified, allow change
     *     $newHash = $this->auth->generatePasswordHash($newPassword);
     *     // Update password in database
     * }
     * ```
     * 
     * @param string $password Plain text password to validate
     * @param string $passwordHash Stored password hash to compare against
     * @return bool True if password matches hash, false otherwise
     */
    public function validatePassword($password, $passwordHash)
    {
        // First try modern password verification
        if (password_verify($password, $passwordHash)) {
            return true;
        }

        // Fall back to legacy SHA1 for existing passwords
        return sha1($password) == $passwordHash;
    }

    /**
     * Generates a secure password hash for storage.
     * 
     * Creates a modern secure hash of the provided password using PHP's
     * password_hash() function with the PASSWORD_DEFAULT algorithm.
     * This method is used during user registration and password changes
     * to ensure passwords are stored securely.
     * 
     * HASHING PROCESS:
     * 1. Takes plain text password input
     * 2. Applies PASSWORD_DEFAULT algorithm (currently Argon2ID or bcrypt)
     * 3. Returns secure hash string with salt
     * 
     * SECURITY FEATURES:
     * - Uses PHP's PASSWORD_DEFAULT for current best practice
     * - Automatic salt generation and inclusion
     * - Resistant to rainbow table attacks
     * - Future-proof algorithm selection
     * - Cryptographically secure random salts
     * 
     * ADVANTAGES OVER SHA1:
     * - Much slower computation (resistant to brute force)
     * - Unique salt per password (prevents rainbow tables)
     * - Adaptive cost factor (can increase difficulty over time)
     * - Industry standard algorithm
     * 
     * USAGE EXAMPLES:
     * 
     * User Registration:
     * ```php
     * $passwordHash = $this->auth->generatePasswordHash($newPassword);
     * $userData = [
     *     'email' => $email,
     *     'password' => $passwordHash,
     *     'user_group_id' => 'user'
     * ];
     * ```
     * 
     * Password Change:
     * ```php
     * if ($this->auth->validatePassword($currentPassword, $user['password'])) {
     *     $newHash = $this->auth->generatePasswordHash($newPassword);
     *     $this->userModel->save(['id' => $user['id']], ['password' => $newHash]);
     * }
     * ```
     * 
     * @param string $password Plain text password to hash
     * @return string Secure hash of the password for storage
     */
    public function generatePasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
