<?php

/**
 * ===================================================================
 * HOIST PHP FRAMEWORK - BASE CONTROLLER CLASS
 * ===================================================================
 * 
 * The Controller class serves as the base class for all application
 * controllers in the Hoist PHP framework. It provides common functionality,
 * dependency injection, and lifecycle management for all controllers.
 * 
 * Key Features:
 * - Dependency Injection: Automatic injection of framework services
 * - Service Access: Direct access to commonly used services
 * - Lifecycle Hooks: Before and after method execution hooks
 * - Request Handling: Built-in request and response management
 * - Session Management: Direct access to session and flash messages
 * - Model Access: Convenient access to all registered models
 * 
 * Usage:
 * All application controllers should extend this base class:
 * 
 * class UserController extends Controller {
 *     public function login() {
 *         $email = $this->request->post('email');
 *         $user = $this->models->user->getByEmail($email);
 *         // ... controller logic
 *     }
 * }
 * 
 * @package HoistPHP\Core\Libraries
 * @author  Hoist PHP Framework Team
 * @version 1.0.0
 */
class Controller
{
    // ===============================================================
    // DEPENDENCY INJECTION PROPERTIES
    // ===============================================================

    /** @var Instance Main application instance container */
    public $instance;

    /** @var object Container of all registered application models */
    public $models;

    /** @var Request HTTP request handling service */
    public $request;

    /** @var Session Session management and flash message service */
    public $session;

    /** @var Router URL routing and parameter extraction service */
    public $router;

    // ===============================================================
    // CONSTRUCTOR AND DEPENDENCY INJECTION
    // ===============================================================

    /**
     * Initializes the controller with framework dependencies.
     * 
     * Automatically injects commonly used framework services into
     * controller properties for convenient access. This follows the
     * dependency injection pattern to make services easily accessible
     * without requiring manual service location.
     * 
     * Injected Services:
     * - $this->instance: Full application container for accessing any service
     * - $this->request: HTTP request data and parameter handling
     * - $this->models: All registered models for data operations
     * - $this->session: Session management and flash messages
     * - $this->router: URL routing and parameter extraction
     * 
     * Additional services can be accessed via $this->instance->serviceName
     * when needed (auth, database, view, etc.).
     * 
     * @param Instance $instance The main application service container
     */
    public function __construct($instance)
    {
        $this->instance = $instance;
        $this->request = $instance->request;
        $this->models = $instance->models;
        $this->session = $instance->session;
        $this->router = $instance->router;
    }

    // ===============================================================
    // CONTROLLER LIFECYCLE HOOKS
    // ===============================================================

    /**
     * Pre-execution hook called before the main controller method.
     * 
     * This method is automatically called by the router before executing
     * the main controller method. It can be overridden in child controllers
     * to implement common functionality such as:
     * 
     * - Authentication checks
     * - Permission validation
     * - Request preprocessing
     * - Common data loading
     * - Security validations
     * - Logging and monitoring
     * 
     * Example usage:
     * public function before() {
     *     // Require authentication for all methods in this controller
     *     $this->instance->auth->required();
     *     
     *     // Load common data needed by all methods
     *     $this->commonData = $this->loadCommonData();
     * }
     * 
     * @return void
     */
    public function before()
    {
        // Override in child controllers for pre-execution logic
    }

    /**
     * Post-execution hook called after the main controller method.
     * 
     * This method is automatically called by the router after executing
     * the main controller method. It handles cleanup operations and can
     * be overridden in child controllers for additional functionality.
     * 
     * Default behavior:
     * - Performs application cleanup via $instance->cleanup()
     * - Updates flash message session data
     * - Clears temporary data
     * 
     * Additional functionality can include:
     * - Response post-processing
     * - Logging and analytics
     * - Cache management
     * - Performance monitoring
     * - Resource cleanup
     * 
     * Example usage:
     * public function after() {
     *     parent::after(); // Always call parent cleanup
     *     
     *     // Additional cleanup or logging
     *     $this->logRequest();
     * }
     * 
     * @return void
     */
    public function after()
    {
        $this->instance->cleanup();
    }
}
