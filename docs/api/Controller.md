# Controller API Documentation

## Overview

The Controller API provides the base class for all application controllers in the Hoist PHP framework. It offers dependency injection, lifecycle management, and convenient access to framework services for building structured web applications and APIs.

## Class: Controller

**Location**: `Core/Libraries/Controller.php`  
**Usage**: Extend this class for all application controllers  
**Version**: 1.0.0 with comprehensive service injection

---

## Properties

### Injected Framework Services

#### `$instance`

**Type**: `Instance`  
**Description**: Main application instance container providing access to all framework services

**Usage:**

```php
// Access any framework service
$this->instance->auth->required();
$this->instance->view->render('template');
$this->instance->database->query('SELECT * FROM users');
```

#### `$models`

**Type**: `object`  
**Description**: Container of all registered application models for data operations

**Usage:**

```php
// Access model methods directly
$user = $this->models->user->getById(123);
$posts = $this->models->post->getAllPublished();
$this->models->analytics->trackPageView();
```

#### `$request`

**Type**: `Request`  
**Description**: HTTP request handling service for accessing request data and parameters

**Usage:**

```php
// Get request data
$email = $this->request->post('email');
$userId = $this->request->get('id');
$isAjax = $this->request->isAjax();
```

#### `$session`

**Type**: `Session`  
**Description**: Session management and flash message service

**Usage:**

```php
// Session operations
$this->session->set('user_id', 123);
$this->session->setFlashData('success', 'User created!');
$userId = $this->session->get('user_id');
```

#### `$router`

**Type**: `Router`  
**Description**: URL routing and parameter extraction service

**Usage:**

```php
// Get route parameters
$userId = $this->router->param('id');
$action = $this->router->param('action');
```

---

## Constructor

### `__construct($instance)`

Initializes the controller with framework dependencies through dependency injection.

**Parameters:**

-   `$instance` (Instance): The main application service container

**Automatic Injection:**

-   `$this->instance`: Full service container
-   `$this->request`: HTTP request handling
-   `$this->models`: All registered models
-   `$this->session`: Session management
-   `$this->router`: URL routing

**Example:**

```php
// Controllers are automatically instantiated by the framework
// No manual construction needed

class UserController extends Controller
{
    public function __construct($instance)
    {
        parent::__construct($instance);

        // Additional initialization if needed
        $this->customService = $instance->customService;
    }
}
```

---

## Lifecycle Hooks

### `before()`

Pre-execution hook called before the main controller method.

**Called**: Automatically by the router before method execution  
**Override**: Implement in child controllers for common functionality  
**Return**: `void`

**Common Use Cases:**

-   Authentication validation
-   Permission checks
-   Request preprocessing
-   Common data loading
-   Security validations

**Example:**

```php
class AdminController extends Controller
{
    public function before()
    {
        // Require authentication for all admin methods
        $this->instance->auth->required();

        // Require admin group membership
        $this->instance->auth->requireGroup('admin');

        // Load common admin data
        $this->adminData = [
            'current_user' => $this->instance->auth->user,
            'permissions' => $this->instance->auth->getPermissions(),
            'unread_notifications' => $this->models->notification->getUnreadCount()
        ];

        // Set security headers
        $this->instance->response->setHeader('X-Frame-Options', 'DENY');
    }

    public function dashboard()
    {
        // before() has already validated auth and loaded adminData
        $stats = $this->models->analytics->getDashboardStats();

        $this->instance->view->render('admin/dashboard', [
            'admin_data' => $this->adminData,
            'stats' => $stats
        ]);
    }
}
```

### `after()`

Post-execution hook called after the main controller method.

**Called**: Automatically by the router after method execution  
**Default**: Performs `$instance->cleanup()` and session management  
**Override**: Extend in child controllers for additional cleanup  
**Return**: `void`

**Default Operations:**

-   Application cleanup via `$instance->cleanup()`
-   Flash message session updates
-   Temporary data clearing

**Example:**

```php
class ApiController extends Controller
{
    public function after()
    {
        // Always call parent cleanup first
        parent::after();

        // Log API request for analytics
        $this->logApiRequest();

        // Clear sensitive data from memory
        if (isset($this->sensitiveData)) {
            unset($this->sensitiveData);
        }

        // Update rate limiting counters
        $this->updateRateLimit();
    }

    private function logApiRequest()
    {
        $this->models->analytics->logApiCall([
            'endpoint' => $this->router->getCurrentRoute(),
            'method' => $this->request->method(),
            'user_id' => $this->instance->auth->user['id'] ?? null,
            'response_time' => microtime(true) - REQUEST_START_TIME
        ]);
    }
}
```

---

## Service Access Patterns

### Direct Property Access

Use injected properties for frequently accessed services:

```php
class PostController extends Controller
{
    public function create()
    {
        // Direct access to injected services
        $title = $this->request->post('title');
        $content = $this->request->post('content');

        $postId = $this->models->post->create([
            'title' => $title,
            'content' => $content,
            'author_id' => $this->session->get('user_id')
        ]);

        $this->session->setFlashData('success', 'Post created successfully!');
        $this->instance->response->redirect('/posts/' . $postId);
    }
}
```

### Instance Container Access

Use `$this->instance` for additional services:

```php
class UserController extends Controller
{
    public function profile()
    {
        // Authentication check
        $this->instance->auth->required();

        // Get current user
        $user = $this->instance->auth->user;

        // Validate permissions
        if (!$this->instance->auth->hasPermission('view_profile')) {
            $this->instance->response->sendError('Access denied', 403);
            return;
        }

        // Render with view service
        $this->instance->view->render('user/profile', ['user' => $user]);
    }
}
```

---

## Complete Controller Examples

### Basic Web Controller

```php
class HomeController extends Controller
{
    public function index()
    {
        // Get recent posts for homepage
        $recentPosts = $this->models->post->getRecent(5);

        // Get featured content
        $featured = $this->models->content->getFeatured();

        // Check if user is logged in
        $isLoggedIn = $this->instance->auth->check();

        // Render homepage
        $this->instance->view->render('home/index', [
            'recent_posts' => $recentPosts,
            'featured' => $featured,
            'is_logged_in' => $isLoggedIn
        ]);
    }

    public function about()
    {
        $teamMembers = $this->models->team->getAllActive();

        $this->instance->view->render('home/about', [
            'team_members' => $teamMembers
        ]);
    }
}
```

### Authentication Controller

```php
class AuthController extends Controller
{
    public function before()
    {
        // Only allow unauthenticated users for login/register
        if (in_array($this->router->param('action'), ['login', 'register'])) {
            if ($this->instance->auth->check()) {
                $this->instance->response->redirect('/dashboard');
                return;
            }
        }
    }

    public function login()
    {
        if ($this->request->method() === 'POST') {
            $email = $this->request->post('email');
            $password = $this->request->post('password');
            $remember = $this->request->post('remember');

            if ($this->instance->auth->login($email, $password, $remember)) {
                $this->session->setFlashData('success', 'Login successful!');

                // Redirect to intended page or dashboard
                $redirect = $this->session->get('redirect_after_login', '/dashboard');
                $this->session->unset('redirect_after_login');
                $this->instance->response->redirect($redirect);
            } else {
                $this->session->setFlashData('error', 'Invalid email or password');
                $this->instance->response->redirect('/login');
            }
        } else {
            $this->instance->view->render('auth/login');
        }
    }

    public function logout()
    {
        $this->instance->auth->logout();
        $this->session->setFlashData('success', 'You have been logged out');
        $this->instance->response->redirect('/');
    }

    public function register()
    {
        if ($this->request->method() === 'POST') {
            try {
                $userData = $this->request->only(['name', 'email', 'password']);

                // Validate input
                $validation = $this->instance->validation->validate($userData, [
                    'name' => 'required|min:2|max:100',
                    'email' => 'required|email|unique:users',
                    'password' => 'required|min:8'
                ]);

                if (!$validation['valid']) {
                    $this->session->setFlashData('error', 'Validation failed');
                    $this->session->setFlashData('validation_errors', $validation['errors']);
                    $this->instance->response->redirect('/register');
                    return;
                }

                // Create user
                $userId = $this->models->user->create($userData);

                // Auto-login
                $this->instance->auth->loginById($userId);

                $this->session->setFlashData('success', 'Account created successfully!');
                $this->instance->response->redirect('/dashboard');

            } catch (Exception $e) {
                $this->session->setFlashData('error', 'Registration failed: ' . $e->getMessage());
                $this->instance->response->redirect('/register');
            }
        } else {
            $this->instance->view->render('auth/register');
        }
    }
}
```

### API Controller

```php
class ApiController extends Controller
{
    public function before()
    {
        // Set CORS headers for all API requests
        $this->instance->response->setCorsHeaders('*', ['GET', 'POST', 'PUT', 'DELETE'], [
            'Content-Type', 'Authorization'
        ]);

        // Handle preflight requests
        if ($this->request->method() === 'options') {
            $this->instance->response->setStatusCode(200)->send();
            return;
        }

        // Require API authentication
        $this->validateApiAuth();
    }

    public function getUsers()
    {
        try {
            $page = $this->request->get('page', 1);
            $limit = $this->request->get('limit', 20);

            $users = $this->models->user->getPaginated($page, $limit);
            $total = $this->models->user->getTotalCount();

            $this->instance->response->sendJson([
                'users' => $users,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);

        } catch (Exception $e) {
            $this->instance->response->sendError('Failed to retrieve users', 500, [
                'details' => $e->getMessage()
            ]);
        }
    }

    public function createUser()
    {
        try {
            $userData = $this->request->only(['name', 'email', 'password', 'group']);

            // Validate required fields
            if (empty($userData['name']) || empty($userData['email'])) {
                $this->instance->response->sendError('Missing required fields', 400);
                return;
            }

            $userId = $this->models->user->create($userData);
            $user = $this->models->user->getById($userId);

            $this->instance->response->sendJson($user, 201);

        } catch (Exception $e) {
            $this->instance->response->sendError('Failed to create user', 422, [
                'details' => $e->getMessage()
            ]);
        }
    }

    private function validateApiAuth()
    {
        $apiKey = $this->request->header('Authorization');

        if (!$apiKey || !$this->models->apiKey->validate($apiKey)) {
            $this->instance->response->sendError('Invalid API key', 401);
            return;
        }
    }

    public function after()
    {
        parent::after();

        // Log API usage
        $this->models->analytics->logApiCall([
            'endpoint' => $this->request->getUri(),
            'method' => $this->request->method(),
            'response_code' => $this->instance->response->getStatusCode(),
            'timestamp' => time()
        ]);
    }
}
```

### CRUD Resource Controller

```php
class PostController extends Controller
{
    public function before()
    {
        // Require authentication for all post operations
        $this->instance->auth->required();

        // Check permissions for write operations
        $writeActions = ['create', 'store', 'edit', 'update', 'delete'];
        if (in_array($this->router->param('action'), $writeActions)) {
            if (!$this->instance->auth->hasPermission('manage_posts')) {
                $this->instance->response->sendError('Insufficient permissions', 403);
                return;
            }
        }
    }

    public function index()
    {
        $posts = $this->models->post->getAllByAuthor($this->instance->auth->user['id']);

        if ($this->request->wantsJson()) {
            $this->instance->response->sendJson(['posts' => $posts]);
        } else {
            $this->instance->view->render('posts/index', ['posts' => $posts]);
        }
    }

    public function show()
    {
        $postId = $this->router->param('id');
        $post = $this->models->post->getById($postId);

        if (!$post) {
            if ($this->request->wantsJson()) {
                $this->instance->response->sendError('Post not found', 404);
            } else {
                $this->session->setFlashData('error', 'Post not found');
                $this->instance->response->redirect('/posts');
            }
            return;
        }

        if ($this->request->wantsJson()) {
            $this->instance->response->sendJson(['post' => $post]);
        } else {
            $this->instance->view->render('posts/show', ['post' => $post]);
        }
    }

    public function create()
    {
        $this->instance->view->render('posts/create');
    }

    public function store()
    {
        $postData = $this->request->only(['title', 'content', 'status']);
        $postData['author_id'] = $this->instance->auth->user['id'];

        try {
            $postId = $this->models->post->create($postData);

            $this->session->setFlashData('success', 'Post created successfully!');
            $this->instance->response->redirect('/posts/' . $postId);

        } catch (Exception $e) {
            $this->session->setFlashData('error', 'Failed to create post: ' . $e->getMessage());
            $this->instance->response->redirect('/posts/create');
        }
    }

    public function edit()
    {
        $postId = $this->router->param('id');
        $post = $this->models->post->getById($postId);

        if (!$post || $post['author_id'] != $this->instance->auth->user['id']) {
            $this->session->setFlashData('error', 'Post not found or access denied');
            $this->instance->response->redirect('/posts');
            return;
        }

        $this->instance->view->render('posts/edit', ['post' => $post]);
    }

    public function update()
    {
        $postId = $this->router->param('id');
        $post = $this->models->post->getById($postId);

        if (!$post || $post['author_id'] != $this->instance->auth->user['id']) {
            $this->session->setFlashData('error', 'Post not found or access denied');
            $this->instance->response->redirect('/posts');
            return;
        }

        $postData = $this->request->only(['title', 'content', 'status']);

        try {
            $this->models->post->update($postId, $postData);

            $this->session->setFlashData('success', 'Post updated successfully!');
            $this->instance->response->redirect('/posts/' . $postId);

        } catch (Exception $e) {
            $this->session->setFlashData('error', 'Failed to update post: ' . $e->getMessage());
            $this->instance->response->redirect('/posts/' . $postId . '/edit');
        }
    }

    public function delete()
    {
        $postId = $this->router->param('id');
        $post = $this->models->post->getById($postId);

        if (!$post || $post['author_id'] != $this->instance->auth->user['id']) {
            $this->instance->response->sendError('Post not found or access denied', 404);
            return;
        }

        try {
            $this->models->post->delete($postId);

            if ($this->request->wantsJson()) {
                $this->instance->response->sendSuccess(null, 'Post deleted successfully');
            } else {
                $this->session->setFlashData('success', 'Post deleted successfully!');
                $this->instance->response->redirect('/posts');
            }

        } catch (Exception $e) {
            if ($this->request->wantsJson()) {
                $this->instance->response->sendError('Failed to delete post', 500);
            } else {
                $this->session->setFlashData('error', 'Failed to delete post: ' . $e->getMessage());
                $this->instance->response->redirect('/posts');
            }
        }
    }
}
```

---

## Best Practices

### 1. Authentication and Authorization

```php
public function before()
{
    // Always check authentication first
    $this->instance->auth->required();

    // Then check specific permissions
    if (!$this->instance->auth->hasPermission('required_permission')) {
        $this->instance->response->sendError('Access denied', 403);
        return;
    }
}
```

### 2. Input Validation

```php
public function createUser()
{
    $userData = $this->request->only(['name', 'email', 'password']);

    // Always validate input
    if (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
        $this->instance->response->sendError('Invalid email', 400);
        return;
    }
}
```

### 3. Error Handling

```php
public function processData()
{
    try {
        // Risky operation
        $result = $this->models->data->processComplexData();
        $this->instance->response->sendJson($result);

    } catch (Exception $e) {
        // Log error for debugging
        error_log('Data processing failed: ' . $e->getMessage());

        // Return user-friendly error
        $this->instance->response->sendError('Processing failed', 500);
    }
}
```

### 4. Content Negotiation

```php
public function getData()
{
    $data = $this->models->data->getAll();

    if ($this->request->wantsJson()) {
        $this->instance->response->sendJson($data);
    } else {
        $this->instance->view->render('data/index', ['data' => $data]);
    }
}
```

---

## Framework Integration

The Controller base class seamlessly integrates with all framework components:

-   **Request**: Direct access via `$this->request` for handling HTTP input
-   **Response**: Available via `$this->instance->response` for output management
-   **Models**: Direct access via `$this->models` for data operations
-   **Authentication**: Available via `$this->instance->auth` for security
-   **Session**: Direct access via `$this->session` for state management
-   **Router**: Direct access via `$this->router` for URL parameters
-   **View**: Available via `$this->instance->view` for template rendering

The Controller API provides a solid foundation for building scalable, maintainable web applications with proper separation of concerns and comprehensive framework integration.
