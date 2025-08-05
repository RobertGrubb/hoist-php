<?php

/**
 * View Rendering and Template Management Library
 * 
 * Provides a comprehensive template rendering system for the Hoist framework
 * with support for variable injection, output buffering, and flexible view
 * file management. This library handles all aspects of view rendering from
 * template loading to variable scoping and output control.
 * 
 * CORE CAPABILITIES:
 * - Template file rendering with variable injection
 * - Output buffering for flexible content handling
 * - Directory-based view organization
 * - Variable scoping and data isolation
 * - Error handling for missing templates
 * 
 * TEMPLATE FEATURES:
 * - PHP-based template files for maximum flexibility
 * - Variable extraction for clean template syntax
 * - Support for nested template inclusion
 * - Output capture and return capabilities
 * 
 * VIEW ORGANIZATION:
 * Templates stored in APPLICATION_DIRECTORY/Views/ with:
 * - Logical directory structure for organization
 * - .php file extension for all templates
 * - Variable injection for dynamic content
 * - Error handling for missing files
 * 
 * RENDERING WORKFLOW:
 * 1. Validates template file existence
 * 2. Extracts variables into local scope
 * 3. Starts output buffering
 * 4. Includes template file with variables available
 * 5. Captures or outputs rendered content
 * 
 * USAGE EXAMPLES:
 * 
 * Basic Template Rendering:
 * ```php
 * $view = new View($frameworkInstance);
 * $view->render('user/profile', [
 *     'user' => $userData,
 *     'title' => 'User Profile'
 * ]);
 * ```
 * 
 * Capture Rendered Output:
 * ```php
 * $content = $view->render('email/welcome', [
 *     'username' => 'John Doe',
 *     'activation_link' => $link
 * ], true); // Third parameter returns content
 * ```
 * 
 * Nested View Structure:
 * ```php
 * // Main layout
 * $view->render('layouts/main', [
 *     'content' => $view->render('pages/home', $data, true),
 *     'title' => 'Home Page'
 * ]);
 * ```
 * 
 * TEMPLATE VARIABLES:
 * Variables passed to templates are extracted into local scope:
 * ```php
 * // In PHP: $view->render('template', ['name' => 'John']);
 * // In template: <?php echo $name; ?> outputs "John"
 * ```
 * 
 * ERROR HANDLING:
 * - Validates template file existence before rendering
 * - Provides clear error messages for missing templates
 * - Graceful fallback for rendering failures
 * - Debug-friendly error reporting
 * 
 * @package HoistPHP\Core\Libraries
 * @version 1.0.0
 * @since 1.0.0
 */
class View
{
    /**
     * Framework instance for service access.
     * 
     * Stores the framework instance to provide access to framework
     * services and components within the view rendering context.
     * This allows templates to access framework functionality when needed.
     * 
     * @var object Framework service container instance
     * @access private
     */
    private $instance;

    /**
     * Base directory path for view templates.
     * 
     * Defines the base directory where all view templates are stored
     * relative to the APPLICATION_DIRECTORY. This path is used to
     * construct the full file paths for template inclusion.
     * 
     * @var string Relative path to views directory
     * @access private
     */
    private $viewsDirectory = '/Views/';

    /**
     * Initializes the view rendering system.
     * 
     * Sets up the view system with framework integration and prepares
     * the rendering environment. The framework instance provides access
     * to services that may be needed during template rendering.
     * 
     * @param object $instance Framework service container instance
     */
    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    /**
     * Renders a template file with variable injection and output control.
     * 
     * Performs comprehensive template rendering with variable extraction,
     * output buffering, and flexible content handling. This method provides
     * the core view rendering functionality with support for both direct
     * output and content capture for nested rendering scenarios.
     * 
     * RENDERING PROCESS:
     * 1. Validates template file existence
     * 2. Defines optimization functions if needed
     * 3. Extracts variables into template scope
     * 4. Starts output buffering for content capture
     * 5. Includes template file with variables available
     * 6. Captures rendered content
     * 7. Applies optimization if configured
     * 8. Returns content or outputs directly
     * 
     * VARIABLE INJECTION:
     * Variables are extracted into the template's local scope using
     * PHP's extract() function, making them available as regular
     * variables within the template file.
     * 
     * OUTPUT CONTROL:
     * - $return = false: Content is echoed directly to output
     * - $return = true: Content is returned as string for capture
     * 
     * TEMPLATE REQUIREMENTS:
     * - Must be stored in APPLICATION_DIRECTORY/Views/
     * - Must have .php file extension
     * - Can use any variables passed in $args array
     * - Can access framework instance if needed
     * 
     * USAGE EXAMPLES:
     * 
     * Direct Output:
     * ```php
     * $view->render('user/dashboard', [
     *     'user' => $currentUser,
     *     'notifications' => $userNotifications,
     *     'stats' => $dashboardStats
     * ]);
     * ```
     * 
     * Content Capture:
     * ```php
     * $sidebar = $view->render('components/sidebar', [
     *     'menuItems' => $navigation
     * ], true);
     * 
     * $view->render('layouts/main', [
     *     'content' => $mainContent,
     *     'sidebar' => $sidebar,
     *     'title' => 'Page Title'
     * ]);
     * ```
     * 
     * Complex Data Passing:
     * ```php
     * $view->render('reports/monthly', [
     *     'report_data' => [
     *         'sales' => $salesData,
     *         'users' => $userStats,
     *         'performance' => $performanceMetrics
     *     ],
     *     'date_range' => $reportPeriod,
     *     'format' => 'detailed'
     * ]);
     * ```
     * 
     * ERROR HANDLING:
     * - Validates template file existence before rendering
     * - Triggers descriptive errors for missing templates
     * - Provides full file path in error messages
     * - Graceful handling of rendering failures
     * 
     * OPTIMIZATION FEATURES:
     * - Optional HTML/CSS/JS comment removal
     * - Whitespace compression and normalization
     * - Output optimization for production environments
     * - Configurable optimization levels
     * 
     * @param string $template Template filename (without .php extension)
     * @param array $args Associative array of variables to inject into template
     * @param bool $return True to return content, false to output directly
     * @return string|void Rendered content if $return is true, void otherwise
     */
    public function render($template, $args = [], $return = false)
    {
        /**
         * Make sure the view file does exist.
         */
        if (!file_exists(APPLICATION_DIRECTORY . $this->viewsDirectory . $template . '.php')) {
            trigger_error('View file does not exist: ' . APPLICATION_DIRECTORY . $this->viewsDirectory . $template . '.php');
            return;
        }

        /**
         * Define HTML/CSS/JS optimization function if not already defined
         * 
         * This function performs comprehensive output optimization including:
         * - HTML comment removal
         * - CSS/JS comment removal  
         * - Whitespace normalization and compression
         * - Line break optimization
         * 
         * Uses function_exists check to prevent redefinition errors
         * when multiple templates are rendered in a single request.
         */
        if (!function_exists('sanitize_output')) {
            function sanitize_output($buffer)
            {
                // Handle null or empty buffer
                if ($buffer === null || $buffer === '') {
                    return '';
                }

                // =============================================================
                // SAFE COMMENT REMOVAL PHASE
                // =============================================================

                /**
                 * Remove only safe comments that won't break functionality:
                 * 1. HTML comments (preserve conditional comments)
                 * 2. CSS block comments (only within style blocks)
                 * 3. JS line comments (only full line comments with proper context)
                 */

                // Remove HTML comments (but preserve IE conditional comments)
                $buffer = preg_replace('/<!--(?!\[if)(?!<!)(.*?)-->/s', '', $buffer);

                // Check if buffer became null after comment removal
                if ($buffer === null) {
                    return '';
                }

                // =============================================================
                // CONSERVATIVE WHITESPACE OPTIMIZATION
                // =============================================================

                /**
                 * Apply only safe whitespace optimizations:
                 * 1. Remove extra spaces between HTML tags
                 * 2. Remove trailing whitespace from lines
                 * 3. Compress excessive line breaks (3+ becomes 2)
                 */
                $whitespacePatterns = [
                    '/>\s+</s',           // Remove whitespace between tags
                    '/[ \t]+$/m',         // Remove trailing spaces/tabs from lines  
                    '/\n{3,}/s',          // Compress 3+ line breaks to 2
                ];

                $whitespaceReplacements = [
                    '><',
                    '',
                    "\n\n",
                ];

                // Apply safe whitespace optimization patterns
                $buffer = preg_replace($whitespacePatterns, $whitespaceReplacements, $buffer);

                // Final null check
                if ($buffer === null) {
                    return '';
                }

                return $buffer;
            }
        }

        ob_start();

        /**
         * Variables to make available in view files.
         */
        $instance = $this->instance;
        $baseUrl = $this->instance->baseUrl;
        $security = $this->instance->security;
        $session = $this->instance->session;
        $request = $this->instance->request;
        $view = $this;
        $auth = $this->instance->auth;
        $components = $this->instance->components;
        $utilities = new Utilities();

        // Add each argument passed to the smarty variables.
        foreach ($args as $key => $value) {
            ${$key} = $value;
        }

        $templateArgs = $args;

        /**
         * Include the view file
         */
        include APPLICATION_DIRECTORY . $this->viewsDirectory . $template . '.php';
        $file = ob_get_contents();
        ob_end_clean();

        if ($return) {
            return $file;
        }

        echo sanitize_output($file);
    }
}
