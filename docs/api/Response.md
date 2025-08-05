# Response API Documentation

## Overview

The Response API provides comprehensive HTTP response management with modern features, security headers, content negotiation, and flexible response formatting. Built for production-ready web applications and APIs.

## Class: Response

**Location**: `Core/Libraries/Response.php`  
**Access**: Available as `$this->response` in controllers  
**Version**: 2.0.0 with modern security features

---

## Properties

### Private Properties

#### HTTP Status Codes

-   **Complete 1xx-5xx Support**: All standard HTTP status codes (100-599)
-   **Status Messages**: Proper status text for each code
-   **Validation**: Prevents invalid status codes

#### Headers and Content

-   **Headers Management**: Multiple header support with proper formatting
-   **Content Type Handling**: MIME type and charset management
-   **Cookie Support**: Secure cookie configuration
-   **Security Headers**: Default security headers included

---

## Methods

### Status Code Management

#### `setStatusCode($code)`

Sets the HTTP status code with validation.

**Parameters:**

-   `$code` (int): HTTP status code (100-599)

**Returns:** `Response` - For method chaining

**Throws:** `InvalidArgumentException` - For invalid status codes

**Example:**

```php
// Set specific status codes
$this->response->setStatusCode(201); // Created
$this->response->setStatusCode(404); // Not Found
$this->response->setStatusCode(500); // Internal Server Error

// Method chaining
$this->response->setStatusCode(400)
    ->setContentType('application/json')
    ->sendJson(['error' => 'Bad Request']);

// API controller example
public function createUser() {
    try {
        $user = $this->models->user->createUser($this->request->post());
        $this->response->setStatusCode(201)
            ->sendJson($user);
    } catch (Exception $e) {
        $this->response->setStatusCode(422)
            ->sendError('Validation failed', 422, ['details' => $e->getMessage()]);
    }
}
```

#### `getStatusCode()`

Gets the current HTTP status code.

**Returns:** `int` - Current status code

**Example:**

```php
$currentStatus = $this->response->getStatusCode(); // Returns: 200 (default)

// Conditional response handling
if ($this->response->getStatusCode() >= 400) {
    error_log("Error response: " . $this->response->getStatusCode());
}
```

#### `setStatus($status)`

Alias for `setStatusCode()` for backward compatibility.

**Parameters:**

-   `$status` (int): HTTP status code

**Returns:** `Response` - For method chaining

---

### Header Management

#### `setHeader($name, $value, $replace = true)`

Sets a response header with optional multi-value support.

**Parameters:**

-   `$name` (string): Header name
-   `$value` (string): Header value
-   `$replace` (bool): Whether to replace existing header

**Returns:** `Response` - For method chaining

**Example:**

```php
// Basic header setting
$this->response->setHeader('Cache-Control', 'no-cache, no-store');
$this->response->setHeader('X-API-Version', '1.0');

// Multiple values for same header
$this->response->setHeader('Set-Cookie', 'session=abc123', false);
$this->response->setHeader('Set-Cookie', 'preferences=theme-dark', false);

// Security headers
$this->response->setHeader('X-Frame-Options', 'DENY');
$this->response->setHeader('Content-Security-Policy', "default-src 'self'");

// API rate limiting
public function handleApiRequest() {
    $remaining = $this->getRateLimitRemaining();
    $this->response->setHeader('X-RateLimit-Remaining', (string)$remaining);

    if ($remaining <= 0) {
        $this->response->setStatusCode(429)
            ->setHeader('Retry-After', '3600')
            ->sendError('Rate limit exceeded');
    }
}
```

#### `getHeader($name)`

Gets a response header value.

**Parameters:**

-   `$name` (string): Header name (case-insensitive)

**Returns:** `string|array|null` - Header value(s) or null if not set

**Example:**

```php
$contentType = $this->response->getHeader('Content-Type');
$cacheControl = $this->response->getHeader('cache-control'); // Case-insensitive

// Check if header is set
if ($this->response->getHeader('X-Custom-Header')) {
    // Header exists
}
```

#### `removeHeader($name)`

Removes a response header.

**Parameters:**

-   `$name` (string): Header name

**Returns:** `Response` - For method chaining

**Example:**

```php
// Remove default security header for specific response
$this->response->removeHeader('X-Frame-Options');

// Conditional header removal
if ($this->request->isAjax()) {
    $this->response->removeHeader('Cache-Control');
}
```

#### `setHeaders(array $headers)`

Sets multiple headers at once.

**Parameters:**

-   `$headers` (array): Associative array of headers

**Returns:** `Response` - For method chaining

**Example:**

```php
// Set multiple headers
$this->response->setHeaders([
    'Cache-Control' => 'max-age=3600',
    'X-API-Version' => '2.0',
    'Access-Control-Allow-Origin' => '*'
]);

// API response headers
$apiHeaders = [
    'X-Total-Count' => $totalUsers,
    'X-Page-Number' => $page,
    'X-Items-Per-Page' => $limit
];
$this->response->setHeaders($apiHeaders);
```

---

### Content Type Management

#### `setContentType($contentType, $charset = null)`

Sets the content type and optional charset.

**Parameters:**

-   `$contentType` (string): MIME type
-   `$charset` (string|null): Character encoding

**Returns:** `Response` - For method chaining

**Example:**

```php
// Basic content types
$this->response->setContentType('application/json');
$this->response->setContentType('text/xml', 'UTF-8');
$this->response->setContentType('application/pdf');

// File download
public function downloadReport() {
    $this->response->setContentType('application/vnd.ms-excel')
        ->setHeader('Content-Disposition', 'attachment; filename="report.xlsx"')
        ->setContent($excelData)
        ->send();
}
```

#### `json()`

Sets content type to JSON.

**Returns:** `Response` - For method chaining

**Example:**

```php
$this->response->json()
    ->setContent(json_encode($data))
    ->send();

// Method chaining for API responses
$this->response->json()->sendJson($users);
```

#### `xml()`

Sets content type to XML.

**Returns:** `Response` - For method chaining

**Example:**

```php
$xmlData = $this->generateXmlFeed($posts);
$this->response->xml()
    ->setContent($xmlData)
    ->send();
```

#### `text()`

Sets content type to plain text.

**Returns:** `Response` - For method chaining

**Example:**

```php
// Plain text response
$this->response->text()
    ->setContent("Server Status: OK\nUptime: 24 days")
    ->send();
```

#### `html()`

Sets content type to HTML.

**Returns:** `Response` - For method chaining

**Example:**

```php
$this->response->html()
    ->setContent($htmlContent)
    ->send();
```

---

### Content Management

#### `setContent($content)`

Sets the response content/body.

**Parameters:**

-   `$content` (mixed): Response content

**Returns:** `Response` - For method chaining

**Example:**

```php
// String content
$this->response->setContent('Hello, World!');

// Array content (will be JSON encoded if content type is JSON)
$this->response->json()->setContent(['message' => 'Success']);

// File content
$fileContent = file_get_contents('report.pdf');
$this->response->setContentType('application/pdf')
    ->setContent($fileContent);
```

#### `getContent()`

Gets the current response content.

**Returns:** `mixed` - Current response content

**Example:**

```php
$currentContent = $this->response->getContent();

// Modify existing content
$content = $this->response->getContent();
$content .= "\n\nAdditional information";
$this->response->setContent($content);
```

#### `appendContent($content)`

Appends content to the existing response.

**Parameters:**

-   `$content` (string): Content to append

**Returns:** `Response` - For method chaining

**Example:**

```php
$this->response->setContent('Initial content');
$this->response->appendContent(' - Additional data');
$this->response->appendContent(' - More data');
// Result: "Initial content - Additional data - More data"

// Building dynamic responses
public function buildReport() {
    $this->response->text()->setContent("Report Generated\n\n");

    foreach ($this->getReportSections() as $section) {
        $this->response->appendContent($section . "\n");
    }

    $this->response->send();
}
```

---

### Cookie Management

#### `setCookie($name, $value, $expires = 0, $path = '/', $domain = '', $secure = false, $httpOnly = true, $sameSite = 'Lax')`

Sets a cookie with security options.

**Parameters:**

-   `$name` (string): Cookie name
-   `$value` (string): Cookie value
-   `$expires` (int): Expiration timestamp (0 = session cookie)
-   `$path` (string): Cookie path
-   `$domain` (string): Cookie domain
-   `$secure` (bool): HTTPS only
-   `$httpOnly` (bool): HTTP only (no JavaScript access)
-   `$sameSite` (string): SameSite attribute ('Strict', 'Lax', 'None')

**Returns:** `Response` - For method chaining

**Example:**

```php
// Session cookie
$this->response->setCookie('user_preference', 'dark_mode');

// Long-term cookie
$expires = time() + (30 * 24 * 60 * 60); // 30 days
$this->response->setCookie('remember_token', $token, $expires);

// Secure cookie for HTTPS
$this->response->setCookie('secure_data', $data, 0, '/', '', true, true, 'Strict');

// Authentication example
public function login() {
    if ($this->auth->login($email, $password)) {
        $token = $this->auth->generateRememberToken();
        $this->response->setCookie('remember_token', $token, time() + (30 * 24 * 60 * 60));
        $this->response->redirect('/dashboard');
    }
}
```

---

### Security Headers

#### `setCorsHeaders($origins = '*', $methods = ['GET', 'POST', 'PUT', 'DELETE'], $headers = [], $credentials = false, $maxAge = 3600)`

Sets CORS (Cross-Origin Resource Sharing) headers.

**Parameters:**

-   `$origins` (string|array): Allowed origins
-   `$methods` (string|array): Allowed HTTP methods
-   `$headers` (string|array): Allowed headers
-   `$credentials` (bool): Allow credentials
-   `$maxAge` (int): Preflight cache time in seconds

**Returns:** `Response` - For method chaining

**Example:**

```php
// Basic CORS for API
$this->response->setCorsHeaders('*', ['GET', 'POST'], ['Content-Type', 'Authorization']);

// Specific origins
$this->response->setCorsHeaders([
    'https://myapp.com',
    'https://admin.myapp.com'
], ['GET', 'POST', 'PUT', 'DELETE'], [], true);

// API endpoint with CORS
public function apiEndpoint() {
    $this->response->setCorsHeaders('*', ['GET', 'POST'], ['Content-Type']);

    if ($this->request->method() === 'options') {
        $this->response->setStatusCode(200)->send();
        return;
    }

    // Handle actual request
    $this->response->sendJson(['data' => $apiData]);
}
```

---

### Convenience Response Methods

#### `sendJson($data, $statusCode = 200, $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)`

Sends a JSON response and exits.

**Parameters:**

-   `$data` (mixed): Data to encode as JSON
-   `$statusCode` (int): HTTP status code
-   `$flags` (int): JSON encoding flags

**Returns:** `void` - Exits after sending

**Example:**

```php
// Simple JSON response
$this->response->sendJson(['message' => 'Success']);

// With status code
$this->response->sendJson(['user' => $userData], 201);

// API responses
public function getUsers() {
    $users = $this->models->user->getAllUsers();
    $this->response->sendJson([
        'users' => $users,
        'total' => count($users),
        'page' => $this->request->get('page', 1)
    ]);
}

public function createUser() {
    $userData = $this->request->post();

    try {
        $newUser = $this->models->user->createUser($userData);
        $this->response->sendJson($newUser, 201);
    } catch (Exception $e) {
        $this->response->sendJson(['error' => $e->getMessage()], 400);
    }
}
```

#### `sendError($message, $statusCode = 500, $details = [])`

Sends a standardized error response.

**Parameters:**

-   `$message` (string): Error message
-   `$statusCode` (int): HTTP status code
-   `$details` (array): Additional error details

**Returns:** `void` - Exits after sending

**Example:**

```php
// Simple error
$this->response->sendError('User not found', 404);

// With details
$this->response->sendError('Validation failed', 422, [
    'fields' => ['email' => 'Email is required', 'password' => 'Password too short']
]);

// Error handling in controller
public function updateUser() {
    $userId = $this->router->param('id');

    if (!$userId || !is_numeric($userId)) {
        $this->response->sendError('Invalid user ID', 400);
        return;
    }

    $user = $this->models->user->getUserById($userId);
    if (!$user) {
        $this->response->sendError('User not found', 404);
        return;
    }

    // Update logic...
}
```

#### `sendSuccess($data = null, $message = 'Success', $statusCode = 200)`

Sends a standardized success response.

**Parameters:**

-   `$data` (mixed): Response data
-   `$message` (string): Success message
-   `$statusCode` (int): HTTP status code

**Returns:** `void` - Exits after sending

**Example:**

```php
// Simple success
$this->response->sendSuccess();

// With data
$this->response->sendSuccess($userData, 'User created successfully', 201);

// API success responses
public function deleteUser() {
    $userId = $this->router->param('id');

    if ($this->models->user->deleteUser($userId)) {
        $this->response->sendSuccess(null, 'User deleted successfully');
    } else {
        $this->response->sendError('Failed to delete user', 500);
    }
}
```

#### `redirect($url, $statusCode = 302)`

Sends a redirect response.

**Parameters:**

-   `$url` (string): Redirect URL
-   `$statusCode` (int): HTTP status code (301, 302, 303, 307, 308)

**Returns:** `void` - Exits after sending

**Example:**

```php
// Temporary redirect (302)
$this->response->redirect('/login');

// Permanent redirect (301)
$this->response->redirect('/new-url', 301);

// Post-login redirect
public function login() {
    if ($this->auth->login($email, $password)) {
        $redirectUrl = $_SESSION['redirect_after_login'] ?? '/dashboard';
        unset($_SESSION['redirect_after_login']);
        $this->response->redirect($redirectUrl);
    } else {
        $this->session->setFlashData('error', 'Invalid credentials');
        $this->response->redirect('/login');
    }
}
```

#### `download($filePath, $downloadName = null, $deleteAfter = false)`

Sends a file download response.

**Parameters:**

-   `$filePath` (string): Path to file
-   `$downloadName` (string|null): Download filename
-   `$deleteAfter` (bool): Whether to delete file after download

**Returns:** `void` - Exits after sending

**Example:**

```php
// Simple file download
$this->response->download('/path/to/report.pdf');

// Custom download name
$this->response->download('/tmp/export_123.csv', 'user_export.csv');

// Temporary file download
$this->response->download('/tmp/temp_report.pdf', 'report.pdf', true);

// Generated report download
public function exportUsers() {
    $this->auth->requireGroup('admin');

    $users = $this->models->user->getAllUsers();
    $csvContent = $this->generateCsv($users);

    $tempFile = tempnam(sys_get_temp_dir(), 'users_export_');
    file_put_contents($tempFile, $csvContent);

    $this->response->download($tempFile, 'users_export.csv', true);
}
```

---

### Response Sending

#### `sendHeaders()`

Sends HTTP headers (called automatically by `send()`).

**Returns:** `Response` - For method chaining

**Example:**

```php
// Manual header sending (advanced usage)
$this->response->setStatusCode(200)
    ->setContentType('text/plain')
    ->sendHeaders();

echo "Custom content output";
```

#### `send()`

Sends the complete response and exits.

**Returns:** `void` - Exits after sending

**Example:**

```php
$this->response->setStatusCode(200)
    ->setContentType('text/html')
    ->setContent('<h1>Hello World</h1>')
    ->send();

// With method chaining
$this->response->html()
    ->setContent($htmlContent)
    ->send();
```

#### `output($data = null)`

Legacy method for backward compatibility.

**Parameters:**

-   `$data` (mixed): Data to output

**Returns:** `void` - Exits after sending

**Example:**

```php
// Backward compatibility
$this->response->json();
$this->response->output(['message' => 'Hello']);

// Equivalent to:
$this->response->sendJson(['message' => 'Hello']);
```

---

## Practical Usage Examples

### API Controller

```php
class ApiController extends Controller
{
    public function before()
    {
        // Set CORS headers for all API endpoints
        $this->response->setCorsHeaders('*', ['GET', 'POST', 'PUT', 'DELETE'], [
            'Content-Type', 'Authorization'
        ]);

        // Handle preflight requests
        if ($this->request->method() === 'options') {
            $this->response->setStatusCode(200)->send();
        }
    }

    public function getUser()
    {
        $userId = $this->router->param('id');

        if (!$userId || !is_numeric($userId)) {
            $this->response->sendError('Invalid user ID', 400);
            return;
        }

        $user = $this->models->user->getUserById($userId);

        if (!$user) {
            $this->response->sendError('User not found', 404);
            return;
        }

        $this->response->sendJson($user);
    }

    public function createUser()
    {
        try {
            $userData = $this->request->only(['name', 'email', 'password']);
            $newUser = $this->models->user->createUser($userData);

            $this->response->sendSuccess($newUser, 'User created successfully', 201);

        } catch (Exception $e) {
            $this->response->sendError('Failed to create user', 422, [
                'details' => $e->getMessage()
            ]);
        }
    }
}
```

### File Download Controller

```php
class DownloadController extends Controller
{
    public function report()
    {
        $this->auth->required();
        $this->auth->requireGroup(['admin', 'manager']);

        $reportId = $this->router->param('id');
        $report = $this->models->report->getById($reportId);

        if (!$report || !file_exists($report['file_path'])) {
            $this->response->sendError('Report not found', 404);
            return;
        }

        // Log download
        $this->models->analytics->logDownload($reportId, $this->auth->user['id']);

        $this->response->download($report['file_path'], $report['filename']);
    }

    public function generateCsv()
    {
        $this->auth->requireGroup('admin');

        $users = $this->models->user->getAllUsers();
        $csv = $this->generateUsersCsv($users);

        $tempFile = tempnam(sys_get_temp_dir(), 'users_');
        file_put_contents($tempFile, $csv);

        $filename = 'users_export_' . date('Y-m-d') . '.csv';
        $this->response->download($tempFile, $filename, true);
    }
}
```

### Content Negotiation

```php
class PostsController extends Controller
{
    public function index()
    {
        $posts = $this->models->post->getAllPosts();

        if ($this->request->wantsJson()) {
            // API request
            $this->response->sendJson([
                'posts' => $posts,
                'total' => count($posts)
            ]);
        } else {
            // Web request
            $this->view->render('posts/index', ['posts' => $posts]);
        }
    }

    public function feed()
    {
        $posts = $this->models->post->getRecentPosts(20);
        $xml = $this->generateRssFeed($posts);

        $this->response->xml()
            ->setHeader('Cache-Control', 'max-age=3600')
            ->setContent($xml)
            ->send();
    }
}
```

---

## Security Features

### Default Security Headers

The Response class automatically sets these security headers:

-   `X-Content-Type-Options: nosniff`
-   `X-Frame-Options: DENY`
-   `X-XSS-Protection: 1; mode=block`
-   `Referrer-Policy: strict-origin-when-cross-origin`

### Secure Cookie Defaults

-   `httpOnly: true` - Prevents JavaScript access
-   `sameSite: 'Lax'` - CSRF protection
-   Configurable `secure` flag for HTTPS

### CORS Configuration

-   Flexible origin validation
-   Method and header restrictions
-   Credential handling
-   Preflight caching

---

## Framework Integration

The Response API integrates seamlessly with other framework components:

-   **Request**: Content negotiation based on Accept headers
-   **Authentication**: Automatic error responses for unauthorized access
-   **Router**: Parameter-based response handling
-   **View**: Template rendering for HTML responses
-   **Session**: Flash message integration with redirects
-   **Cache**: Cache header management for performance

The Response API provides enterprise-level HTTP response handling with modern security features and flexible content management.
