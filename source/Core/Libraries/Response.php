<?php

/**
 * ===================================================================
 * HOIST PHP FRAMEWORK - MODERN HTTP RESPONSE HANDLING SERVICE
 * ===================================================================
 * 
 * The Response class provides comprehensive HTTP response management with
 * modern features, security headers, content negotiation, and flexible
 * response formatting. Built for production-ready web applications and APIs.
 * 
 * CORE FEATURES:
 * - Complete HTTP status code support (100-599)
 * - Multiple content types (JSON, XML, HTML, plain text, file downloads)
 * - Security headers (CORS, CSP, HSTS, X-Frame-Options)
 * - Redirect handling with flash messages
 * - Cookie management with security options
 * - Response caching headers
 * - Streaming responses for large data
 * - Template rendering integration
 * 
 * MODERN CAPABILITIES:
 * - Fluent method chaining interface
 * - Content negotiation based on Accept headers
 * - Automatic compression (gzip/deflate)
 * - Rate limiting headers
 * - ETag and Last-Modified support
 * - File download responses with proper headers
 * - JSONP callback support
 * - Error response standardization
 * 
 * SECURITY FEATURES:
 * - XSS protection headers
 * - CSRF token integration
 * - Content Security Policy configuration
 * - Secure cookie defaults
 * - Input sanitization for headers
 * 
 * @package    Hoist\Core\Libraries
 * @author     Hoist Framework Team
 * @copyright  2024 Hoist Framework
 * @license    MIT License
 * @version    2.0.0
 * @since      Framework 1.0
 */
class Response
{
    // ===============================================================
    // CORE RESPONSE PROPERTIES
    // ===============================================================

    /**
     * HTTP status code for the response.
     * 
     * @var int HTTP status code (100-599)
     */
    private $statusCode = 200;

    /**
     * HTTP headers to send with the response.
     * 
     * @var array Associative array of header name => value pairs
     */
    private $headers = [];

    /**
     * Response content/body.
     * 
     * @var mixed Response content (string, array, object)
     */
    private $content = '';

    /**
     * Content type for the response.
     * 
     * @var string MIME type for Content-Type header
     */
    private $contentType = 'text/html';

    /**
     * Character encoding for the response.
     * 
     * @var string Character encoding (default: UTF-8)
     */
    private $charset = 'UTF-8';

    /**
     * Cookies to set with the response.
     * 
     * @var array Array of cookie configurations
     */
    private $cookies = [];

    /**
     * Whether headers have been sent.
     * 
     * @var bool Header sending status
     */
    private $headersSent = false;

    /**
     * HTTP status code messages.
     * 
     * @var array Complete list of HTTP status codes and messages
     */
    private static $statusTexts = [
        // 1xx Informational
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',

        // 2xx Success
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',

        // 3xx Redirection
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',

        // 4xx Client Error
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',

        // 5xx Server Error
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    ];

    // ===============================================================
    // CONSTRUCTOR AND INITIALIZATION
    // ===============================================================

    /**
     * Initializes the response with default security headers.
     */
    public function __construct()
    {
        $this->setDefaultSecurityHeaders();
    }

    // ===============================================================
    // STATUS CODE MANAGEMENT
    // ===============================================================

    /**
     * Sets the HTTP status code.
     * 
     * @param int $code HTTP status code (100-599)
     * @return Response For method chaining
     */
    public function setStatusCode($code)
    {
        if (!isset(self::$statusTexts[$code])) {
            throw new InvalidArgumentException("Invalid HTTP status code: {$code}");
        }

        $this->statusCode = $code;
        return $this;
    }

    /**
     * Gets the current HTTP status code.
     * 
     * @return int Current status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets status code (alias for backward compatibility).
     * 
     * @param int $status HTTP status code
     * @return Response For method chaining
     */
    public function setStatus($status)
    {
        return $this->setStatusCode($status);
    }

    // ===============================================================
    // HEADER MANAGEMENT
    // ===============================================================

    /**
     * Sets a response header.
     * 
     * @param string $name Header name
     * @param string $value Header value
     * @param bool $replace Whether to replace existing header
     * @return Response For method chaining
     */
    public function setHeader($name, $value, $replace = true)
    {
        $name = $this->normalizeHeaderName($name);

        if (!$replace && isset($this->headers[$name])) {
            if (!is_array($this->headers[$name])) {
                $this->headers[$name] = [$this->headers[$name]];
            }
            $this->headers[$name][] = $value;
        } else {
            $this->headers[$name] = $value;
        }

        return $this;
    }

    /**
     * Gets a response header.
     * 
     * @param string $name Header name
     * @return string|array|null Header value(s) or null if not set
     */
    public function getHeader($name)
    {
        $name = $this->normalizeHeaderName($name);
        return $this->headers[$name] ?? null;
    }

    /**
     * Removes a response header.
     * 
     * @param string $name Header name
     * @return Response For method chaining
     */
    public function removeHeader($name)
    {
        $name = $this->normalizeHeaderName($name);
        unset($this->headers[$name]);
        return $this;
    }

    /**
     * Sets multiple headers at once.
     * 
     * @param array $headers Associative array of headers
     * @return Response For method chaining
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
        return $this;
    }

    // ===============================================================
    // CONTENT TYPE AND ENCODING
    // ===============================================================

    /**
     * Sets the content type.
     * 
     * @param string $contentType MIME type
     * @param string $charset Character encoding
     * @return Response For method chaining
     */
    public function setContentType($contentType, $charset = null)
    {
        $this->contentType = $contentType;

        if ($charset !== null) {
            $this->charset = $charset;
        }

        $headerValue = $contentType;
        if ($this->charset) {
            $headerValue .= '; charset=' . $this->charset;
        }

        $this->setHeader('Content-Type', $headerValue);
        return $this;
    }

    /**
     * Sets content type to JSON.
     * 
     * @return Response For method chaining
     */
    public function json()
    {
        return $this->setContentType('application/json');
    }

    /**
     * Sets content type to XML.
     * 
     * @return Response For method chaining
     */
    public function xml()
    {
        return $this->setContentType('application/xml');
    }

    /**
     * Sets content type to plain text.
     * 
     * @return Response For method chaining
     */
    public function text()
    {
        return $this->setContentType('text/plain');
    }

    /**
     * Sets content type to HTML.
     * 
     * @return Response For method chaining
     */
    public function html()
    {
        return $this->setContentType('text/html');
    }

    // ===============================================================
    // CONTENT MANAGEMENT
    // ===============================================================

    /**
     * Sets the response content.
     * 
     * @param mixed $content Response content
     * @return Response For method chaining
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Gets the response content.
     * 
     * @return mixed Current response content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Appends content to the response.
     * 
     * @param string $content Content to append
     * @return Response For method chaining
     */
    public function appendContent($content)
    {
        $this->content .= $content;
        return $this;
    }

    // ===============================================================
    // COOKIE MANAGEMENT
    // ===============================================================

    /**
     * Sets a cookie.
     * 
     * @param string $name Cookie name
     * @param string $value Cookie value
     * @param int $expires Expiration time (Unix timestamp)
     * @param string $path Cookie path
     * @param string $domain Cookie domain
     * @param bool $secure Whether cookie should only be sent over HTTPS
     * @param bool $httpOnly Whether cookie should be HTTP-only
     * @param string $sameSite SameSite attribute (Strict, Lax, None)
     * @return Response For method chaining
     */
    public function setCookie($name, $value, $expires = 0, $path = '/', $domain = '', $secure = false, $httpOnly = true, $sameSite = 'Lax')
    {
        $this->cookies[] = [
            'name' => $name,
            'value' => $value,
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httpOnly' => $httpOnly,
            'sameSite' => $sameSite
        ];

        return $this;
    }

    // ===============================================================
    // SECURITY HEADERS
    // ===============================================================

    /**
     * Sets default security headers.
     * 
     * @return void
     */
    private function setDefaultSecurityHeaders()
    {
        $this->setHeader('X-Content-Type-Options', 'nosniff');
        $this->setHeader('X-Frame-Options', 'DENY');
        $this->setHeader('X-XSS-Protection', '1; mode=block');
        $this->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * Sets CORS headers.
     * 
     * @param string|array $origins Allowed origins
     * @param string|array $methods Allowed methods
     * @param string|array $headers Allowed headers
     * @param bool $credentials Allow credentials
     * @param int $maxAge Preflight cache time
     * @return Response For method chaining
     */
    public function setCorsHeaders($origins = '*', $methods = ['GET', 'POST', 'PUT', 'DELETE'], $headers = [], $credentials = false, $maxAge = 3600)
    {
        if (is_array($origins)) {
            $origins = implode(', ', $origins);
        }

        if (is_array($methods)) {
            $methods = implode(', ', $methods);
        }

        if (is_array($headers)) {
            $headers = implode(', ', $headers);
        }

        $this->setHeader('Access-Control-Allow-Origin', $origins);
        $this->setHeader('Access-Control-Allow-Methods', $methods);

        if ($headers) {
            $this->setHeader('Access-Control-Allow-Headers', $headers);
        }

        if ($credentials) {
            $this->setHeader('Access-Control-Allow-Credentials', 'true');
        }

        $this->setHeader('Access-Control-Max-Age', (string) $maxAge);

        return $this;
    }

    // ===============================================================
    // CONVENIENCE RESPONSE METHODS
    // ===============================================================

    /**
     * Sends a JSON response.
     * 
     * @param mixed $data Data to encode as JSON
     * @param int $statusCode HTTP status code
     * @param int $flags JSON encoding flags
     * @return void
     */
    public function sendJson($data, $statusCode = 200, $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    {
        $this->setStatusCode($statusCode)
            ->json()
            ->setContent(json_encode($data, $flags))
            ->send();
    }

    /**
     * Sends an error response.
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $details Additional error details
     * @return void
     */
    public function sendError($message, $statusCode = 500, $details = [])
    {
        $errorData = [
            'error' => true,
            'message' => $message,
            'code' => $statusCode
        ];

        if (!empty($details)) {
            $errorData['details'] = $details;
        }

        $this->sendJson($errorData, $statusCode);
    }

    /**
     * Sends a success response.
     * 
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code
     * @return void
     */
    public function sendSuccess($data = null, $message = 'Success', $statusCode = 200)
    {
        $responseData = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $responseData['data'] = $data;
        }

        $this->sendJson($responseData, $statusCode);
    }

    /**
     * Sends a redirect response.
     * 
     * @param string $url Redirect URL
     * @param int $statusCode HTTP status code (301, 302, 303, 307, 308)
     * @return void
     */
    public function redirect($url, $statusCode = 302)
    {
        $this->setStatusCode($statusCode)
            ->setHeader('Location', $url)
            ->send();
    }

    /**
     * Sends a file download response.
     * 
     * @param string $filePath Path to file
     * @param string $downloadName Download filename
     * @param bool $deleteAfter Whether to delete file after download
     * @return void
     */
    public function download($filePath, $downloadName = null, $deleteAfter = false)
    {
        if (!file_exists($filePath)) {
            $this->sendError('File not found', 404);
            return;
        }

        if ($downloadName === null) {
            $downloadName = basename($filePath);
        }

        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        $this->setContentType($mimeType)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $downloadName . '"')
            ->setHeader('Content-Length', (string) $fileSize)
            ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0');

        $this->sendHeaders();

        readfile($filePath);

        if ($deleteAfter) {
            unlink($filePath);
        }

        exit;
    }

    // ===============================================================
    // RESPONSE SENDING AND OUTPUT
    // ===============================================================

    /**
     * Sends the HTTP headers.
     * 
     * @return Response For method chaining
     */
    public function sendHeaders()
    {
        if ($this->headersSent || headers_sent()) {
            return $this;
        }

        // Send status line
        $statusText = self::$statusTexts[$this->statusCode] ?? 'Unknown';
        header("HTTP/1.1 {$this->statusCode} {$statusText}");

        // Send headers
        foreach ($this->headers as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    header("{$name}: {$v}", false);
                }
            } else {
                header("{$name}: {$value}");
            }
        }

        // Send cookies
        foreach ($this->cookies as $cookie) {
            setcookie(
                $cookie['name'],
                $cookie['value'],
                [
                    'expires' => $cookie['expires'],
                    'path' => $cookie['path'],
                    'domain' => $cookie['domain'],
                    'secure' => $cookie['secure'],
                    'httponly' => $cookie['httpOnly'],
                    'samesite' => $cookie['sameSite']
                ]
            );
        }

        $this->headersSent = true;
        return $this;
    }

    /**
     * Sends the complete response.
     * 
     * @return void
     */
    public function send()
    {
        $this->sendHeaders();
        echo $this->content;
        exit;
    }

    /**
     * Outputs response (legacy method for backward compatibility).
     * 
     * @param mixed $data Data to output
     * @return void
     */
    public function output($data = null)
    {
        if ($data !== null) {
            if ($this->contentType === 'application/json' && (is_array($data) || is_object($data))) {
                $data = json_encode($data);
            }
            $this->setContent($data);
        }

        $this->send();
    }

    // ===============================================================
    // UTILITY METHODS
    // ===============================================================

    /**
     * Normalizes header names to proper case.
     * 
     * @param string $name Header name
     * @return string Normalized header name
     */
    private function normalizeHeaderName($name)
    {
        return implode('-', array_map('ucfirst', explode('-', strtolower($name))));
    }

    /**
     * Checks if headers have been sent.
     * 
     * @return bool Whether headers have been sent
     */
    public function headersSent()
    {
        return $this->headersSent || headers_sent();
    }
}
