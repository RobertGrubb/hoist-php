# 📖 Core Concepts

Understanding the fundamental concepts of Hoist PHP will help you build robust applications efficiently.

## Framework Architecture

### **MVC Pattern**

Hoist PHP follows the Model-View-Controller (MVC) architectural pattern:

```
┌─────────────┐    ┌──────────────┐    ┌─────────────┐
│   REQUEST   │───▶│   ROUTER     │───▶│ CONTROLLER  │
└─────────────┘    └──────────────┘    └─────┬───────┘
                                             │
┌─────────────┐    ┌──────────────┐         │
│  RESPONSE   │◀───│     VIEW     │◀────────┤
└─────────────┘    └──────────────┘         │
                           ▲                │
                           │                │
                   ┌───────────────┐        │
                   │     MODEL     │◀───────┘
                   └───────────────┘
```

**Components:**

-   **Router**: Maps URLs to controllers
-   **Controller**: Handles business logic
-   **Model**: Manages data and database interactions
-   **View**: Renders templates and responses

### **Service Container**

The framework uses dependency injection through the `Instance` class:

```php
class Instance
{
    public $auth;        // Authentication
    public $cache;       // Caching system
    public $database;    // Database connections
    public $models;      // Model instances
    public $request;     // HTTP request
    public $response;    // HTTP response
    public $router;      // URL routing
    public $session;     // Session management
    public $settings;    // Configuration
    public $view;        // Template rendering
}
```

**Access Pattern:**

```php
// In controllers
$this->instance->database->client->select('users', '*');
$this->instance->cache->set('key', $data, 3600);
$this->instance->view->render('template', $data);
```

## Request Lifecycle

### **1. Bootstrap**

```php
// public/index.php
require_once '../Core/Bootstrap.php';

// Bootstrap.php initializes:
// - Autoloader
// - Error handling
// - Service container
// - Configuration
```

### **2. Routing**

```php
// Router processes the request
$router = new Router($instance);
$router->route($request->getUri(), $request->getMethod());

// Routes.php defines mappings
return [
    ['method' => 'GET', 'url' => '/users', 'target' => 'UserController@index'],
    ['method' => 'POST', 'url' => '/users', 'target' => 'UserController@store'],
];
```

### **3. Controller Execution**

```php
class UserController extends Controller
{
    public function index()
    {
        // 1. Process request
        $filters = $this->request->input('filters', []);

        // 2. Interact with models
        $users = $this->instance->models->user->getFiltered($filters);

        // 3. Return response
        if ($this->request->wantsJson()) {
            return $this->instance->response->json($users);
        }

        $this->instance->view->render('users/index', ['users' => $users]);
    }
}
```

### **4. Response**

```php
// Automatic response handling
// - JSON for API requests
// - HTML for web requests
// - Error pages for exceptions
// - Redirects for form submissions
```

## Configuration System

### **Settings Structure**

```php
// Core/Libraries/Settings.php
class Settings
{
    private $config = [
        'app' => [
            'name' => 'Hoist PHP',
            'environment' => 'development',
            'debug' => true,
            'timezone' => 'UTC'
        ],
        'database' => [
            'default' => 'file',
            'connections' => [
                'file' => ['driver' => 'json', 'path' => 'Application/Database/'],
                'mysql' => ['host' => 'localhost', 'database' => 'app']
            ]
        ],
        'cache' => [
            'default' => 'file',
            'stores' => [
                'file' => ['driver' => 'file', 'path' => 'cache/'],
                'redis' => ['host' => '127.0.0.1', 'port' => 6379]
            ]
        ]
    ];
}
```

### **Environment Configuration**

```php
// Different settings per environment
public function getEnvironmentConfig()
{
    $env = $_ENV['APP_ENV'] ?? 'development';

    $configs = [
        'development' => [
            'debug' => true,
            'cache_enabled' => false
        ],
        'production' => [
            'debug' => false,
            'cache_enabled' => true
        ]
    ];

    return $configs[$env] ?? $configs['development'];
}
```

## Database Abstraction

### **FileDatabase (Default)**

JSON-based storage for rapid development:

```php
// Automatic file management
$users = $this->instance->models->user->getAll();

// File structure:
// Application/Database/app/users.json
[
    {"id": 1, "name": "John", "email": "john@example.com"},
    {"id": 2, "name": "Jane", "email": "jane@example.com"}
]
```

### **SQL Databases**

Traditional database support:

```php
// Configuration
'database' => [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'hoist_app',
            'username' => 'root',
            'password' => 'secret'
        ]
    ]
]

// Usage
$users = $this->instance->database->client->select('users', '*', [
    'status' => 'active'
]);
```

## Caching Strategy

### **Multi-Tier Caching**

```php
class Cache
{
    // Memory cache (fastest)
    private $memory = [];

    // File cache (persistent)
    private $fileCache;

    // External cache (shared)
    private $externalCache; // Redis/Memcached

    public function get($key)
    {
        // 1. Check memory first
        if (isset($this->memory[$key])) {
            return $this->memory[$key];
        }

        // 2. Check file cache
        if ($data = $this->fileCache->get($key)) {
            $this->memory[$key] = $data;
            return $data;
        }

        // 3. Check external cache
        if ($data = $this->externalCache->get($key)) {
            $this->memory[$key] = $data;
            $this->fileCache->set($key, $data);
            return $data;
        }

        return null;
    }
}
```

### **Cache Usage Patterns**

```php
// Simple caching
$this->instance->cache->set('user.1', $user, 3600);
$user = $this->instance->cache->get('user.1');

// Cache with callback
$stats = $this->instance->cache->remember('daily.stats', 3600, function() {
    return $this->calculateDailyStats();
});

// Cache tags for group invalidation
$this->instance->cache->tags(['users', 'active'])->set('active.users', $users);
$this->instance->cache->tags(['users'])->flush(); // Clears all user-related cache
```

## Security Model

### **Input Sanitization**

```php
// Automatic sanitization in Request class
$input = $this->request->input('name'); // XSS-safe
$raw = $this->request->raw('content');  // Unsanitized

// Manual sanitization
$clean = $this->instance->security->sanitize($input);
$escaped = $this->instance->security->escape($input);
```

### **CSRF Protection**

```php
// Automatic CSRF token generation
<form method="post">
    <?= $this->instance->security->csrfField() ?>
    <!-- form fields -->
</form>

// Automatic verification in Controller
if (!$this->instance->security->verifyCsrf()) {
    throw new SecurityException('Invalid CSRF token');
}
```

### **Authentication**

```php
// Login user
$this->instance->auth->login($user);

// Check authentication
if ($this->instance->auth->check()) {
    $user = $this->instance->auth->user();
}

// Logout
$this->instance->auth->logout();
```

## Error Handling

### **Exception Hierarchy**

```php
Exception
├── ValidationException     // Form validation errors
├── AuthenticationException // Login failures
├── AuthorizationException  // Permission denied
├── NotFoundException      // 404 errors
├── DatabaseException      // Database errors
└── SecurityException      // Security violations
```

### **Error Response**

```php
try {
    $this->processPayment($amount);
} catch (ValidationException $e) {
    return $this->instance->response->sendError($e->getErrors(), 422);
} catch (PaymentException $e) {
    return $this->instance->response->sendError('Payment failed', 400);
} catch (Exception $e) {
    // Log error and return generic message
    error_log($e->getMessage());
    return $this->instance->response->sendError('Server error', 500);
}
```

## Best Practices

### **Controller Guidelines**

```php
class ApiController extends Controller
{
    // ✅ Single responsibility
    public function createUser()
    {
        // ✅ Validate input
        $validated = $this->request->validate([
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users'
        ]);

        // ✅ Use models for business logic
        $user = $this->instance->models->user->create($validated);

        // ✅ Return consistent responses
        return $this->instance->response->json([
            'success' => true,
            'data' => $user,
            'message' => 'User created successfully'
        ], 201);
    }
}
```

### **Model Guidelines**

```php
class UserModel extends Model
{
    // ✅ Encapsulate data access
    public function getActiveUsers()
    {
        return $this->database->select('users', '*', [
            'status' => 'active',
            'verified' => true
        ]);
    }

    // ✅ Business logic in models
    public function promoteToAdmin($userId)
    {
        $user = $this->find($userId);
        if (!$user || $user['role'] === 'admin') {
            return false;
        }

        return $this->update($userId, [
            'role' => 'admin',
            'promoted_at' => date('Y-m-d H:i:s')
        ]);
    }
}
```

### **Performance Tips**

```php
// ✅ Use caching for expensive operations
$results = $this->instance->cache->remember('reports.monthly', 3600, function() {
    return $this->generateMonthlyReport();
});

// ✅ Lazy load relationships
$user = $this->instance->models->user->find($id);
$posts = $this->instance->models->post->getByUser($id); // When needed

// ✅ Optimize database queries
$users = $this->instance->database->client->select('users',
    ['id', 'name', 'email'], // Only needed columns
    ['status' => 'active'],   // Specific conditions
    ['ORDER' => ['created_at' => 'DESC'], 'LIMIT' => 50]
);
```

---

These core concepts form the foundation of Hoist PHP development. Understanding them will help you build maintainable, scalable applications efficiently.

**Next:** [Components Guide](../components/) - Deep dive into framework components.
