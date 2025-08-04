<?php

/**
 * Model - Base Database Model Class
 * 
 * The Model class provides a base class for all database models in the CBI
 * application. It implements the Active Record pattern with a focus on
 * security, multi-tenancy, and ease of use. All application models extend
 * this base class to inherit standard CRUD operations and data filtering.
 * 
 * Key Features:
 * - Standard CRUD operations (Create, Read, Update, Delete)
 * - Hidden field filtering for sensitive data
 * - Guard deletion with dependency checking
 * - Multi-tenant data isolation
 * - Integration with Medoo ORM for secure queries
 * - Error handling and logging
 * - Method chaining for query options
 * 
 * Security Features:
 * - Hidden fields automatically stripped from results
 * - Prepared statements via Medoo (prevents SQL injection)
 * - Guard deletion prevents orphaned references
 * - Error logging for debugging
 * 
 * Usage:
 * class UserModel extends Model {
 *     public $table = 'users';
 *     public $hiddenFields = ['password', 'reset_token'];
 * }
 * 
 * @package CBI\Core\Libraries
 * @author CBI Development Team
 */
class Model
{
    // =========================================================================
    // PROPERTIES AND DEPENDENCIES
    // =========================================================================

    /** @var Instance Application instance container for service access */
    public $instance;

    /** @var Database Database connection service */
    public $database;

    /** @var object All registered application models */
    public $models;

    /** @var object All registered application libraries */
    public $libraries;

    /** @var FileDatabase File-based database service */
    public $fileDatabase;

    /** @var string|false Database table name - must be set in child classes */
    public $table = false;

    /** @var array Fields to hide from query results (passwords, tokens, etc.) */
    public $hiddenFields = [];

    /** @var array Options for modifying query behavior (method chaining) */
    public $opts = [];

    // =========================================================================
    // CONSTRUCTOR AND INITIALIZATION
    // =========================================================================

    /**
     * Initializes the model with application dependencies
     * 
     * Sets up access to all application services including database,
     * other models, libraries, and file database. This
     * provides the model with everything needed for data operations.
     * 
     * @param Instance $instance Application service container
     */
    public function __construct($instance)
    {
        $this->instance = $instance;
        $this->database = $instance->database;
        $this->models = $instance->models;
        $this->libraries = $instance->libraries;
        $this->fileDatabase = $instance->fileDatabase;
    }

    /**
     * Model instantiation hook for child classes
     * 
     * This method is called after the model is constructed and can be
     * overridden in child classes to perform model-specific initialization
     * such as setting default values, configuring relationships, etc.
     * 
     * @return void
     */
    public function instantiate()
    {
        // Override in child classes for custom initialization
    }

    // =========================================================================
    // QUERY OPTIONS AND CONFIGURATION
    // =========================================================================

    /**
     * Sets options for modifying query behavior (method chaining)
     * 
     * Options allow temporary modification of model behavior for specific
     * queries. Common options include:
     * - includeHiddenFields: Include normally hidden fields in results
     * - customWhere: Custom where conditions
     * - customOrder: Custom ordering
     * 
     * @param array $opts Options array to modify query behavior
     * @return self Returns self for method chaining
     */
    public function options($opts = [])
    {
        $this->opts = $opts;
        return $this;
    }

    // =========================================================================
    // DATA RETRIEVAL METHODS (READ OPERATIONS)
    // =========================================================================

    /**
     * Retrieves a single record from the database table
     * 
     * This method fetches one record based on the provided where conditions.
     * It automatically applies hidden field filtering and default ordering
     * if configured in the model.
     * 
     * @param int|array|null $where Filter conditions:
     *   - int: Treated as ID lookup (WHERE id = $where)
     *   - array: Complex where conditions for Medoo
     *   - null: Throws exception (filter required)
     * @param string $select Fields to select (* for all visible fields)
     * @return array|null Single record or null if not found
     * @throws Exception If table not set or where condition missing
     */
    public function get($where = null, $select = '*')
    {
        if (!$this->table) {
            throw new \Exception('Model requires a table to be set.');
        }

        if (is_null($where)) {
            throw new \Exception('Get method must have a filter passed. Int|Array');
        }

        // Convert numeric ID to array format for Medoo
        $where = is_numeric($where) ? ['id' => $where] : $where;

        // Apply default ordering if configured and not already specified
        if (isset($this->defaultOrder) && !isset($where['ORDER'])) {
            if (is_array($this->defaultOrder)) {
                $where['ORDER'] = $this->defaultOrder;
            }
        }

        return $this->filterData($this->database->client->get($this->table, $select, $where));
    }

    /**
     * Retrieves multiple records from the database table
     * 
     * This method fetches multiple records based on the provided where
     * conditions. It automatically applies hidden field filtering and
     * default ordering if configured in the model.
     * 
     * @param array|null $where Filter conditions array for Medoo
     * @param string $select Fields to select (* for all visible fields)
     * @return array Array of records (empty array if none found)
     * @throws Exception If table not set, where condition missing, or invalid format
     */
    public function getMany($where = null, $select = '*')
    {
        if (!$this->table) {
            throw new \Exception('Model requires a table to be set.');
        }

        if (is_null($where)) {
            throw new \Exception('Get method must have a filter passed. Int|Array');
        }

        if (!is_array($where)) {
            throw new \Exception('GetMany method must have an array passed.');
        }

        // Apply default ordering if configured and not already specified
        if (isset($this->defaultOrder) && !isset($where['ORDER'])) {
            if (is_array($this->defaultOrder)) {
                $where['ORDER'] = $this->defaultOrder;
            }
        }

        return $this->filterData($this->database->client->select($this->table, $select, $where));
    }

    /**
     * Counts records in the database table
     * 
     * Returns the number of records matching the specified conditions.
     * Useful for pagination, statistics, and validation checks.
     * 
     * @param array|null $where Filter conditions array for Medoo
     * @return int Number of matching records
     * @throws Exception If table not set or where condition missing
     */
    public function count($where = null)
    {
        if (!$this->table) {
            throw new \Exception('Model requires a table to be set.');
        }

        if (is_null($where)) {
            throw new \Exception('Get method must have a filter passed. Int|Array');
        }

        if (!is_array($where)) {
            throw new \Exception('GetMany method must have an array passed.');
        }

        return $this->database->client->count($this->table, $where);
    }

    // =========================================================================
    // DATA MODIFICATION METHODS (WRITE OPERATIONS)
    // =========================================================================

    /**
     * Updates existing records in the database table
     * 
     * This method provides a safe way to update database records with
     * error handling and logging. It supports both single ID updates
     * and complex where condition updates.
     * 
     * @param int|array $where Update conditions:
     *   - int: Update record with this ID
     *   - array: Complex where conditions for Medoo
     * @param array $data Associative array of field => value pairs to update
     * @return bool True on success, false on failure
     * @throws Exception If table not set
     */
    public function save($where, $data)
    {
        if (!$this->table) {
            throw new \Exception('Model methods require a table to be set.');
        }

        // Convert single ID to array format for Medoo
        if (!is_array($where)) {
            $where = [
                'id' => $where,
            ];
        }

        try {
            $res = $this->database->client->update($this->table, $data, $where);
        } catch (\Exception $e) {
            // Log error for debugging but don't expose details to user
            error_log($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Creates new records in the database table
     * 
     * This method inserts new data into the database table with error
     * handling and returns the ID of the newly created record.
     * 
     * @param array $data Associative array of field => value pairs to insert
     * @return mixed ID of newly created record, or false on failure
     * @throws Exception If table not set
     */
    public function create($data)
    {
        if (!$this->table) {
            throw new \Exception('Model methods require a table to be set.');
        }

        try {
            $this->database->client->insert($this->table, $data);
        } catch (\Exception $e) {
            // Log error for debugging but don't expose details to user
            error_log($e->getMessage());
            return false;
        }

        // Return ID of newly created record
        return $this->database->client->id();
    }

    /**
     * Deletes records from the database table
     * 
     * This method safely deletes records from the database with error
     * handling and logging. Supports both single ID deletion and
     * complex where condition deletion.
     * 
     * @param int|array $where Delete conditions:
     *   - int: Delete record with this ID
     *   - array: Complex where conditions for Medoo
     * @return bool True on success, false on failure
     * @throws Exception If table not set
     */
    public function delete($where)
    {
        if (!$this->table) {
            throw new \Exception('Model methods require a table to be set.');
        }

        // Convert single ID to array format for Medoo
        if (!is_array($where)) {
            $where = [
                'id' => $where
            ];
        }

        try {
            $this->database->client->delete($this->table, $where);
        } catch (\Exception $e) {
            // Log error for debugging but don't expose details to user
            error_log($e->getMessage());
            return false;
        }

        return true;
    }

    // =========================================================================
    // ADVANCED DELETION WITH DEPENDENCY CHECKING
    // =========================================================================

    /**
     * Performs safe deletion with dependency checking and cleanup
     * 
     * This method provides advanced deletion functionality that:
     * 1. Checks for dependent records that would prevent deletion
     * 2. Reports what dependencies exist if deletion is blocked
     * 3. Performs the deletion if safe to do so
     * 4. Cleans up specified associated records
     * 
     * This prevents orphaned records and maintains referential integrity
     * while providing helpful error messages to users.
     * 
     * @param array $guards Array of dependency checks to perform:
     *   - table: Table name to check for dependencies
     *   - where: Where conditions to check for existing records
     *   - customTitle: Optional custom name for error messages
     * @param int $id ID of the record to delete
     * @param array $associationsToDelete Associated records to clean up:
     *   - table: Table name containing associated records
     *   - where: Where conditions to identify records to delete
     * @return array Response array with success/error status and messages
     */
    public function guardDelete($guards, $id, $associationsToDelete = [])
    {
        /**
         * Initialize the standard response format
         * 
         * Provides consistent response structure for all guard deletion
         * operations with success/error flags and message arrays.
         */
        $res = [
            'success' => false,
            'error' => false,
            'message' => '',
            'errors' => [],
        ];

        /**
         * DEPENDENCY CHECK PHASE
         * 
         * Check each guard condition to determine if deletion is safe.
         * If any dependent records exist, mark as error and collect
         * the names of blocking dependencies for user feedback.
         */
        $canDelete = true;
        foreach ($guards as $guard) {
            $data = $this->database->client->select($guard['table'], '*', $guard['where']);
            if (count($data)) {
                $canDelete = false;
                $res['error'] = true;
                // Use custom title if provided, otherwise format table name
                $res['errors'][] = isset($guard['customTitle']) ?
                    $guard['customTitle'] :
                    str_replace('_', ' ', $guard['table']) . "s";
            }
        }

        /**
         * EARLY TERMINATION FOR BLOCKED DELETION
         * 
         * If dependency checks found blocking records, return error
         * response with details about what's preventing deletion.
         */
        if ($res['error']) {
            $res['message'] = "An error occurred: There is still information associated with this " . str_replace('_', ' ', $this->table);
            return $res;
        }

        /**
         * DELETION PHASE
         * 
         * If all dependency checks passed, proceed with deleting
         * the main record using the standard delete method.
         */
        $deleted = $this->delete($id);
        if (!$deleted) {
            $res['error'] = "A problem occurred during the deletion process. Please try again.";
            return $res;
        }

        /**
         * CLEANUP PHASE
         * 
         * Delete any specified associated records to maintain
         * database consistency and prevent orphaned data.
         */
        foreach ($associationsToDelete as $assoc) {
            $this->database->client->delete($assoc['table'], $assoc['where']);
        }

        $res['success'] = true;
        return $res;
    }

    // =========================================================================
    // DATA FILTERING AND SECURITY
    // =========================================================================

    /**
     * Removes sensitive fields from a single record
     * 
     * This method strips fields marked as hidden (like passwords, tokens,
     * etc.) from individual database records to prevent accidental exposure
     * of sensitive data in API responses or views.
     * 
     * @param array $data Single database record
     * @return array Record with hidden fields removed
     */
    private function stripHiddenFields($data)
    {
        if (is_array($this->hiddenFields)) {
            foreach ($this->hiddenFields as $field) {
                if (array_key_exists($field, $data)) {
                    unset($data[$field]);
                }
            }
        }

        return $data;
    }

    /**
     * Applies data filtering to query results based on model configuration
     * 
     * This method handles the filtering of sensitive data from query results.
     * It respects the includeHiddenFields option and applies hidden field
     * stripping to both single records and arrays of records.
     * 
     * Security Features:
     * - Automatically strips hidden fields unless explicitly requested
     * - Handles both single records and arrays of records
     * - Resets options after filtering to prevent option leakage
     * 
     * @param mixed $data Query result data (array, single record, or null)
     * @return mixed Filtered data with hidden fields removed
     */
    private function filterData($data)
    {
        // Return non-array data as-is (null, false, etc.)
        if (!is_array($data)) {
            return $data;
        }

        // Return empty arrays as-is
        if (count($data)) {
            return $data;
        }

        // Check if hidden fields should be included in this query
        if (isset($this->opts['includeHiddenFields'])) {
            if ($this->opts['includeHiddenFields'] === true) {
                $this->reset();
                return $data;
            }
        }

        // Handle arrays of records (multiple rows)
        if (count(array_filter($data, 'is_array')) > 0) {
            foreach ($data as $key => $item) {
                $data[$key] = $this->stripHiddenFields($item);
            }

            $this->reset();
            return $data;
        }

        // Handle single record
        $this->reset();
        return $this->stripHiddenFields($data);
    }

    /**
     * Resets model options after query execution
     * 
     * This method clears the options array to prevent options from
     * one query affecting subsequent queries. Called automatically
     * after data filtering to ensure clean state.
     * 
     * @return void
     */
    private function reset()
    {
        $this->opts = [];
    }
}
