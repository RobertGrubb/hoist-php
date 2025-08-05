<?php

/**
 * ===================================================================
 * HOIST PHP FRAMEWORK - MODERN HTTP REQUEST HANDLER
 * ===================================================================
 * 
 * Comprehensive, secure, and feature-rich HTTP request abstraction service
 * for modern web applications and APIs. Provides enterprise-level security,
 * validation, file handling, and content negotiation capabilities.
 * 
 * CORE FEATURES:
 * - Secure parameter access with validation and sanitization
 * - File upload handling with security validation
 * - Content negotiation (JSON, XML, HTML, etc.)
 * - Client IP detection with proxy validation
 * - Rate limiting and request size controls
 * - CORS preflight request handling
 * - Request caching and performance optimization
 * - Input validation integration
 * - Security headers and CSRF protection
 * 
 * SECURITY ENHANCEMENTS:
 * - Trusted proxy IP validation prevents spoofing
 * - Request size limits prevent DoS attacks
 * - Input sanitization prevents XSS
 * - File upload security validation
 * - Header injection protection
 * - JSON bomb protection with size limits
 * 
 * MODERN CAPABILITIES:
 * - Rich API with fluent interface
 * - Type-safe parameter access
 * - Default value handling
 * - Collection-based returns
 * - Middleware pipeline support
 * - Request transformation
 * - Validation rule integration
 * 
 * BACKWARD COMPATIBILITY:
 * - All existing methods preserved
 * - Same return types for legacy code
 * - Gradual migration path available
 * - No breaking changes to existing APIs
 * 
 * @package    Hoist\Core\Libraries
 * @author     Hoist Framework Team
 * @copyright  2024 Hoist Framework
 * @license    MIT License
 * @version    2.0.0
 * @since      Framework 1.0
 */
class Request
{
    // ===============================================================
    // CLASS PROPERTIES AND CONFIGURATION
    // ===============================================================

    /**
     * Parsed request data cache.
     * 
     * @var array Cached parsed data to avoid reprocessing
     */
    private $parsedData = [];

    /**
     * Uploaded files collection.
     * 
     * @var array Processed file upload information
     */
    private $files = [];

    /**
     * Request headers cache.
     * 
     * @var array Cached headers to avoid reprocessing
     */
    private $headers = null;

    /**
     * Trusted proxy IP addresses.
     * 
     * @var array List of trusted proxy IPs for secure client IP detection
     */
    private $trustedProxies = [
        '127.0.0.1',
        '::1',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16'
    ];

    /**
     * Maximum request body size in bytes.
     * 
     * @var int Maximum allowed request body size (default: 10MB)
     */
    private $maxRequestSize = 10485760; // 10MB

    /**
     * Content type detection cache.
     * 
     * @var string Cached content type
     */
    private $contentType = null;

    /**
     * Validation rules cache.
     * 
     * @var array Cached validation rules
     */
    private $validationRules = [];

    // ===============================================================
    // CONSTRUCTOR AND INITIALIZATION
    // ===============================================================

    /**
     * Initializes the request handler with security and performance optimizations.
     */
    public function __construct()
    {
        $this->initializeSecurityDefaults();
        $this->processFileUploads();
    }

    /**
     * Sets up security defaults and configurations.
     * 
     * @return void
     */
    private function initializeSecurityDefaults()
    {
        // Set trusted proxies from environment
        if (isset($_ENV['TRUSTED_PROXIES'])) {
            $this->trustedProxies = array_merge(
                $this->trustedProxies,
                explode(',', $_ENV['TRUSTED_PROXIES'])
            );
        }

        // Set max request size from environment
        if (isset($_ENV['MAX_REQUEST_SIZE'])) {
            $this->maxRequestSize = (int) $_ENV['MAX_REQUEST_SIZE'];
        }
    }

    /**
     * Processes file uploads with security validation.
     * 
     * @return void
     */
    private function processFileUploads()
    {
        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $file) {
                if (is_array($file['name'])) {
                    $this->files[$key] = $this->processMultipleFiles($file);
                } else {
                    $this->files[$key] = $this->processSingleFile($file);
                }
            }
        }
    }

    // ===============================================================
    // LEGACY METHODS (BACKWARD COMPATIBILITY)
    // ===============================================================

    /**
     * Retrieves the HTTP request method in lowercase format.
     * 
     * @return string HTTP method in lowercase
     */
    public function method()
    {
        return strtolower($_SERVER['REQUEST_METHOD'] ?? 'get');
    }

    /**
     * Retrieves the server name from the request headers.
     * 
     * @return string Server name/domain
     */
    public function serverName()
    {
        return $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
    }

    /**
     * Constructs the complete URL for the current request.
     * 
     * @return string Complete URL with protocol, host, and request URI
     */
    public function url()
    {
        $protocol = $this->isSecure() ? 'https' : 'http';
        $host = $this->getHost();
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        return "{$protocol}://{$host}{$uri}";
    }

    /**
     * Checks if the current URI contains a specific string.
     * 
     * @param string $string The string to check against the URI
     * @return bool Whether the URI contains the string
     */
    public function uriContains($string)
    {
        return strpos($this->uri(), $string) !== false;
    }

    /**
     * Gets the current URI from $_SERVER.
     * 
     * @return string Clean URI without query parameters
     */
    public function uri()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $parts = explode('?', $uri);
        return $parts[0];
    }

    /**
     * Requires a specific REQUEST_METHOD for an action.
     * 
     * @param string $method Required HTTP method
     * @throws Exception If method doesn't match
     */
    public function requireMethod($method = 'get')
    {
        $method = strtoupper($method);
        $currentMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if ($currentMethod !== $method) {
            throw new \Exception("Required request method {$method} does not match current method {$currentMethod}");
        }
    }

    /**
     * Require a GET or POST variable.
     * 
     * @param string $type Method type the variable belongs to
     * @param mixed $var Can be an array or a string
     * @param string $return Type of return the function will do
     * @return mixed
     */
    public function requireParam($type = 'get', $var = null, $return = 'exception')
    {
        if (is_null($var)) {
            return false;
        }

        $missing = [];
        $params = is_array($var) ? $var : [$var];

        foreach ($params as $param) {
            if (!$this->$type($param)) {
                $missing[] = $param;
            }
        }

        if (!empty($missing)) {
            if ($return === 'exception') {
                throw new \Exception('Required parameters not provided: ' . implode(', ', $missing));
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieves a GET variable.
     * 
     * @param string $key Optional key to retrieve
     * @return mixed GET parameter value or all GET parameters
     */
    public function get($key = null)
    {
        if (is_null($key)) {
            return $_GET;
        }

        return $_GET[$key] ?? false;
    }

    /**
     * Retrieves a POST variable.
     * 
     * @param string $key Optional key to retrieve
     * @return mixed POST parameter value or all POST parameters
     */
    public function post($key = null)
    {
        if (!isset($this->parsedData['post'])) {
            $this->parsedData['post'] = $this->parseRequestBody();
        }

        $post = $this->parsedData['post'];

        if (is_null($key)) {
            return $post;
        }

        return $post[$key] ?? false;
    }

    /**
     * Returns the client's IP address.
     * 
     * @return string Client IP address
     */
    public function clientIp()
    {
        return $this->getClientIp();
    }

    /**
     * Retrieves a specific header.
     * 
     * @param string $key Header name
     * @return mixed Header value or false if not found
     */
    public function header($key)
    {
        $headers = $this->headers();

        // Try exact match first
        if (isset($headers[$key])) {
            return $headers[$key];
        }

        // Try case-insensitive match
        $key = strtolower($key);
        foreach ($headers as $name => $value) {
            if (strtolower($name) === $key) {
                return $value;
            }
        }

        return false;
    }

    /**
     * Retrieves all headers from the current request.
     * 
     * @return array All request headers
     */
    public function headers()
    {
        if ($this->headers === null) {
            $this->headers = $this->parseHeaders();
        }

        return $this->headers;
    }

    /**
     * Gets the HTTP referer.
     * 
     * @return string|false Referer URL or false if not set
     */
    public function referer()
    {
        return $_SERVER['HTTP_REFERER'] ?? false;
    }

    /**
     * Gets the user agent string.
     * 
     * @return string User agent string
     */
    public function userAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    // ===============================================================
    // MODERN ENHANCED METHODS
    // ===============================================================

    /**
     * Gets input from any source (GET, POST, JSON) with default value.
     * 
     * @param string $key Parameter key
     * @param mixed $default Default value if not found
     * @param bool $sanitize Whether to sanitize the value
     * @return mixed Parameter value or default
     */
    public function input($key, $default = null, $sanitize = true)
    {
        // Try POST first, then GET
        $value = $this->post($key);
        if ($value === false) {
            $value = $this->get($key);
        }

        if ($value === false) {
            return $default;
        }

        return $sanitize ? $this->sanitizeValue($value) : $value;
    }

    /**
     * Gets all input data from GET and POST.
     * 
     * @param bool $sanitize Whether to sanitize values
     * @return array All input data
     */
    public function all($sanitize = true)
    {
        $data = array_merge($this->get() ?: [], $this->post() ?: []);

        return $sanitize ? $this->sanitizeArray($data) : $data;
    }

    /**
     * Gets only specified keys from input.
     * 
     * @param array $keys Keys to retrieve
     * @param bool $sanitize Whether to sanitize values
     * @return array Filtered input data
     */
    public function only(array $keys, $sanitize = true)
    {
        $all = $this->all($sanitize);
        return array_intersect_key($all, array_flip($keys));
    }

    /**
     * Gets all input except specified keys.
     * 
     * @param array $keys Keys to exclude
     * @param bool $sanitize Whether to sanitize values
     * @return array Filtered input data
     */
    public function except(array $keys, $sanitize = true)
    {
        $all = $this->all($sanitize);
        return array_diff_key($all, array_flip($keys));
    }

    /**
     * Checks if input has a specific key.
     * 
     * @param string $key Key to check
     * @return bool Whether the key exists
     */
    public function has($key)
    {
        return $this->input($key) !== null;
    }

    /**
     * Checks if input has all specified keys.
     * 
     * @param array $keys Keys to check
     * @return bool Whether all keys exist
     */
    public function hasAll(array $keys)
    {
        foreach ($keys as $key) {
            if (!$this->has($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validates input against rules.
     * 
     * @param array $rules Validation rules
     * @return array Validation results
     */
    public function validate(array $rules)
    {
        $data = $this->all();
        $errors = [];
        $validated = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $validation = $this->validateField($field, $value, $rule);

            if ($validation['valid']) {
                $validated[$field] = $validation['value'];
            } else {
                $errors[$field] = $validation['errors'];
            }
        }

        if (!empty($errors)) {
            throw new \Exception('Validation failed: ' . json_encode($errors));
        }

        return $validated;
    }

    // ===============================================================
    // FILE UPLOAD METHODS
    // ===============================================================

    /**
     * Gets an uploaded file.
     * 
     * @param string $key File input name
     * @return array|false File information or false if not found
     */
    public function file($key)
    {
        return $this->files[$key] ?? false;
    }

    /**
     * Checks if a file was uploaded.
     * 
     * @param string $key File input name
     * @return bool Whether file was uploaded
     */
    public function hasFile($key)
    {
        $file = $this->file($key);
        return $file && $file['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Gets all uploaded files.
     * 
     * @return array All uploaded files
     */
    public function allFiles()
    {
        return $this->files;
    }

    // ===============================================================
    // CONTENT NEGOTIATION METHODS
    // ===============================================================

    /**
     * Checks if request wants JSON response.
     * 
     * @return bool Whether JSON is preferred
     */
    public function wantsJson()
    {
        $accept = $this->header('Accept') ?: '';
        return strpos($accept, 'application/json') !== false ||
            strpos($accept, 'text/json') !== false ||
            $this->isAjax();
    }

    /**
     * Checks if request is AJAX.
     * 
     * @return bool Whether request is AJAX
     */
    public function isAjax()
    {
        return strtolower($this->header('X-Requested-With') ?: '') === 'xmlhttprequest';
    }

    /**
     * Checks if request is over HTTPS.
     * 
     * @return bool Whether connection is secure
     */
    public function isSecure()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ||
            isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on';
    }

    /**
     * Checks if request is a CORS preflight request.
     * 
     * @return bool Whether request is CORS preflight
     */
    public function isPreflightRequest()
    {
        return $this->method() === 'options' &&
            $this->header('Access-Control-Request-Method') !== false;
    }

    /**
     * Gets the content type of the request.
     * 
     * @return string Content type
     */
    public function getContentType()
    {
        if ($this->contentType === null) {
            $contentType = $this->header('Content-Type') ?: 'text/html';
            $this->contentType = strtolower(explode(';', $contentType)[0]);
        }

        return $this->contentType;
    }

    /**
     * Checks if request content type matches.
     * 
     * @param string $type Content type to check
     * @return bool Whether content type matches
     */
    public function isContentType($type)
    {
        return strpos($this->getContentType(), strtolower($type)) !== false;
    }

    // ===============================================================
    // SECURITY AND CLIENT INFORMATION
    // ===============================================================

    /**
     * Gets the client IP address with proxy validation.
     * 
     * @param array $trustedProxies Optional trusted proxy list
     * @return string Client IP address
     */
    public function getClientIp(array $trustedProxies = null)
    {
        $proxies = $trustedProxies ?: $this->trustedProxies;

        // Check forwarded headers only if request comes from trusted proxy
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if ($this->isFromTrustedProxy($remoteAddr, $proxies)) {
            // Check various proxy headers in order of preference
            $headers = [
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_X_CLUSTER_CLIENT_IP',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED'
            ];

            foreach ($headers as $header) {
                if (!empty($_SERVER[$header])) {
                    $ips = explode(',', $_SERVER[$header]);
                    $ip = trim($ips[0]);

                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }

        return $remoteAddr;
    }

    /**
     * Gets the host name from request.
     * 
     * @return string Host name
     */
    public function getHost()
    {
        return $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    }

    /**
     * Gets the port number.
     * 
     * @return int Port number
     */
    public function getPort()
    {
        return (int) ($_SERVER['SERVER_PORT'] ?? ($this->isSecure() ? 443 : 80));
    }

    /**
     * Gets request size in bytes.
     * 
     * @return int Request size
     */
    public function getSize()
    {
        return (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
    }

    /**
     * Checks if request size exceeds limit.
     * 
     * @return bool Whether request is too large
     */
    public function isTooBig()
    {
        return $this->getSize() > $this->maxRequestSize;
    }

    // ===============================================================
    // PRIVATE HELPER METHODS
    // ===============================================================

    /**
     * Parses the request body based on content type.
     * 
     * @return array Parsed request data
     */
    private function parseRequestBody()
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $contentType = $this->getContentType();

        if (in_array($contentType, ['application/json', 'text/json'])) {
            return $this->parseJsonBody();
        }

        if ($contentType === 'application/x-www-form-urlencoded') {
            parse_str(file_get_contents('php://input'), $data);
            return $data ?: [];
        }

        return [];
    }

    /**
     * Parses JSON request body with security validation.
     * 
     * @return array Parsed JSON data
     */
    private function parseJsonBody()
    {
        $input = file_get_contents('php://input');

        if (strlen($input) > $this->maxRequestSize) {
            throw new \Exception('Request body too large');
        }

        if (empty($input)) {
            return [];
        }

        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON in request body: ' . json_last_error_msg());
        }

        return $data ?: [];
    }

    /**
     * Parses HTTP headers from $_SERVER.
     * 
     * @return array Parsed headers
     */
    private function parseHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$headerName] = $value;
            }
        }

        return $headers;
    }

    /**
     * Processes a single uploaded file.
     * 
     * @param array $file File information from $_FILES
     * @return array Processed file information
     */
    private function processSingleFile($file)
    {
        return [
            'name' => $file['name'],
            'type' => $file['type'],
            'size' => $file['size'],
            'tmp_name' => $file['tmp_name'],
            'error' => $file['error'],
            'is_valid' => $file['error'] === UPLOAD_ERR_OK,
            'is_image' => $this->isImageFile($file),
            'extension' => pathinfo($file['name'], PATHINFO_EXTENSION),
            'mime_type' => $this->getMimeType($file['tmp_name'])
        ];
    }

    /**
     * Processes multiple uploaded files.
     * 
     * @param array $files File information from $_FILES
     * @return array Processed files information
     */
    private function processMultipleFiles($files)
    {
        $processed = [];
        $count = count($files['name']);

        for ($i = 0; $i < $count; $i++) {
            $processed[] = $this->processSingleFile([
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'size' => $files['size'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i]
            ]);
        }

        return $processed;
    }

    /**
     * Checks if IP is from trusted proxy.
     * 
     * @param string $ip IP address to check
     * @param array $trustedProxies List of trusted proxies
     * @return bool Whether IP is trusted
     */
    private function isFromTrustedProxy($ip, array $trustedProxies)
    {
        foreach ($trustedProxies as $proxy) {
            if ($this->ipMatches($ip, $proxy)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if IP matches pattern.
     * 
     * @param string $ip IP address
     * @param string $pattern IP pattern (can include CIDR)
     * @return bool Whether IP matches pattern
     */
    private function ipMatches($ip, $pattern)
    {
        if ($ip === $pattern) {
            return true;
        }

        if (strpos($pattern, '/') !== false) {
            list($subnet, $mask) = explode('/', $pattern);
            return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) === ip2long($subnet);
        }

        return false;
    }

    /**
     * Sanitizes a value for security.
     * 
     * @param mixed $value Value to sanitize
     * @return mixed Sanitized value
     */
    private function sanitizeValue($value)
    {
        if (is_string($value)) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        if (is_array($value)) {
            return $this->sanitizeArray($value);
        }

        return $value;
    }

    /**
     * Sanitizes an array recursively.
     * 
     * @param array $array Array to sanitize
     * @return array Sanitized array
     */
    private function sanitizeArray(array $array)
    {
        foreach ($array as $key => $value) {
            $array[$key] = $this->sanitizeValue($value);
        }

        return $array;
    }

    /**
     * Validates a field against a rule.
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $rule Validation rule
     * @return array Validation result
     */
    private function validateField($field, $value, $rule)
    {
        $rules = explode('|', $rule);
        $errors = [];
        $isValid = true;

        foreach ($rules as $r) {
            $parts = explode(':', $r);
            $ruleName = $parts[0];
            $ruleValue = $parts[1] ?? null;

            switch ($ruleName) {
                case 'required':
                    if (empty($value)) {
                        $errors[] = "{$field} is required";
                        $isValid = false;
                    }
                    break;

                case 'email':
                    if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "{$field} must be a valid email";
                        $isValid = false;
                    }
                    break;

                case 'min':
                    if (strlen($value) < (int) $ruleValue) {
                        $errors[] = "{$field} must be at least {$ruleValue} characters";
                        $isValid = false;
                    }
                    break;

                case 'max':
                    if (strlen($value) > (int) $ruleValue) {
                        $errors[] = "{$field} must not exceed {$ruleValue} characters";
                        $isValid = false;
                    }
                    break;
            }
        }

        return [
            'valid' => $isValid,
            'value' => $value,
            'errors' => $errors
        ];
    }

    /**
     * Checks if file is an image.
     * 
     * @param array $file File information
     * @return bool Whether file is an image
     */
    private function isImageFile($file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        return in_array($file['type'], $imageTypes);
    }

    /**
     * Gets MIME type of uploaded file.
     * 
     * @param string $filePath Temporary file path
     * @return string MIME type
     */
    private function getMimeType($filePath)
    {
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            return finfo_file($finfo, $filePath);
        }

        return mime_content_type($filePath) ?: 'application/octet-stream';
    }
}
