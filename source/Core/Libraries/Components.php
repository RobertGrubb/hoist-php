<?php

/**
 * ===================================================================
 * HOIST PHP FRAMEWORK - DYNAMIC COMPONENT RENDERING SYSTEM
 * ===================================================================
 * 
 * The Components library provides a powerful dynamic component loading and 
 * rendering system for creating reusable UI elements throughout the Hoist PHP
 * Framework. This class automatically discovers and registers PHP components 
 * from the Application Components directory, enabling modular and maintainable
 * view architecture.
 * 
 * Key Features:
 * - Automatic Component Discovery: Scans /Components directory recursively
 * - Dynamic Registration: Components auto-register on framework initialization
 * - Closure-Based Architecture: Flexible component definitions with full framework access
 * - Data Injection: Pass dynamic data to components for customization
 * - Hierarchical Organization: Support for nested component directory structures
 * - Error Handling: Graceful fallback for missing or invalid components
 * - Performance Optimized: Components cached after initial discovery
 * 
 * Component Architecture:
 * Components are PHP files that return closures accepting ($instance, $data) parameters.
 * They should be placed in APPLICATION_DIRECTORY/Components/ with full subdirectory support.
 * Each component has access to the complete framework instance and can utilize all services.
 * 
 * Usage Examples:
 * 
 * // Basic component rendering
 * echo $components->render('Button', ['text' => 'Click Me', 'class' => 'primary']);
 * 
 * // Form input component with validation (using dot notation)
 * echo $components->render('Form.Input', [
 *     'name' => 'email', 
 *     'type' => 'email',
 *     'required' => true,
 *     'value' => $oldInput['email'] ?? ''
 * ]);
 * 
 * // Complex component with framework services
 * echo $components->render('User.Card', [
 *     'user' => $user,
 *     'permissions' => $auth->getPermissions()
 * ]);
 * 
 * // Nested component rendering (Admin/Dashboard/Stats.php)
 * echo $components->render('Admin.Dashboard.Stats', ['metrics' => $dashboardData]);
 * 
 * Component File Structure:
 * ```php
 * // /Components/button.php
 * <?php
 * return function($instance, $data) {
 *     $text = $data['text'] ?? 'Button';
 *     $class = $data['class'] ?? 'default';
 *     
 *     return "<button class=\"btn btn-{$class}\">{$text}</button>";
 * };
 * ```
 * 
 * Integration with Framework:
 * Components have full access to framework services including authentication,
 * database operations, validation, session management, and more through the
 * $instance parameter, making them powerful and flexible building blocks.
 * 
 * @package HoistPHP\Core\Libraries
 * @author  Hoist PHP Framework Team
 * @version 2.0.0
 * @since   Framework 1.0
 */
class Components
{

    // =========================================================================
    // PROPERTIES
    // =========================================================================

    /**
     * Application instance reference
     * 
     * Stores reference to the main application instance, providing components
     * access to all application services including views, database, etc.
     * 
     * @var object Application instance
     */
    public $instance;

    /**
     * Registered component closures
     * 
     * Array of component name => closure mappings. Components are automatically
     * discovered and registered during initialization from the Components directory.
     * 
     * @var array<string, callable> Component name to closure mapping
     */
    private $components = [];

    // =========================================================================
    // INITIALIZATION
    // =========================================================================

    /**
     * Initializes component system with automatic component discovery
     * 
     * Sets up the component system by storing the application instance
     * and triggering automatic discovery and registration of all available
     * components from the Components directory structure.
     * 
     * @param object $instance Application instance for component access
     */
    public function __construct($instance)
    {
        $this->instance = $instance;
        $this->register();
    }

    // =========================================================================
    // COMPONENT MANAGEMENT
    // =========================================================================

    /**
     * Discovers and registers all available components
     * 
     * Scans the Application Components directory recursively for PHP files
     * and registers them as components. Each component file should return
     * a callable (closure) that accepts ($instance, $data) parameters.
     * 
     * Registration Process:
     * 1. Scan /Components directory recursively for *.php files
     * 2. Include each component file
     * 3. Verify returned value is callable
     * 4. Register component using dot notation (Form.Card for Form/Card.php)
     * 5. Skip non-callable components with graceful error handling
     * 
     * Component File Requirements:
     * - Must return a callable/closure
     * - Closure should accept ($instance, $data) parameters
     * - Directory structure becomes dot notation (Form/Card.php = Form.Card)
     * 
     * @return void
     */
    private function register()
    {
        $this->scanDirectory(APPLICATION_DIRECTORY . "/Components");
    }

    /**
     * Recursively scans directory for component files
     * 
     * @param string $directory Directory to scan
     * @param string $namespace Current namespace prefix for dot notation
     * @return void
     */
    private function scanDirectory($directory, $namespace = '')
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $directory . DIRECTORY_SEPARATOR . $item;

            if (is_dir($fullPath)) {
                // Recursively scan subdirectories
                $subNamespace = $namespace ? $namespace . '.' . $item : $item;
                $this->scanDirectory($fullPath, $subNamespace);
            } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'php') {
                // Register component file
                $component = include $fullPath;
                if (!is_callable($component)) {
                    continue;
                }

                // Create component name using dot notation
                $componentName = pathinfo($item, PATHINFO_FILENAME);
                $fullComponentName = $namespace ? $namespace . '.' . $componentName : $componentName;

                $this->components[$fullComponentName] = $component;
            }
        }
    }

    /**
     * Renders a registered component with provided data
     * 
     * Executes a registered component closure with the application instance
     * and provided data array. Components receive full access to application
     * services through the instance parameter.
     * 
     * Component Function Signature:
     * ```php
     * function($instance, $data) {
     *     // Access application services: $instance->view, $instance->db, etc.
     *     // Use data: $data['property']
     *     return "rendered HTML";
     * }
     * ```
     * 
     * Component Naming Examples:
     * - Card.php -> 'Card'
     * - Form/Input.php -> 'Form.Input'
     * - Admin/User/Card.php -> 'Admin.User.Card'
     * 
     * @param string $component Component name in dot notation (e.g., 'Form.Card')
     * @param array $data Data array to pass to component function
     * @return mixed Component output (typically HTML string) or false if component not found
     */
    public function render($component, $data = [])
    {
        if (!isset($this->components[$component])) {
            return false;
        }

        return $this->components[$component]($this->instance, $data);
    }

    /**
     * Get list of all registered components
     * 
     * Returns an array of all registered component names, useful for
     * debugging and development to see what components are available.
     * 
     * @return array Array of registered component names
     */
    public function getRegisteredComponents()
    {
        return array_keys($this->components);
    }

    /**
     * Check if a component is registered
     * 
     * @param string $component Component name in dot notation
     * @return bool True if component exists, false otherwise
     */
    public function exists($component)
    {
        return isset($this->components[$component]);
    }
}
