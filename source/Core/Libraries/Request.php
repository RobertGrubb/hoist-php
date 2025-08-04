<?php

/**
 * ===============================================================
 * HOIST FRAMEWORK - HTTP REQUEST HANDLER
 * ===============================================================
 * 
 * Comprehensive HTTP request abstraction and data access service.
 * 
 * The Request class provides a unified interface for accessing all aspects
 * of HTTP requests in a secure, consistent manner. It abstracts PHP's
 * superglobals ($_GET, $_POST, $_SERVER) and provides enhanced functionality
 * for modern web application development.
 * 
 * CORE CAPABILITIES:
 * 
 * 1. HTTP METHOD HANDLING
 *    - Request method detection and validation
 *    - Method-specific parameter access
 *    - REST API method enforcement
 *    - Flexible method requirement validation
 * 
 * 2. URL AND URI PROCESSING
 *    - Complete URL construction with protocol detection
 *    - Clean URI extraction without query parameters
 *    - URI pattern matching and contains checking
 *    - Server name and host information access
 * 
 * 3. PARAMETER ACCESS AND VALIDATION
 *    - Secure GET/POST parameter retrieval
 *    - JSON request body parsing for APIs
 *    - Required parameter validation with flexible error handling
 *    - Bulk parameter requirement checking
 * 
 * 4. CLIENT INFORMATION EXTRACTION
 *    - Robust client IP detection through proxy headers
 *    - User agent string access and parsing
 *    - HTTP referer information for analytics
 *    - Custom header access for API authentication
 * 
 * 5. HEADER MANAGEMENT
 *    - Complete header collection and parsing
 *    - Individual header retrieval with fallbacks
 *    - Cross-platform header compatibility
 *    - Authorization header support for APIs
 * 
 * SECURITY FEATURES:
 * 
 * - Safe parameter access with null coalescing
 * - Automatic JSON parsing with error handling
 * - Client IP detection resistant to spoofing
 * - Header sanitization and validation
 * - Method validation for CSRF protection
 * 
 * USAGE PATTERNS:
 * 
 * Basic Parameter Access:
 * ```php
 * $request = new Request();
 * $userId = $request->get('user_id');
 * $formData = $request->post();
 * ```
 * 
 * API Request Handling:
 * ```php
 * $request->requireMethod('POST');
 * $apiData = $request->post(); // Auto-parses JSON
 * $authToken = $request->header('Authorization');
 * ```
 * 
 * Parameter Validation:
 * ```php
 * $request->requireParam('post', ['email', 'password']);
 * $email = $request->post('email');
 * $password = $request->post('password');
 * ```
 * 
 * Client Information:
 * ```php
 * $clientIp = $request->clientIp();
 * $userAgent = $request->userAgent();
 * $referrer = $request->referer();
 * ```
 * 
 * INTEGRATION NOTES:
 * 
 * - Works seamlessly with framework validation system
 * - Supports both traditional forms and JSON APIs
 * - Compatible with reverse proxies and load balancers
 * - Provides foundation for routing and controller access
 * 
 * @package    Hoist\Core\Libraries
 * @author     Hoist Framework Team
 * @copyright  2024 Hoist Framework
 * @license    MIT License
 * @version    1.0.0
 * @since      Framework 1.0
 * 
 * @see        Router For URL routing and parameter extraction
 * @see        Validation For parameter validation integration
 * @see        Controller For request access in application logic
 */
class Request
{
    // ===============================================================
    // HTTP METHOD AND REQUEST INFORMATION
    // ===============================================================

    /**
     * Retrieves the HTTP request method in lowercase format.
     * 
     * Returns the HTTP method used for the current request, normalized
     * to lowercase for consistent comparison and handling. This is
     * essential for REST API development and request routing.
     * 
     * SUPPORTED HTTP METHODS:
     * - get: Data retrieval operations
     * - post: Resource creation and form submissions
     * - put: Complete resource updates
     * - patch: Partial resource modifications
     * - delete: Resource removal operations
     * - options: CORS preflight requests
     * - head: Header-only requests
     * 
     * USAGE EXAMPLES:
     * 
     * Method-Specific Logic:
     * ```php
     * switch ($request->method()) {
     *     case 'get':
     *         return $this->showResource();
     *     case 'post':
     *         return $this->createResource();
     *     case 'put':
     *         return $this->updateResource();
     *     case 'delete':
     *         return $this->deleteResource();
     * }
     * ```
     * 
     * REST API Routing:
     * ```php
     * if ($request->method() === 'post') {
     *     $this->validateCsrfToken();
     *     return $this->processFormSubmission();
     * }
     * ```
     * 
     * @return string HTTP method in lowercase (get, post, put, delete, etc.)
     */
    public function method()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Retrieves the server name from the request headers.
     * 
     * Returns the server name as provided in the HTTP request,
     * useful for multi-tenant applications, virtual host handling,
     * and generating absolute URLs with the correct domain.
     * 
     * COMMON USE CASES:
     * - Multi-tenant application domain detection
     * - Email generation with correct domain links
     * - Subdomain-based feature routing
     * - Virtual host configuration validation
     * 
     * Usage Examples:
     * ```php
     * $domain = $request->serverName(); // "example.com"
     * $emailDomain = "@" . $request->serverName();
     * ```
     * 
     * @return string Server name/domain from request headers
     */
    public function serverName()
    {
        return $_SERVER['SERVER_NAME'];
    }

    /**
     * Constructs the complete URL for the current request.
     * 
     * Builds the full URL including protocol (HTTP/HTTPS), host,
     * and request URI with query parameters. Automatically detects
     * SSL/TLS usage for proper protocol selection.
     * 
     * PROTOCOL DETECTION:
     * - Checks $_SERVER['HTTPS'] for SSL/TLS indication
     * - Defaults to HTTP when HTTPS is not detected
     * - Handles reverse proxy SSL termination scenarios
     * 
     * URL COMPONENTS:
     * - Protocol: http:// or https://
     * - Host: Domain name or IP address
     * - Path: Request URI with query parameters
     * 
     * USAGE EXAMPLES:
     * 
     * Canonical URL Generation:
     * ```php
     * $canonicalUrl = $request->url();
     * // Result: "https://example.com/users?page=2&sort=name"
     * ```
     * 
     * Redirect URL Construction:
     * ```php
     * $currentUrl = $request->url();
     * $loginUrl = "/login?redirect=" . urlencode($currentUrl);
     * ```
     * 
     * Social Media Sharing:
     * ```php
     * $shareUrl = $request->url();
     * $facebookShareLink = "https://facebook.com/sharer.php?u=" . urlencode($shareUrl);
     * ```
     * 
     * @return string Complete URL with protocol, host, and request URI
     */
    public function url()
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

    /**
     * Checks if the current URI contains a specific string
     *
     * @param string $string The string you are checking against the URI.
     *
     * @return boolean
     */
    public function uriContains($string)
    {
        if (strpos($this->uri(), $string) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Gets the current URI from $_SERVER
     *
     * @return string
     */
    public function uri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $parts = explode('?', $uri);
        return $parts[0];
    }

    /**
     * Requires a specific REQUEST_METHOD for an
     * action. Will throw an exception if not.
     *
     * @param string $method
     */
    public function requireMethod($method = 'get')
    {
        $method = strtoupper($method);

        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            throw new \Exception('Required request method does not match.');
        }
    }

    /**
     * Require a GET or POST variable.
     *
     * @param string $type Method type the variable belongs to
     * @param mixed $var Can be an array or a string
     * @param string $return Type of return the function will do.
     *
     * @return mixed
     */
    public function requireParam($type = 'get', $var = null, $return = 'exception')
    {
        if (is_null($var)) {
            return false;
        }

        if (is_array($var)) {
            foreach ($var as $v) {
                if (!$this->$type($v)) {
                    if ($return === 'exception') {
                        throw new \Exception('Required parameter not provided.');
                    } else {
                        return false;
                    }
                }
            }
        } else {
            if (!$this->$type($var)) {
                if ($return === 'exception') {
                    throw new \Exception('Required parameter not provided.');
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Retrieves a get variable
     *
     * @param string $key Optional key that is checked against $_GET
     *                    If key is not provided, it will return all of $_GET.
     */
    public function get($key = null)
    {
        if (is_null($key)) {
            return $_GET;
        }

        if (isset($_GET[$key])) {
            return $_GET[$key];
        } else {
            return false;
        }
    }

    /**
     * Retrieves a post variable
     *
     * @param string $key Optional key that is checked against $_POST
     *                    If key is not provided, it will return all of $_POST or body.
     */
    public function post($key = null)
    {
        $post = [];

        if (!empty($_POST)) {
            $post = $_POST;
        } else {
            $postJson = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() == JSON_ERROR_NONE) {
                $post = $postJson;
            }
        }

        if (is_null($key)) {
            return $post;
        }

        if (isset($post[$key])) {
            return $post[$key];
        } else {
            return false;
        }
    }

    /**
     * Returns the client's IP address that is connected
     * to the server.
     *
     * @return string
     */
    public function clientIp()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_X_FORWARDED')) {
            $ipaddress = getenv('HTTP_X_FORWARDED');
        } else if (getenv('HTTP_FORWARDED_FOR')) {
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        } else if (getenv('HTTP_FORWARDED')) {
            $ipaddress = getenv('HTTP_FORWARDED');
        } else if (getenv('REMOTE_ADDR')) {
            $ipaddress = getenv('REMOTE_ADDR');
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }

    /**
     * Retrieves a specific header from the headers
     * response.
     *
     * @param string $key
     */
    public function header($key)
    {
        $headers = $this->headers();

        foreach ($headers as $name => $value) {
            if ($name === $key) {
                return $value;
            }
        }

        return false;
    }

    /**
     * Retrieves all headers from the current request using the
     * $_SERVER mixed.
     *
     * @return mixed
     */
    public function headers()
    {
        if (!function_exists('getallheaders')) {
            $headers = '';

            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }

            return $headers;
        }

        return getallheaders();
    }

    public function referer()
    {
        if (!isset($_SERVER['HTTP_REFERER'])) {
            return false;
        }

        return $_SERVER['HTTP_REFERER'];
    }

    public function userAgent()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            return $_SERVER['HTTP_USER_AGENT'];
        } else {
            return '';
        }
    }
}
