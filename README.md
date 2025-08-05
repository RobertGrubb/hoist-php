# Hoist PHP Framework

A lightweight, modern PHP MVC framework designed for rapid development with optional database flexibility and minimal configuration overhead.

![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-blue)
![License](https://img.shields.io/badge/License-MIT-green)
![Framework](https://img.shields.io/badge/Framework-MVC-orange)

## Table of Contents

-   [What is Hoist PHP?](#what-is-hoist-php)
-   [Why Choose Hoist?](#why-choose-hoist)
-   [Key Features](#key-features)
-   [Getting Started](#getting-started)
-   [Project Structure](#project-structure)
-   [Configuration](#configuration)
-   [Routing](#routing)
-   [Controllers](#controllers)
-   [Models & Database](#models--database)
-   [Views](#views)
-   [Authentication](#authentication)
-   [Deployment](#deployment)
-   [Contributing](#contributing)

---

## What is Hoist PHP?

Hoist is a **lightweight, convention-over-configuration PHP MVC framework** that prioritizes developer productivity and deployment simplicity. Built for modern PHP applications, it provides a clean architecture with minimal boilerplate while offering the flexibility to scale from simple websites to complex web applications.

Unlike monolithic frameworks, Hoist takes a **pragmatic approach** - it gives you the essential MVC structure you need without the bloat, while maintaining the flexibility to add complexity only when required.

## Why Choose Hoist?

### 🚀 **Rapid Development**

-   **Zero configuration** setup - works out of the box
-   **Convention-based routing** - `/users/create` automatically maps to `UsersController::create()`
-   **FileDatabase system** - Start building without database setup
-   **Docker-ready** - Single command deployment

### 🪶 **Lightweight & Fast**

-   **Minimal footprint** - Core framework under 50KB
-   **No unnecessary dependencies** - Only what you actually need
-   **Optional MySQL** - Database connection only when configured
-   **Direct file includes** - No complex autoloading overhead

### 🔧 **Flexible Architecture**

-   **Dual storage options** - FileDatabase for development, MySQL for production
-   **Optional components** - Enable features only when needed
-   **Custom routing** - Convention-based OR explicit route registration
-   **Middleware support** - Before/after controller hooks

### 📦 **Production Ready**

-   **Docker containerization** - Consistent deployment environments
-   **Security built-in** - Password hashing, input validation, CSRF protection
-   **Error handling** - Graceful degradation and informative error pages
-   **Environment configuration** - `.env` file support for different environments

### 🎯 **Developer Experience**

-   **Intuitive naming** - Clear, predictable file and class organization
-   **Comprehensive documentation** - Well-documented core with PHPDoc
-   **Modern PHP** - Built for PHP 8.1+ with type hints and modern features
-   **Debugging friendly** - Clear error messages and development tools

## Key Features

### 🗂️ **Dual Database System**

```php
// FileDatabase (JSON-based) - Zero setup
$users = $this->instance->models->user->getAll();

// MySQL (Optional) - When you need relational data
if ($this->instance->database->hasMySQL()) {
    $users = $this->instance->database->client->select('users', '*');
}
```

### 🛣️ **Smart Routing**

```php
// Convention-based (automatic)
// /users/profile → UsersController::profile()
// /user-settings → UserSettingsController::index()

// Explicit routing (when needed)
$router->registerRoute('GET', '/api/users/:id', 'ApiController@getUser');
$router->registerRoute('POST', '/api/login', 'AuthController@login');
```

### 🏗️ **MVC Architecture**

```php
// Controllers with dependency injection
class UsersController extends Controller {
    public function create() {
        $user = $this->instance->models->user->create([
            'email' => $this->instance->request->post('email'),
            'password' => password_hash($this->instance->request->post('password'), PASSWORD_DEFAULT)
        ]);

        $this->instance->view->render('users/success', ['user' => $user]);
    }
}
```

### 🔐 **Built-in Authentication**

```php
// Simple authentication with secure password hashing
if ($this->instance->auth->login($email, $password)) {
    // User authenticated
    $user = $this->instance->auth->user();
} else {
    // Authentication failed
}
```

## Getting Started

### Prerequisites

-   **PHP 8.1+** with extensions: `pdo`, `pdo_mysql`, `zip`, `curl`
-   **Docker** (recommended) or **Apache/Nginx**
-   **Composer** for dependency management

### Quick Start with Docker

1. **Clone the repository**

```bash
git clone https://github.com/RobertGrubb/hoist-php.git
cd hoist-php
```

2. **Install dependencies**

```bash
cd source
composer install
```

3. **Start with Docker**

```bash
docker-compose up -d
```

4. **Visit your application**
   Open http://localhost in your browser. You should see the Hoist welcome page!

### Manual Installation

1. **Clone and install**

```bash
git clone https://github.com/RobertGrubb/hoist-php.git
cd hoist-php/source
composer install
```

2. **Configure web server**
   Point your web server document root to the `source/public/` directory.

3. **Set permissions**

```bash
chmod -R 755 Application/Database/
```

4. **Configure environment** (optional)

```bash
cp .env.example .env
# Edit .env with your settings
```

### Your First Controller

Create `source/Application/Controllers/HelloController.php`:

```php
<?php

class HelloController extends Controller
{
    public function index()
    {
        $this->instance->view->render('hello/index', [
            'message' => 'Welcome to Hoist PHP!'
        ]);
    }

    public function api()
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Hello from Hoist API!',
            'timestamp' => date('c')
        ]);
    }
}
```

Create the view `source/Application/Views/hello/index.php`:

```html
<h1><?= htmlspecialchars($message) ?></h1>
<p>Your Hoist application is running successfully!</p>
```

Visit `/hello` to see your controller in action!

## Project Structure

```
hoist-php/
├── docker-compose.yml          # Docker configuration
├── Dockerfile                  # Container definition
├── README.md                   # This file
└── source/                     # Application source code
    ├── composer.json           # Dependencies
    ├── public/                 # Web server document root
    │   ├── index.php          # Application entry point
    │   └── assets/            # CSS, JS, images
    ├── Application/           # Your application code
    │   ├── Controllers/       # Request handlers
    │   ├── Models/           # Data layer
    │   ├── Views/            # Templates
    │   ├── Database/         # FileDatabase storage
    │   │   └── app/          # JSON data files
    │   ├── Constants.php     # Application constants
    │   ├── Routes.php        # Custom route definitions
    │   └── Redirects.php     # URL redirections
    └── Core/                 # Framework core (don't modify)
        ├── Bootstrap.php     # Framework initialization
        ├── Instance.php      # Service container
        └── Libraries/        # Core framework classes
```

## Configuration

### Environment Variables

Create a `.env` file in the `source/` directory:

```env
# Database (optional - leave empty for FileDatabase only)
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=secret
DB_NAME=hoist_app

# Application
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost

# Security
APP_KEY=your-secret-key-here
```

### Application Constants

Edit `source/Application/Constants.php`:

```php
<?php

// Application settings
define('APPLICATION_NAME', 'My Hoist App');
define('APPLICATION_VERSION', '1.0.0');
define('APPLICATION_ENVIRONMENT', $_ENV['APP_ENV'] ?? 'development');

// Paths
define('APPLICATION_DIRECTORY', __DIR__);
define('PUBLIC_DIRECTORY', APPLICATION_DIRECTORY . '/../public');

// Security
define('SECURITY_SALT', $_ENV['APP_KEY'] ?? 'change-this-key');
```

## Routing

### Convention-Based Routing (Automatic)

Hoist automatically maps URLs to controllers with support for both flat and nested directory structures:

```
URL Pattern                →  Controller Location                    →  Method
/                         →  Controllers/IndexController           →  index()
/users                    →  Controllers/UsersController           →  index()
/users/create             →  Controllers/UsersController           →  create()
/admin/reports            →  Controllers/Admin/ReportsController   →  index()
/admin/reports/users      →  Controllers/Admin/ReportsController   →  users()
/api/v1/health            →  Controllers/Api/V1Controller          →  health()
/user-profile/edit        →  Controllers/UserProfileController     →  edit()
```

### Nested Directory Support

**NEW**: Organize your controllers in subdirectories for better code organization:

```php
// Controllers/Admin/ReportsController.php
class ReportsController extends Controller
{
    public function users()    // URL: /admin/reports/users
    public function sales()    // URL: /admin/reports/sales
    public function export()   // URL: /admin/reports/export
}

// Controllers/Api/V1Controller.php
class V1Controller extends Controller
{
    public function users()    // URL: /api/v1/users
    public function health()   // URL: /api/v1/health
}

// Controllers/Dashboard/AnalyticsController.php
class AnalyticsController extends Controller
{
    public function sales()    // URL: /dashboard/analytics/sales
    public function traffic()  // URL: /dashboard/analytics/traffic
}
```

**Directory Structure Example:**

```
Controllers/
├── IndexController.php              # /
├── UsersController.php              # /users
├── Admin/                           # Admin section
│   ├── ReportsController.php        # /admin/reports
│   ├── UsersController.php          # /admin/users
│   └── SettingsController.php       # /admin/settings
├── Api/                             # API endpoints
│   ├── V1Controller.php             # /api/v1
│   └── V2Controller.php             # /api/v2
└── Dashboard/                       # Dashboard features
    ├── AnalyticsController.php      # /dashboard/analytics
    └── ReportsController.php        # /dashboard/reports
```

**How It Works:**

1. Router tries **longest path first**: `/admin/reports/users` looks for `Controllers/Admin/ReportsController.php@users()`
2. **Fallback to shorter paths**: If not found, tries `Controllers/Admin/Reports/UsersController.php@index()`
3. **Backward compatibility**: Falls back to flat structure `Controllers/UsersController.php` if no nested match
4. **Automatic path resolution**: Converts dashes to CamelCase (`user-settings` → `UserSettings`)

### Custom Routes

Register custom routes in `source/Application/Routes.php`:

```php
<?php

// API routes
$this->instance->router->registerRoute('GET', '/api/users', 'ApiController@listUsers');
$this->instance->router->registerRoute('GET', '/api/users/:id', 'ApiController@getUser');
$this->instance->router->registerRoute('POST', '/api/users', 'ApiController@createUser');

// Custom patterns
$this->instance->router->registerRoute('GET', '/blog/:year/:month/:slug', 'BlogController@article');

// Closure routes for simple endpoints
$this->instance->router->registerRoute('GET', '/health', function() {
    http_response_code(200);
    echo json_encode(['status' => 'healthy', 'timestamp' => time()]);
});
```

### Route Parameters

Access route parameters in controllers:

```php
class ApiController extends Controller
{
    public function getUser()
    {
        $userId = $this->instance->router->param('id');
        $user = $this->instance->models->user->getById($userId);

        header('Content-Type: application/json');
        echo json_encode($user);
    }
}
```

## Controllers

### Basic Controller Structure

```php
<?php

class UsersController extends Controller
{
    // Runs before any method in this controller
    public function before()
    {
        // Authentication check, logging, etc.
        if (!$this->instance->auth->isLoggedIn()) {
            $this->instance->response->redirect('/login');
        }
    }

    public function index()
    {
        $users = $this->instance->models->user->getAll();
        $this->instance->view->render('users/index', ['users' => $users]);
    }

    public function create()
    {
        if ($this->instance->request->isPost()) {
            $userData = [
                'email' => $this->instance->request->post('email'),
                'password' => password_hash($this->instance->request->post('password'), PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $user = $this->instance->models->user->create($userData);
            $this->instance->response->redirect('/users');
        }

        $this->instance->view->render('users/create');
    }

    // Runs after any method in this controller
    public function after()
    {
        // Cleanup, logging, etc.
    }
}
```

## Request Handling

Hoist provides a modern, secure Request class with enterprise-level features for handling HTTP requests, file uploads, content negotiation, and security validation.

### Modern Input Access

```php
<?php

class ApiController extends Controller
{
    public function createUser()
    {
        // Universal input access with defaults
        $name = $this->instance->request->input('name', 'Anonymous');
        $email = $this->instance->request->input('email');

        // Get all input data from GET/POST
        $allData = $this->instance->request->all();

        // Get only specific fields
        $userData = $this->instance->request->only(['name', 'email', 'age']);

        // Get all except specific fields
        $safeData = $this->instance->request->except(['password', 'token']);

        // Check if parameters exist
        if ($this->instance->request->has('email')) {
            // Process email
        }

        // Check for multiple required parameters
        if ($this->instance->request->hasAll(['name', 'email', 'password'])) {
            // All required fields present
        }
    }
}
```

### Built-in Validation

```php
<?php

class UserController extends Controller
{
    public function register()
    {
        try {
            // Validate input with rules
            $validated = $this->instance->request->validate([
                'username' => 'required|min:3|max:20',
                'email' => 'required|email',
                'password' => 'required|min:8',
                'age' => 'required'
            ]);

            // Process validated data
            $user = $this->instance->models->user->create($validated);

            return $this->instance->response->sendSuccess([
                'message' => 'User created successfully',
                'user_id' => $user['id']
            ]);

        } catch (Exception $e) {
            return $this->instance->response->sendError($e->getMessage(), 400);
        }
    }
}
```

### File Upload Handling

```php
<?php

class MediaController extends Controller
{
    public function uploadImage()
    {
        // Check if file was uploaded
        if (!$this->instance->request->hasFile('image')) {
            return $this->instance->response->sendError('No file uploaded', 400);
        }

        $file = $this->instance->request->file('image');

        // File information includes security validation
        if (!$file['is_valid']) {
            return $this->instance->response->sendError('File upload failed', 400);
        }

        // Check file size (5MB limit)
        if ($file['size'] > 5242880) {
            return $this->instance->response->sendError('File too large', 400);
        }

        // Validate image files
        if ($file['is_image']) {
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array(strtolower($file['extension']), $allowedTypes)) {
                return $this->instance->response->sendError('Invalid image type', 400);
            }
        }

        // File info includes: name, type, size, tmp_name, error, is_valid,
        // is_image, extension, mime_type
        $filename = uniqid() . '_' . $file['name'];
        // Process file upload...
    }
}
```

### Content Negotiation

```php
<?php

class ApiController extends Controller
{
    public function getData()
    {
        $data = $this->instance->models->data->getAll();

        // Automatically detect client preference
        if ($this->instance->request->wantsJson()) {
            return $this->instance->response->json($data);
        }

        // Check for specific request types
        if ($this->instance->request->isAjax()) {
            return $this->instance->response->json(['ajax' => true, 'data' => $data]);
        }

        // Traditional HTML response
        $this->instance->view->render('data/index', ['data' => $data]);
    }

    public function handleCors()
    {
        // Handle CORS preflight requests
        if ($this->instance->request->isPreflightRequest()) {
            $this->instance->response->setCorsHeaders(
                '*',
                'GET,POST,PUT,DELETE',
                'Content-Type,Authorization'
            );
            return $this->instance->response->sendSuccess('', 200);
        }

        // Regular API logic...
    }
}
```

### Security Features

```php
<?php

class SecurityController extends Controller
{
    public function clientInfo()
    {
        // Secure client IP detection (proxy-aware)
        $clientIp = $this->instance->request->getClientIp();

        // Check if request is secure
        if ($this->instance->request->isSecure()) {
            // HTTPS connection
        }

        // Request size validation
        if ($this->instance->request->isTooBig()) {
            return $this->instance->response->sendError('Request too large', 413);
        }

        // Get sanitized input (XSS protection)
        $safeInput = $this->instance->request->all(true); // true = sanitize

        // Client information
        $info = [
            'ip' => $this->instance->request->getClientIp(),
            'user_agent' => $this->instance->request->userAgent(),
            'host' => $this->instance->request->getHost(),
            'port' => $this->instance->request->getPort(),
            'size' => $this->instance->request->getSize(),
            'content_type' => $this->instance->request->getContentType(),
            'is_secure' => $this->instance->request->isSecure(),
            'referer' => $this->instance->request->referer()
        ];

        return $this->instance->response->json($info);
    }
}
```

### Legacy Methods (Backward Compatible)

All existing Request methods continue to work exactly as before:

```php
// Traditional methods still work
$userId = $this->instance->request->get('id');
$formData = $this->instance->request->post();
$clientIp = $this->instance->request->clientIp();
$headers = $this->instance->request->headers();
$method = $this->instance->request->method();

// Method validation
$this->instance->request->requireMethod('POST');

// Parameter validation
$this->instance->request->requireParam('post', ['email', 'password']);
```

## Models & Database

### FileDatabase (Default)

Perfect for development and small applications:

```php
<?php

class UserModel extends Model
{
    protected $table = 'users';

    public function create($data)
    {
        return $this->fileDatabase->insert($this->table, $data);
    }

    public function getById($id)
    {
        return $this->fileDatabase->get($this->table, ['id' => $id]);
    }

    public function getAll()
    {
        return $this->fileDatabase->all($this->table);
    }

    public function update($id, $data)
    {
        return $this->fileDatabase->update($this->table, $data, ['id' => $id]);
    }
}
```

### MySQL (Optional)

When you need relational database features:

```php
<?php

class UserModel extends Model
{
    protected $table = 'users';

    public function getById($id)
    {
        // Use MySQL if available, fallback to FileDatabase
        if ($this->instance->database->hasMySQL()) {
            return $this->instance->database->client->get($this->table, '*', ['id' => $id]);
        }

        return $this->fileDatabase->get($this->table, ['id' => $id]);
    }
}
```

## Caching

Hoist includes a powerful caching system with multiple driver support and automatic fallback. The cache service provides high-performance data storage for frequently accessed information.

### Basic Caching

```php
// Store data in cache
$this->instance->cache->set('user.profile.' . $userId, $userData, 3600); // 1 hour TTL

// Retrieve from cache
$cachedUser = $this->instance->cache->get('user.profile.' . $userId);

if ($cachedUser === null) {
    // Cache miss - fetch from database
    $userData = $this->instance->models->user->find($userId);
    $this->instance->cache->set('user.profile.' . $userId, $userData, 3600);
}
```

### Remember Pattern

The `remember()` method simplifies cache-or-execute logic:

```php
// Cache for 1 hour, execute closure if cache miss
$popularPosts = $this->instance->cache->remember('posts.popular', 3600, function() {
    return $this->instance->models->post->getPopular(10);
});

// Cache forever (until manually cleared)
$siteSettings = $this->instance->cache->rememberForever('site.settings', function() {
    return $this->instance->models->settings->getAll();
});
```

### Cache Tags for Group Operations

```php
// Tag cache entries for easy group invalidation
$this->instance->cache->tags(['users', 'profiles'])
    ->set('user.profile.' . $userId, $userData, 3600);

$this->instance->cache->tags(['users', 'posts'])
    ->set('user.posts.' . $userId, $userPosts, 1800);

// Clear all user-related cache
$this->instance->cache->tags(['users'])->flush();
```

### Cache Drivers

**File Driver (Default):**

-   Zero configuration required
-   Stores cache in `Application/Cache/` directory
-   Perfect for development and small applications

**Redis Driver:**

-   High-performance, distributed caching
-   Set `CACHE_DRIVER=redis` in `.env`
-   Automatic fallback to file cache if unavailable

**Memcached Driver:**

-   Memory-based caching with horizontal scaling
-   Set `CACHE_DRIVER=memcached` in `.env`
-   Multiple server support for load balancing

### Environment Configuration

```bash
# .env file
CACHE_DRIVER=redis
CACHE_TTL=3600
CACHE_PREFIX=myapp_

# Redis settings
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DATABASE=0
```

### Controller Example

```php
class PostsController extends Controller
{
    public function popular()
    {
        $posts = $this->instance->cache->remember('posts.popular', 1800, function() {
            return $this->instance->models->post->getPopular(20);
        });

        $this->instance->view->render('posts/popular', [
            'posts' => $posts,
            'title' => 'Popular Posts'
        ]);
    }

    public function invalidateCache()
    {
        // Clear specific cache
        $this->instance->cache->forget('posts.popular');

        // Or clear by tags
        $this->instance->cache->tags(['posts'])->flush();

        $this->instance->response->redirect('/admin');
    }
}
```

## HTTP Response

Hoist includes a modern, feature-rich Response class for handling all types of HTTP responses with security headers, content negotiation, and fluent method chaining.

### JSON API Responses

```php
// Simple JSON response
$this->instance->response->sendJson(['message' => 'Hello World']);

// Success response with data
$this->instance->response->sendSuccess($userData, 'User retrieved successfully');

// Error response with details
$this->instance->response->sendError(
    'Validation failed',
    400,
    ['field' => 'email', 'code' => 'INVALID_FORMAT']
);
```

### Status Codes and Headers

```php
// Set custom status code
$this->instance->response->setStatusCode(201)->sendJson(['created' => true]);

// Add custom headers
$this->instance->response
    ->setHeader('X-Custom-Header', 'value')
    ->setHeader('Cache-Control', 'max-age=3600')
    ->sendJson($data);

// CORS headers for API endpoints
$this->instance->response
    ->setCorsHeaders(['https://example.com'], ['GET', 'POST'], ['Content-Type'])
    ->sendJson($apiData);
```

### Content Types and Responses

```php
// Different content types
$this->instance->response->json()->setContent($data)->send();
$this->instance->response->xml()->setContent($xmlData)->send();
$this->instance->response->text()->setContent('Plain text')->send();
$this->instance->response->html()->setContent('<h1>HTML</h1>')->send();

// File downloads
$this->instance->response->download('/path/to/file.pdf', 'report.pdf');

// Redirects
$this->instance->response->redirect('/dashboard', 302);
```

### Security Features

```php
// Security headers are automatically set
// X-Content-Type-Options: nosniff
// X-Frame-Options: DENY
// X-XSS-Protection: 1; mode=block
// Referrer-Policy: strict-origin-when-cross-origin

// Set secure cookies
$this->instance->response->setCookie(
    'session_token',
    $token,
    time() + 3600, // 1 hour
    '/',           // path
    '',            // domain
    true,          // secure (HTTPS only)
    true,          // httpOnly
    'Strict'       // sameSite
);
```

### Fluent Interface Examples

```php
// Method chaining for complex responses
$this->instance->response
    ->setStatusCode(201)
    ->setHeader('Location', '/users/' . $userId)
    ->setCookie('last_action', 'user_created')
    ->sendSuccess(['id' => $userId], 'User created successfully');
```

## Views

### Basic View Rendering

```php
// In controller
$this->instance->view->render('users/profile', [
    'user' => $user,
    'title' => 'User Profile'
]);
```

### View Template (`source/Application/Views/users/profile.php`)

```html
<?php include APPLICATION_DIRECTORY . '/Views/includes/header.php'; ?>

<div class="container">
    <h1><?= htmlspecialchars($title) ?></h1>

    <div class="user-profile">
        <h2><?= htmlspecialchars($user['name']) ?></h2>
        <p>
            Email:
            <?= htmlspecialchars($user['email']) ?>
        </p>
        <p>
            Member since:
            <?= date('F j, Y', strtotime($user['created_at'])) ?>
        </p>
    </div>
</div>

<?php include APPLICATION_DIRECTORY . '/Views/includes/footer.php'; ?>
```

### Components and Includes

Create reusable components in `source/Application/Views/_components/`:

```php
<!-- _components/user_card.php -->
<div class="user-card">
    <h3><?= htmlspecialchars($user['name']) ?></h3>
    <p><?= htmlspecialchars($user['email']) ?></p>
</div>

<!-- Usage in views -->
<?php foreach ($users as $user): ?>
    <?php include APPLICATION_DIRECTORY . '/Views/_components/user_card.php'; ?>
<?php endforeach; ?>
```

## Authentication

### Login System

```php
// In AuthController
public function login()
{
    if ($this->instance->request->isPost()) {
        $email = $this->instance->request->post('email');
        $password = $this->instance->request->post('password');

        if ($this->instance->auth->login($email, $password)) {
            $this->instance->response->redirect('/dashboard');
        } else {
            $this->instance->view->render('auth/login', [
                'error' => 'Invalid credentials'
            ]);
        }
    }

    $this->instance->view->render('auth/login');
}
```

### Authentication Checks

```php
// Check if user is logged in
if ($this->instance->auth->isLoggedIn()) {
    $user = $this->instance->auth->user();
    echo "Welcome, " . $user['email'];
}

// Check user role
if ($this->instance->auth->hasRole('admin')) {
    // Admin-only functionality
}

// Logout
$this->instance->auth->logout();
```

## Deployment

### Docker Deployment (Recommended)

1. **Build for production**

```bash
docker build -t my-hoist-app .
```

2. **Run with environment variables**

```bash
docker run -d \
  -p 80:80 \
  -e DB_HOST=your-db-host \
  -e DB_USER=your-db-user \
  -e DB_PASSWORD=your-db-password \
  -e DB_NAME=your-db-name \
  my-hoist-app
```

3. **Docker Compose for production**

```yaml
# docker-compose.prod.yml
services:
    app:
        build: .
        ports:
            - "80:80"
        environment:
            - DB_HOST=database
            - DB_USER=hoist_user
            - DB_PASSWORD=secure_password
            - DB_NAME=hoist_production
        depends_on:
            - database

    database:
        image: mysql:8.0
        environment:
            - MYSQL_ROOT_PASSWORD=root_password
            - MYSQL_DATABASE=hoist_production
            - MYSQL_USER=hoist_user
            - MYSQL_PASSWORD=secure_password
        volumes:
            - mysql_data:/var/lib/mysql

volumes:
    mysql_data:
```

### Traditional Server Deployment

1. **Upload files** to your web server
2. **Set document root** to `source/public/`
3. **Configure permissions**

```bash
chmod -R 755 source/Application/Database/
chmod 644 source/.env
```

4. **Configure environment** variables in `.env`

## Contributing

We welcome contributions! Please follow these guidelines:

1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature/amazing-feature`
3. **Make your changes** with clear, documented code
4. **Add tests** if applicable
5. **Commit changes**: `git commit -m 'Add amazing feature'`
6. **Push to branch**: `git push origin feature/amazing-feature`
7. **Open a Pull Request**

### Development Setup

```bash
git clone https://github.com/RobertGrubb/hoist-php.git
cd hoist-php
docker-compose up -d
```

### Code Standards

-   Follow PSR-12 coding standards
-   Document all public methods with PHPDoc
-   Use meaningful variable and method names
-   Include error handling and validation

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

-   **Documentation**: [GitHub Wiki](https://github.com/RobertGrubb/hoist-php/wiki)
-   **Issues**: [GitHub Issues](https://github.com/RobertGrubb/hoist-php/issues)
-   **Discussions**: [GitHub Discussions](https://github.com/RobertGrubb/hoist-php/discussions)

---

**Built with ❤️ for developers who want to ship fast without sacrificing quality.**
