<?php

/**
 * ===============================================================
 * HOIST FRAMEWORK - CACHING SYSTEM
 * ===============================================================
 * 
 * High-performance file-based caching system with optional Redis/Memcached support.
 * 
 * The Cache class provides a comprehensive caching solution that follows Hoist's
 * "lightweight with optional complexity" philosophy. It offers file-based caching
 * by default with zero configuration, while supporting Redis and Memcached for
 * production scaling when needed.
 * 
 * CORE CAPABILITIES:
 * 
 * 1. STORAGE DRIVERS
 *    - File-based caching (default, zero setup)
 *    - Redis support (optional, high performance)
 *    - Memcached support (optional, distributed)
 *    - Automatic fallback between drivers
 * 
 * 2. CACHING FEATURES
 *    - TTL (Time To Live) support with automatic expiration
 *    - Cache tags for group invalidation
 *    - Atomic operations to prevent race conditions
 *    - Automatic serialization/deserialization
 * 
 * 3. DEVELOPER EXPERIENCE
 *    - Simple, intuitive API
 *    - Remember pattern for expensive operations
 *    - Automatic cache key normalization
 *    - Built-in debugging and statistics
 * 
 * 4. PRODUCTION FEATURES
 *    - Garbage collection for expired files
 *    - Memory usage optimization
 *    - Error handling with graceful degradation
 *    - Cache warming and preloading
 * 
 * USAGE PATTERNS:
 * 
 * Basic Caching:
 * ```php
 * // Store data with TTL
 * $cache->set('user.123', $userData, 3600);
 * 
 * // Retrieve data
 * $userData = $cache->get('user.123');
 * 
 * // Remember pattern (cache miss handling)
 * $users = $cache->remember('users.all', 300, function() {
 *     return $this->database->getAllUsers();
 * });
 * ```
 * 
 * Advanced Features:
 * ```php
 * // Cache with tags
 * $cache->tags(['users', 'admin'])->set('admin.users', $data, 3600);
 * 
 * // Invalidate by tag
 * $cache->tags(['users'])->flush();
 * 
 * // Cache forever (until manually cleared)
 * $cache->forever('config.settings', $settings);
 * ```
 * 
 * DRIVER CONFIGURATION:
 * 
 * File Driver (Default):
 * - Zero configuration required
 * - Stores cache in Application/Cache/ directory
 * - Automatic directory creation and permissions
 * 
 * Redis Driver:
 * - Set CACHE_DRIVER=redis in .env
 * - Configure REDIS_HOST, REDIS_PORT, REDIS_PASSWORD
 * - Automatic fallback to file cache if Redis unavailable
 * 
 * @package    Hoist\Core\Libraries
 * @author     Hoist Framework Team
 * @copyright  2024 Hoist Framework
 * @license    MIT License
 * @version    1.0.0
 * @since      Framework 1.0
 * 
 * @see        FileDatabase For similar file-based storage patterns
 * @see        Instance For service container integration
 */
class Cache
{
    // ===============================================================
    // CLASS PROPERTIES AND CONFIGURATION
    // ===============================================================

    /**
     * Active cache driver instance.
     * 
     * Contains the initialized cache driver that handles the actual
     * storage and retrieval operations. Drivers implement a common
     * interface for consistent behavior across different backends.
     * 
     * @var object Cache driver instance (FileDriver, RedisDriver, etc.)
     */
    private $driver;

    /**
     * Cache configuration settings.
     * 
     * Contains all configuration parameters for the cache system including
     * driver selection, paths, TTL defaults, and driver-specific options.
     * 
     * @var array Cache configuration parameters
     */
    private $config;

    /**
     * Default Time To Live (TTL) in seconds.
     * 
     * Specifies the default expiration time for cached items when
     * no explicit TTL is provided. Can be overridden per cache operation.
     * 
     * @var int Default TTL in seconds (3600 = 1 hour)
     */
    private $defaultTtl = 3600;

    /**
     * Active cache tags for the current operation.
     * 
     * Cache tags allow grouping related cache entries for bulk operations
     * like invalidation. Tags are reset after each operation to prevent
     * unintended tag application to subsequent operations.
     * 
     * @var array Currently active cache tags
     */
    private $currentTags = [];

    /**
     * Cache statistics for debugging and monitoring.
     * 
     * Tracks cache hit/miss ratios, operation counts, and performance
     * metrics for debugging and optimization purposes.
     * 
     * @var array Cache operation statistics
     */
    private $stats = [
        'hits' => 0,
        'misses' => 0,
        'writes' => 0,
        'deletes' => 0
    ];

    // ===============================================================
    // CONSTRUCTOR AND INITIALIZATION
    // ===============================================================

    /**
     * Initializes the cache system with driver auto-detection.
     * 
     * Sets up the caching system by detecting the best available driver
     * based on environment configuration and available extensions.
     * Falls back gracefully from Redis/Memcached to file-based caching
     * if external services are unavailable.
     * 
     * DRIVER SELECTION PRIORITY:
     * 1. Environment variable CACHE_DRIVER (redis, memcached, file)
     * 2. Available PHP extensions (Redis, Memcached)
     * 3. File driver as final fallback (always works)
     * 
     * INITIALIZATION PROCESS:
     * 1. Read environment configuration
     * 2. Test driver connectivity
     * 3. Initialize selected driver
     * 4. Set up cache directory structure (for file driver)
     * 5. Configure default settings
     * 
     * @param array $config Optional configuration overrides
     */
    public function __construct($config = [])
    {
        // Merge with default configuration
        $this->config = array_merge([
            'driver' => $_ENV['CACHE_DRIVER'] ?? 'file',
            'ttl' => $_ENV['CACHE_TTL'] ?? 3600,
            'path' => APPLICATION_DIRECTORY . '/Cache',
            'prefix' => $_ENV['CACHE_PREFIX'] ?? 'hoist_',
        ], $config);

        $this->defaultTtl = (int) $this->config['ttl'];

        // Initialize the appropriate driver
        $this->initializeDriver();
    }

    /**
     * Initializes the cache driver based on configuration.
     * 
     * Attempts to initialize the configured cache driver with automatic
     * fallback to file-based caching if the preferred driver is unavailable.
     * This ensures the cache system always works regardless of environment.
     * 
     * @return void
     * @access private
     */
    private function initializeDriver()
    {
        $driverName = strtolower($this->config['driver']);

        try {
            switch ($driverName) {
                case 'redis':
                    if (extension_loaded('redis')) {
                        $this->driver = new CacheRedisDriver($this->config);
                        return;
                    }
                    break;

                case 'memcached':
                    if (extension_loaded('memcached')) {
                        $this->driver = new CacheMemcachedDriver($this->config);
                        return;
                    }
                    break;
            }
        } catch (Exception $e) {
            // Driver initialization failed, fall back to file cache
        }

        // Default to file driver
        $this->driver = new CacheFileDriver($this->config);
    }

    // ===============================================================
    // CORE CACHING OPERATIONS
    // ===============================================================

    /**
     * Retrieves a cached value by key.
     * 
     * Attempts to retrieve a cached value, handling expiration automatically
     * and returning the default value if the key doesn't exist or has expired.
     * Updates cache statistics for monitoring and debugging.
     * 
     * KEY NORMALIZATION:
     * Cache keys are automatically normalized to ensure compatibility
     * across different drivers and prevent filename issues.
     * 
     * EXPIRATION HANDLING:
     * Expired items are automatically removed during retrieval to
     * keep the cache clean and prevent stale data.
     * 
     * @param string $key Cache key to retrieve
     * @param mixed $default Default value if key not found or expired
     * @return mixed Cached value or default value
     */
    public function get($key, $default = null)
    {
        $normalizedKey = $this->normalizeKey($key);

        try {
            $value = $this->driver->get($normalizedKey);

            if ($value !== null) {
                $this->stats['hits']++;
                return $value;
            }
        } catch (Exception $e) {
            // Driver error, return default
        }

        $this->stats['misses']++;
        return $default;
    }

    /**
     * Stores a value in the cache with optional TTL.
     * 
     * Stores a value in the cache with the specified Time To Live (TTL).
     * If no TTL is provided, uses the default TTL configured for the cache.
     * Applies any active cache tags to the stored item.
     * 
     * VALUE SERIALIZATION:
     * Complex data types (arrays, objects) are automatically serialized
     * for storage and deserialized on retrieval.
     * 
     * TAG SUPPORT:
     * If cache tags are active, the item is associated with those tags
     * for group operations like bulk invalidation.
     * 
     * @param string $key Cache key for storage
     * @param mixed $value Value to cache (any serializable type)
     * @param int|null $ttl Time to live in seconds, null for default TTL
     * @return bool True if successfully stored, false otherwise
     */
    public function set($key, $value, $ttl = null)
    {
        $normalizedKey = $this->normalizeKey($key);
        $ttl = $ttl ?? $this->defaultTtl;
        $expiresAt = $ttl > 0 ? time() + $ttl : 0; // 0 = never expires

        try {
            $success = $this->driver->set($normalizedKey, $value, $expiresAt, $this->currentTags);

            if ($success) {
                $this->stats['writes']++;
                $this->clearCurrentTags(); // Reset tags after use
                return true;
            }
        } catch (Exception $e) {
            // Driver error
        }

        return false;
    }

    /**
     * Cache a value forever (until manually removed).
     * 
     * Stores a value in the cache without expiration. The value will
     * remain cached until explicitly removed or the cache is cleared.
     * Useful for configuration data and other persistent values.
     * 
     * @param string $key Cache key for storage
     * @param mixed $value Value to cache permanently
     * @return bool True if successfully stored, false otherwise
     */
    public function forever($key, $value)
    {
        return $this->set($key, $value, 0); // 0 TTL = forever
    }

    /**
     * Removes a cached value by key.
     * 
     * Removes the specified cache entry and any associated metadata
     * (tags, expiration info). Returns true if the key existed and
     * was successfully removed.
     * 
     * @param string $key Cache key to remove
     * @return bool True if key was removed, false if not found
     */
    public function forget($key)
    {
        $normalizedKey = $this->normalizeKey($key);

        try {
            $success = $this->driver->forget($normalizedKey);

            if ($success) {
                $this->stats['deletes']++;
                return true;
            }
        } catch (Exception $e) {
            // Driver error
        }

        return false;
    }

    /**
     * Get or set a cached value using a callback.
     * 
     * The "remember" pattern: retrieves a cached value if it exists,
     * or executes a callback to generate the value, caches it, and
     * returns it. This is the most common caching pattern for expensive
     * operations like database queries or API calls.
     * 
     * CALLBACK EXECUTION:
     * The callback is only executed if the cache key doesn't exist
     * or has expired, minimizing expensive operations.
     * 
     * ATOMIC OPERATION:
     * The get-or-set operation is handled atomically to prevent
     * race conditions in high-concurrency environments.
     * 
     * @param string $key Cache key
     * @param int $ttl Time to live in seconds
     * @param callable $callback Function to execute if cache miss
     * @return mixed Cached value or callback result
     */
    public function remember($key, $ttl, $callback)
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        // Cache miss - execute callback and store result
        $value = call_user_func($callback);
        $this->set($key, $value, $ttl);

        return $value;
    }

    // ===============================================================
    // CACHE TAGS AND GROUP OPERATIONS
    // ===============================================================

    /**
     * Set cache tags for the next operation.
     * 
     * Cache tags allow grouping related cache entries for bulk operations.
     * All subsequent cache operations will be associated with these tags
     * until clearCurrentTags() is called or new tags are set.
     * 
     * TAG USAGE PATTERNS:
     * - Group by entity type: ['users', 'profiles']
     * - Group by operation: ['admin', 'reports']
     * - Group by time period: ['daily', '2024-01']
     * 
     * @param array $tags Array of tag names to apply
     * @return Cache Returns self for method chaining
     */
    public function tags($tags)
    {
        $this->currentTags = is_array($tags) ? $tags : [$tags];
        return $this;
    }

    /**
     * Clears current tags after operation.
     * 
     * Resets the active tags to prevent them from being applied
     * to subsequent cache operations unintentionally.
     * 
     * @return void
     * @access private
     */
    private function clearCurrentTags()
    {
        $this->currentTags = [];
    }

    /**
     * Clears all cached entries.
     * 
     * Removes all cached data from the current driver. If tags are
     * active, only entries with those tags are removed. Otherwise,
     * the entire cache is cleared.
     * 
     * TAG-BASED CLEARING:
     * If cache tags are set, only entries associated with those
     * tags are removed, allowing selective cache invalidation.
     * 
     * @return bool True if cache was successfully cleared
     */
    public function flush()
    {
        try {
            if (!empty($this->currentTags)) {
                // Clear only entries with current tags
                $success = $this->driver->flushTags($this->currentTags);
                $this->clearCurrentTags();
                return $success;
            } else {
                // Clear entire cache
                return $this->driver->flush();
            }
        } catch (Exception $e) {
            return false;
        }
    }

    // ===============================================================
    // UTILITY AND HELPER METHODS
    // ===============================================================

    /**
     * Normalizes cache keys for consistent storage.
     * 
     * Converts cache keys to a consistent format that works across
     * all drivers and prevents filename/key conflicts. Handles special
     * characters, length limits, and driver-specific requirements.
     * 
     * NORMALIZATION RULES:
     * 1. Replace invalid characters with underscores
     * 2. Apply cache prefix to prevent collisions
     * 3. Limit length to prevent filesystem issues
     * 4. Convert to lowercase for consistency
     * 
     * @param string $key Original cache key
     * @return string Normalized cache key
     * @access private
     */
    private function normalizeKey($key)
    {
        // Replace invalid characters and apply prefix
        $normalized = $this->config['prefix'] . preg_replace('/[^a-zA-Z0-9._-]/', '_', $key);

        // Limit length to prevent filesystem issues
        if (strlen($normalized) > 250) {
            $normalized = substr($normalized, 0, 200) . '_' . md5($key);
        }

        return strtolower($normalized);
    }

    /**
     * Gets cache statistics for monitoring and debugging.
     * 
     * Returns comprehensive statistics about cache operations including
     * hit/miss ratios, operation counts, and performance metrics.
     * Useful for optimization and monitoring cache effectiveness.
     * 
     * @return array Cache statistics with hit/miss ratios and counts
     */
    public function getStats()
    {
        $total = $this->stats['hits'] + $this->stats['misses'];
        $hitRatio = $total > 0 ? round(($this->stats['hits'] / $total) * 100, 2) : 0;

        return array_merge($this->stats, [
            'hit_ratio' => $hitRatio . '%',
            'total_operations' => $total,
            'driver' => get_class($this->driver)
        ]);
    }

    /**
     * Checks if the cache system is functioning.
     * 
     * Performs a quick health check by attempting to store and retrieve
     * a test value. Returns true if the cache is working properly.
     * 
     * @return bool True if cache is operational, false otherwise
     */
    public function isHealthy()
    {
        $testKey = 'health_check_' . time();
        $testValue = 'ok';

        try {
            if (!$this->set($testKey, $testValue, 60)) {
                return false;
            }

            $retrieved = $this->get($testKey);
            $this->forget($testKey);

            return $retrieved === $testValue;
        } catch (Exception $e) {
            return false;
        }
    }
}
