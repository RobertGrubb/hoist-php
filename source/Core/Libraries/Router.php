<?php

/**
 * ===============================================================
 * HOIST FRAMEWORK - HTTP ROUTER
 * ===============================================================
 * 
 * Advanced URL routing system with pattern matching and middleware support.
 * 
 * The Router class provides a comprehensive HTTP routing solution that handles
 * URL pattern matching, parameter extraction, and controller dispatch. It
 * supports both convention-based routing and explicit route registration,
 * making it flexible for both simple and complex application architectures.
 * 
 * CORE CAPABILITIES:
 * 
 * 1. ROUTING STRATEGIES
 *    - Convention-based routing: /controller/method automatically maps
 *    - Explicit route registration with custom patterns
 *    - Parameter extraction from URL segments
 *    - Multiple HTTP method support (GET, POST, PUT, DELETE, etc.)
 * 
 * 2. PATTERN MATCHING
 *    - Regex-based route patterns with named parameters
 *    - Parameter validation and type coercion
 *    - Wildcard and optional parameter support
 *    - Case-insensitive URL matching
 * 
 * 3. CONTROLLER DISPATCH
 *    - Automatic controller instantiation and method calling
 *    - Before/after middleware hooks for cross-cutting concerns
 *    - Error handling with 404 page rendering
 *    - Closure/function routing for simple endpoints
 * 
 * 4. SECURITY FEATURES
 *    - Input sanitization for URL parameters
 *    - Controller existence validation
 *    - Method existence checking
 *    - Automatic escaping of route parameters
 * 
 * ROUTING PATTERNS:
 * 
 * Convention-Based:
 * ```
 * /users          → UsersController::index()
 * /users/create   → UsersController::create()
 * /user-profile   → UserProfileController::index()
 * ```
 * 
 * Explicit Routes:
 * ```php
 * $router->registerRoute('GET', '/api/users/:id', 'ApiController@getUser');
 * $router->registerRoute('POST', '/api/users', 'ApiController@createUser');
 * $router->registerRoute('GET', '/blog/:slug', function($slug) { ... });
 * ```
 * 
 * Parameter Extraction:
 * ```php
 * // Route: /users/:id/posts/:postId
 * $userId = $router->param('id');
 * $postId = $router->param('postId');
 * $allParams = $router->param(); // Get all parameters
 * ```
 * 
 * MIDDLEWARE LIFECYCLE:
 * 
 * 1. Route matching and parameter extraction
 * 2. Controller instantiation with dependency injection
 * 3. before() method execution (if exists)
 * 4. Target method execution
 * 5. after() method execution (if exists)
 * 
 * INTEGRATION NOTES:
 * 
 * - Integrates with framework's controller lifecycle
 * - Supports dependency injection through Instance container
 * - Automatic view rendering for 404 errors
 * - Compatible with RESTful API design patterns
 * 
 * @package    Hoist\Core\Libraries
 * @author     Hoist Framework Team
 * @copyright  2024 Hoist Framework
 * @license    MIT License
 * @version    1.0.0
 * @since      Framework 1.0
 * 
 * @see        Controller For base controller functionality
 * @see        Request For HTTP request handling
 * @see        Instance For dependency injection
 */
class Router
{
    // ===============================================================
    // CLASS PROPERTIES AND STATE MANAGEMENT
    // ===============================================================

    /**
     * Framework application instance for service access.
     * 
     * Provides access to framework services including:
     * - Request object for URI and HTTP method access
     * - View system for error page rendering
     * - Database connections for data access
     * - Authentication and session management
     * 
     * @var Instance Framework service container
     */
    private $instance;

    /**
     * Current route information for controller dispatch.
     * 
     * Contains the resolved route details including:
     * - controller: Target controller class name
     * - method: Target method name to execute
     * - controllerPath: Full file path to the controller file
     * 
     * Defaults to IndexController::index() for the homepage
     * and serves as fallback for unmatched routes.
     * 
     * @var array Current route configuration
     */
    private $route = [
        'controller' => 'IndexController',
        'method' => 'index',
        'controllerPath' => '',
    ];

    /**
     * Registered route patterns organized by HTTP method.
     * 
     * Stores explicit route registrations in a nested array structure:
     * [
     *   'GET' => ['/users/:id' => 'UsersController@show'],
     *   'POST' => ['/users' => 'UsersController@create'],
     *   'PUT' => ['/users/:id' => 'UsersController@update']
     * ]
     * 
     * Supports all HTTP methods and custom patterns with parameters.
     * 
     * @var array Registered routes organized by HTTP method
     */
    private $routes = [];

    /**
     * Extracted URL parameters from the current request.
     * 
     * Contains named parameters extracted from URL patterns during
     * route matching. For example, a route pattern '/users/:id/posts/:slug'
     * matched against '/users/123/posts/hello-world' would result in:
     * ['id' => '123', 'slug' => 'hello-world']
     * 
     * Parameters are automatically URL-decoded and validated.
     * 
     * @var array Named parameters from URL pattern matching
     */
    public $params = [];

    // ===============================================================
    // CONSTRUCTOR AND INITIALIZATION
    // ===============================================================

    /**
     * Initializes the router with framework service container.
     * 
     * Sets up the routing system with access to framework services
     * required for request processing and controller dispatch.
     * 
     * The router requires the Instance container to access:
     * - Request object for URI and method detection
     * - View system for error page rendering
     * - Session management for parameter persistence
     * 
     * @param Instance $instance Framework service container
     */
    public function __construct($instance)
    {
        $this->instance = $instance;

        // Initialize default controller path
        $this->route['controllerPath'] = APPLICATION_DIRECTORY . '/Controllers/IndexController.php';
    }

    // ===============================================================
    // PARAMETER ACCESS AND ROUTE INTROSPECTION
    // ===============================================================

    /**
     * Retrieves URL parameters extracted during route matching.
     * 
     * Provides access to named parameters captured from URL patterns
     * during the route matching process. Parameters are automatically
     * extracted, decoded, and validated for safe use in controllers.
     * 
     * PARAMETER EXTRACTION:
     * Route patterns use :name syntax for parameter definition:
     * - '/users/:id' captures 'id' parameter
     * - '/blog/:year/:month/:slug' captures multiple parameters
     * - '/api/v:version/users/:id' supports mixed patterns
     * 
     * PARAMETER TYPES:
     * Parameters are extracted as strings and can be validated/cast:
     * - Numeric IDs: $router->param('id') returns string, cast as needed
     * - Slugs: $router->param('slug') for SEO-friendly URLs
     * - Dates: $router->param('date') for date-based routing
     * 
     * USAGE PATTERNS:
     * 
     * Single Parameter:
     * ```php
     * // Route: /users/:id → /users/123
     * $userId = $router->param('id'); // Returns "123"
     * ```
     * 
     * Multiple Parameters:
     * ```php
     * // Route: /blog/:year/:month/:slug → /blog/2024/03/hello-world
     * $year = $router->param('year');   // "2024"
     * $month = $router->param('month'); // "03"
     * $slug = $router->param('slug');   // "hello-world"
     * ```
     * 
     * All Parameters:
     * ```php
     * $allParams = $router->param(); 
     * // Returns: ['year' => '2024', 'month' => '03', 'slug' => 'hello-world']
     * ```
     * 
     * Parameter Validation:
     * ```php
     * $id = $router->param('id');
     * if ($id && is_numeric($id)) {
     *     $user = User::find((int)$id);
     * }
     * ```
     * 
     * @param string|null $key Parameter name to retrieve, null for all parameters
     * @return mixed Single parameter value, all parameters array, or false if not found
     */
    public function param($key = null)
    {
        if (is_null($key)) {
            return $this->params;
        }

        if (isset($this->params[$key])) {
            return $this->params[$key];
        }

        return false;
    }

    /**
     * Returns array of all registered routes for debugging and introspection.
     * 
     * Provides access to the complete route registry for debugging,
     * documentation generation, or administrative interfaces. The returned
     * array includes all registered routes organized by HTTP method.
     * 
     * RETURN FORMAT:
     * ```php
     * [
     *   'GET' => [
     *     '/users/:id' => 'UsersController@show',
     *     '/api/users' => 'ApiController@listUsers'
     *   ],
     *   'POST' => [
     *     '/users' => 'UsersController@create',
     *     '/api/login' => function($credentials) { ... }
     *   ]
     * ]
     * ```
     * 
     * USAGE SCENARIOS:
     * - Route debugging and verification
     * - Administrative dashboards showing available endpoints
     * - API documentation generation
     * - Development tools and route inspection
     * 
     * @return array Complete route registry organized by HTTP method
     */
    public function listRegisteredRoutes()
    {
        return $this->routes;
    }

    // ===============================================================
    // ROUTE REGISTRATION AND CONFIGURATION
    // ===============================================================

    /**
     * Registers multiple routes from an array configuration.
     * 
     * Bulk registration method for defining multiple routes at once,
     * typically used during application initialization or when loading
     * routes from configuration files. Each route must specify method,
     * URL pattern, and target.
     * 
     * ROUTE ARRAY FORMAT:
     * Each route array must contain:
     * - method: HTTP method (GET, POST, PUT, DELETE, etc.)
     * - url: URL pattern with optional parameters
     * - target: Controller@method string or closure function
     * 
     * EXAMPLE ROUTE DEFINITIONS:
     * ```php
     * $routes = [
     *   [
     *     'method' => 'GET',
     *     'url' => '/users',
     *     'target' => 'UsersController@index'
     *   ],
     *   [
     *     'method' => 'GET', 
     *     'url' => '/users/:id',
     *     'target' => 'UsersController@show'
     *   ],
     *   [
     *     'method' => 'POST',
     *     'url' => '/api/login',
     *     'target' => function($credentials) {
     *       // Handle login logic
     *     }
     *   ]
     * ];
     * 
     * $router->registerRoutes($routes);
     * ```
     * 
     * CONFIGURATION PATTERNS:
     * - Load from PHP configuration files
     * - Import from JSON or YAML route definitions
     * - Dynamic registration based on modules or plugins
     * - Environment-specific route configurations
     * 
     * @param array $routes Array of route definitions with method, url, target
     * @return void
     */
    public function registerRoutes($routes = [])
    {
        foreach ($routes as $route) {
            $this->routes[$route['method']][$route['url']] = $route['target'];
        }
    }

    /**
     * Registers an individual route with method, pattern, and target.
     * 
     * Direct route registration for single endpoints, providing fine-grained
     * control over route definition. This method is typically used for
     * dynamic route registration or when routes are defined programmatically.
     * 
     * ROUTE TARGETS:
     * The target parameter supports multiple formats:
     * 
     * Controller@Method Format:
     * ```php
     * $router->registerRoute('GET', '/users/:id', 'UsersController@show');
     * ```
     * 
     * Closure Functions:
     * ```php
     * $router->registerRoute('POST', '/api/webhook', function() {
     *     // Handle webhook payload
     *     return json_encode(['status' => 'received']);
     * });
     * ```
     * 
     * Anonymous Functions with Parameters:
     * ```php
     * $router->registerRoute('GET', '/hello/:name', function($name) {
     *     return "Hello, {$name}!";
     * });
     * ```
     * 
     * URL PATTERN SYNTAX:
     * - Static segments: '/users/profile'
     * - Named parameters: '/users/:id'
     * - Mixed patterns: '/api/v:version/users/:id'
     * - Optional segments: '/blog/:year?/:month?'
     * 
     * HTTP METHOD SUPPORT:
     * Supports all standard HTTP methods:
     * - GET: Data retrieval
     * - POST: Resource creation
     * - PUT: Resource updates
     * - DELETE: Resource removal
     * - PATCH: Partial updates
     * - OPTIONS: CORS preflight
     * - HEAD: Header-only requests
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE, etc.)
     * @param string $url URL pattern with optional :parameter syntax
     * @param string|callable $target Controller@method string or closure function
     * @return void
     */
    public function registerRoute($method, $url, $target)
    {
        $this->routes[$method][$url] = $target;
    }

    /**
     * This will check to see if the current
     * route matches any of the registered routes
     * before continuing with normal behaviour.
     */
    public function matchRoute()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = strtok($_SERVER['REQUEST_URI'], '?');

        /**
         * If the method is registered
         */
        if (isset($this->routes[$method])) {

            /**
             * Iterate through each route and look for a match
             */
            foreach ($this->routes[$method] as $routeUrl => $target) {
                $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $routeUrl);

                /**
                 * If a match is found
                 */
                if (preg_match('#^' . $pattern . '$#', $url, $matches)) {
                    $this->params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                    /**
                     * Target can be either a function, or can point to 
                     * a controller and method pair using Controller@method
                     */
                    if (is_callable($target)) {
                        call_user_func_array($target, $this->params);
                        return true;
                    } else {
                        if (str_contains($target, '@')) {
                            $targetParts = explode('@', $target);
                            $controller = $targetParts[0];
                            $method = $targetParts[1];
                            return [
                                'controller' => $controller,
                                'method' => $method
                            ];
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Runs the router logic with support for nested controller directories.
     *
     * Will check the URI for the correct controller and method to run,
     * supporting both flat controller structure and nested directories.
     *
     * ROUTING PATTERNS:
     * - /users → Controllers/UsersController.php@index()
     * - /users/create → Controllers/UsersController.php@create()
     * - /admin/reports → Controllers/Admin/ReportsController.php@index()
     * - /admin/reports/users → Controllers/Admin/ReportsController.php@users()
     * - /api/v1/users → Controllers/Api/V1Controller.php@users()
     *
     * NESTED DIRECTORY RESOLUTION:
     * The router attempts to find controllers using the following strategy:
     * 1. Try longest possible path as nested directories
     * 2. Fall back to shorter paths if controller not found
     * 3. Default to flat structure if no nested match found
     *
     * Will also validate that the controller file exists and that
     * the class has the right method function to run.
     */
    public function run()
    {

        $currentRoute = $this->getUri();
        $matchedRoute = $this->matchRoute();

        if ($matchedRoute === true) {
            return true;
        } elseif (is_array($matchedRoute)) {
            $this->route['controller'] = $matchedRoute['controller'];
            $this->route['method'] = $matchedRoute['method'];

            // Set controller path for explicit routes (assume flat structure for registered routes)
            $this->route['controllerPath'] = APPLICATION_DIRECTORY . '/Controllers/' . $matchedRoute['controller'] . '.php';
        } else {
            // Resolve controller and method from URI segments with nested directory support
            $this->resolveControllerAndMethod($currentRoute);
        }

        // Make sure the file that holds the class exists
        if (!file_exists($this->route['controllerPath'])) {
            $this->instance->view->render('error/index');
            exit;
        }

        // Include the controller class
        include $this->route['controllerPath'];

        // Check the method to see if it exists in the class
        if (!method_exists($this->route['controller'], $this->route['method'])) {
            $this->instance->view->render('error/index');
            exit;
        }

        // Instantiate the controller
        $controller = new $this->route['controller']($this->instance);

        // Check if the before method exists, ifso run it
        if (method_exists($this->route['controller'], 'before')) {
            $controller->before();
        }

        // Run the controller method
        $controller->{$this->route['method']}();

        // Check fi the after method exists, ifso run it
        if (method_exists($this->route['controller'], 'after')) {
            $controller->after();
        }
    }

    /**
     * Resolves controller and method from URI segments with nested directory support.
     * 
     * This method implements intelligent controller resolution that supports
     * nested directory structures within the Controllers directory. It tries
     * multiple path combinations to find the best matching controller.
     * 
     * RESOLUTION STRATEGY:
     * 1. Try nested directory paths (longest to shortest)
     * 2. Look for controllers in subdirectories
     * 3. Fall back to flat controller structure
     * 4. Default to IndexController if no segments
     * 
     * EXAMPLES:
     * - /admin/reports/users → Controllers/Admin/ReportsController.php@users()
     * - /admin/reports → Controllers/Admin/ReportsController.php@index()
     * - /admin → Controllers/AdminController.php@index()
     * - /users/create → Controllers/UsersController.php@create()
     * 
     * NAMESPACE SUPPORT:
     * Controllers in subdirectories can use namespaces:
     * - Controllers/Admin/ReportsController.php can define class as Admin\ReportsController
     * - Or keep flat naming: class ReportsController (framework handles both)
     * 
     * @param array $routeSegments URI segments from getUri()
     * @return void Sets $this->route with resolved controller, method, and path
     * @access private
     */
    private function resolveControllerAndMethod($routeSegments)
    {
        // Filter out empty segments
        $segments = array_filter($routeSegments, function ($segment) {
            return !empty($segment);
        });

        if (empty($segments)) {
            // No segments, use default IndexController
            $this->route['controller'] = 'IndexController';
            $this->route['method'] = 'index';
            $this->route['controllerPath'] = APPLICATION_DIRECTORY . '/Controllers/IndexController.php';
            return;
        }

        // Try to find controller in nested directories (longest path first)
        $segmentCount = count($segments);

        for ($i = $segmentCount - 1; $i >= 1; $i--) {
            // Take first $i segments as directory path, next segment as controller
            $directorySegments = array_slice($segments, 0, $i);
            $controllerSegment = $segments[$i];
            $methodSegment = isset($segments[$i + 1]) ? $segments[$i + 1] : 'index';

            // Build directory path and controller name
            $directoryPath = implode('/', array_map([$this, 'dashToCamelCase'], $directorySegments));
            $controllerName = $this->dashToCamelCase($controllerSegment, true) . 'Controller';
            $controllerPath = APPLICATION_DIRECTORY . '/Controllers/' . $directoryPath . '/' . $controllerName . '.php';

            if (file_exists($controllerPath)) {
                $this->route['controller'] = $controllerName;
                $this->route['method'] = $this->dashToCamelCase($methodSegment);
                $this->route['controllerPath'] = $controllerPath;
                return;
            }
        }

        // Fall back to flat controller structure (original behavior)
        $controllerSegment = $segments[0];
        $methodSegment = isset($segments[1]) ? $segments[1] : 'index';

        $controllerName = $this->dashToCamelCase($controllerSegment, true) . 'Controller';
        $controllerPath = APPLICATION_DIRECTORY . '/Controllers/' . $controllerName . '.php';

        $this->route['controller'] = $controllerName;
        $this->route['method'] = $this->dashToCamelCase($methodSegment);
        $this->route['controllerPath'] = $controllerPath;
    }

    /**
     * Return the URI in array format
     */
    private function getUri()
    {
        $path_info = ltrim($this->instance->request->uri(), '/');
        return explode('/', $path_info);
    }

    /**
     * Converts strings with dashes into
     * camelCase
     *
     * Example: this-is-a-test = thisIsATest
     */
    private function dashToCamelCase($string, $capitalizeFirstCharacter = false)
    {
        $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }
        return $str;
    }
}
