# Router API Documentation

## Overview

The Router API provides advanced URL routing with pattern matching, parameter extraction, and flexible controller dispatch. Supports both convention-based routing and explicit route registration for modern web applications and APIs.

## Class: Router

**Location**: `Core/Libraries/Router.php`  
**Dependencies**: Instance, Request, View  
**Access**: Available as `$this->router` in controllers

---

## Properties

### Public Properties

#### `$params`

-   **Type**: `array`
-   **Description**: Extracted URL parameters from route pattern matching
-   **Example**: `['id' => '123', 'slug' => 'hello-world']`

---

## Methods

### Constructor

#### `__construct($instance)`

Initializes router with framework service container.

**Parameters:**

-   `$instance` (Instance): Framework service container

**Usage:**

```php
// Automatically handled by framework - no direct instantiation needed
```

---

### Parameter Access

#### `param($key = null)`

Retrieves URL parameters extracted during route matching.

**Parameters:**

-   `$key` (string|null): Parameter name to retrieve, null for all parameters

**Returns:** `mixed` - Single parameter value, all parameters array, or false if not found

**Examples:**

```php
// Single parameter access
// Route: /users/:id → /users/123
$userId = $this->router->param('id'); // Returns "123"

// Multiple parameters
// Route: /blog/:year/:month/:slug → /blog/2024/03/hello-world
$year = $this->router->param('year');   // "2024"
$month = $this->router->param('month'); // "03"
$slug = $this->router->param('slug');   // "hello-world"

// All parameters
$allParams = $this->router->param();
// Returns: ['year' => '2024', 'month' => '03', 'slug' => 'hello-world']

// Parameter validation in controller
class BlogController extends Controller
{
    public function showPost()
    {
        $id = $this->router->param('id');
        if ($id && is_numeric($id)) {
            $post = $this->models->post->getById((int)$id);
            $this->view->render('blog/post', ['post' => $post]);
        } else {
            $this->view->render('error/404');
        }
    }
}
```

#### `listRegisteredRoutes()`

Returns array of all registered routes for debugging and introspection.

**Returns:** `array` - Complete route registry organized by HTTP method

**Example:**

```php
$routes = $this->router->listRegisteredRoutes();
// Returns:
// [
//   'GET' => [
//     '/users/:id' => 'UsersController@show',
//     '/api/users' => 'ApiController@listUsers'
//   ],
//   'POST' => [
//     '/users' => 'UsersController@create',
//     '/api/login' => function($credentials) { ... }
//   ]
// ]

// Usage for debugging or admin interfaces
public function routeDebug() {
    $routes = $this->router->listRegisteredRoutes();
    $this->view->render('admin/routes', ['routes' => $routes]);
}
```

---

### Route Registration

#### `registerRoute($method, $url, $target)`

Registers an individual route with method, pattern, and target.

**Parameters:**

-   `$method` (string): HTTP method (GET, POST, PUT, DELETE, etc.)
-   `$url` (string): URL pattern with optional :parameter syntax
-   `$target` (string|callable): Controller@method string or closure function

**Examples:**

```php
// Controller@Method routes
$this->router->registerRoute('GET', '/users/:id', 'UsersController@show');
$this->router->registerRoute('POST', '/users', 'UsersController@create');
$this->router->registerRoute('PUT', '/users/:id', 'UsersController@update');
$this->router->registerRoute('DELETE', '/users/:id', 'UsersController@delete');

// Closure routes
$this->router->registerRoute('GET', '/hello/:name', function($name) {
    return "Hello, " . htmlspecialchars($name) . "!";
});

// API routes
$this->router->registerRoute('POST', '/api/webhook', function() {
    $payload = file_get_contents('php://input');
    // Process webhook
    return json_encode(['status' => 'received']);
});

// Complex patterns
$this->router->registerRoute('GET', '/api/v:version/users/:id', 'ApiController@getUser');
$this->router->registerRoute('GET', '/blog/:year/:month/:slug', 'BlogController@showPost');
```

#### `registerRoutes($routes)`

Registers multiple routes from an array configuration.

**Parameters:**

-   `$routes` (array): Array of route definitions

**Route Array Format:**

```php
[
    [
        'method' => 'GET',
        'url' => '/users',
        'target' => 'UsersController@index'
    ],
    [
        'method' => 'GET',
        'url' => '/users/:id',
        'target' => 'UsersController@show'
    ]
]
```

**Examples:**

```php
// Basic route registration
$routes = [
    ['method' => 'GET', 'url' => '/users', 'target' => 'UsersController@index'],
    ['method' => 'GET', 'url' => '/users/:id', 'target' => 'UsersController@show'],
    ['method' => 'POST', 'url' => '/users', 'target' => 'UsersController@create'],
    ['method' => 'PUT', 'url' => '/users/:id', 'target' => 'UsersController@update'],
    ['method' => 'DELETE', 'url' => '/users/:id', 'target' => 'UsersController@delete']
];

$this->router->registerRoutes($routes);

// API routes with versioning
$apiRoutes = [
    ['method' => 'GET', 'url' => '/api/v1/users', 'target' => 'Api\\V1Controller@users'],
    ['method' => 'GET', 'url' => '/api/v1/posts', 'target' => 'Api\\V1Controller@posts'],
    ['method' => 'POST', 'url' => '/api/v1/auth', 'target' => 'Api\\V1Controller@authenticate']
];

$this->router->registerRoutes($apiRoutes);

// Load routes from configuration file
$configRoutes = include 'config/routes.php';
$this->router->registerRoutes($configRoutes);
```

---

### Route Execution

#### `run()`

Executes the router logic with support for nested controller directories.

**Returns:** `void` - Handles controller dispatch and execution

**Routing Patterns:**

-   `/users` → `Controllers/UsersController.php@index()`
-   `/users/create` → `Controllers/UsersController.php@create()`
-   `/admin/reports` → `Controllers/Admin/ReportsController.php@index()`
-   `/admin/reports/users` → `Controllers/Admin/ReportsController.php@users()`
-   `/api/v1/users` → `Controllers/Api/V1Controller.php@users()`

**Controller Lifecycle:**

1. Route matching and parameter extraction
2. Controller file validation and inclusion
3. Controller instantiation with dependency injection
4. `before()` method execution (if exists)
5. Target method execution
6. `after()` method execution (if exists)

**Example Usage:**

```php
// Typically called automatically by framework bootstrap
// In bootstrap/initialization:
$router = new Router($instance);

// Register custom routes
$router->registerRoute('GET', '/api/users/:id', 'ApiController@getUser');

// Run the router
$router->run();
```

---

## URL Pattern Syntax

### Parameter Definition

#### Named Parameters

```php
// Single parameter
'/users/:id'           // Matches: /users/123
'/blog/:slug'          // Matches: /blog/hello-world

// Multiple parameters
'/users/:id/posts/:postId'  // Matches: /users/123/posts/456
'/blog/:year/:month/:slug'  // Matches: /blog/2024/03/hello-world
```

#### Mixed Patterns

```php
// Static and dynamic segments
'/api/v:version/users/:id'     // Matches: /api/v1/users/123
'/download/:type/:filename'    // Matches: /download/pdf/report.pdf
'/shop/:category/item/:sku'    // Matches: /shop/electronics/item/ABC123
```

### Pattern Matching

The router uses regex-based pattern matching:

-   `:parameter` becomes `(?P<parameter>[^/]+)`
-   Matches any characters except forward slash
-   Parameters are automatically URL-decoded
-   Case-insensitive matching

---

## Convention-Based Routing

### Basic Patterns

```php
// URL → Controller@Method
'/'                    → IndexController@index()
'/users'              → UsersController@index()
'/users/create'       → UsersController@create()
'/user-profile'       → UserProfileController@index()
'/admin'              → AdminController@index()
```

### Nested Directory Support

```php
// Nested controller resolution
'/admin/reports'              → Controllers/Admin/ReportsController@index()
'/admin/reports/users'        → Controllers/Admin/ReportsController@users()
'/admin/settings/security'    → Controllers/Admin/SettingsController@security()
'/api/v1/users'              → Controllers/Api/V1Controller@users()
'/api/v2/posts/recent'       → Controllers/Api/V2Controller@postsRecent()
```

**Resolution Strategy:**

1. Try longest possible path as nested directories
2. Fall back to shorter paths if controller not found
3. Default to flat structure if no nested match found

---

## Practical Usage Examples

### RESTful API Routes

```php
class ApiBootstrap
{
    public function registerApiRoutes($router)
    {
        $apiRoutes = [
            // User management
            ['method' => 'GET',    'url' => '/api/users',     'target' => 'ApiController@listUsers'],
            ['method' => 'GET',    'url' => '/api/users/:id', 'target' => 'ApiController@getUser'],
            ['method' => 'POST',   'url' => '/api/users',     'target' => 'ApiController@createUser'],
            ['method' => 'PUT',    'url' => '/api/users/:id', 'target' => 'ApiController@updateUser'],
            ['method' => 'DELETE', 'url' => '/api/users/:id', 'target' => 'ApiController@deleteUser'],

            // Authentication
            ['method' => 'POST', 'url' => '/api/auth/login',  'target' => 'ApiController@login'],
            ['method' => 'POST', 'url' => '/api/auth/logout', 'target' => 'ApiController@logout'],
            ['method' => 'POST', 'url' => '/api/auth/refresh', 'target' => 'ApiController@refreshToken'],

            // Posts
            ['method' => 'GET',    'url' => '/api/posts',        'target' => 'ApiController@listPosts'],
            ['method' => 'GET',    'url' => '/api/posts/:id',    'target' => 'ApiController@getPost'],
            ['method' => 'POST',   'url' => '/api/posts',        'target' => 'ApiController@createPost'],
            ['method' => 'PUT',    'url' => '/api/posts/:id',    'target' => 'ApiController@updatePost'],
            ['method' => 'DELETE', 'url' => '/api/posts/:id',    'target' => 'ApiController@deletePost']
        ];

        $router->registerRoutes($apiRoutes);
    }
}
```

### Closure-Based Routes

```php
// Simple responses
$router->registerRoute('GET', '/ping', function() {
    return 'pong';
});

// JSON API endpoints
$router->registerRoute('GET', '/api/status', function() {
    return json_encode([
        'status' => 'ok',
        'timestamp' => time(),
        'version' => '1.0.0'
    ]);
});

// Parameter handling in closures
$router->registerRoute('GET', '/hello/:name', function($name) {
    return "Hello, " . htmlspecialchars($name) . "!";
});

// Multiple parameters
$router->registerRoute('GET', '/calculate/:op/:a/:b', function($op, $a, $b) {
    $a = (float) $a;
    $b = (float) $b;

    switch ($op) {
        case 'add': return json_encode(['result' => $a + $b]);
        case 'sub': return json_encode(['result' => $a - $b]);
        case 'mul': return json_encode(['result' => $a * $b]);
        case 'div': return json_encode(['result' => $b != 0 ? $a / $b : 'error']);
        default: return json_encode(['error' => 'Invalid operation']);
    }
});
```

### Controller with Route Parameters

```php
class BlogController extends Controller
{
    public function showPost()
    {
        // Route: /blog/:year/:month/:slug
        $year = $this->router->param('year');
        $month = $this->router->param('month');
        $slug = $this->router->param('slug');

        // Validate parameters
        if (!$year || !$month || !$slug) {
            $this->view->render('error/404');
            return;
        }

        if (!is_numeric($year) || !is_numeric($month)) {
            $this->view->render('error/404');
            return;
        }

        // Load post
        $post = $this->models->post->getBySlugAndDate($slug, $year, $month);

        if (!$post) {
            $this->view->render('error/404');
            return;
        }

        $this->view->render('blog/post', [
            'post' => $post,
            'year' => $year,
            'month' => $month
        ]);
    }

    public function categoryPosts()
    {
        // Route: /blog/category/:category
        $category = $this->router->param('category');

        $posts = $this->models->post->getByCategory($category);
        $categoryInfo = $this->models->category->getBySlug($category);

        $this->view->render('blog/category', [
            'posts' => $posts,
            'category' => $categoryInfo,
            'categorySlug' => $category
        ]);
    }
}
```

### Protected Routes with Middleware

```php
class AdminController extends Controller
{
    public function before()
    {
        // Apply authentication and authorization to all admin routes
        $this->auth->required();
        $this->auth->requireGroup('admin');

        // Log admin access
        $route = $this->router->param();
        $this->auth->logAdminAction('route_access', [
            'route' => $_SERVER['REQUEST_URI'],
            'params' => $route
        ]);
    }

    public function userDetails()
    {
        // Route: /admin/users/:id
        $userId = $this->router->param('id');

        if (!$userId || !is_numeric($userId)) {
            $this->session->setFlashData('error', 'Invalid user ID');
            $this->instance->redirect('/admin/users');
            return;
        }

        $user = $this->models->user->getUserById((int)$userId);

        if (!$user) {
            $this->session->setFlashData('error', 'User not found');
            $this->instance->redirect('/admin/users');
            return;
        }

        $this->view->render('admin/user-details', ['user' => $user]);
    }
}
```

---

## Advanced Features

### Route Caching

```php
// For high-traffic applications, consider route caching
class RouterCache
{
    public static function cacheRoutes($router)
    {
        $routes = $router->listRegisteredRoutes();
        file_put_contents('cache/routes.json', json_encode($routes));
    }

    public static function loadCachedRoutes($router)
    {
        if (file_exists('cache/routes.json')) {
            $routes = json_decode(file_get_contents('cache/routes.json'), true);
            $router->registerRoutes($routes);
            return true;
        }
        return false;
    }
}
```

### Route Groups and Middleware

```php
// Custom route grouping implementation
class RouteGroup
{
    public static function apiRoutes($router, $version = 'v1')
    {
        $prefix = "/api/{$version}";

        $routes = [
            ['method' => 'GET', 'url' => $prefix . '/users', 'target' => "Api\\{$version}Controller@users"],
            ['method' => 'GET', 'url' => $prefix . '/posts', 'target' => "Api\\{$version}Controller@posts"]
        ];

        $router->registerRoutes($routes);
    }

    public static function adminRoutes($router)
    {
        $routes = [
            ['method' => 'GET', 'url' => '/admin/dashboard', 'target' => 'AdminController@dashboard'],
            ['method' => 'GET', 'url' => '/admin/users', 'target' => 'AdminController@users'],
            ['method' => 'GET', 'url' => '/admin/settings', 'target' => 'AdminController@settings']
        ];

        $router->registerRoutes($routes);
    }
}
```

---

## Error Handling

The Router handles errors gracefully:

-   **Controller not found**: Renders error/index view
-   **Method not found**: Renders error/index view
-   **Invalid routes**: Falls back to convention-based routing
-   **Missing parameters**: Available as false in param() method

```php
// Error handling in controllers
public function showUser()
{
    $id = $this->router->param('id');

    if (!$id) {
        $this->view->render('error/400'); // Bad request
        return;
    }

    if (!is_numeric($id)) {
        $this->view->render('error/400'); // Bad request
        return;
    }

    $user = $this->models->user->getUserById((int)$id);

    if (!$user) {
        $this->view->render('error/404'); // Not found
        return;
    }

    $this->view->render('users/show', ['user' => $user]);
}
```

---

## Framework Integration

The Router integrates seamlessly with other framework components:

-   **Controllers**: Automatic instantiation with dependency injection
-   **Authentication**: Route protection through controller middleware
-   **Views**: Error page rendering for failed routes
-   **Request**: URI parsing and method detection
-   **Session**: Flash messaging for route-related errors

The Router API provides flexible URL handling that scales from simple websites to complex APIs while maintaining clean, readable code.
