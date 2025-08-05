# Request API Documentation

## Overview

The Request API provides comprehensive HTTP request handling with modern security features, file upload processing, content negotiation, and input validation. Built for enterprise-level web applications and APIs.

## Class: Request

**Location**: `Core/Libraries/Request.php`  
**Access**: Available as `$this->request` in controllers  
**Version**: 2.0.0 with backward compatibility

---

## Core Methods

### HTTP Request Information

#### `method()`

Retrieves the HTTP request method in lowercase format.

**Returns:** `string` - HTTP method in lowercase

**Example:**

```php
$method = $this->request->method();
// Returns: 'get', 'post', 'put', 'delete', etc.

// Usage in controller
public function handleRequest() {
    switch ($this->request->method()) {
        case 'get':
            return $this->showForm();
        case 'post':
            return $this->processForm();
        case 'put':
            return $this->updateResource();
        case 'delete':
            return $this->deleteResource();
        default:
            $this->view->render('error/405'); // Method not allowed
    }
}
```

#### `uri()`

Gets the current URI without query parameters.

**Returns:** `string` - Clean URI path

**Example:**

```php
// Request: /users/123?tab=profile&edit=true
$uri = $this->request->uri(); // Returns: "/users/123"

// Usage for route matching
public function isApiRequest() {
    return strpos($this->request->uri(), '/api/') === 0;
}

public function getCurrentSection() {
    $segments = explode('/', trim($this->request->uri(), '/'));
    return $segments[0] ?? 'home';
}
```

#### `url()`

Constructs the complete URL for the current request.

**Returns:** `string` - Complete URL with protocol, host, and URI

**Example:**

```php
$fullUrl = $this->request->url();
// Returns: "https://example.com/users/123?tab=profile"

// Usage for redirects or logging
public function logRequest() {
    error_log("Request: " . $this->request->url());
}

public function getCurrentUrlForRedirect() {
    $_SESSION['redirect_after_login'] = $this->request->url();
}
```

#### `uriContains($string)`

Checks if the current URI contains a specific string.

**Parameters:**

-   `$string` (string): String to search for in URI

**Returns:** `bool` - Whether URI contains the string

**Example:**

```php
// Check for admin section
if ($this->request->uriContains('/admin/')) {
    $this->auth->requireGroup('admin');
}

// Check for API endpoints
if ($this->request->uriContains('/api/')) {
    $this->response->setContentType('application/json');
}

// Dynamic navigation highlighting
$isUsersSection = $this->request->uriContains('/users/');
$isPostsSection = $this->request->uriContains('/posts/');
```

---

### Parameter Access

#### `get($key = null)`

Retrieves GET parameters.

**Parameters:**

-   `$key` (string|null): Parameter name, null for all parameters

**Returns:** `mixed` - Parameter value, all parameters array, or false if not found

**Example:**

```php
// Single parameter
$userId = $this->request->get('id');
$searchTerm = $this->request->get('q');

// All GET parameters
$allParams = $this->request->get();

// With validation
$page = $this->request->get('page');
$page = is_numeric($page) ? (int)$page : 1;

// Search functionality
public function search() {
    $query = $this->request->get('q');
    $category = $this->request->get('category');
    $page = $this->request->get('page') ?: 1;

    if (empty($query)) {
        $this->view->render('search/form');
        return;
    }

    $results = $this->models->post->search($query, $category, $page);
    $this->view->render('search/results', [
        'results' => $results,
        'query' => $query,
        'category' => $category,
        'page' => $page
    ]);
}
```

#### `post($key = null)`

Retrieves POST parameters with support for JSON content.

**Parameters:**

-   `$key` (string|null): Parameter name, null for all parameters

**Returns:** `mixed` - Parameter value, all parameters array, or false if not found

**Features:**

-   Automatic JSON parsing for `application/json` content-type
-   Form data parsing for `application/x-www-form-urlencoded`
-   Caching for performance

**Example:**

```php
// Form data
$email = $this->request->post('email');
$password = $this->request->post('password');

// All POST data
$formData = $this->request->post();

// JSON API handling
// Content-Type: application/json
// Body: {"name": "John", "email": "john@example.com"}
$name = $this->request->post('name');     // "John"
$email = $this->request->post('email');   // "john@example.com"

// Login example
public function authenticate() {
    $email = $this->request->post('email');
    $password = $this->request->post('password');

    if (!$email || !$password) {
        $this->session->setFlashData('error', 'Email and password required');
        $this->instance->redirect('/login');
        return;
    }

    if ($this->auth->login($email, $password)) {
        $this->instance->redirect('/dashboard');
    } else {
        $this->session->setFlashData('error', 'Invalid credentials');
        $this->instance->redirect('/login');
    }
}
```

---

### Modern Parameter Methods

#### `input($key, $default = null, $sanitize = true)`

Gets input from any source (GET, POST, JSON) with default value and sanitization.

**Parameters:**

-   `$key` (string): Parameter key
-   `$default` (mixed): Default value if not found
-   `$sanitize` (bool): Whether to sanitize the value

**Returns:** `mixed` - Parameter value or default

**Example:**

```php
// With defaults
$page = $this->request->input('page', 1);
$limit = $this->request->input('limit', 20);
$searchTerm = $this->request->input('q', '');

// Without sanitization for trusted input
$rawData = $this->request->input('data', null, false);

// API endpoint example
public function listUsers() {
    $page = (int) $this->request->input('page', 1);
    $limit = (int) $this->request->input('limit', 20);
    $status = $this->request->input('status', 'active');

    // Validate limits
    $limit = min(max($limit, 1), 100); // Between 1 and 100
    $page = max($page, 1); // At least 1

    $users = $this->models->user->getUsersPaginated($page, $limit, $status);
    $this->response->json($users);
}
```

#### `all($sanitize = true)`

Gets all input data from GET and POST.

**Parameters:**

-   `$sanitize` (bool): Whether to sanitize values

**Returns:** `array` - All input data

**Example:**

```php
// Get all input data
$data = $this->request->all();

// Without sanitization
$rawData = $this->request->all(false);

// Form processing example
public function updateProfile() {
    $this->auth->required();

    $data = $this->request->all();

    // Remove sensitive fields
    unset($data['password'], $data['admin']);

    // Validate required fields
    $required = ['name', 'email'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $this->session->setFlashData('error', "Field {$field} is required");
            $this->instance->redirect('/profile/edit');
            return;
        }
    }

    // Update user profile
    $userId = $this->auth->user['id'];
    $this->models->user->updateUser($userId, $data);

    $this->session->setFlashData('success', 'Profile updated successfully');
    $this->instance->redirect('/profile');
}
```

#### `only(array $keys, $sanitize = true)`

Gets only specified keys from input.

**Parameters:**

-   `$keys` (array): Keys to retrieve
-   `$sanitize` (bool): Whether to sanitize values

**Returns:** `array` - Filtered input data

**Example:**

```php
// Get only specific fields
$userData = $this->request->only(['name', 'email', 'phone']);

// Registration example
public function register() {
    $userData = $this->request->only([
        'name', 'email', 'password', 'password_confirmation'
    ]);

    // Validate
    if (empty($userData['name']) || empty($userData['email'])) {
        $this->session->setFlashData('error', 'Name and email required');
        $this->instance->redirect('/register');
        return;
    }

    if ($userData['password'] !== $userData['password_confirmation']) {
        $this->session->setFlashData('error', 'Passwords do not match');
        $this->instance->redirect('/register');
        return;
    }

    // Remove confirmation field
    unset($userData['password_confirmation']);

    // Hash password
    $userData['password'] = $this->auth->generatePasswordHash($userData['password']);

    // Create user
    $newUser = $this->models->user->createUser($userData);

    if ($newUser) {
        $this->auth->loginWithCreatedAccount($newUser);
        $this->instance->redirect('/welcome');
    }
}
```

#### `except(array $keys, $sanitize = true)`

Gets all input except specified keys.

**Parameters:**

-   `$keys` (array): Keys to exclude
-   `$sanitize` (bool): Whether to sanitize values

**Returns:** `array` - Filtered input data

**Example:**

```php
// Exclude sensitive fields
$safeData = $this->request->except(['password', 'token', 'admin']);

// Update user profile excluding system fields
public function updateUser() {
    $this->auth->requireGroup('admin');

    $userId = $this->router->param('id');

    // Get all data except system fields
    $userData = $this->request->except([
        'id', 'created_at', 'updated_at', 'password'
    ]);

    if ($this->models->user->updateUser($userId, $userData)) {
        $this->session->setFlashData('success', 'User updated successfully');
    } else {
        $this->session->setFlashData('error', 'Failed to update user');
    }

    $this->instance->redirect("/admin/users/{$userId}");
}
```

#### `has($key)`

Checks if input has a specific key.

**Parameters:**

-   `$key` (string): Key to check

**Returns:** `bool` - Whether the key exists

**Example:**

```php
// Check for optional parameters
if ($this->request->has('advanced_search')) {
    // Show advanced search form
}

// Conditional processing
public function processForm() {
    if ($this->request->has('preview')) {
        return $this->showPreview();
    }

    if ($this->request->has('save_draft')) {
        return $this->saveDraft();
    }

    if ($this->request->has('publish')) {
        return $this->publishPost();
    }

    $this->view->render('error/400');
}
```

#### `hasAll(array $keys)`

Checks if input has all specified keys.

**Parameters:**

-   `$keys` (array): Keys to check

**Returns:** `bool` - Whether all keys exist

**Example:**

```php
// Validate required fields
if (!$this->request->hasAll(['name', 'email', 'message'])) {
    $this->session->setFlashData('error', 'All fields are required');
    $this->instance->redirect('/contact');
    return;
}

// API validation
public function createPost() {
    $this->auth->required();

    $required = ['title', 'content', 'category'];

    if (!$this->request->hasAll($required)) {
        $this->response->json([
            'error' => 'Missing required fields',
            'required' => $required
        ], 400);
        return;
    }

    $postData = $this->request->only($required);
    $postData['user_id'] = $this->auth->user['id'];

    $post = $this->models->post->createPost($postData);
    $this->response->json($post, 201);
}
```

---

### File Upload Handling

#### `file($key)`

Gets an uploaded file with security validation.

**Parameters:**

-   `$key` (string): File input name

**Returns:** `array|false` - File information or false if not found

**File Information Structure:**

```php
[
    'name' => 'original_filename.jpg',
    'type' => 'image/jpeg',
    'size' => 12345,
    'tmp_name' => '/tmp/phpXXXXXX',
    'error' => 0,
    'is_valid' => true,
    'is_image' => true,
    'extension' => 'jpg',
    'mime_type' => 'image/jpeg'
]
```

**Example:**

```php
// Single file upload
public function uploadAvatar() {
    $this->auth->required();

    $file = $this->request->file('avatar');

    if (!$file || !$file['is_valid']) {
        $this->session->setFlashData('error', 'Please select a valid file');
        $this->instance->redirect('/profile/edit');
        return;
    }

    // Validate file type
    if (!$file['is_image']) {
        $this->session->setFlashData('error', 'Only image files allowed');
        $this->instance->redirect('/profile/edit');
        return;
    }

    // Validate file size (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        $this->session->setFlashData('error', 'File too large (max 2MB)');
        $this->instance->redirect('/profile/edit');
        return;
    }

    // Generate unique filename
    $extension = $file['extension'];
    $filename = uniqid('avatar_') . '.' . $extension;
    $uploadPath = 'public/uploads/avatars/' . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Update user avatar
        $this->models->user->updateUser($this->auth->user['id'], [
            'avatar' => $filename
        ]);

        $this->session->setFlashData('success', 'Avatar updated successfully');
    } else {
        $this->session->setFlashData('error', 'Failed to upload file');
    }

    $this->instance->redirect('/profile');
}
```

#### `hasFile($key)`

Checks if a file was uploaded successfully.

**Parameters:**

-   `$key` (string): File input name

**Returns:** `bool` - Whether file was uploaded

**Example:**

```php
public function uploadDocument() {
    if (!$this->request->hasFile('document')) {
        $this->session->setFlashData('error', 'No file uploaded');
        $this->instance->redirect('/upload');
        return;
    }

    $file = $this->request->file('document');
    // Process file...
}
```

#### `allFiles()`

Gets all uploaded files.

**Returns:** `array` - All uploaded files

**Example:**

```php
// Multiple file upload
public function uploadGallery() {
    $this->auth->required();

    $files = $this->request->allFiles();
    $uploadedFiles = [];

    foreach ($files as $fieldName => $file) {
        if (is_array($file)) {
            // Multiple files in one field
            foreach ($file as $index => $singleFile) {
                if ($singleFile['is_valid'] && $singleFile['is_image']) {
                    $uploadedFiles[] = $this->processImageUpload($singleFile);
                }
            }
        } else {
            // Single file
            if ($file['is_valid'] && $file['is_image']) {
                $uploadedFiles[] = $this->processImageUpload($file);
            }
        }
    }

    $this->view->render('gallery/upload-results', [
        'uploaded' => $uploadedFiles
    ]);
}
```

---

### HTTP Headers and Client Information

#### `header($key)`

Retrieves a specific HTTP header.

**Parameters:**

-   `$key` (string): Header name (case-insensitive)

**Returns:** `mixed` - Header value or false if not found

**Example:**

```php
// Get specific headers
$contentType = $this->request->header('Content-Type');
$userAgent = $this->request->header('User-Agent');
$authorization = $this->request->header('Authorization');

// API authentication
public function authenticateApi() {
    $authHeader = $this->request->header('Authorization');

    if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
        $this->response->json(['error' => 'Missing or invalid authorization header'], 401);
        return false;
    }

    $token = substr($authHeader, 7); // Remove "Bearer "

    // Validate token
    $user = $this->models->user->getUserByApiToken($token);

    if (!$user) {
        $this->response->json(['error' => 'Invalid token'], 401);
        return false;
    }

    // Set authenticated user
    $this->auth->setUser($user);
    return true;
}
```

#### `headers()`

Retrieves all HTTP headers.

**Returns:** `array` - All request headers

**Example:**

```php
$allHeaders = $this->request->headers();

// Debug headers
public function debugRequest() {
    $this->auth->requireGroup('admin');

    $debug = [
        'method' => $this->request->method(),
        'uri' => $this->request->uri(),
        'headers' => $this->request->headers(),
        'params' => $this->request->all(),
        'files' => $this->request->allFiles()
    ];

    $this->view->render('debug/request', ['debug' => $debug]);
}
```

#### `clientIp()`

Returns the client's IP address with proxy support.

**Returns:** `string` - Client IP address

**Features:**

-   Proxy header validation (X-Forwarded-For, etc.)
-   Trusted proxy configuration
-   Security validation to prevent spoofing

**Example:**

```php
$clientIp = $this->request->clientIp();

// Rate limiting by IP
public function checkRateLimit() {
    $ip = $this->request->clientIp();
    $key = "rate_limit_{$ip}";

    $attempts = $this->cache->get($key, 0);

    if ($attempts >= 10) {
        $this->response->json(['error' => 'Rate limit exceeded'], 429);
        return false;
    }

    $this->cache->set($key, $attempts + 1, 3600); // 1 hour
    return true;
}

// Logging with IP
public function logSecurityEvent($event, $details = []) {
    $logData = [
        'event' => $event,
        'ip' => $this->request->clientIp(),
        'user_agent' => $this->request->userAgent(),
        'uri' => $this->request->uri(),
        'details' => $details,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    error_log(json_encode($logData));
}
```

#### `userAgent()`

Gets the user agent string.

**Returns:** `string` - User agent string

**Example:**

```php
$userAgent = $this->request->userAgent();

// Browser detection
public function isMobile() {
    $ua = strtolower($this->request->userAgent());
    return strpos($ua, 'mobile') !== false ||
           strpos($ua, 'android') !== false ||
           strpos($ua, 'iphone') !== false;
}

// Analytics
public function trackVisit() {
    $data = [
        'ip' => $this->request->clientIp(),
        'user_agent' => $this->request->userAgent(),
        'referer' => $this->request->referer(),
        'uri' => $this->request->uri(),
        'timestamp' => date('Y-m-d H:i:s')
    ];

    $this->models->analytics->recordVisit($data);
}
```

#### `referer()`

Gets the HTTP referer.

**Returns:** `string|false` - Referer URL or false if not set

**Example:**

```php
$referer = $this->request->referer();

// Redirect back
public function redirectBack($default = '/') {
    $referer = $this->request->referer();

    // Validate referer is from same domain
    if ($referer && strpos($referer, $_SERVER['HTTP_HOST']) !== false) {
        $this->instance->redirect($referer);
    } else {
        $this->instance->redirect($default);
    }
}
```

---

### Content Negotiation

#### `wantsJson()`

Checks if request wants JSON response.

**Returns:** `bool` - Whether JSON is preferred

**Example:**

```php
public function listUsers() {
    $users = $this->models->user->getAllUsers();

    if ($this->request->wantsJson()) {
        $this->response->json($users);
    } else {
        $this->view->render('users/index', ['users' => $users]);
    }
}
```

#### `isAjax()`

Checks if request is AJAX.

**Returns:** `bool` - Whether request is AJAX

**Example:**

```php
public function search() {
    $results = $this->models->post->search($this->request->get('q'));

    if ($this->request->isAjax()) {
        $this->response->json($results);
    } else {
        $this->view->render('search/results', ['results' => $results]);
    }
}
```

#### `isSecure()`

Checks if request is over HTTPS.

**Returns:** `bool` - Whether connection is secure

**Example:**

```php
public function before() {
    // Force HTTPS for sensitive pages
    if (!$this->request->isSecure() && in_array($this->method, ['login', 'register', 'checkout'])) {
        $this->instance->redirect('https://' . $this->request->getHost() . $this->request->uri());
    }
}
```

---

### Security and Validation

#### `requireMethod($method = 'get')`

Requires a specific HTTP method.

**Parameters:**

-   `$method` (string): Required HTTP method

**Throws:** `Exception` if method doesn't match

**Example:**

```php
public function updateUser() {
    $this->request->requireMethod('put'); // Only allow PUT requests

    // Update logic here
}

public function deleteUser() {
    $this->request->requireMethod('delete'); // Only allow DELETE requests

    // Delete logic here
}
```

#### `requireParam($type, $var, $return = 'exception')`

Requires specific parameters to be present.

**Parameters:**

-   `$type` (string): Parameter type ('get' or 'post')
-   `$var` (string|array): Parameter name(s) to require
-   `$return` (string): Return type ('exception' or 'boolean')

**Example:**

```php
public function createUser() {
    // Require specific POST parameters
    $this->request->requireParam('post', ['name', 'email', 'password']);

    // Continue with user creation
}

// With boolean return
public function search() {
    if (!$this->request->requireParam('get', 'q', 'boolean')) {
        $this->view->render('search/form');
        return;
    }

    // Perform search
}
```

---

## Advanced Features

### Input Validation

```php
public function validateInput() {
    try {
        $validated = $this->request->validate([
            'name' => 'required|min:2|max:50',
            'email' => 'required|email',
            'age' => 'required|numeric|min:18'
        ]);

        // Use validated data
        $user = $this->models->user->createUser($validated);

    } catch (Exception $e) {
        $this->session->setFlashData('error', 'Validation failed');
        $this->instance->redirect('/form');
    }
}
```

### File Upload Security

```php
public function secureFileUpload() {
    $file = $this->request->file('upload');

    if (!$file['is_valid']) {
        throw new Exception('Invalid file upload');
    }

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!in_array($file['mime_type'], $allowedTypes)) {
        throw new Exception('File type not allowed');
    }

    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File too large');
    }

    // Process upload...
}
```

### Request Size Limits

```php
// Check request size
if ($this->request->isTooBig()) {
    $this->response->json(['error' => 'Request too large'], 413);
    return;
}
```

---

## Framework Integration

The Request API integrates with other framework components:

-   **Router**: URI parsing and route parameter access
-   **Authentication**: User authentication through headers
-   **Validation**: Built-in validation system
-   **Response**: Content negotiation for response format
-   **Security**: Input sanitization and validation
-   **File System**: Secure file upload handling

The Request API provides enterprise-level HTTP request handling with modern security features and backward compatibility.
