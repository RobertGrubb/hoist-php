# 🗺️ Routing System

Hoist PHP provides a flexible and powerful routing system that maps URLs to controllers and actions. The router supports parameter extraction, middleware, route groups, and automatic resource routing.

## Basic Routing

### **Simple Routes**

```php
// Application/Routes.php
class Routes
{
    public static function define($router)
    {
        // Basic GET route
        $router->get('/', 'IndexController@index');

        // POST route for form submission
        $router->post('/contact', 'ContactController@submit');

        // Multiple HTTP methods
        $router->match(['GET', 'POST'], '/search', 'SearchController@index');

        // Any HTTP method
        $router->any('/webhook', 'WebhookController@handle');

        // Named routes for URL generation
        $router->get('/dashboard', 'DashboardController@index', 'dashboard');
        $router->get('/profile', 'UserController@profile', 'user.profile');
    }
}
```

### **Route Parameters**

```php
class Routes
{
    public static function define($router)
    {
        // Required parameters
        $router->get('/user/{id}', 'UserController@show');
        $router->get('/post/{id}/comment/{commentId}', 'CommentController@show');

        // Optional parameters
        $router->get('/blog/{category?}', 'BlogController@index');
        $router->get('/page/{slug?}', 'PageController@show');

        // Parameter constraints
        $router->get('/user/{id:[0-9]+}', 'UserController@show'); // Only numbers
        $router->get('/slug/{name:[a-z-]+}', 'PageController@showBySlug'); // Only lowercase letters and hyphens

        // Multiple parameters with constraints
        $router->get('/api/v{version:[1-3]}/user/{id:[0-9]+}', 'ApiController@getUser');
    }
}
```

## RESTful Resource Routes

### **Resource Controllers**

```php
class Routes
{
    public static function define($router)
    {
        // Full resource routes (generates 7 routes)
        $router->resource('users', 'UserController');
        // Creates:
        // GET    /users          -> UserController@index
        // GET    /users/create   -> UserController@create
        // POST   /users          -> UserController@store
        // GET    /users/{id}     -> UserController@show
        // GET    /users/{id}/edit -> UserController@edit
        // PUT    /users/{id}     -> UserController@update
        // DELETE /users/{id}     -> UserController@destroy

        // API resource routes (excludes create/edit forms)
        $router->apiResource('posts', 'PostController');
        // Creates:
        // GET    /posts          -> PostController@index
        // POST   /posts          -> PostController@store
        // GET    /posts/{id}     -> PostController@show
        // PUT    /posts/{id}     -> PostController@update
        // DELETE /posts/{id}     -> PostController@destroy

        // Partial resources
        $router->resource('comments', 'CommentController', ['only' => ['index', 'show', 'store']]);
        $router->resource('uploads', 'UploadController', ['except' => ['edit', 'update']]);
    }
}
```

### **Nested Resources**

```php
class Routes
{
    public static function define($router)
    {
        // Nested resources
        $router->group(['prefix' => 'users/{userId}'], function($router) {
            $router->resource('posts', 'UserPostController');
            // Creates routes like: /users/123/posts, /users/123/posts/456, etc.
        });

        // Deep nesting
        $router->group(['prefix' => 'categories/{categoryId}'], function($router) {
            $router->group(['prefix' => 'posts/{postId}'], function($router) {
                $router->resource('comments', 'PostCommentController');
                // Creates: /categories/1/posts/2/comments, etc.
            });
        });
    }
}
```

## Route Groups

### **Grouped Routes with Shared Attributes**

```php
class Routes
{
    public static function define($router)
    {
        // Admin routes with prefix and middleware
        $router->group([
            'prefix' => 'admin',
            'middleware' => ['auth', 'admin'],
            'namespace' => 'Admin'
        ], function($router) {
            $router->get('/', 'DashboardController@index');
            $router->resource('users', 'UserController');
            $router->resource('settings', 'SettingsController');
        });

        // API routes
        $router->group([
            'prefix' => 'api/v1',
            'middleware' => ['api', 'throttle:60,1'],
            'namespace' => 'Api'
        ], function($router) {
            $router->get('/status', 'StatusController@check');
            $router->apiResource('users', 'UserController');
            $router->apiResource('posts', 'PostController');
        });

        // Guest only routes
        $router->group(['middleware' => 'guest'], function($router) {
            $router->get('/login', 'AuthController@showLogin');
            $router->post('/login', 'AuthController@login');
            $router->get('/register', 'AuthController@showRegister');
            $router->post('/register', 'AuthController@register');
        });

        // Authenticated routes
        $router->group(['middleware' => 'auth'], function($router) {
            $router->get('/dashboard', 'DashboardController@index');
            $router->get('/profile', 'UserController@profile');
            $router->resource('projects', 'ProjectController');
        });
    }
}
```

## Advanced Routing Features

### **Route Model Binding**

```php
class UserController extends Controller
{
    // Automatic model binding by parameter name
    public function show($id)
    {
        // Router automatically loads user model
        $user = $this->instance->models->user->find($id);

        if (!$user) {
            return $this->instance->response->setStatusCode(404)
                ->json(['error' => 'User not found']);
        }

        return $this->instance->response->json($user);
    }

    // Custom binding logic
    public function showBySlug($slug)
    {
        $user = $this->instance->models->user->findBySlug($slug);

        if (!$user) {
            return $this->instance->response->setStatusCode(404)
                ->json(['error' => 'User not found']);
        }

        return $this->instance->response->json($user);
    }
}

// Custom route binding in Routes.php
class Routes
{
    public static function define($router)
    {
        // Bind route parameters to models
        $router->bind('user', function($value) {
            if (is_numeric($value)) {
                return Instance::get()->models->user->find($value);
            } else {
                return Instance::get()->models->user->findBySlug($value);
            }
        });

        $router->get('/user/{user}', 'UserController@show');
    }
}
```

### **Route Caching and Performance**

```php
class Router
{
    private $routeCache = null;

    public function loadRoutes()
    {
        // Check if routes are cached
        $cacheFile = CACHE_PATH . '/routes.php';

        if (file_exists($cacheFile) && !$this->isDebugMode()) {
            $this->routeCache = include $cacheFile;
            return;
        }

        // Build routes and cache them
        $this->buildRoutes();
        $this->cacheRoutes($cacheFile);
    }

    private function cacheRoutes($cacheFile)
    {
        $routeData = var_export($this->routes, true);
        $content = "<?php\nreturn {$routeData};";
        file_put_contents($cacheFile, $content);
    }
}
```

## Route Middleware

### **Middleware Application**

```php
class Routes
{
    public static function define($router)
    {
        // Global middleware
        $router->middleware('throttle:100,1');

        // Route-specific middleware
        $router->get('/api/sensitive', 'ApiController@sensitive')
               ->middleware(['auth', 'verified', 'throttle:10,1']);

        // Middleware groups
        $router->middlewareGroup('web', [
            'session',
            'csrf',
            'throttle:60,1'
        ]);

        $router->middlewareGroup('api', [
            'throttle:60,1',
            'json_response'
        ]);

        // Apply middleware groups
        $router->group(['middleware' => 'web'], function($router) {
            // Web routes
        });

        $router->group(['middleware' => 'api'], function($router) {
            // API routes
        });
    }
}
```

### **Custom Middleware**

```php
// Core/Libraries/Middleware/AuthMiddleware.php
class AuthMiddleware
{
    public function handle($request, $next)
    {
        if (!Instance::get()->auth->check()) {
            if ($request->wantsJson()) {
                return Instance::get()->response
                    ->setStatusCode(401)
                    ->json(['error' => 'Unauthorized']);
            } else {
                return Instance::get()->response
                    ->redirect('/login')
                    ->with('error', 'Please log in to continue');
            }
        }

        return $next($request);
    }
}

// Core/Libraries/Middleware/AdminMiddleware.php
class AdminMiddleware
{
    public function handle($request, $next)
    {
        if (!Instance::get()->auth->user()['is_admin']) {
            return Instance::get()->response
                ->setStatusCode(403)
                ->json(['error' => 'Admin access required']);
        }

        return $next($request);
    }
}

// Core/Libraries/Middleware/ThrottleMiddleware.php
class ThrottleMiddleware
{
    public function handle($request, $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
            return $this->buildResponse($key, $maxAttempts);
        }

        $this->hit($key, $decayMinutes);

        $response = $next($request);

        return $this->addHeaders(
            $response, $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }
}
```

## URL Generation

### **Named Route URLs**

```php
class UrlHelper
{
    public static function route($name, $parameters = [])
    {
        $router = Instance::get()->router;
        return $router->url($name, $parameters);
    }

    public static function action($controller, $action, $parameters = [])
    {
        return "/controller/" . strtolower($controller) . "/" . $action .
               ($parameters ? "?" . http_build_query($parameters) : "");
    }
}

// In views or controllers
class BlogController extends Controller
{
    public function index()
    {
        $posts = $this->instance->models->post->getAll();

        // Generate URLs for each post
        foreach ($posts as &$post) {
            $post['url'] = UrlHelper::route('post.show', ['id' => $post['id']]);
            $post['edit_url'] = UrlHelper::route('post.edit', ['id' => $post['id']]);
        }

        $this->instance->view->render('blog/index', ['posts' => $posts]);
    }
}

// In views
echo '<a href="' . UrlHelper::route('user.profile', ['id' => $user['id']]) . '">View Profile</a>';
echo '<a href="' . UrlHelper::action('PostController', 'create') . '">Create Post</a>';
```

## Route Parameters and Validation

### **Parameter Extraction and Validation**

```php
class PostController extends Controller
{
    public function show($id)
    {
        // Validate parameter
        if (!is_numeric($id) || $id <= 0) {
            return $this->instance->response
                ->setStatusCode(400)
                ->json(['error' => 'Invalid post ID']);
        }

        $post = $this->instance->models->post->find($id);

        if (!$post) {
            return $this->instance->response
                ->setStatusCode(404)
                ->json(['error' => 'Post not found']);
        }

        // Check permissions
        if (!$this->canViewPost($post)) {
            return $this->instance->response
                ->setStatusCode(403)
                ->json(['error' => 'Access denied']);
        }

        return $this->instance->response->json($post);
    }

    public function showByCategory($category, $slug = null)
    {
        // Multiple parameters
        $posts = $this->instance->models->post->getByCategory($category);

        if ($slug) {
            $post = $this->findPostBySlug($posts, $slug);
            if ($post) {
                return $this->instance->response->json($post);
            }
        }

        return $this->instance->response->json($posts);
    }

    private function canViewPost($post)
    {
        // Check if post is published or user owns it
        return $post['status'] === 'published' ||
               $this->instance->auth->id() === $post['user_id'] ||
               $this->instance->auth->hasRole('admin');
    }
}
```

## API Versioning

### **Version-Based Routing**

```php
class Routes
{
    public static function define($router)
    {
        // API v1
        $router->group([
            'prefix' => 'api/v1',
            'namespace' => 'Api\\V1',
            'middleware' => ['api']
        ], function($router) {
            $router->apiResource('users', 'UserController');
            $router->apiResource('posts', 'PostController');
        });

        // API v2 with breaking changes
        $router->group([
            'prefix' => 'api/v2',
            'namespace' => 'Api\\V2',
            'middleware' => ['api']
        ], function($router) {
            $router->apiResource('users', 'UserController');
            $router->apiResource('posts', 'PostController');
            $router->apiResource('comments', 'CommentController'); // New in v2
        });

        // Accept version in header
        $router->group([
            'prefix' => 'api',
            'middleware' => ['api', 'version_header']
        ], function($router) {
            $router->apiResource('users', 'UserController');
        });
    }
}

// Version middleware
class VersionMiddleware
{
    public function handle($request, $next)
    {
        $version = $request->header('Accept-Version', 'v1');

        // Set the namespace based on version
        $namespace = 'Api\\' . ucfirst($version);

        // Store version for controller resolution
        $request->attributes['api_version'] = $version;

        return $next($request);
    }
}
```

## Subdomain Routing

### **Multi-Tenant Routing**

```php
class Routes
{
    public static function define($router)
    {
        // Admin subdomain
        $router->group(['domain' => 'admin.{domain}'], function($router) {
            $router->get('/', 'Admin\\DashboardController@index');
            $router->resource('users', 'Admin\\UserController');
        });

        // API subdomain
        $router->group(['domain' => 'api.{domain}'], function($router) {
            $router->group(['prefix' => 'v1'], function($router) {
                $router->apiResource('users', 'Api\\UserController');
            });
        });

        // Tenant-specific routing
        $router->group(['domain' => '{tenant}.{domain}'], function($router) {
            $router->get('/', 'TenantController@dashboard');
            $router->resource('projects', 'ProjectController');
        });

        // Wildcard subdomain
        $router->group(['domain' => '*.example.com'], function($router) {
            $router->get('/', 'SubdomainController@handle');
        });
    }
}
```

## Route Testing

### **Testing Routes**

```php
class RouteTest
{
    public function testBasicRoutes()
    {
        $router = new Router();

        // Test route registration
        $router->get('/test', 'TestController@index');
        $this->assertTrue($router->hasRoute('GET', '/test'));

        // Test route matching
        $match = $router->match('GET', '/test');
        $this->assertEquals('TestController', $match['controller']);
        $this->assertEquals('index', $match['action']);

        // Test parameters
        $router->get('/user/{id}', 'UserController@show');
        $match = $router->match('GET', '/user/123');
        $this->assertEquals(['id' => '123'], $match['parameters']);
    }

    public function testResourceRoutes()
    {
        $router = new Router();
        $router->resource('posts', 'PostController');

        // Test all resource routes exist
        $this->assertTrue($router->hasRoute('GET', '/posts'));
        $this->assertTrue($router->hasRoute('POST', '/posts'));
        $this->assertTrue($router->hasRoute('GET', '/posts/123'));
        $this->assertTrue($router->hasRoute('PUT', '/posts/123'));
        $this->assertTrue($router->hasRoute('DELETE', '/posts/123'));
    }
}
```

---

The routing system provides flexible URL mapping with powerful features for modern web applications and APIs. Use these patterns to create clean, RESTful routes with proper parameter handling and middleware protection.

**Next:** [Database Integration](../database/overview.md) - Learn about database connections and queries.
