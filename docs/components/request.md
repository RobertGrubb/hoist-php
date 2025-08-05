# 🎯 Request Handling

The Request class is the heart of HTTP request processing in Hoist PHP, providing a modern, secure, and feature-rich API for handling all types of HTTP interactions.

## Quick Reference

### **Common Methods**

```php
// Input handling
$name = $this->request->input('name');
$email = $this->request->input('email', 'default@example.com');
$all = $this->request->all();

// Request information
$method = $this->request->method();
$isPost = $this->request->isPost();
$wantsJson = $this->request->wantsJson();

// File uploads
$file = $this->request->file('avatar');
$files = $this->request->allFiles();

// Validation
$validated = $this->request->validate([
    'name' => 'required|min:2',
    'email' => 'required|email'
]);
```

## Input Handling

### **Basic Input Access**

```php
public function processForm()
{
    // Get single input with default
    $name = $this->request->input('name', 'Anonymous');

    // Get all inputs
    $data = $this->request->all();

    // Get only specific inputs
    $credentials = $this->request->only(['email', 'password']);

    // Get all except specific inputs
    $profile = $this->request->except(['password', 'password_confirmation']);

    // Check if input exists
    if ($this->request->has('email')) {
        $email = $this->request->input('email');
    }

    // Check if input is filled (not empty)
    if ($this->request->filled('description')) {
        $description = $this->request->input('description');
    }
}
```

### **Array Input Handling**

```php
public function processArrays()
{
    // HTML: <input name="tags[]" value="php">
    $tags = $this->request->input('tags', []);

    // HTML: <input name="user[name]" value="John">
    $userName = $this->request->input('user.name');
    $userData = $this->request->input('user');

    // Nested arrays
    // HTML: <input name="products[0][name]" value="Laptop">
    $products = $this->request->input('products');
    $firstProductName = $this->request->input('products.0.name');
}
```

### **Raw and Unfiltered Input**

```php
public function handleRawInput()
{
    // Get sanitized input (default)
    $content = $this->request->input('content');

    // Get raw, unfiltered input
    $rawContent = $this->request->raw('content');

    // Get JSON payload
    $jsonData = $this->request->json();

    // Get specific JSON field
    $userId = $this->request->json('user_id');
}
```

## Request Information

### **HTTP Method Detection**

```php
public function handleMethod()
{
    $method = $this->request->method(); // GET, POST, PUT, DELETE, etc.

    // Method checking
    if ($this->request->isGet()) {
        // Handle GET request
    }

    if ($this->request->isPost()) {
        // Handle POST request
    }

    if ($this->request->isMethod('PUT')) {
        // Handle PUT request
    }

    // AJAX detection
    if ($this->request->isAjax()) {
        return $this->instance->response->json($data);
    }

    // JSON API request
    if ($this->request->wantsJson()) {
        return $this->instance->response->json($data);
    }
}
```

### **URL and Path Information**

```php
public function getUrlInfo()
{
    $fullUrl = $this->request->fullUrl();        // http://example.com/path?query=1
    $url = $this->request->url();                // http://example.com/path
    $path = $this->request->path();              // /path
    $query = $this->request->getQueryString();   // query=1

    // Check if URL matches pattern
    if ($this->request->is('admin/*')) {
        // Administrative section
    }

    if ($this->request->is('api/v*/users')) {
        // API endpoint
    }
}
```

### **Headers and Content Type**

```php
public function handleHeaders()
{
    // Get specific header
    $userAgent = $this->request->header('User-Agent');
    $authHeader = $this->request->header('Authorization');

    // Get all headers
    $headers = $this->request->headers();

    // Content type detection
    $contentType = $this->request->getContentType();

    if ($this->request->isJson()) {
        $data = $this->request->json();
    }

    if ($this->request->isXml()) {
        $xml = $this->request->getContent();
    }
}
```

## File Uploads

### **Single File Upload**

```php
public function uploadAvatar()
{
    if ($this->request->hasFile('avatar')) {
        $file = $this->request->file('avatar');

        // File information
        $originalName = $file->getOriginalName();
        $extension = $file->getExtension();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Validation
        if ($file->isValid()) {
            // Move to permanent location
            $filename = 'avatar_' . time() . '.' . $extension;
            $path = $file->move('uploads/avatars/', $filename);

            // Save to database
            $this->instance->models->user->update($userId, [
                'avatar' => $path
            ]);

            return $this->instance->response->sendSuccess('Avatar uploaded successfully');
        }
    }

    return $this->instance->response->sendError('No valid file uploaded');
}
```

### **Multiple File Upload**

```php
public function uploadGallery()
{
    $files = $this->request->allFiles();
    $uploadedFiles = [];

    foreach ($files['gallery'] as $file) {
        if ($file->isValid()) {
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                continue;
            }

            // Generate unique filename
            $filename = uniqid() . '_' . $file->getOriginalName();
            $path = $file->move('uploads/gallery/', $filename);

            $uploadedFiles[] = [
                'original_name' => $file->getOriginalName(),
                'filename' => $filename,
                'path' => $path,
                'size' => $file->getSize()
            ];
        }
    }

    return $this->instance->response->json([
        'success' => true,
        'uploaded_files' => $uploadedFiles
    ]);
}
```

### **File Validation**

```php
public function validateFileUpload()
{
    $validation = $this->request->validate([
        'document' => 'required|file|max:5120|mimes:pdf,doc,docx',
        'images.*' => 'image|max:2048'
    ]);

    $file = $this->request->file('document');

    // Manual validation
    if ($file->getSize() > 5 * 1024 * 1024) { // 5MB
        return $this->instance->response->sendError('File too large');
    }

    $allowedMimes = ['application/pdf', 'application/msword'];
    if (!in_array($file->getMimeType(), $allowedMimes)) {
        return $this->instance->response->sendError('Invalid file type');
    }
}
```

## Validation

### **Basic Validation**

```php
public function validateBasicForm()
{
    try {
        $validated = $this->request->validate([
            'name' => 'required|min:2|max:50',
            'email' => 'required|email|unique:users',
            'age' => 'required|integer|min:18|max:120',
            'website' => 'url',
            'bio' => 'max:500'
        ]);

        // Process validated data
        $user = $this->instance->models->user->create($validated);

        return $this->instance->response->sendSuccess('User created successfully');

    } catch (ValidationException $e) {
        return $this->instance->response->sendError($e->getErrors(), 422);
    }
}
```

### **Advanced Validation Rules**

```php
public function validateAdvanced()
{
    $rules = [
        // Required fields
        'name' => 'required|string|min:2|max:100',
        'email' => 'required|email|unique:users,email,' . $userId,

        // Numeric validation
        'price' => 'required|numeric|min:0|max:9999.99',
        'quantity' => 'required|integer|between:1,100',

        // Date validation
        'birth_date' => 'required|date|before:today',
        'event_date' => 'required|date|after:tomorrow',

        // String validation
        'username' => 'required|alpha_num|min:3|max:20|unique:users',
        'password' => 'required|min:8|confirmed',
        'phone' => 'required|regex:/^\+?[1-9]\d{1,14}$/',

        // Array validation
        'tags' => 'required|array|min:1|max:5',
        'tags.*' => 'string|max:20',

        // File validation
        'avatar' => 'required|image|max:2048|dimensions:min_width=100,min_height=100',
        'documents.*' => 'file|mimes:pdf,doc,docx|max:10240'
    ];

    $validated = $this->request->validate($rules);
}
```

### **Custom Validation Messages**

```php
public function validateWithCustomMessages()
{
    $rules = [
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8'
    ];

    $messages = [
        'email.required' => 'Please provide your email address',
        'email.email' => 'Please provide a valid email address',
        'email.unique' => 'This email is already registered',
        'password.min' => 'Password must be at least 8 characters long'
    ];

    $validated = $this->request->validate($rules, $messages);
}
```

### **Conditional Validation**

```php
public function validateConditional()
{
    $rules = [
        'account_type' => 'required|in:personal,business',
        'company_name' => 'required_if:account_type,business|max:100',
        'tax_id' => 'required_if:account_type,business|numeric',
        'personal_id' => 'required_if:account_type,personal|numeric'
    ];

    $validated = $this->request->validate($rules);
}
```

## Security Features

### **Input Sanitization**

```php
public function handleSanitization()
{
    // Automatic XSS protection (default)
    $safeName = $this->request->input('name'); // HTML entities encoded

    // Raw input (when needed)
    $rawContent = $this->request->raw('content');

    // Manual sanitization
    $cleanContent = $this->request->sanitize($rawContent, [
        'allowed_tags' => ['p', 'br', 'strong', 'em'],
        'remove_empty_tags' => true
    ]);
}
```

### **CSRF Protection**

```php
public function handleCsrf()
{
    // CSRF verification (automatic in POST requests)
    if (!$this->request->verifyCsrf()) {
        return $this->instance->response->sendError('Invalid CSRF token', 419);
    }

    // Get CSRF token for forms
    $token = $this->request->getCsrfToken();

    $this->instance->view->render('form', [
        'csrf_token' => $token
    ]);
}
```

### **Rate Limiting**

```php
public function handleRateLimit()
{
    $key = 'api.user.' . $this->instance->auth->id();

    if ($this->request->rateLimitExceeded($key, 100, 3600)) { // 100 requests per hour
        return $this->instance->response->sendError('Rate limit exceeded', 429);
    }

    // Process request...
}
```

## API Development

### **JSON API Handling**

```php
public function apiEndpoint()
{
    // Check if client wants JSON
    if (!$this->request->wantsJson()) {
        return $this->instance->response->sendError('JSON API endpoint', 406);
    }

    // Get JSON payload
    $data = $this->request->json();

    // Validate JSON structure
    $validated = $this->request->validate([
        'name' => 'required|string',
        'data.*.id' => 'required|integer',
        'data.*.value' => 'required|string'
    ]);

    // Process and return JSON response
    $result = $this->processApiData($validated);

    return $this->instance->response->json([
        'success' => true,
        'data' => $result,
        'meta' => [
            'timestamp' => time(),
            'version' => '1.0'
        ]
    ]);
}
```

### **Content Negotiation**

```php
public function handleContentNegotiation()
{
    $acceptHeader = $this->request->header('Accept');

    if (strpos($acceptHeader, 'application/json') !== false) {
        return $this->instance->response->json($data);
    }

    if (strpos($acceptHeader, 'application/xml') !== false) {
        return $this->instance->response->xml($data);
    }

    // Default to HTML
    $this->instance->view->render('data/show', ['data' => $data]);
}
```

## Real-World Examples

### **User Registration**

```php
public function register()
{
    try {
        $validated = $this->request->validate([
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'terms' => 'required|accepted',
            'avatar' => 'image|max:2048'
        ]);

        // Handle avatar upload
        if ($this->request->hasFile('avatar')) {
            $avatar = $this->request->file('avatar');
            $avatarPath = $avatar->move('uploads/avatars/',
                'avatar_' . uniqid() . '.' . $avatar->getExtension()
            );
            $validated['avatar'] = $avatarPath;
        }

        // Create user
        $user = $this->instance->models->user->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => password_hash($validated['password'], PASSWORD_DEFAULT),
            'avatar' => $validated['avatar'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Log the user in
        $this->instance->auth->login($user);

        if ($this->request->wantsJson()) {
            return $this->instance->response->json([
                'success' => true,
                'user' => $user,
                'token' => $this->instance->auth->generateToken()
            ]);
        }

        return $this->instance->response->redirect('/dashboard')
            ->with('success', 'Registration successful!');

    } catch (ValidationException $e) {
        if ($this->request->wantsJson()) {
            return $this->instance->response->json([
                'success' => false,
                'errors' => $e->getErrors()
            ], 422);
        }

        return $this->instance->response->back()
            ->withErrors($e->getErrors())
            ->withInput();
    }
}
```

### **File Upload with Progress**

```php
public function uploadWithProgress()
{
    // Handle chunked upload
    if ($this->request->has('chunk')) {
        return $this->handleChunkedUpload();
    }

    // Regular upload
    if (!$this->request->hasFile('file')) {
        return $this->instance->response->sendError('No file provided');
    }

    $file = $this->request->file('file');

    // Validate file
    if (!$file->isValid()) {
        return $this->instance->response->sendError('Invalid file');
    }

    // Check file size (100MB max)
    if ($file->getSize() > 100 * 1024 * 1024) {
        return $this->instance->response->sendError('File too large');
    }

    // Move file with progress tracking
    $filename = uniqid() . '_' . $file->getOriginalName();
    $path = 'uploads/files/' . $filename;

    if ($file->move('uploads/files/', $filename)) {
        // Store file metadata
        $fileRecord = $this->instance->models->file->create([
            'original_name' => $file->getOriginalName(),
            'filename' => $filename,
            'path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_at' => date('Y-m-d H:i:s')
        ]);

        return $this->instance->response->json([
            'success' => true,
            'file' => $fileRecord
        ]);
    }

    return $this->instance->response->sendError('Upload failed');
}
```

## Performance Tips

### **Input Caching**

```php
public function optimizeInputAccess()
{
    // Cache frequently accessed inputs
    static $cachedInputs = [];

    $key = 'user_preferences';
    if (!isset($cachedInputs[$key])) {
        $cachedInputs[$key] = $this->request->input('preferences', []);
    }

    return $cachedInputs[$key];
}
```

### **Large File Handling**

```php
public function handleLargeFiles()
{
    // Stream large file uploads
    if ($this->request->hasFile('large_file')) {
        $file = $this->request->file('large_file');

        // Use streaming for files > 10MB
        if ($file->getSize() > 10 * 1024 * 1024) {
            return $this->streamFileUpload($file);
        }
    }
}
```

---

The Request class provides everything needed for robust HTTP request handling in modern web applications. Its clean API, comprehensive validation, and security features make it ideal for both traditional web apps and modern APIs.

**Next:** [Response Handling](response.md) - Learn about HTTP response generation.
