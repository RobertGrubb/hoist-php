<?php

/**
 * Application Settings Management Library
 * 
 * Provides comprehensive configuration and settings management for the Hoist
 * framework with FileDatabase persistence, caching, and runtime modification
 * capabilities. This library handles application-wide settings storage,
 * retrieval, and updates with automatic synchronization using JSON files.
 * 
 * CORE CAPABILITIES:
 * - FileDatabase-backed settings persistence (JSON files)
 * - Runtime setting retrieval and modification
 * - Automatic setting caching for performance
 * - Bulk settings loading and initialization
 * - Dynamic setting updates with immediate effect
 * 
 * SETTINGS ARCHITECTURE:
 * - FileDatabase table: 'settings' as JSON file
 * - Record structure: id, name, description, setting, value, type, options, enabled, timestamps
 * - In-memory cache: $values array for fast access
 * - Automatic initialization: Loads all settings on construction
 * - Real-time updates: Immediate reflection of changes
 * 
 * FILEDATABASE INTEGRATION:
 * Uses the framework's FileDatabase service to:
 * - Load all settings on initialization from JSON file
 * - Persist setting changes to JSON file
 * - Maintain data consistency across requests
 * - Support atomic file operations
 * 
 * PERFORMANCE FEATURES:
 * - Settings cached in memory after initial load
 * - JSON file-based storage for fast access
 * - Direct array access for maximum speed
 * - Efficient batch loading patterns
 * 
 * USAGE EXAMPLES:
 * 
 * Basic Setting Retrieval:
 * ```php
 * $settings = new Settings($frameworkInstance);
 * $siteName = $settings->get('seo.title');
 * $brandTitle = $settings->get('brand.title');
 * ```
 * 
 * Setting Updates:
 * ```php
 * $settings->set('seo.title', 'My New Site');
 * $settings->set('brand.title', 'My Brand');
 * ```
 * 
 * Multiple Settings Access:
 * ```php
 * $seoSettings = [
 *     'title' => $settings->get('seo.title'),
 *     'description' => $settings->get('seo.description'),
 *     'keywords' => $settings->get('seo.keywords')
 * ];
 * ```
 * 
 * SETTING CATEGORIES:
 * Organized setting groups:
 * - SEO settings (seo.title, seo.description, seo.keywords, seo.image)
 * - Brand settings (brand.title)
 * - Analytics settings (analytics.google_tag)
 * - Custom application settings
 * 
 * @package HoistPHP\Core\Libraries
 * @version 1.0.0
 * @since 1.0.0
 */
class Settings
{
    /**
     * Framework instance for service access.
     * 
     * Stores the framework instance to provide access to FileDatabase
     * and other framework components needed for settings management
     * and persistence operations.
     * 
     * @var object Framework service container instance
     * @access private
     */
    private $instance;

    /**
     * FileDatabase instance for JSON-based settings storage.
     * 
     * Direct access to the FileDatabase service for performing settings
     * operations on the JSON file storage system. This provides the
     * persistence layer for all setting management operations.
     * 
     * @var FileDatabase JSON-based database instance
     * @access private
     */
    private $fileDatabase;

    /**
     * In-memory cache of all application settings.
     * 
     * Stores all settings as key-value pairs for fast access during
     * request processing. This cache is populated during initialization
     * and updated when settings are modified.
     * 
     * @var array Associative array of setting name => value pairs
     * @access public
     */
    public $values = [];

    /**
     * Initializes the settings system with FileDatabase loading.
     * 
     * Sets up the settings management system by storing the framework
     * instance, initializing the FileDatabase connection, and immediately
     * loading all settings from the JSON file into memory for fast access
     * during request processing.
     * 
     * INITIALIZATION PROCESS:
     * 1. Stores framework instance for service access
     * 2. Initializes FileDatabase with 'app' database
     * 3. Calls setInitial() to load all settings from JSON file
     * 4. Populates $values array with current settings
     * 5. System ready for setting operations
     * 
     * @param object $instance Framework service container instance
     */
    public function __construct($instance)
    {
        $this->instance = $instance;
        $this->fileDatabase = new FileDatabase('app');

        $this->setInitial();
    }

    /**
     * Loads all settings from FileDatabase into memory cache.
     * 
     * Performs initial loading of all application settings from the
     * JSON file database into the in-memory cache for fast access. This
     * method is called during initialization and can be used to refresh
     * the settings cache when needed.
     * 
     * LOADING PROCESS:
     * 1. Clears existing values array
     * 2. Queries FileDatabase for all settings from 'settings' table
     * 3. Populates values array with setting => value pairs
     * 4. Cache ready for fast setting access
     * 
     * JSON FILE STRUCTURE:
     * Expects records from 'settings.json' with structure:
     * - setting: Column containing the setting name/key
     * - value: Column containing the setting value
     * - Additional metadata: name, description, type, options, enabled, etc.
     * 
     * @return void
     */
    public function setInitial()
    {
        $this->values = [];
        $settings = $this->fileDatabase->table('settings')->all();

        foreach ($settings as $setting) {
            $this->values[$setting['setting']] = $setting['value'];
        }
    }

    /**
     * Checks if a setting exists in the FileDatabase.
     * 
     * Verifies whether a specific setting key exists in the JSON file
     * by performing a FileDatabase query. This method is useful for
     * conditional setting operations and validation of setting existence
     * before performing updates or retrievals.
     * 
     * FILEDATABASE OPERATION:
     * Performs a query to the 'settings' table to check for the
     * existence of the specified setting key. Returns the full
     * setting record if found, or null if not found.
     * 
     * @param string $key Setting name/key to check for existence
     * @return array|null Setting record if exists, null otherwise
     */
    public function exists($key)
    {
        $result = $this->fileDatabase->table('settings')
            ->where('setting', '=', $key)
            ->get();

        return $result !== false ? $result : null;
    }

    /**
     * Retrieves a setting value from the memory cache.
     * 
     * Gets the value of a specific setting from the in-memory cache
     * for maximum performance. This method provides fast access to
     * setting values without database queries during request processing.
     * 
     * CACHE ACCESS:
     * - Checks memory cache first for immediate response
     * - Returns null if setting doesn't exist
     * - No database queries for optimal performance
     * - Values returned as stored in database
     * 
     * VALUE TYPES:
     * Settings can contain various data types:
     * - Strings: Most common setting type
     * - Numbers: Stored as strings, cast as needed
     * - JSON: Complex data structures serialized
     * - Booleans: Stored as '1'/'0' or 'true'/'false'
     * 
     * USAGE EXAMPLES:
     * 
     * Basic Retrieval:
     * ```php
     * $siteName = $settings->get('site_name');
     * $maxUsers = $settings->get('max_users');
     * ```
     * 
     * With Default Values (manual):
     * ```php
     * $theme = $settings->get('default_theme') ?? 'light';
     * $timeout = $settings->get('session_timeout') ?? 3600;
     * ```
     * 
     * Type Casting:
     * ```php
     * $isEnabled = (bool)$settings->get('feature_enabled');
     * $maxSize = (int)$settings->get('max_upload_size');
     * ```
     * 
     * @param string $key Setting name/key to retrieve
     * @return mixed Setting value if exists, null if not found
     */
    public function get($key)
    {
        if (!isset($this->values[$key]))
            return null;
        return $this->values[$key];
    }

    /**
     * Sets or updates a setting value with FileDatabase persistence.
     * 
     * Creates a new setting or updates an existing one with automatic
     * JSON file persistence and cache refresh. This method handles both
     * INSERT and UPDATE operations transparently based on setting existence.
     * 
     * OPERATION WORKFLOW:
     * 1. Checks if setting exists in FileDatabase
     * 2. Updates existing setting or inserts new one with full record structure
     * 3. Refreshes memory cache with current values
     * 4. Setting immediately available for use
     * 
     * FILEDATABASE OPERATIONS:
     * - UPDATE: For existing settings, updates value and timestamp
     * - INSERT: For new settings, creates complete record with metadata
     * - REFRESH: Reloads all settings after modification
     * 
     * CACHE MANAGEMENT:
     * After each set operation, the entire cache is refreshed to ensure
     * consistency and immediately reflect the changes in subsequent
     * get() operations.
     * 
     * NEW SETTING STRUCTURE:
     * When creating new settings, includes:
     * - name: Human-readable setting name
     * - description: Setting purpose description
     * - setting: The setting key
     * - value: The setting value
     * - type: Input type (input, textarea, file, etc.)
     * - options: Additional options (null by default)
     * - enabled: Whether setting is active (1 by default)
     * - timestamps: created_at and updated_at
     * 
     * USAGE EXAMPLES:
     * 
     * Creating New Settings:
     * ```php
     * $settings->set('site.name', 'My Application');
     * $settings->set('maintenance.mode', 'false');
     * $settings->set('api.rate_limit', '1000');
     * ```
     * 
     * Updating Existing Settings:
     * ```php
     * $settings->set('seo.title', 'Updated Site Title');
     * $settings->set('brand.title', 'New Brand Name');
     * ```
     * 
     * @param string $key Setting name/key to set
     * @param mixed $value Setting value to store
     * @return void
     */
    public function set($key, $value)
    {
        $existingSetting = $this->exists($key);

        if ($existingSetting) {
            // Update existing setting
            $this->fileDatabase->table('settings')
                ->where('setting', '=', $key)
                ->update([
                    'value' => $value,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } else {
            // Insert new setting with default structure
            $this->fileDatabase->table('settings')->insert([
                'name' => ucwords(str_replace('.', ' ', $key)),
                'description' => 'Custom setting for ' . $key,
                'setting' => $key,
                'value' => $value,
                'type' => 'input',
                'options' => null,
                'enabled' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        $this->setInitial();
    }

    /**
     * Retrieves all settings matching a group prefix pattern.
     * 
     * Fetches all settings that start with a specific group prefix,
     * enabling organized setting management through namespacing. This
     * method is useful for retrieving related settings as a batch
     * for configuration or administrative purposes.
     * 
     * GROUP PATTERN MATCHING:
     * Uses FileDatabase WHERE clause with string matching to find
     * settings where the setting name starts with the specified group
     * prefix followed by a dot (.), enabling hierarchical setting organization.
     * 
     * NAMING CONVENTION:
     * Settings are organized using dot notation:
     * - seo.title, seo.description, seo.keywords, seo.image
     * - brand.title
     * - analytics.google_tag
     * - custom.setting_name
     * 
     * USAGE EXAMPLES:
     * 
     * SEO Settings Group:
     * ```php
     * $seoSettings = $settings->group('seo');
     * // Returns: seo.title, seo.description, seo.keywords, seo.image
     * ```
     * 
     * Brand Configuration:
     * ```php
     * $brandSettings = $settings->group('brand');
     * // Returns: brand.title, brand.logo, etc.
     * ```
     * 
     * Analytics Configuration:
     * ```php
     * $analyticsSettings = $settings->group('analytics');
     * // Returns: analytics.google_tag, analytics.facebook_pixel, etc.
     * ```
     * 
     * @param string $group Group prefix to search for (without trailing dot)
     * @return array Array of setting records matching the group pattern
     */
    public function group($group)
    {
        // Get all settings and filter by group prefix
        $allSettings = $this->fileDatabase->table('settings')->all();
        $groupSettings = [];

        foreach ($allSettings as $setting) {
            if (strpos($setting['setting'], $group . '.') === 0) {
                $groupSettings[] = $setting;
            }
        }

        return $groupSettings;
    }
}
