# Response Management

The Response class provides comprehensive HTTP response handling with modern features, security headers, content negotiation, and fluent method chaining for building robust web applications and APIs.

## Basic Response Usage

### Simple Responses

```php
class ApiController extends Controller
{
    public function index()
    {
        // Set content type to JSON and send data
        $this->instance->response->json()
            ->setContent(json_encode(['message' => 'Hello API']))
            ->send();
    }

    public function user($id)
    {
        $user = $this->models->user->get($id);

        if (!$user) {
            $this->instance->response->sendError('User not found', 404);
            return;
        }

        $this->instance->response->sendSuccess($user);
    }
}
```

### Content Types

```php
// JSON responses
$this->instance->response->json()
    ->setContent(json_encode($data))
    ->send();

// HTML responses (default)
$this->instance->response->html()
    ->setContent('<h1>Welcome</h1>')
    ->send();

// Plain text responses
$this->instance->response->text()
    ->setContent('Hello, World!')
    ->send();

// XML responses
$this->instance->response->xml()
    ->setContent($xmlString)
    ->send();
```

## JSON API Responses

### Success Responses

```php
// Simple success response
$this->instance->response->sendSuccess($userData);

// Success with custom message
$this->instance->response->sendSuccess($userData, 'User created successfully', 201);

// Success without data
$this->instance->response->sendSuccess(null, 'Operation completed');
```

### Error Responses

```php
// Simple error
$this->instance->response->sendError('Invalid input', 400);

// Error with details
$this->instance->response->sendError('Validation failed', 422, [
    'email' => 'Email is required',
    'password' => 'Password must be at least 8 characters'
]);

// Server error
$this->instance->response->sendError('Internal server error', 500);
```

### Direct JSON Responses

```php
// Direct JSON encoding and sending
$data = ['users' => $users, 'total' => count($users)];
$this->instance->response->sendJson($data);

// With custom status code and encoding flags
$this->instance->response->sendJson($data, 201, JSON_PRETTY_PRINT);
```

## Status Codes

### Setting Status Codes

```php
// Method chaining
$this->instance->response->setStatusCode(201)
    ->json()
    ->setContent(json_encode($newUser))
    ->send();

// Alias method
$this->instance->response->setStatus(404)
    ->sendError('Resource not found');

// Common status codes
$response = $this->instance->response;
$response->setStatusCode(200); // OK
$response->setStatusCode(201); // Created
$response->setStatusCode(400); // Bad Request
$response->setStatusCode(401); // Unauthorized
$response->setStatusCode(403); // Forbidden
$response->setStatusCode(404); // Not Found
$response->setStatusCode(422); // Unprocessable Entity
$response->setStatusCode(500); // Internal Server Error
```

## Headers Management

### Setting Headers

```php
$response = $this->instance->response;

// Set individual header
$response->setHeader('X-API-Version', '1.0');

// Set multiple headers
$response->setHeaders([
    'X-API-Version' => '1.0',
    'X-Rate-Limit' => '100',
    'Cache-Control' => 'no-cache'
]);

// Set header without replacing existing
$response->setHeader('X-Custom', 'value1', false);
$response->setHeader('X-Custom', 'value2', false); // Both values will be sent
```

### Reading and Removing Headers

```php
// Get header value
$apiVersion = $response->getHeader('X-API-Version');

// Remove header
$response->removeHeader('X-Unwanted-Header');

// Check if headers have been sent
if (!$response->headersSent()) {
    $response->setHeader('Last-Minute-Header', 'value');
}
```

## Security Features

### Default Security Headers

The Response class automatically sets security headers:

```php
// These are set by default:
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
```

### CORS Configuration

```php
// Simple CORS (allow all origins)
$this->instance->response->setCorsHeaders();

// Specific origins
$this->instance->response->setCorsHeaders(['https://mydomain.com', 'https://app.mydomain.com']);

// Full CORS configuration
$this->instance->response->setCorsHeaders(
    ['https://mydomain.com'],                    // origins
    ['GET', 'POST', 'PUT', 'DELETE'],           // methods
    ['Content-Type', 'Authorization', 'X-API-Key'], // headers
    true,                                        // credentials
    7200                                         // max age (seconds)
);
```

## Redirects

### Simple Redirects

```php
// Temporary redirect (302)
$this->instance->response->redirect('/login');

// Permanent redirect (301)
$this->instance->response->redirect('/new-url', 301);

// Other redirect types
$this->instance->response->redirect('/other', 303); // See Other
$this->instance->response->redirect('/temp', 307);  // Temporary Redirect
$this->instance->response->redirect('/perm', 308);  // Permanent Redirect
```

### Redirects with Flash Messages

```php
// Set flash message before redirect
$this->instance->session->setFlash('success', 'User created successfully');
$this->instance->response->redirect('/users');

// Error case
$this->instance->session->setFlash('error', 'Invalid credentials');
$this->instance->response->redirect('/login');
```

## Cookie Management

### Setting Cookies

```php
$response = $this->instance->response;

// Simple cookie
$response->setCookie('user_preference', 'dark_mode');

// Cookie with options
$response->setCookie(
    'session_token',           // name
    $sessionToken,            // value
    time() + 3600,           // expires (1 hour)
    '/',                     // path
    'mydomain.com',          // domain
    true,                    // secure (HTTPS only)
    true,                    // httpOnly (no JS access)
    'Strict'                 // sameSite
);

// Remember me cookie (30 days)
$response->setCookie('remember_token', $token, time() + (30 * 24 * 60 * 60));
```

### Cookie Security Options

```php
// Secure cookie for authentication
$response->setCookie(
    'auth_token',
    $token,
    time() + 3600,     // 1 hour
    '/',               // path
    '',                // domain (current domain)
    true,              // secure (HTTPS only)
    true,              // httpOnly (prevent XSS)
    'Strict'           // sameSite (CSRF protection)
);
```

## File Downloads

### Basic File Downloads

```php
// Simple file download
$this->instance->response->download('/path/to/file.pdf');

// Custom download name
$this->instance->response->download('/path/to/report.pdf', 'Monthly-Report.pdf');

// Delete file after download (for temporary files)
$this->instance->response->download('/tmp/export.csv', 'data-export.csv', true);
```

### Download with Error Handling

```php
public function downloadFile($fileId)
{
    $file = $this->models->file->get($fileId);

    if (!$file) {
        $this->instance->response->sendError('File not found', 404);
        return;
    }

    $filePath = "/uploads/" . $file['filename'];

    if (!file_exists($filePath)) {
        $this->instance->response->sendError('File no longer exists', 410);
        return;
    }

    $this->instance->response->download($filePath, $file['original_name']);
}
```

## Content Management

### Setting and Managing Content

```php
$response = $this->instance->response;

// Set content
$response->setContent('<h1>Hello World</h1>');

// Append content
$response->setContent('<div>')
    ->appendContent('<p>First paragraph</p>')
    ->appendContent('<p>Second paragraph</p>')
    ->appendContent('</div>');

// Get current content
$currentContent = $response->getContent();
```

## Method Chaining

### Fluent Interface Examples

```php
// Complex response with chaining
$this->instance->response
    ->setStatusCode(201)
    ->json()
    ->setHeader('X-API-Version', '2.0')
    ->setHeader('Location', '/api/users/' . $newUserId)
    ->setCorsHeaders(['https://app.mydomain.com'])
    ->setContent(json_encode(['id' => $newUserId, 'message' => 'User created']))
    ->send();

// API response with caching
$this->instance->response
    ->setStatusCode(200)
    ->json()
    ->setHeaders([
        'Cache-Control' => 'public, max-age=3600',
        'ETag' => '"' . md5($content) . '"',
        'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT'
    ])
    ->setContent(json_encode($data))
    ->send();
```

## Error Handling Patterns

### API Error Responses

```php
class ApiController extends Controller
{
    public function create()
    {
        try {
            $data = $this->request->getJsonData();

            if (!$this->validation->validate($data, $this->getUserRules())) {
                $this->instance->response->sendError(
                    'Validation failed',
                    422,
                    $this->validation->getErrors()
                );
                return;
            }

            $userId = $this->models->user->create($data);
            $user = $this->models->user->get($userId);

            $this->instance->response->sendSuccess($user, 'User created', 201);

        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->instance->response->sendError('Internal server error', 500);
        }
    }
}
```

### Web Form Responses

```php
class UserController extends Controller
{
    public function store()
    {
        try {
            $data = $this->request->post();

            if (!$this->validation->validate($data, $this->getUserRules())) {
                $this->instance->session->setFlash('error', 'Please check your input');
                $this->instance->session->setFlash('form_errors', $this->validation->getErrors());
                $this->instance->response->redirect('/users/create');
                return;
            }

            $userId = $this->models->user->create($data);
            $this->instance->session->setFlash('success', 'User created successfully');
            $this->instance->response->redirect('/users/' . $userId);

        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->instance->session->setFlash('error', 'An error occurred');
            $this->instance->response->redirect('/users/create');
        }
    }
}
```

## Best Practices

### 1. Always Handle Errors Gracefully

```php
if (!$user) {
    $this->instance->response->sendError('User not found', 404);
    return; // Important: return after sending response
}
```

### 2. Use Appropriate Status Codes

```php
// Resource created
$this->instance->response->sendSuccess($newUser, 'User created', 201);

// No content (for DELETE operations)
$this->instance->response->setStatusCode(204)->send();

// Validation errors
$this->instance->response->sendError('Invalid input', 422, $errors);
```

### 3. Set Security Headers for APIs

```php
$this->instance->response
    ->setCorsHeaders(['https://yourdomain.com'])
    ->setHeader('X-API-Version', '1.0')
    ->sendJson($data);
```

### 4. Use Method Chaining Consistently

```php
// Good - readable and concise
$this->instance->response
    ->setStatusCode(200)
    ->json()
    ->setContent(json_encode($data))
    ->send();

// Avoid - harder to read
$response = $this->instance->response;
$response->setStatusCode(200);
$response->json();
$response->setContent(json_encode($data));
$response->send();
```

The Response class provides a comprehensive, secure foundation for handling all types of HTTP responses in modern web applications and APIs.
