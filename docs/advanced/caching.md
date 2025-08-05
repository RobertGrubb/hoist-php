# ⚡ Caching & Performance

Hoist PHP provides multiple caching strategies and performance optimization techniques to ensure your applications run efficiently at scale. The framework supports file-based caching, Redis, Memcached, and application-level optimizations.

## Cache Configuration

### **Cache Setup and Drivers**

```php
// config/cache.php
return [
    'default' => env('CACHE_DRIVER', 'file'),

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => env('CACHE_PATH', 'storage/cache'),
            'prefix' => env('CACHE_PREFIX', 'hoist_'),
        ],

        'redis' => [
            'driver' => 'redis',
            'host' => env('REDIS_HOST', 'localhost'),
            'port' => env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => env('REDIS_CACHE_DB', 1),
            'prefix' => env('CACHE_PREFIX', 'hoist_'),
        ],

        'memcached' => [
            'driver' => 'memcached',
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', 'localhost'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ]
            ],
            'prefix' => env('CACHE_PREFIX', 'hoist_'),
        ],

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ]
    ]
];
```

### **Cache Manager Implementation**

```php
class CacheManager
{
    private $stores = [];
    private $config;
    private $defaultStore;

    public function __construct($config)
    {
        $this->config = $config;
        $this->defaultStore = $config['default'];
    }

    public function store($name = null)
    {
        $name = $name ?: $this->defaultStore;

        if (!isset($this->stores[$name])) {
            $this->stores[$name] = $this->createStore($name);
        }

        return $this->stores[$name];
    }

    public function get($key, $default = null)
    {
        return $this->store()->get($key, $default);
    }

    public function put($key, $value, $ttl = null)
    {
        return $this->store()->put($key, $value, $ttl);
    }

    public function forget($key)
    {
        return $this->store()->forget($key);
    }

    public function flush()
    {
        return $this->store()->flush();
    }

    public function remember($key, $ttl, $callback)
    {
        return $this->store()->remember($key, $ttl, $callback);
    }

    public function rememberForever($key, $callback)
    {
        return $this->store()->rememberForever($key, $callback);
    }

    private function createStore($name)
    {
        $config = $this->config['stores'][$name];

        switch ($config['driver']) {
            case 'file':
                return new FileCache($config);
            case 'redis':
                return new RedisCache($config);
            case 'memcached':
                return new MemcachedCache($config);
            case 'array':
                return new ArrayCache($config);
            default:
                throw new InvalidArgumentException("Unsupported cache driver: {$config['driver']}");
        }
    }
}
```

## Cache Implementations

### **File-Based Cache**

```php
class FileCache implements CacheInterface
{
    private $path;
    private $prefix;

    public function __construct($config)
    {
        $this->path = rtrim($config['path'], '/');
        $this->prefix = $config['prefix'] ?? '';

        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    public function get($key, $default = null)
    {
        $filePath = $this->getFilePath($key);

        if (!file_exists($filePath)) {
            return $default;
        }

        $content = file_get_contents($filePath);
        $data = unserialize($content);

        // Check if expired
        if ($data['expires'] !== null && time() > $data['expires']) {
            $this->forget($key);
            return $default;
        }

        return $data['value'];
    }

    public function put($key, $value, $ttl = null)
    {
        $filePath = $this->getFilePath($key);
        $expires = $ttl ? time() + $ttl : null;

        $data = [
            'value' => $value,
            'expires' => $expires,
            'created' => time()
        ];

        $content = serialize($data);

        // Ensure directory exists
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($filePath, $content, LOCK_EX) !== false;
    }

    public function forget($key)
    {
        $filePath = $this->getFilePath($key);

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return true;
    }

    public function flush()
    {
        $files = glob($this->path . '/' . $this->prefix . '*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    public function remember($key, $ttl, $callback)
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    public function rememberForever($key, $callback)
    {
        return $this->remember($key, null, $callback);
    }

    private function getFilePath($key)
    {
        $hash = hash('sha256', $this->prefix . $key);
        return $this->path . '/' . substr($hash, 0, 2) . '/' . substr($hash, 2);
    }
}
```

### **Redis Cache**

```php
class RedisCache implements CacheInterface
{
    private $redis;
    private $prefix;

    public function __construct($config)
    {
        $this->redis = new Redis();
        $this->redis->connect($config['host'], $config['port']);

        if ($config['password']) {
            $this->redis->auth($config['password']);
        }

        $this->redis->select($config['database']);
        $this->prefix = $config['prefix'] ?? '';
    }

    public function get($key, $default = null)
    {
        $value = $this->redis->get($this->prefix . $key);

        if ($value === false) {
            return $default;
        }

        return unserialize($value);
    }

    public function put($key, $value, $ttl = null)
    {
        $serialized = serialize($value);

        if ($ttl) {
            return $this->redis->setex($this->prefix . $key, $ttl, $serialized);
        } else {
            return $this->redis->set($this->prefix . $key, $serialized);
        }
    }

    public function forget($key)
    {
        return $this->redis->del($this->prefix . $key) > 0;
    }

    public function flush()
    {
        if ($this->prefix) {
            $keys = $this->redis->keys($this->prefix . '*');
            if (!empty($keys)) {
                return $this->redis->del(...$keys) > 0;
            }
            return true;
        } else {
            return $this->redis->flushDB();
        }
    }

    public function remember($key, $ttl, $callback)
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    public function rememberForever($key, $callback)
    {
        return $this->remember($key, null, $callback);
    }

    public function increment($key, $value = 1)
    {
        return $this->redis->incrBy($this->prefix . $key, $value);
    }

    public function decrement($key, $value = 1)
    {
        return $this->redis->decrBy($this->prefix . $key, $value);
    }
}
```

## Application-Level Caching

### **Model Caching**

```php
class CachedModel extends Model
{
    protected $cachePrefix = 'model';
    protected $cacheTtl = 3600; // 1 hour
    protected $cacheStore = 'default';

    public function find($id)
    {
        $cacheKey = $this->getCacheKey('find', $id);

        return Instance::get()->cache->store($this->cacheStore)->remember(
            $cacheKey,
            $this->cacheTtl,
            function() use ($id) {
                return parent::find($id);
            }
        );
    }

    public function getAll()
    {
        $cacheKey = $this->getCacheKey('all');

        return Instance::get()->cache->store($this->cacheStore)->remember(
            $cacheKey,
            $this->cacheTtl,
            function() {
                return parent::getAll();
            }
        );
    }

    public function create($data)
    {
        $result = parent::create($data);

        // Invalidate relevant cache keys
        $this->invalidateCache(['all', 'count']);

        return $result;
    }

    public function update($id, $data)
    {
        $result = parent::update($id, $data);

        if ($result) {
            // Invalidate specific and general cache keys
            $this->invalidateCache(['find:' . $id, 'all', 'count']);
        }

        return $result;
    }

    public function delete($id)
    {
        $result = parent::delete($id);

        if ($result) {
            $this->invalidateCache(['find:' . $id, 'all', 'count']);
        }

        return $result;
    }

    protected function getCacheKey($operation, ...$params)
    {
        $key = $this->cachePrefix . ':' . $this->table . ':' . $operation;

        if (!empty($params)) {
            $key .= ':' . implode(':', $params);
        }

        return $key;
    }

    protected function invalidateCache($keys)
    {
        $cache = Instance::get()->cache->store($this->cacheStore);

        foreach ($keys as $key) {
            $fullKey = $this->getCacheKey($key);
            $cache->forget($fullKey);
        }
    }

    public function clearModelCache()
    {
        $cache = Instance::get()->cache->store($this->cacheStore);
        $pattern = $this->cachePrefix . ':' . $this->table . ':*';

        // This would need implementation specific to cache store
        $this->forgetByPattern($cache, $pattern);
    }
}
```

### **Query Result Caching**

```php
class CachedQueryBuilder extends QueryBuilder
{
    private $cacheKey;
    private $cacheTtl;
    private $cacheStore = 'default';
    private $shouldCache = false;

    public function cache($ttl = 3600, $key = null, $store = null)
    {
        $this->shouldCache = true;
        $this->cacheTtl = $ttl;
        $this->cacheKey = $key;
        $this->cacheStore = $store ?: $this->cacheStore;

        return $this;
    }

    public function get()
    {
        if (!$this->shouldCache) {
            return parent::get();
        }

        $cacheKey = $this->cacheKey ?: $this->generateCacheKey();

        return Instance::get()->cache->store($this->cacheStore)->remember(
            $cacheKey,
            $this->cacheTtl,
            function() {
                return parent::get();
            }
        );
    }

    public function first()
    {
        if (!$this->shouldCache) {
            return parent::first();
        }

        $cacheKey = $this->cacheKey ?: $this->generateCacheKey() . ':first';

        return Instance::get()->cache->store($this->cacheStore)->remember(
            $cacheKey,
            $this->cacheTtl,
            function() {
                return parent::first();
            }
        );
    }

    public function count()
    {
        if (!$this->shouldCache) {
            return parent::count();
        }

        $cacheKey = $this->cacheKey ?: $this->generateCacheKey() . ':count';

        return Instance::get()->cache->store($this->cacheStore)->remember(
            $cacheKey,
            $this->cacheTtl,
            function() {
                return parent::count();
            }
        );
    }

    private function generateCacheKey()
    {
        $queryComponents = [
            'table' => $this->table,
            'select' => $this->select,
            'where' => $this->where,
            'orderBy' => $this->orderBy,
            'limit' => $this->limit,
            'offset' => $this->offset
        ];

        return 'query:' . hash('sha256', serialize($queryComponents));
    }
}
```

## View and Output Caching

### **View Caching**

```php
class CachedView extends View
{
    private $cacheEnabled = false;
    private $cacheTtl = 3600;
    private $cacheKey;

    public function cache($ttl = 3600, $key = null)
    {
        $this->cacheEnabled = true;
        $this->cacheTtl = $ttl;
        $this->cacheKey = $key;

        return $this;
    }

    public function render($template, $data = [])
    {
        if (!$this->cacheEnabled) {
            return parent::render($template, $data);
        }

        $cacheKey = $this->cacheKey ?: $this->generateViewCacheKey($template, $data);

        return Instance::get()->cache->remember(
            $cacheKey,
            $this->cacheTtl,
            function() use ($template, $data) {
                return parent::render($template, $data);
            }
        );
    }

    private function generateViewCacheKey($template, $data)
    {
        $keyData = [
            'template' => $template,
            'data_hash' => hash('sha256', serialize($data)),
            'user_id' => Instance::get()->auth->id()
        ];

        return 'view:' . hash('sha256', serialize($keyData));
    }

    public function invalidateViewCache($pattern = null)
    {
        if ($pattern) {
            $this->forgetByPattern('view:' . $pattern);
        } else {
            $this->forgetByPattern('view:*');
        }
    }
}
```

### **Fragment Caching**

```php
class FragmentCache
{
    private $cache;

    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
    }

    public function fragment($key, $ttl, $callback)
    {
        $cacheKey = 'fragment:' . $key;

        return $this->cache->remember($cacheKey, $ttl, $callback);
    }

    public function invalidateFragment($key)
    {
        $cacheKey = 'fragment:' . $key;
        return $this->cache->forget($cacheKey);
    }

    public function invalidateFragmentGroup($group)
    {
        return $this->cache->forgetByPattern("fragment:{$group}:*");
    }
}

// Usage in views
class PostController extends Controller
{
    public function show($id)
    {
        $post = $this->instance->models->post->find($id);

        // Cache expensive computations
        $relatedPosts = $this->instance->fragmentCache->fragment(
            "related_posts:{$post['category_id']}",
            1800, // 30 minutes
            function() use ($post) {
                return $this->instance->models->post
                    ->where('category_id', $post['category_id'])
                    ->where('id', '!=', $post['id'])
                    ->limit(5)
                    ->get();
            }
        );

        $this->instance->view->render('post/show', [
            'post' => $post,
            'related_posts' => $relatedPosts
        ]);
    }
}
```

## Performance Optimization

### **Output Compression and Minification**

```php
class OutputOptimizer
{
    private $config;

    public function __construct($config = [])
    {
        $this->config = array_merge([
            'compression' => true,
            'minify_html' => true,
            'minify_css' => true,
            'minify_js' => true,
            'gzip_level' => 6
        ], $config);
    }

    public function optimize($content, $contentType = 'text/html')
    {
        // Minify content based on type
        if ($this->config['minify_html'] && strpos($contentType, 'text/html') !== false) {
            $content = $this->minifyHtml($content);
        } elseif ($this->config['minify_css'] && strpos($contentType, 'text/css') !== false) {
            $content = $this->minifyCss($content);
        } elseif ($this->config['minify_js'] && strpos($contentType, 'javascript') !== false) {
            $content = $this->minifyJs($content);
        }

        // Apply compression
        if ($this->config['compression'] && $this->supportsCompression()) {
            return $this->compress($content);
        }

        return $content;
    }

    private function minifyHtml($html)
    {
        // Remove comments (except IE conditionals)
        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+\]|<!|>))(?:(?!-->).)*-->/s', '', $html);

        // Remove extra whitespace
        $html = preg_replace('/\s+/', ' ', $html);

        // Remove whitespace around block elements
        $html = preg_replace('/>\s+</', '><', $html);

        return trim($html);
    }

    private function minifyCss($css)
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

        // Remove unnecessary whitespace
        $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
        $css = preg_replace('/\s+/', ' ', $css);

        // Remove whitespace around certain characters
        $css = str_replace([' {', '{ ', ' }', '} ', '; ', ' ;', ', ', ' ,'], ['{', '{', '}', '}', ';', ';', ',', ','], $css);

        return trim($css);
    }

    private function minifyJs($js)
    {
        // Basic JS minification (for production, use a proper minifier)
        // Remove single-line comments
        $js = preg_replace('/\/\/.*$/m', '', $js);

        // Remove multi-line comments
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);

        // Remove extra whitespace
        $js = preg_replace('/\s+/', ' ', $js);

        return trim($js);
    }

    private function compress($content)
    {
        if (function_exists('gzcompress')) {
            header('Content-Encoding: gzip');
            return gzcompress($content, $this->config['gzip_level']);
        }

        return $content;
    }

    private function supportsCompression()
    {
        return isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
               strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false;
    }
}
```

### **Asset Caching and CDN**

```php
class AssetManager
{
    private $config;
    private $cache;

    public function __construct($config, CacheManager $cache)
    {
        $this->config = $config;
        $this->cache = $cache;
    }

    public function url($asset, $version = null)
    {
        $version = $version ?: $this->getAssetVersion($asset);
        $baseUrl = $this->config['cdn_url'] ?? $this->config['base_url'];

        return $baseUrl . '/' . ltrim($asset, '/') . '?v=' . $version;
    }

    public function css($files)
    {
        if (!is_array($files)) {
            $files = [$files];
        }

        $html = '';
        foreach ($files as $file) {
            $url = $this->url('assets/styles/' . $file);
            $html .= '<link rel="stylesheet" href="' . $url . '">' . "\n";
        }

        return $html;
    }

    public function js($files)
    {
        if (!is_array($files)) {
            $files = [$files];
        }

        $html = '';
        foreach ($files as $file) {
            $url = $this->url('assets/scripts/' . $file);
            $html .= '<script src="' . $url . '"></script>' . "\n";
        }

        return $html;
    }

    public function preload($assets)
    {
        $html = '';
        foreach ($assets as $asset) {
            $url = $this->url($asset['src']);
            $as = $asset['as'] ?? 'script';
            $html .= '<link rel="preload" href="' . $url . '" as="' . $as . '">' . "\n";
        }

        return $html;
    }

    private function getAssetVersion($asset)
    {
        $cacheKey = 'asset_version:' . $asset;

        return $this->cache->remember($cacheKey, 86400, function() use ($asset) {
            $filePath = $this->config['public_path'] . '/' . $asset;

            if (file_exists($filePath)) {
                return filemtime($filePath);
            }

            return time();
        });
    }

    public function invalidateAssetCache($asset = null)
    {
        if ($asset) {
            $this->cache->forget('asset_version:' . $asset);
        } else {
            $this->cache->forgetByPattern('asset_version:*');
        }
    }
}
```

## Database Query Optimization

### **Query Performance Monitoring**

```php
class QueryProfiler
{
    private $queries = [];
    private $startTime;
    private $enabled = false;

    public function enable()
    {
        $this->enabled = true;
        $this->startTime = microtime(true);
    }

    public function logQuery($sql, $params, $time)
    {
        if (!$this->enabled) {
            return;
        }

        $this->queries[] = [
            'sql' => $sql,
            'params' => $params,
            'time' => $time,
            'timestamp' => microtime(true) - $this->startTime
        ];
    }

    public function getQueries()
    {
        return $this->queries;
    }

    public function getTotalTime()
    {
        return array_sum(array_column($this->queries, 'time'));
    }

    public function getSlowQueries($threshold = 0.1)
    {
        return array_filter($this->queries, function($query) use ($threshold) {
            return $query['time'] > $threshold;
        });
    }

    public function getReport()
    {
        return [
            'total_queries' => count($this->queries),
            'total_time' => $this->getTotalTime(),
            'average_time' => count($this->queries) ? $this->getTotalTime() / count($this->queries) : 0,
            'slow_queries' => count($this->getSlowQueries()),
            'queries' => $this->queries
        ];
    }
}
```

### **Connection Pooling and Optimization**

```php
class OptimizedDatabase extends PDODatabase
{
    private $connectionPool = [];
    private $maxConnections = 10;
    private $currentConnections = 0;
    private $queryProfiler;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->maxConnections = $config['max_connections'] ?? 10;
        $this->queryProfiler = new QueryProfiler();

        if ($config['enable_profiling'] ?? false) {
            $this->queryProfiler->enable();
        }
    }

    public function query($sql, $params = [])
    {
        $startTime = microtime(true);

        try {
            $result = parent::query($sql, $params);

            $executionTime = microtime(true) - $startTime;
            $this->queryProfiler->logQuery($sql, $params, $executionTime);

            return $result;

        } catch (Exception $e) {
            $this->queryProfiler->logQuery($sql, $params, microtime(true) - $startTime);
            throw $e;
        }
    }

    public function getConnection()
    {
        if ($this->currentConnections < $this->maxConnections) {
            $this->currentConnections++;
            return $this->createNewConnection();
        }

        // Wait for available connection or create new one
        return $this->waitForConnection() ?: $this->createNewConnection();
    }

    public function releaseConnection($connection)
    {
        $this->connectionPool[] = $connection;
        $this->currentConnections--;
    }

    private function createNewConnection()
    {
        // Create optimized PDO connection
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'"
        ];

        return new PDO($this->buildDsn(), $this->config['username'], $this->config['password'], $options);
    }

    private function waitForConnection($timeout = 5)
    {
        $start = time();

        while (time() - $start < $timeout) {
            if (!empty($this->connectionPool)) {
                return array_pop($this->connectionPool);
            }
            usleep(100000); // Wait 100ms
        }

        return null;
    }

    public function getProfiler()
    {
        return $this->queryProfiler;
    }
}
```

---

This comprehensive caching and performance system provides multiple layers of optimization including data caching, query result caching, view caching, output optimization, and database performance monitoring. Use these components to build high-performance applications that scale efficiently.

**Next:** [Deployment](../deployment/overview.md) - Learn about deploying Hoist PHP applications to production.
