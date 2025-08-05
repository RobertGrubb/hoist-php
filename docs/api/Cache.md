# Cache API Documentation

## Overview

The Cache API provides a high-performance caching system for the Hoist PHP framework with multiple storage drivers and advanced features. It follows the "lightweight with optional complexity" philosophy, offering file-based caching by default with optional Redis and Memcached support for production scaling.

## Class: Cache

**Location**: `Core/Libraries/Cache.php`  
**Access**: Available as `$this->instance->cache` in controllers  
**Version**: 1.0.0 with multi-driver support  
**Features**: TTL support, cache tags, remember pattern, automatic fallback

---

## Storage Drivers

### File Driver (Default)

**Configuration**: Zero setup required  
**Storage**: `Application/Cache/` directory  
**Features**: Automatic directory creation, TTL support, tag support

### Redis Driver (Optional)

**Configuration**: Set `CACHE_DRIVER=redis` in environment  
**Requirements**: PHP Redis extension  
**Features**: High performance, distributed caching, automatic fallback

### Memcached Driver (Optional)

**Configuration**: Set `CACHE_DRIVER=memcached` in environment  
**Requirements**: PHP Memcached extension  
**Features**: Distributed caching, memory efficiency, cluster support

---

## Properties

### Private Properties

#### `$driver`

**Type**: `object`  
**Description**: Active cache driver instance handling storage operations

#### `$config`

**Type**: `array`  
**Description**: Cache configuration settings including driver selection and paths

#### `$defaultTtl`

**Type**: `int`  
**Default**: `3600` (1 hour)  
**Description**: Default Time To Live in seconds for cached items

#### `$currentTags`

**Type**: `array`  
**Description**: Active cache tags for the current operation

#### `$stats`

**Type**: `array`  
**Description**: Cache statistics for debugging and monitoring

---

## Constructor

### `__construct($config = [])`

Initializes the cache system with driver auto-detection.

**Parameters:**

-   `$config` (array): Optional configuration overrides

**Driver Selection Priority:**

1. Environment variable `CACHE_DRIVER`
2. Available PHP extensions (Redis, Memcached)
3. File driver as fallback (always works)

**Configuration Options:**

-   `driver`: Cache driver ('file', 'redis', 'memcached')
-   `ttl`: Default TTL in seconds
-   `path`: Cache directory path (file driver)
-   `prefix`: Cache key prefix

**Example:**

```php
// Cache is automatically instantiated by the framework
// Access via: $this->instance->cache->method()
```

---

## Core Cache Operations

### `get($key, $default = null)`

Retrieves a cached value by key with automatic expiration handling.

**Parameters:**

-   `$key` (string): Cache key to retrieve
-   `$default` (mixed): Default value if key not found or expired

**Returns:** `mixed` - Cached value or default value

**Example:**

```php
// Basic retrieval
$userData = $this->instance->cache->get('user.123');

// With default value
$settings = $this->instance->cache->get('app.settings', []);

// Controller usage
public function getUserProfile($userId)
{
    $user = $this->instance->cache->get("user.{$userId}");

    if (!$user) {
        $user = $this->models->user->get($userId);
        $this->instance->cache->set("user.{$userId}", $user, 3600);
    }

    return $user;
}

// Complex data retrieval
$apiResponse = $this->instance->cache->get('api.weather.12345', [
    'temperature' => 'unknown',
    'status' => 'unavailable'
]);
```

### `set($key, $value, $ttl = null)`

Stores a value in the cache with optional TTL and tag support.

**Parameters:**

-   `$key` (string): Cache key for storage
-   `$value` (mixed): Value to cache (any serializable type)
-   `$ttl` (int|null): Time to live in seconds, null for default TTL

**Returns:** `bool` - True if successfully stored, false otherwise

**Example:**

```php
// Basic storage with default TTL
$this->instance->cache->set('user.data', $userData);

// Custom TTL (5 minutes)
$this->instance->cache->set('temp.data', $tempData, 300);

// Cache API responses
public function getCachedApiData($endpoint)
{
    $cacheKey = 'api.' . md5($endpoint);
    $data = $this->instance->cache->get($cacheKey);

    if (!$data) {
        $data = $this->fetchFromAPI($endpoint);
        $this->instance->cache->set($cacheKey, $data, 1800); // 30 minutes
    }

    return $data;
}

// Cache complex data structures
$reportData = [
    'summary' => $summaryData,
    'details' => $detailData,
    'generated_at' => time()
];
$this->instance->cache->set('monthly.report.2024-01', $reportData, 86400); // 24 hours

// Short-term caching for rate limiting
$this->instance->cache->set("rate_limit.{$userId}", $requestCount, 60); // 1 minute
```

### `forever($key, $value)`

Stores a value permanently until manually removed.

**Parameters:**

-   `$key` (string): Cache key for storage
-   `$value` (mixed): Value to cache permanently

**Returns:** `bool` - True if successfully stored, false otherwise

**Example:**

```php
// Cache configuration data
$this->instance->cache->forever('app.config', $configData);

// Cache compiled templates
$this->instance->cache->forever('template.compiled.header', $compiledTemplate);

// Application settings
public function loadApplicationSettings()
{
    $settings = $this->instance->cache->get('app.settings');

    if (!$settings) {
        $settings = $this->models->setting->getAllSettings();
        $this->instance->cache->forever('app.settings', $settings);
    }

    return $settings;
}

// Static lookup data
$countryList = $this->models->country->getAllCountries();
$this->instance->cache->forever('lookup.countries', $countryList);
```

### `forget($key)`

Removes a cached value by key.

**Parameters:**

-   `$key` (string): Cache key to remove

**Returns:** `bool` - True if key was removed, false if not found

**Example:**

```php
// Remove specific user data
$this->instance->cache->forget('user.123');

// Clear temporary data
$this->instance->cache->forget('temp.upload.token');

// User logout cleanup
public function logout()
{
    $userId = $this->auth->user['id'];

    // Clear user-specific caches
    $this->instance->cache->forget("user.{$userId}");
    $this->instance->cache->forget("user.permissions.{$userId}");
    $this->instance->cache->forget("user.preferences.{$userId}");

    $this->auth->logout();
}

// Content update invalidation
public function updatePost($postId)
{
    // Update post data
    $this->models->post->update($postId, $postData);

    // Clear related caches
    $this->instance->cache->forget("post.{$postId}");
    $this->instance->cache->forget("post.comments.{$postId}");
    $this->instance->cache->forget('posts.recent');
}
```

### `remember($key, $ttl, $callback)`

Gets cached value or executes callback to generate and cache it (remember pattern).

**Parameters:**

-   `$key` (string): Cache key
-   `$ttl` (int): Time to live in seconds
-   `$callback` (callable): Function to execute if cache miss

**Returns:** `mixed` - Cached value or callback result

**Example:**

```php
// Database query caching
$users = $this->instance->cache->remember('users.active', 3600, function() {
    return $this->models->user->getActiveUsers();
});

// Expensive calculation caching
$reportData = $this->instance->cache->remember('monthly.report', 86400, function() {
    return $this->generateMonthlyReport();
});

// API call caching
public function getWeatherData($city)
{
    return $this->instance->cache->remember("weather.{$city}", 1800, function() use ($city) {
        return $this->apiClient->getWeather($city);
    });
}

// Complex query with parameters
public function getUsersByGroup($groupId, $page = 1)
{
    $cacheKey = "users.group.{$groupId}.page.{$page}";

    return $this->instance->cache->remember($cacheKey, 3600, function() use ($groupId, $page) {
        return $this->models->user->getUsersByGroup($groupId, $page);
    });
}

// Template compilation caching
public function renderTemplate($templateName, $data)
{
    $cacheKey = "template.{$templateName}." . md5(serialize($data));

    return $this->instance->cache->remember($cacheKey, 3600, function() use ($templateName, $data) {
        return $this->compileTemplate($templateName, $data);
    });
}
```

---

## Cache Tags and Group Operations

### `tags($tags)`

Sets cache tags for group operations with method chaining.

**Parameters:**

-   `$tags` (array|string): Array of tag names or single tag

**Returns:** `Cache` - Returns self for method chaining

**Example:**

```php
// Single tag
$this->instance->cache->tags('users')->set('user.list', $users, 3600);

// Multiple tags
$this->instance->cache->tags(['users', 'admin'])->set('admin.users', $adminUsers, 3600);

// Method chaining with tags
$this->instance->cache->tags(['posts', 'public'])
    ->set('posts.recent', $recentPosts, 1800);

// Tagging related data
public function cacheUserData($userId, $userData)
{
    $this->instance->cache->tags(['users', "user.{$userId}"])
        ->set("user.{$userId}", $userData, 3600);

    $this->instance->cache->tags(['users', 'profiles'])
        ->set("profile.{$userId}", $userData['profile'], 3600);
}

// Hierarchical tagging
$this->instance->cache->tags(['reports', 'monthly', '2024'])
    ->set('report.monthly.2024-01', $reportData, 86400);
```

### `flush()`

Clears cached entries (all or by tags).

**Returns:** `bool` - True if cache was successfully cleared

**Tag Behavior:**

-   If tags are active: Only clears entries with those tags
-   If no tags: Clears entire cache

**Example:**

```php
// Clear entire cache
$this->instance->cache->flush();

// Clear by tags
$this->instance->cache->tags(['users'])->flush();

// Clear multiple tag groups
$this->instance->cache->tags(['posts', 'comments'])->flush();

// User-specific cache clearing
public function clearUserCaches($userId)
{
    $this->instance->cache->tags(["user.{$userId}"])->flush();
}

// Content management cache clearing
public function clearContentCaches()
{
    // Clear all post-related caches
    $this->instance->cache->tags(['posts'])->flush();

    // Clear all comment-related caches
    $this->instance->cache->tags(['comments'])->flush();

    // Clear navigation caches
    $this->instance->cache->tags(['navigation'])->flush();
}

// Time-based cache clearing
public function clearOldReports()
{
    // Clear reports older than current month
    $lastMonth = date('Y-m', strtotime('-1 month'));
    $this->instance->cache->tags(['reports', $lastMonth])->flush();
}
```

---

## Utility Methods

### `getStats()`

Gets cache statistics for monitoring and debugging.

**Returns:** `array` - Cache statistics with hit/miss ratios and counts

**Statistics Included:**

-   `hits`: Number of cache hits
-   `misses`: Number of cache misses
-   `writes`: Number of cache writes
-   `deletes`: Number of cache deletions
-   `hit_ratio`: Hit ratio percentage
-   `total_operations`: Total cache operations
-   `driver`: Active driver class name

**Example:**

```php
// Get cache performance stats
$stats = $this->instance->cache->getStats();

// Display in admin dashboard
public function cacheStatsWidget()
{
    $stats = $this->instance->cache->getStats();

    return [
        'hit_ratio' => $stats['hit_ratio'],
        'total_operations' => $stats['total_operations'],
        'active_driver' => basename($stats['driver'])
    ];
}

// Performance monitoring
public function monitorCachePerformance()
{
    $stats = $this->instance->cache->getStats();

    if (floatval($stats['hit_ratio']) < 50.0) {
        // Log poor cache performance
        error_log("Low cache hit ratio: {$stats['hit_ratio']}");
    }

    return $stats;
}

// Debug output
var_dump($this->instance->cache->getStats());
// Output:
// array(7) {
//   ["hits"]=> int(45)
//   ["misses"]=> int(12)
//   ["writes"]=> int(12)
//   ["deletes"]=> int(3)
//   ["hit_ratio"]=> string(6) "78.95%"
//   ["total_operations"]=> int(57)
//   ["driver"]=> string(15) "CacheFileDriver"
// }
```

### `isHealthy()`

Performs a health check on the cache system.

**Returns:** `bool` - True if cache is operational, false otherwise

**Example:**

```php
// Health check
if (!$this->instance->cache->isHealthy()) {
    error_log('Cache system is not functioning properly');
}

// Application monitoring
public function systemHealthCheck()
{
    $health = [
        'database' => $this->instance->database->isConnected(),
        'cache' => $this->instance->cache->isHealthy(),
        'session' => session_status() === PHP_SESSION_ACTIVE
    ];

    return $health;
}

// Graceful degradation
public function getCriticalData()
{
    if ($this->instance->cache->isHealthy()) {
        return $this->instance->cache->remember('critical.data', 3600, function() {
            return $this->fetchCriticalData();
        });
    } else {
        // Cache unavailable, fetch directly
        return $this->fetchCriticalData();
    }
}
```

---

## Complete Usage Examples

### User Data Caching

```php
class UserController extends Controller
{
    public function getUser($userId)
    {
        // Try cache first
        $user = $this->instance->cache->get("user.{$userId}");

        if (!$user) {
            // Cache miss - fetch from database
            $user = $this->models->user->get($userId);

            if ($user) {
                // Cache for 1 hour with user tag
                $this->instance->cache->tags(['users', "user.{$userId}"])
                    ->set("user.{$userId}", $user, 3600);
            }
        }

        return $user;
    }

    public function updateUser($userId)
    {
        // Update user data
        $userData = $this->request->post();
        $success = $this->models->user->update($userId, $userData);

        if ($success) {
            // Clear user-specific caches
            $this->instance->cache->tags(["user.{$userId}"])->flush();

            // Update cache with new data
            $updatedUser = $this->models->user->get($userId);
            $this->instance->cache->tags(['users', "user.{$userId}"])
                ->set("user.{$userId}", $updatedUser, 3600);

            $this->response->sendJson($updatedUser);
        } else {
            $this->response->sendError('Update failed', 500);
        }
    }

    public function getUserPermissions($userId)
    {
        $cacheKey = "user.permissions.{$userId}";

        return $this->instance->cache->remember($cacheKey, 1800, function() use ($userId) {
            return $this->models->user->getPermissions($userId);
        });
    }
}
```

### Content Management Caching

```php
class PostController extends Controller
{
    public function index()
    {
        $page = $this->request->get('page', 1);
        $category = $this->request->get('category');

        // Create cache key based on parameters
        $cacheKey = 'posts.list.' . md5(serialize(['page' => $page, 'category' => $category]));

        $posts = $this->instance->cache->remember($cacheKey, 1800, function() use ($page, $category) {
            return $this->models->post->getPaginated($page, 20, $category);
        });

        $this->view->render('posts/index', ['posts' => $posts]);
    }

    public function show($postId)
    {
        $post = $this->instance->cache->remember("post.{$postId}", 3600, function() use ($postId) {
            return $this->models->post->getWithComments($postId);
        });

        if (!$post) {
            $this->response->sendError('Post not found', 404);
            return;
        }

        // Cache related posts
        $relatedPosts = $this->instance->cache->remember("post.{$postId}.related", 7200, function() use ($post) {
            return $this->models->post->getRelated($post['category_id'], $post['id']);
        });

        $this->view->render('posts/show', [
            'post' => $post,
            'related_posts' => $relatedPosts
        ]);
    }

    public function create()
    {
        $postData = $this->request->post();
        $postId = $this->models->post->create($postData);

        if ($postId) {
            // Clear post listing caches
            $this->instance->cache->tags(['posts', 'listings'])->flush();

            // Cache the new post
            $newPost = $this->models->post->get($postId);
            $this->instance->cache->tags(['posts', "post.{$postId}"])
                ->set("post.{$postId}", $newPost, 3600);

            $this->response->sendJson($newPost, 201);
        } else {
            $this->response->sendError('Creation failed', 500);
        }
    }

    public function update($postId)
    {
        $postData = $this->request->post();
        $success = $this->models->post->update($postId, $postData);

        if ($success) {
            // Clear post-specific caches
            $this->instance->cache->tags(["post.{$postId}"])->flush();

            // Clear listing caches that might include this post
            $this->instance->cache->tags(['posts', 'listings'])->flush();

            $this->response->sendJson(['message' => 'Post updated successfully']);
        } else {
            $this->response->sendError('Update failed', 500);
        }
    }
}
```

### API Response Caching

```php
class ApiController extends Controller
{
    public function weather($city)
    {
        $cacheKey = "weather.{$city}";

        $weatherData = $this->instance->cache->remember($cacheKey, 1800, function() use ($city) {
            // Expensive API call
            return $this->weatherService->getCurrentWeather($city);
        });

        $this->response->sendJson($weatherData);
    }

    public function stockPrice($symbol)
    {
        $cacheKey = "stock.{$symbol}";

        // Cache for 5 minutes during market hours
        $ttl = $this->isMarketOpen() ? 300 : 3600;

        $stockData = $this->instance->cache->remember($cacheKey, $ttl, function() use ($symbol) {
            return $this->stockService->getPrice($symbol);
        });

        $this->response->sendJson($stockData);
    }

    public function searchResults()
    {
        $query = $this->request->get('q');
        $page = $this->request->get('page', 1);
        $filters = $this->request->get('filters', []);

        // Create unique cache key for search parameters
        $cacheKey = 'search.' . md5(serialize(['q' => $query, 'page' => $page, 'filters' => $filters]));

        $results = $this->instance->cache->remember($cacheKey, 600, function() use ($query, $page, $filters) {
            return $this->searchService->search($query, $page, $filters);
        });

        $this->response->sendJson($results);
    }
}
```

### Session and Rate Limiting

```php
class AuthController extends Controller
{
    public function login()
    {
        $email = $this->request->post('email');
        $ip = $this->request->getClientIP();

        // Rate limiting with cache
        $rateLimitKey = "rate_limit.login.{$ip}";
        $attempts = $this->instance->cache->get($rateLimitKey, 0);

        if ($attempts >= 5) {
            $this->response->sendError('Too many login attempts. Try again later.', 429);
            return;
        }

        // Attempt login
        if ($this->auth->login($email, $this->request->post('password'))) {
            // Clear rate limit on successful login
            $this->instance->cache->forget($rateLimitKey);

            // Cache user session data
            $userId = $this->auth->user['id'];
            $this->instance->cache->tags(['sessions', "user.{$userId}"])
                ->set("session.{$userId}", $this->auth->user, 3600);

            $this->response->sendJson(['message' => 'Login successful']);
        } else {
            // Increment failed attempts
            $this->instance->cache->set($rateLimitKey, $attempts + 1, 3600);
            $this->response->sendError('Invalid credentials', 401);
        }
    }

    public function logout()
    {
        $userId = $this->auth->user['id'];

        // Clear user session caches
        $this->instance->cache->tags(['sessions', "user.{$userId}"])->flush();

        $this->auth->logout();
        $this->response->sendJson(['message' => 'Logged out successfully']);
    }
}
```

### Configuration and Settings Caching

```php
class ConfigService extends Library
{
    public function getAppConfig()
    {
        return $this->instance->cache->remember('app.config', 0, function() {
            // Forever cache - only cleared when config changes
            return [
                'app_name' => $_ENV['APP_NAME'],
                'version' => $this->getVersion(),
                'features' => $this->getEnabledFeatures(),
                'api_limits' => $this->getApiLimits()
            ];
        });
    }

    public function getThemeSettings()
    {
        return $this->instance->cache->forever('theme.settings', [
            'primary_color' => '#007bff',
            'secondary_color' => '#6c757d',
            'font_family' => 'Inter, sans-serif',
            'logo_url' => '/assets/images/logo.png'
        ]);
    }

    public function updateConfig($key, $value)
    {
        // Update database
        $this->models->setting->updateSetting($key, $value);

        // Clear config caches to force reload
        $this->instance->cache->forget('app.config');
        $this->instance->cache->tags(['config'])->flush();

        return true;
    }

    public function getCountries()
    {
        return $this->instance->cache->forever('lookup.countries', function() {
            return $this->models->country->getAllActive();
        });
    }
}
```

---

## Driver Configuration

### Environment Variables

```env
# Cache driver selection
CACHE_DRIVER=file          # Options: file, redis, memcached
CACHE_TTL=3600            # Default TTL in seconds
CACHE_PREFIX=myapp_       # Cache key prefix

# Redis configuration (if using Redis driver)
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_PASSWORD=secret
REDIS_DATABASE=0

# Memcached configuration (if using Memcached driver)
MEMCACHED_HOST=localhost
MEMCACHED_PORT=11211
```

### File Driver Configuration

```php
// File cache stores in: APPLICATION_DIRECTORY/Cache/
// Automatic directory structure:
// Cache/
// ├── data/          # Cache files
// ├── meta/          # Metadata files
// └── tags/          # Tag association files
```

### Redis Driver Configuration

```php
// Requires php-redis extension
// Automatic fallback to file cache if Redis unavailable
// Production-ready with connection pooling
```

---

## Performance Optimization

### Cache Key Design

```php
// ✅ Good: Descriptive, hierarchical keys
'user.123.profile'
'posts.category.tech.page.1'
'api.weather.london.current'

// ❌ Bad: Generic or conflicting keys
'data'
'temp'
'cache_123'
```

### TTL Strategy

```php
// Static data: Forever cache
$this->instance->cache->forever('countries', $countries);

// Dynamic data: Appropriate TTL
$this->instance->cache->set('user.activity', $activity, 300);    // 5 minutes
$this->instance->cache->set('daily.stats', $stats, 86400);       // 24 hours
$this->instance->cache->set('search.results', $results, 1800);   // 30 minutes
```

### Tag Organization

```php
// Hierarchical tagging for efficient invalidation
$this->instance->cache->tags(['users', 'profiles', "user.{$id}"])
    ->set("user.{$id}.profile", $profile, 3600);

// Clear all user data
$this->instance->cache->tags(['users'])->flush();

// Clear specific user data
$this->instance->cache->tags(["user.{$id}"])->flush();
```

---

## Best Practices

### 1. Use Remember Pattern

```php
// ✅ Good: Remember pattern for expensive operations
$data = $this->instance->cache->remember('expensive.operation', 3600, function() {
    return $this->performExpensiveOperation();
});

// ❌ Bad: Manual cache checking
$data = $this->instance->cache->get('expensive.operation');
if (!$data) {
    $data = $this->performExpensiveOperation();
    $this->instance->cache->set('expensive.operation', $data, 3600);
}
```

### 2. Appropriate TTL Values

```php
// ✅ Good: TTL matches data volatility
$this->instance->cache->set('stock.price', $price, 300);      // 5 min (volatile)
$this->instance->cache->set('user.profile', $profile, 3600);  // 1 hour (stable)
$this->instance->cache->forever('app.config', $config);       // Never changes
```

### 3. Smart Cache Invalidation

```php
// ✅ Good: Clear related caches
public function updateUser($userId) {
    $this->models->user->update($userId, $data);

    // Clear user-specific caches
    $this->instance->cache->tags(["user.{$userId}"])->flush();

    // Clear listings that might include this user
    $this->instance->cache->tags(['user_listings'])->flush();
}
```

### 4. Error Handling

```php
// ✅ Good: Graceful degradation
public function getCriticalData() {
    try {
        return $this->instance->cache->remember('critical.data', 3600, function() {
            return $this->fetchFromDatabase();
        });
    } catch (Exception $e) {
        // Cache failed, fetch directly
        return $this->fetchFromDatabase();
    }
}
```

---

## Framework Integration

The Cache API integrates seamlessly with other framework components:

-   **Models**: Data caching for expensive database queries
-   **Controllers**: Page-level and component caching
-   **Authentication**: Session and permission caching
-   **API**: Response caching for external API calls
-   **Views**: Template and partial rendering cache
-   **Security**: Rate limiting and security token storage

The Cache API provides enterprise-grade caching with automatic driver selection, comprehensive tag support, and production-ready performance optimization for scalable applications.
