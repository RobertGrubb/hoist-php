<?php

/**
 * ===================================================================
 * HOIST PHP FRAMEWORK - HTTP RESPONSE HANDLING SERVICE
 * ===================================================================
 * 
 * The Response class provides a structured way to handle HTTP responses
 * throughout the Hoist PHP framework. It manages response headers, status
 * codes, content types, and output formatting for different response types.
 * 
 * Key Features:
 * - Status Code Management: Handle various HTTP status codes (200, 400, 401, 500)
 * - Content Type Control: Support for HTML, JSON, and plain text responses
 * - Header Management: Proper HTTP header setting and formatting
 * - Output Formatting: Automatic data formatting based on content type
 * - Method Chaining: Fluent interface for easy response configuration
 * - Immediate Termination: Proper response output with script termination
 * 
 * Common Usage Patterns:
 * - API Responses: JSON data with appropriate status codes
 * - Error Handling: Structured error responses with proper HTTP codes
 * - Content Negotiation: Different formats based on request type
 * - Security Responses: Unauthorized and forbidden responses
 * 
 * Usage Examples:
 * $response->setStatus(200)->setContentType('json')->output(['success' => true]);
 * $response->setStatus(401)->output('Unauthorized');
 * 
 * @package HoistPHP\Core\Libraries
 * @author  Hoist PHP Framework Team
 * @version 1.0.0
 */
class Response
{
    // ===============================================================
    // RESPONSE CONFIGURATION PROPERTIES
    // ===============================================================

    /**
     * Current response content type (internal format).
     * 
     * Stores the content type in a simplified internal format that
     * maps to proper MIME types. This allows for easier content type
     * management throughout the framework.
     * 
     * Supported values:
     * - 'html': Standard HTML content (default)
     * - 'json': JSON API responses
     * - 'plain': Plain text responses
     * 
     * @var string Internal content type identifier
     */
    private $contentType = 'html';

    /**
     * Formatted MIME type for HTTP Content-Type header.
     * 
     * Contains the proper MIME type string that will be sent in the
     * HTTP Content-Type header. This is automatically set based on
     * the internal content type.
     * 
     * Examples:
     * - 'text/html' for HTML content
     * - 'application/json' for JSON responses
     * - 'text/plain' for plain text
     * 
     * @var string MIME type for Content-Type header
     */
    private $contentTypeFormatted = 'text/html';

    /**
     * HTTP status code for the response.
     * 
     * Stores the HTTP status code that will be sent with the response.
     * Used to indicate the result of the request processing.
     * 
     * Common status codes:
     * - 200: OK (default)
     * - 400: Bad Request
     * - 401: Unauthorized
     * - 500: Internal Server Error
     * 
     * @var int HTTP status code
     */
    private $status = 200;

    // ===============================================================
    // RESPONSE CONFIGURATION METHODS
    // ===============================================================

    /**
     * Sets the HTTP status code for the response.
     * 
     * Configures the HTTP status code that will be sent with the response.
     * Status codes communicate the result of request processing to clients
     * and are essential for proper HTTP communication.
     * 
     * Supported Status Codes:
     * - 200: OK - Request successful
     * - 400: Bad Request - Client error, invalid request
     * - 401: Unauthorized - Authentication required or failed
     * - 500: Internal Server Error - Server-side error occurred
     * 
     * The method supports method chaining for fluent response building:
     * $response->setStatus(200)->setContentType('json')->output($data);
     * 
     * Use Cases:
     * - API success responses (200)
     * - Validation error responses (400)
     * - Authentication failures (401)
     * - Server error handling (500)
     * 
     * @param int|null $status HTTP status code to set
     * @return Response Returns self for method chaining
     */
    public function setStatus($status = null)
    {
        if (!is_null($status)) {
            $this->status = $status;
        }

        return $this;
    }

    /**
     * Sets the content type for the response.
     * 
     * Configures how the response data should be formatted and what
     * Content-Type header should be sent. This affects both the HTTP
     * header and how data is processed before output.
     * 
     * Supported Content Types:
     * - 'json': Application/JSON for API responses
     *   - Automatically encodes arrays/objects to JSON
     *   - Sets Content-Type: application/json
     *   - Perfect for AJAX and API endpoints
     * 
     * - 'plain': Plain text responses
     *   - Sets Content-Type: text/plain
     *   - No data transformation
     *   - Good for simple text responses
     * 
     * - 'html': HTML content (default)
     *   - Sets Content-Type: text/html
     *   - Standard web page responses
     * 
     * Method chaining is supported for fluent response building.
     * 
     * @param string $type Content type: 'json', 'plain', or 'html'
     * @return Response Returns self for method chaining
     */
    public function setContentType($type)
    {
        switch ($type) {
            case 'json':
                $this->contentType = 'json';
                $this->contentTypeFormatted = 'application/json';
                break;

            case 'plain':
                $this->contentType = 'plain';
                $this->contentTypeFormatted = 'text/plain';
                break;
        }

        return $this;
    }

    // ===============================================================
    // RESPONSE OUTPUT AND TERMINATION
    // ===============================================================

    /**
     * Outputs the response with proper headers and terminates execution.
     * 
     * This method performs the complete response output process:
     * 1. Sets appropriate HTTP status code header
     * 2. Sets Content-Type header based on configured type
     * 3. Formats data according to content type
     * 4. Outputs the data to the client
     * 5. Terminates script execution
     * 
     * Status Code Handling:
     * - 200: HTTP/1.1 200 OK (default for successful requests)
     * - 400: HTTP/1.1 400 Bad Request (client errors)
     * - 401: HTTP/1.1 401 Unauthorized (authentication failures)
     * - 500: HTTP/1.1 500 Internal Server Error (server errors)
     * 
     * Content Type Processing:
     * - JSON: Automatically encodes arrays/objects using json_encode()
     * - Plain/HTML: Outputs data as-is
     * 
     * Security Notes:
     * - Always terminates script execution to prevent additional output
     * - Properly sets headers before any content output
     * - Handles various data types safely
     * 
     * Usage Examples:
     * 
     * // API success response
     * $response->setStatus(200)->setContentType('json')->output([
     *     'success' => true,
     *     'data' => $result
     * ]);
     * 
     * // Error response
     * $response->setStatus(400)->setContentType('json')->output([
     *     'error' => 'Invalid input provided'
     * ]);
     * 
     * // Simple text response
     * $response->setStatus(200)->setContentType('plain')->output('Hello World');
     * 
     * @param mixed $data Data to output (arrays/objects will be JSON encoded for JSON responses)
     * @return void This method terminates script execution
     */
    public function output($data = null)
    {

        // If there is a status present, set header
        switch ($this->status) {
            case 400:
                header("HTTP/1.1 400 Bad Request");
                break;

            case 401:
                header("HTTP/1.1 401 Unauthorized");
                break;

            case 500:
                header("HTTP/1.1 500 Internal Server Error");
                break;

            default:
                header("HTTP/1.1 200 OK");
                break;
        }

        // Set the content type.
        header('Content-type: ' . $this->contentTypeFormatted);

        // If there is data, and the content type is JSON, output it.
        if (!is_null($data)) {
            if ($this->contentType == 'json') {
                if (is_array($data) || is_object($data)) {
                    $data = json_encode($data);
                }
            }
        }

        echo $data;
        exit;
    }
}
