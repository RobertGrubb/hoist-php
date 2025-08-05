# Core Architecture

Hoist PHP follows a simple yet powerful MVC architecture with a central service container (Instance) that manages all framework components and dependencies.

## Framework Overview

### Core Components

1. **Instance** - Central service container and application context
2. **Router** - URL routing with convention-based and explicit routes
3. **Controller** - Base controller with dependency injection
4. **Model** - Base model with Medoo ORM integration and data filtering
5. **View** - Template rendering system
6. **Database** - Medoo ORM wrapper with MySQL support
7. **FileDatabase** - JSON-based database for development/testing

## Service Container (Instance)

The Instance class serves as the main service container, providing:

### Dependency Injection

```php
class UserController extends Controller
{
    public function index()
    {
        // Access services through the instance container
        $users = $this->instance->database->client->select('users', '*');
        $settings = $this->instance->settings->get('app.name');
        $this->instance->view->render('user/index', ['users' => $users]);
    }
}
```

### Available Services

-   `$instance->database` - Medoo ORM wrapper
-   `$instance->fileDatabase` - JSON file storage
-   `$instance->request` - HTTP request handling
-   `$instance->response` - HTTP response management
-   `$instance->session` - Session and flash messages
-   `$instance->auth` - Authentication system
-   `$instance->view` - Template rendering
-   `$instance->router` - URL routing
-   `$instance->security` - Security utilities
-   `$instance->validation` - Input validation
-   `$instance->uploader` - File upload handling
-   `$instance->settings` - Application settings
-   `$instance->cleaner` - Data sanitization
-   `$instance->cron` - Scheduled tasks
-   `$instance->cache` - Caching system

### Model and Library Registration

```php
// Models are auto-registered and accessible via:
$user = $instance->models->user->get(['id' => 123]);

// Libraries can be registered for custom functionality:
$instance->registerLibrary('email', new EmailLibrary());
$instance->libraries->email->send($message);
```

## MVC Pattern

### Controllers

Controllers handle HTTP requests and coordinate between models and views:

```php
class UserController extends Controller
{
    public function before()
    {
        // Called before each method - good for auth checks
        $this->instance->auth->required();
    }

    public function index()
    {
        // Main controller logic
        $users = $this->models->user->getMany(['status' => 'active']);
        $this->instance->view->render('user/index', compact('users'));
    }

    public function after()
    {
        // Called after each method - cleanup happens here
        parent::after(); // Always call parent for framework cleanup
    }
}
```

### Models

Models handle data operations with built-in security features:

```php
class UserModel extends Model
{
    public $table = 'users';
    public $hiddenFields = ['password', 'reset_token']; // Automatically filtered

    public function getActiveUsers()
    {
        return $this->getMany(['status' => 'active']);
    }

    public function createUser($data)
    {
        // Data validation should happen before this
        return $this->create($data);
    }

    // Guard deletion with dependency checking
    public function safeDelete($userId)
    {
        return $this->guardDelete([
            ['table' => 'orders', 'where' => ['user_id' => $userId]]
        ], $userId);
    }
}
```

### Views

Views handle presentation with PHP templates:

```php
<!-- Views/user/index.php -->
<div class="user-list">
    <?php foreach ($users as $user): ?>
        <div class="user-card">
            <h3><?= htmlspecialchars($user['name']) ?></h3>
            <p><?= htmlspecialchars($user['email']) ?></p>
        </div>
    <?php endforeach; ?>
</div>
```

## Routing System

### Convention-Based Routing

URLs automatically map to controllers and methods:

```
/users          → UserController::index()
/users/create   → UserController::create()
/user-profile   → UserProfileController::index()
/admin/reports  → Admin\ReportsController::index()
```

### Explicit Route Registration

For custom routing patterns:

```php
// Register specific routes
$router->registerRoute('GET', '/api/users/:id', 'ApiController@getUser');
$router->registerRoute('POST', '/webhook', function() {
    // Handle webhook
});

// Access route parameters
$userId = $router->param('id');
$allParams = $router->param(); // Get all parameters
```

### Nested Controllers

Supports organizing controllers in subdirectories:

```
Controllers/
  UserController.php          → /users
  Admin/
    ReportsController.php     → /admin/reports
    UserController.php        → /admin/users
  Api/
    V1Controller.php          → /api/v1
```

## Database Architecture

### Dual Database System

Hoist PHP supports both SQL and file-based databases:

#### Medoo ORM (Primary)

```php
// Direct Medoo access
$medoo = $this->instance->database->client;
$users = $medoo->select('users', '*', ['status' => 'active']);

// Through models
$users = $this->models->user->getMany(['status' => 'active']);
```

#### FileDatabase (Development)

```php
// File-based storage with SQL-like interface
$users = $this->instance->fileDatabase
    ->table('users')
    ->where('status', '=', 'active')
    ->order('created_at', 'DESC')
    ->all();
```

## Application Lifecycle

### Bootstrap Process

1. **Environment Setup** - Load environment variables
2. **Core Class Validation** - Ensure all framework classes exist
3. **Service Instantiation** - Create all framework services
4. **Model/Library Registration** - Register application components
5. **Request Processing** - Route and handle requests

### Request Flow

1. **Bootstrap** (`public/index.php`) - Initialize framework
2. **Router** - Match URL to controller/method
3. **Controller Before Hook** - Pre-execution logic
4. **Controller Method** - Main request handling
5. **Controller After Hook** - Cleanup and post-processing

### Initialization Example

```php
// public/index.php
require_once '../source/Core/Bootstrap.php';

// Instance is now available globally
$router = $Instance->router;
$router->run();
```

## Security Features

### Automatic Data Filtering

Models automatically filter sensitive fields:

```php
class UserModel extends Model
{
    public $hiddenFields = ['password', 'reset_token'];

    // These fields are automatically stripped from results
    public function getUser($id)
    {
        return $this->get($id); // password and reset_token excluded
    }
}
```

### Request Validation

```php
$cleanEmail = $this->instance->cleaner->email($_POST['email']);
$isValid = $this->instance->validation->email($cleanEmail);
```

### SQL Injection Protection

Medoo provides automatic prepared statements:

```php
// This is automatically parameterized and safe
$users = $medoo->select('users', '*', [
    'email' => $_POST['email'],
    'status' => 'active'
]);
```

## Environment Configuration

### Required Environment Variables

```bash
# Database connection
DB_HOST=localhost
DB_USER=username
DB_PASSWORD=password
DB_NAME=database_name

# Application settings
BASE_URL=https://yourdomain.com
CACHE_DRIVER=file
```

### CLI vs Web Detection

The framework automatically detects CLI usage:

```php
if ($instance->isCommandLine()) {
    // Running from command line - no web services
    echo "Running CLI command\n";
} else {
    // Web request - full services available
    $instance->view->render('dashboard');
}
```

## Error Handling

### Exception Management

```php
try {
    $userId = $this->models->user->create($userData);
} catch (Exception $e) {
    error_log($e->getMessage());
    $this->instance->session->setFlash('error', 'User creation failed');
    $this->instance->redirect('/users');
}
```

### 404 Handling

The router automatically renders error pages for missing controllers/methods:

```php
// If controller/method not found, renders:
$this->instance->view->render('error/index');
```

## Extension Points

### Custom Libraries

```php
class EmailLibrary extends Library
{
    public function send($to, $subject, $body)
    {
        // Email sending logic
    }
}

// Register in Bootstrap.php
$Instance->registerLibrary('email', new EmailLibrary());
```

### Custom Models

```php
class CustomModel extends Model
{
    public $table = 'custom_table';

    public function customMethod()
    {
        // Custom business logic
    }
}
```

## Best Practices

### 1. Service Access

Always access services through the instance container:

```php
// Good
$this->instance->database->client->select(...);

// Avoid direct instantiation
$db = new Database(); // Don't do this
```

### 2. Model Usage

Use models for all data operations:

```php
// Good - through model
$users = $this->models->user->getMany(['status' => 'active']);

// Avoid - direct database calls in controllers
$users = $this->instance->database->client->select('users', '*', ['status' => 'active']);
```

### 3. Error Handling

Implement proper error handling and user feedback:

```php
try {
    $result = $this->models->user->create($data);
    $this->instance->session->setFlash('success', 'User created successfully');
} catch (Exception $e) {
    error_log($e->getMessage());
    $this->instance->session->setFlash('error', 'Failed to create user');
}
```

The Hoist PHP architecture provides a clean, secure, and maintainable foundation for building PHP applications with modern patterns while keeping complexity manageable.
