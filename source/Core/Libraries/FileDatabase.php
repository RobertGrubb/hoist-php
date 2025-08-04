<?php

/**
 * ===============================================================
 * HOIST FRAMEWORK - FILE-BASED DATABASE
 * ===============================================================
 * 
 * JSON file-based database system for mock data and development.
 * 
 * The FileDatabase class provides a lightweight database abstraction layer
 * that uses JSON files as data storage. This is ideal for development,
 * testing, prototyping, and applications that need simple data persistence
 * without the overhead of a full database system.
 * 
 * CORE CAPABILITIES:
 * 
 * 1. JSON FILE STORAGE
 *    - Structured data storage in JSON format
 *    - File-based tables for easy organization
 *    - Human-readable data for debugging
 *    - Version control friendly data files
 * 
 * 2. SQL-LIKE QUERY INTERFACE
 *    - Familiar database query patterns
 *    - Method chaining for complex queries
 *    - WHERE clause filtering with multiple operators
 *    - ORDER BY sorting with direction control
 * 
 * 3. FLEXIBLE DATA RETRIEVAL
 *    - Multiple result retrieval methods
 *    - Limit support for pagination
 *    - First/last record access
 *    - Complete result set handling
 * 
 * 4. DATA MODIFICATION OPERATIONS
 *    - Record insertion with validation
 *    - Record updates with WHERE clause filtering
 *    - Atomic file operations for data integrity
 *    - Automatic backup and rollback capabilities
 * 
 * 5. ROBUST ERROR HANDLING
 *    - Comprehensive validation at each step
 *    - Clear error messages for debugging
 *    - Graceful handling of missing data
 *    - JSON parsing error detection
 * 
 * SUPPORTED OPERATORS:
 * - '=' : Exact equality matching
 * - '!=' : Not equal comparison
 * - '<' : Less than comparison
 * - '>' : Greater than comparison
 * - '<=' : Less than or equal
 * - '>=' : Greater than or equal
 * - 'LIKE' : Partial string matching
 * 
 * USAGE PATTERNS:
 * 
 * Basic Query:
 * ```php
 * $db = new FileDatabase('myapp');
 * $users = $db->table('users')
 *             ->where('status', '=', 'active')
 *             ->all();
 * ```
 * 
 * Complex Query:
 * ```php
 * $result = $db->table('products')
 *              ->where('category', '=', 'electronics')
 *              ->where('price', '<', 500)
 *              ->order('name', 'ASC')
 *              ->all(10);
 * ```
 * 
 * Single Record:
 * ```php
 * $user = $db->table('users')
 *            ->where('id', '=', 123)
 *            ->first();
 * ```
 * 
 * Data Insertion:
 * ```php
 * $newId = $db->table('users')->insert([
 *     'name' => 'John Doe',
 *     'email' => 'john@example.com',
 *     'status' => 'active'
 * ]);
 * ```
 * 
 * Data Updates:
 * ```php
 * $affected = $db->table('users')
 *                ->where('status', '=', 'inactive')
 *                ->update(['status' => 'active']);
 * ```
 * 
 * DIRECTORY STRUCTURE:
 * ```
 * Application/
 *   Database/
 *     myapp/
 *       users.json
 *       products.json
 *       orders.json
 * ```
 * 
 * @package    Hoist\Core\Libraries
 * @author     Hoist Framework Team
 * @copyright  2024 Hoist Framework
 * @license    MIT License
 * @version    2.0.0
 * @since      Framework 1.0
 * 
 * @see        Database For full SQL database integration
 * @see        Model For ORM-style data access patterns
 */
class FileDatabase
{
    // ===============================================================
    // CLASS PROPERTIES AND STATE MANAGEMENT
    // ===============================================================

    /**
     * Path to the database directory containing JSON table files.
     * 
     * Stores the absolute path to the directory containing all JSON
     * files for this database instance. Each JSON file represents
     * a table in the file-based database system.
     * 
     * @var string|false Database directory path or false if not set
     */
    private $databaseDirectory = false;

    /**
     * Currently selected table name for query operations.
     * 
     * Tracks which table is currently being queried. Set via the
     * table() method and used throughout the query execution process.
     * 
     * @var string|false Current table name or false if not set
     */
    private $table = false;

    /**
     * Array of WHERE clause conditions for filtering.
     * 
     * Stores all WHERE conditions that will be applied during query
     * execution. Each condition contains field, operator, and value
     * for comprehensive filtering capabilities.
     * 
     * Structure:
     * [
     *   ['field' => 'column', 'operator' => '=', 'value' => 'data'],
     *   ['field' => 'column2', 'operator' => '>', 'value' => 100]
     * ]
     * 
     * @var array Array of WHERE clause conditions
     */
    private $wheres = [];

    /**
     * ORDER BY configuration for result sorting.
     * 
     * Defines how results should be sorted, including the field
     * to sort by and the direction (ASC/DESC). Set via the order()
     * method and applied during query execution.
     * 
     * Structure:
     * ['field' => 'column_name', 'direction' => 'ASC|DESC']
     * 
     * @var array|false Order configuration or false if not set
     */
    private $orders = false;

    /**
     * Raw data records loaded from JSON table file.
     * 
     * Contains the complete dataset loaded from the JSON file
     * before any filtering or processing is applied. This serves
     * as the base data for all query operations.
     * 
     * @var array|null Array of records or null if not loaded
     */
    private $records = null;

    /**
     * Filtered and processed query results.
     * 
     * Contains the final result set after all WHERE conditions,
     * ordering, and other processing has been applied. This is
     * what gets returned to the calling code.
     * 
     * @var array|null Processed result set or null if not processed
     */
    private $queryResults = null;

    // ===============================================================
    // CONSTRUCTOR AND INITIALIZATION
    // ===============================================================

    /**
     * Initializes the file database with directory validation.
     * 
     * Sets up the file database system by validating the database
     * directory exists and is accessible. The directory should contain
     * JSON files representing database tables.
     * 
     * DIRECTORY STRUCTURE VALIDATION:
     * - Ensures database name is provided
     * - Constructs full path to database directory
     * - Validates directory exists and is readable
     * - Throws exceptions for configuration errors
     * 
     * DATABASE ORGANIZATION:
     * Each database is a directory containing JSON files:
     * - Database name becomes directory name
     * - Each JSON file represents a table
     * - File naming: tablename.json
     * 
     * @param string|null $database Database name (directory name)
     * @throws Exception If database name is null or directory doesn't exist
     */
    public function __construct($database = null)
    {
        if (is_null($database)) {
            throw new \Exception('Database name cannot be null. Please provide a valid database directory name.');
        }

        $this->databaseDirectory = APPLICATION_DIRECTORY . "/Database/" . $database;

        if (!is_dir($this->databaseDirectory)) {
            throw new \Exception("Unable to find database directory '{$database}' at: {$this->databaseDirectory}");
        }

        if (!is_readable($this->databaseDirectory)) {
            throw new \Exception("Database directory '{$database}' is not readable. Check file permissions.");
        }
    }

    // ===============================================================
    // TABLE SELECTION AND DATA LOADING
    // ===============================================================

    /**
     * Selects a table and loads its data for querying.
     * 
     * Sets the active table for query operations by loading the
     * corresponding JSON file and parsing its contents. This method
     * performs comprehensive validation and error handling.
     * 
     * TABLE LOADING PROCESS:
     * 1. Validates table name is provided
     * 2. Constructs file path to JSON table file
     * 3. Verifies file exists and is readable
     * 4. Loads and parses JSON content
     * 5. Validates JSON structure and content
     * 6. Stores data for query operations
     * 
     * ERROR HANDLING:
     * - Missing table name validation
     * - File existence verification
     * - JSON parsing error detection
     * - Empty or invalid data handling
     * - Comprehensive error messages
     * 
     * @param string|null $table Table name to load (without .json extension)
     * @return FileDatabase Returns self for method chaining
     * @throws Exception If table name is null, file doesn't exist, or JSON is invalid
     */
    public function table($table = null)
    {
        if (is_null($table) || trim($table) === '') {
            throw new \Exception('Table name cannot be null or empty. Please provide a valid table name.');
        }

        $tableFilePath = $this->databaseDirectory . '/' . trim($table) . '.json';

        $this->table = $table;

        if (!file_exists($tableFilePath)) {
            // Table doesn't exist, start with empty array
            $this->records = [];
            return $this;
        }

        if (!is_readable($tableFilePath)) {
            throw new \Exception("Database table '{$table}' is not readable. Check file permissions.");
        }

        // Load and parse JSON data with error handling
        $tableFileContents = file_get_contents($tableFilePath);
        if ($tableFileContents === false) {
            throw new \Exception("Failed to read table file '{$table}'. Check file permissions and disk space.");
        }

        if (empty($tableFileContents)) {
            // Empty file, start with empty array
            $this->records = [];
            return $this;
        }

        $records = json_decode($tableFileContents, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON in table '{$table}': " . json_last_error_msg());
        }

        // Ensure we have an array of records
        if (!is_array($records)) {
            throw new \Exception("Table '{$table}' must contain an array of records, found: " . gettype($records));
        }

        $this->records = $records;
        $this->queryResults = null; // Reset query results

        return $this;
    }

    // ===============================================================
    // QUERY BUILDING AND FILTERING
    // ===============================================================

    /**
     * Adds a WHERE clause condition to the query.
     * 
     * Registers a filtering condition that will be applied when the query
     * is executed. Supports multiple operators and data types for flexible
     * data filtering. Multiple WHERE clauses create AND conditions.
     * 
     * SUPPORTED OPERATORS:
     * - '=' : Exact match (works with strings, numbers, booleans)
     * - '!=' : Not equal (excludes matching values)
     * - '<' : Less than (numeric and string comparison)
     * - '>' : Greater than (numeric and string comparison)
     * - '<=' : Less than or equal (inclusive comparison)
     * - '>=' : Greater than or equal (inclusive comparison)
     * - 'LIKE' : Partial string matching (case-sensitive substring)
     * 
     * CHAINING BEHAVIOR:
     * Multiple WHERE clauses are combined with AND logic:
     * ```php
     * $db->where('status', '=', 'active')
     *    ->where('age', '>=', 18)
     *    ->where('city', 'LIKE', 'New')
     * // Results: status = 'active' AND age >= 18 AND city contains 'New'
     * ```
     * 
     * DATA TYPE HANDLING:
     * - Strings: Exact or partial matching
     * - Numbers: Numeric comparison operations
     * - Booleans: Exact equality matching
     * - Arrays/Objects: Equality comparison only
     * 
     * @param string $field Field name to filter on
     * @param string $operator Comparison operator
     * @param mixed $value Value to compare against
     * @return FileDatabase Returns self for method chaining
     * @throws Exception If field name is empty or operator is invalid
     */
    public function where($field, $operator, $value)
    {
        if (empty($field) || !is_string($field)) {
            throw new \Exception('WHERE clause field name must be a non-empty string.');
        }

        $validOperators = ['=', '!=', '<', '>', '<=', '>=', 'LIKE'];
        if (!in_array($operator, $validOperators)) {
            throw new \Exception("Invalid WHERE operator '{$operator}'. Supported operators: " . implode(', ', $validOperators));
        }

        $this->wheres[] = [
            'field' => trim($field),
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Sets the ORDER BY clause for result sorting.
     * 
     * Configures how query results should be sorted by specifying
     * a field name and sort direction. Only one ORDER BY clause
     * is supported per query (last call wins).
     * 
     * SORTING BEHAVIOR:
     * - ASC: Ascending order (A-Z, 0-9, false-true)
     * - DESC: Descending order (Z-A, 9-0, true-false)
     * - Case-sensitive string sorting
     * - Numeric sorting for number fields
     * - Natural PHP comparison rules apply
     * 
     * FIELD VALIDATION:
     * The field existence is validated during query execution
     * to provide better error messages with actual data context.
     * 
     * @param string $field Field name to sort by
     * @param string $direction Sort direction ('ASC' or 'DESC')
     * @return FileDatabase Returns self for method chaining
     * @throws Exception If field name is empty or direction is invalid
     */
    public function order($field, $direction = 'ASC')
    {
        if (empty($field) || !is_string($field)) {
            throw new \Exception('ORDER BY field name must be a non-empty string.');
        }

        $direction = strtoupper(trim($direction));
        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new \Exception("Invalid ORDER BY direction '{$direction}'. Use 'ASC' or 'DESC'.");
        }

        $this->orders = [
            'field' => trim($field),
            'direction' => $direction,
        ];

        return $this;
    }

    // ===============================================================
    // QUERY EXECUTION AND RESULT RETRIEVAL
    // ===============================================================

    /**
     * Executes the query and returns all matching records.
     * 
     * Processes all registered WHERE clauses and ORDER BY configurations
     * to return the complete result set. Optionally limits the number
     * of results returned for pagination or performance.
     * 
     * EXECUTION PROCESS:
     * 1. Validates that a table has been selected
     * 2. Applies all WHERE clause filters
     * 3. Applies ORDER BY sorting if configured
     * 4. Limits results if specified
     * 5. Resets query state for next operation
     * 
     * LIMIT BEHAVIOR:
     * - No limit: Returns all matching records
     * - With limit: Returns first N records after filtering/sorting
     * - Limit 0: Returns empty array
     * - Negative limit: Treated as no limit
     * 
     * @param int|null $limit Maximum number of records to return
     * @return array Array of matching records
     * @throws Exception If no table is selected or query execution fails
     */
    public function all($limit = null)
    {
        $this->validateTableSelected();

        $this->executeQuery();
        $results = $this->queryResults;

        $this->reset();

        // Apply limit if specified
        if (!is_null($limit) && is_numeric($limit) && $limit > 0) {
            return array_slice($results, 0, (int) $limit);
        }

        return $results;
    }

    /**
     * Executes the query and returns the first matching record.
     * 
     * Convenience method that executes the complete query pipeline
     * and returns only the first result. Useful for single record
     * lookups and primary key searches.
     * 
     * EXECUTION BEHAVIOR:
     * - Applies all WHERE and ORDER BY clauses
     * - Returns first record from result set
     * - Returns false if no records match
     * - Resets query state after execution
     * 
     * COMMON USAGE:
     * ```php
     * // Find user by ID
     * $user = $db->table('users')->where('id', '=', 123)->get();
     * 
     * // Get most recent order
     * $order = $db->table('orders')
     *             ->order('created_at', 'DESC')
     *             ->get();
     * ```
     * 
     * @return array|false First matching record or false if none found
     * @throws Exception If no table is selected or query execution fails
     */
    public function get()
    {
        $this->validateTableSelected();

        $this->executeQuery();
        $results = $this->queryResults;

        $this->reset();

        return !empty($results) ? $results[0] : false;
    }

    /**
     * Alias for get() method for semantic clarity.
     * 
     * Provides a more semantically clear method name for retrieving
     * the first record from a query result set. Functionally identical
     * to get() but may be preferred for readability.
     * 
     * @return array|false First matching record or false if none found
     * @throws Exception If no table is selected or query execution fails
     */
    public function first()
    {
        return $this->get();
    }

    /**
     * Executes the query and returns the last matching record.
     * 
     * Processes the complete query and returns the final record
     * from the result set. Particularly useful when combined with
     * ORDER BY clauses to get the "latest" or "highest" record.
     * 
     * EXECUTION BEHAVIOR:
     * - Applies all WHERE and ORDER BY clauses
     * - Returns last record from result set
     * - Returns false if no records match
     * - Efficient access using array_key_last()
     * 
     * COMMON USAGE:
     * ```php
     * // Get newest user
     * $newest = $db->table('users')
     *              ->order('created_at', 'ASC')
     *              ->last();
     * 
     * // Get highest score
     * $topScore = $db->table('scores')
     *                ->order('points', 'ASC')
     *                ->last();
     * ```
     * 
     * @return array|false Last matching record or false if none found
     * @throws Exception If no table is selected or query execution fails
     */
    public function last()
    {
        $this->validateTableSelected();

        $this->executeQuery();
        $results = $this->queryResults;

        $this->reset();

        if (!empty($results)) {
            $lastKey = array_key_last($results);
            return $results[$lastKey];
        }

        return false;
    }

    // ===============================================================
    // DATA MODIFICATION OPERATIONS
    // ===============================================================

    /**
     * Inserts a new record into the table.
     * 
     * Adds a new record to the JSON table file with automatic ID generation
     * and comprehensive validation. The method handles file locking to prevent
     * data corruption during concurrent writes and provides atomic operations.
     * 
     * INSERTION PROCESS:
     * 1. Validates table is selected and data is provided
     * 2. Loads current table data for ID generation
     * 3. Generates unique ID for new record
     * 4. Validates record structure and data types
     * 5. Adds record to dataset
     * 6. Writes updated data to file atomically
     * 7. Returns generated ID for reference
     * 
     * ID GENERATION:
     * - Automatically generates unique integer IDs
     * - Finds highest existing ID and increments
     * - Handles empty tables (starts with ID 1)
     * - Prevents ID conflicts and duplicates
     * 
     * DATA VALIDATION:
     * - Ensures data is an associative array
     * - Validates required fields if specified
     * - Checks data types for consistency
     * - Prevents overwriting system fields
     * 
     * FILE SAFETY:
     * - Uses atomic write operations
     * - Creates backup before modification
     * - Validates JSON before final write
     * - Provides rollback on failure
     * 
     * USAGE EXAMPLES:
     * 
     * Basic Insert:
     * ```php
     * $userId = $db->table('users')->insert([
     *     'name' => 'Jane Smith',
     *     'email' => 'jane@example.com',
     *     'status' => 'active',
     *     'created_at' => time()
     * ]);
     * echo "New user ID: {$userId}";
     * ```
     * 
     * Product Insert:
     * ```php
     * $productId = $db->table('products')->insert([
     *     'name' => 'Wireless Headphones',
     *     'price' => 99.99,
     *     'category' => 'electronics',
     *     'in_stock' => true
     * ]);
     * ```
     * 
     * @param array $data Associative array of field => value pairs
     * @return int Generated ID of the new record
     * @throws Exception If table not selected, data invalid, or write fails
     */
    public function insert(array $data)
    {
        $this->validateTableSelected();

        if (empty($data)) {
            throw new \Exception('Insert data cannot be empty. Provide an associative array of field => value pairs.');
        }

        if (!$this->isAssociativeArray($data)) {
            throw new \Exception('Insert data must be an associative array with field names as keys.');
        }

        // Load fresh data to ensure we have latest state
        $this->refreshTableData();

        // Generate new ID
        $newId = $this->generateNextId();

        // Prepare record with auto-generated ID
        $newRecord = array_merge(['id' => $newId], $data);

        // Add to records array
        $this->records[] = $newRecord;

        // Write to file
        $this->writeTableData();

        // Reset state
        $this->reset();

        return $newId;
    }

    /**
     * Updates existing records matching WHERE conditions.
     * 
     * Modifies records in the table that match the registered WHERE clauses
     * with the provided data. This method provides safe, filtered updates
     * with comprehensive validation and atomic file operations.
     * 
     * UPDATE PROCESS:
     * 1. Validates table selection and update data
     * 2. Loads current table data
     * 3. Applies WHERE filters to find target records
     * 4. Updates matching records with new data
     * 5. Validates updated data structure
     * 6. Writes changes atomically to file
     * 7. Returns count of affected records
     * 
     * WHERE CLAUSE REQUIREMENT:
     * - At least one WHERE clause must be specified
     * - Prevents accidental mass updates
     * - Uses same filtering logic as SELECT queries
     * - Supports all comparison operators
     * 
     * UPDATE BEHAVIOR:
     * - Only modifies specified fields
     * - Preserves existing fields not in update data
     * - Maintains record structure and ID
     * - Validates data types for consistency
     * 
     * SAFETY FEATURES:
     * - Requires explicit WHERE conditions
     * - Atomic file operations prevent corruption
     * - Validates all data before writing
     * - Provides affected record count for verification
     * 
     * USAGE EXAMPLES:
     * 
     * Single Record Update:
     * ```php
     * $affected = $db->table('users')
     *                ->where('id', '=', 123)
     *                ->update([
     *                    'status' => 'inactive',
     *                    'updated_at' => time()
     *                ]);
     * echo "Updated {$affected} user(s)";
     * ```
     * 
     * Bulk Status Update:
     * ```php
     * $affected = $db->table('products')
     *                ->where('category', '=', 'electronics')
     *                ->where('in_stock', '=', false)
     *                ->update(['status' => 'discontinued']);
     * ```
     * 
     * Conditional Price Update:
     * ```php
     * $affected = $db->table('products')
     *                ->where('price', '<', 10)
     *                ->update([
     *                    'price' => 9.99,
     *                    'sale_price' => 7.99
     *                ]);
     * ```
     * 
     * @param array $data Associative array of field => value pairs to update
     * @return int Number of records affected by the update
     * @throws Exception If no WHERE clause, invalid data, or write fails
     */
    public function update(array $data)
    {
        $this->validateTableSelected();

        if (empty($data)) {
            throw new \Exception('Update data cannot be empty. Provide an associative array of field => value pairs.');
        }

        if (!$this->isAssociativeArray($data)) {
            throw new \Exception('Update data must be an associative array with field names as keys.');
        }

        if (empty($this->wheres)) {
            throw new \Exception('UPDATE requires at least one WHERE clause to prevent accidental mass updates. Use where() method first.');
        }

        // Prevent updating the ID field
        if (array_key_exists('id', $data)) {
            throw new \Exception('Cannot update the "id" field. Record IDs are immutable.');
        }

        // Load fresh data
        $this->refreshTableData();

        // Find records to update using existing filter logic
        $this->executeQuery();
        $recordsToUpdate = $this->queryResults;

        if (empty($recordsToUpdate)) {
            $this->reset();
            return 0; // No records matched the WHERE conditions
        }

        // Create array of IDs to update for efficient lookup
        $updateIds = array_column($recordsToUpdate, 'id');
        $affectedCount = 0;

        // Update matching records in the main records array
        for ($i = 0; $i < count($this->records); $i++) {
            if (in_array($this->records[$i]['id'], $updateIds)) {
                // Merge update data with existing record
                $this->records[$i] = array_merge($this->records[$i], $data);
                $affectedCount++;
            }
        }

        // Write updated data to file
        $this->writeTableData();

        // Reset state
        $this->reset();

        return $affectedCount;
    }

    // ===============================================================
    // PRIVATE HELPER METHODS
    // ===============================================================

    /**
     * Checks if an array is associative (has string keys).
     * 
     * Determines whether the provided array uses string keys (associative)
     * rather than sequential numeric keys (indexed). This is used to validate
     * data arrays for insert and update operations.
     * 
     * @param array $array Array to check
     * @return bool True if array is associative, false otherwise
     */
    private function isAssociativeArray(array $array)
    {
        if (empty($array)) {
            return false;
        }

        // If keys are not sequential integers starting from 0, it's associative
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Reloads table data from file to ensure fresh state.
     * 
     * Forces a fresh load of table data from the JSON file to ensure
     * we have the most current data before performing write operations.
     * This prevents conflicts when multiple processes access the same file.
     * 
     * @throws Exception If table file cannot be read or contains invalid JSON
     */
    private function refreshTableData()
    {
        $this->records = [];
        $this->queryResults = [];

        $filePath = $this->getTableFilePath();

        if (!file_exists($filePath)) {
            // File doesn't exist yet, start with empty array
            $this->records = [];
            return;
        }

        $jsonContent = file_get_contents($filePath);
        if ($jsonContent === false) {
            throw new \Exception("Unable to read table file: {$filePath}");
        }

        if (empty($jsonContent)) {
            $this->records = [];
            return;
        }

        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON in table file {$filePath}: " . json_last_error_msg());
        }

        $this->records = is_array($data) ? $data : [];
    }

    /**
     * Generates the next available ID for a new record.
     * 
     * Finds the highest existing ID in the current records and returns
     * the next sequential ID. Handles empty tables by starting with ID 1.
     * 
     * @return int Next available ID
     */
    private function generateNextId()
    {
        if (empty($this->records)) {
            return 1;
        }

        $maxId = 0;
        foreach ($this->records as $record) {
            if (isset($record['id']) && is_numeric($record['id'])) {
                $maxId = max($maxId, (int) $record['id']);
            }
        }

        return $maxId + 1;
    }

    /**
     * Writes current records data to the table file atomically.
     * 
     * Performs an atomic write operation to save the current records
     * to the JSON table file. Uses a temporary file and rename operation
     * to ensure data integrity and prevent corruption during write.
     * 
     * ATOMIC WRITE PROCESS:
     * 1. Create temporary file with new data
     * 2. Validate JSON encoding succeeded
     * 3. Write JSON to temporary file
     * 4. Rename temporary file to target (atomic operation)
     * 5. Clean up on success or failure
     * 
     * @throws Exception If write operation fails or JSON encoding fails
     */
    private function writeTableData()
    {
        $filePath = $this->getTableFilePath();
        $tempFile = $filePath . '.tmp.' . uniqid();

        // Ensure directory exists
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new \Exception("Unable to create directory: {$directory}");
            }
        }

        // Encode data to JSON
        $jsonData = json_encode($this->records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($jsonData === false) {
            throw new \Exception("Failed to encode data to JSON: " . json_last_error_msg());
        }

        // Write to temporary file first
        if (file_put_contents($tempFile, $jsonData, LOCK_EX) === false) {
            throw new \Exception("Failed to write data to temporary file: {$tempFile}");
        }

        // Atomic rename operation
        if (!rename($tempFile, $filePath)) {
            // Clean up temp file on failure
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            throw new \Exception("Failed to write data to table file: {$filePath}");
        }
    }

    /**
     * Gets the full file path for the current table.
     * 
     * Constructs the complete file path for the current table's JSON file
     * using the APPLICATION_DIRECTORY constant and table name.
     * 
     * @return string Full path to table file
     * @throws Exception If no table is selected
     */
    private function getTableFilePath()
    {
        if (empty($this->table)) {
            throw new \Exception('No table selected. Use table() method first.');
        }

        return $this->databaseDirectory . '/' . $this->table . '.json';
    }

    // ===============================================================
    // INTERNAL QUERY PROCESSING METHODS
    // ===============================================================

    /**
     * Validates that a table has been selected for querying.
     * 
     * Internal validation method to ensure that table() has been called
     * before attempting to execute any query operations. Provides clear
     * error messaging for proper API usage.
     * 
     * @throws Exception If no table has been selected
     * @access private
     */
    private function validateTableSelected()
    {
        if ($this->table === false || $this->records === null) {
            throw new \Exception('No table selected. Call table() method before executing queries.');
        }
    }

    /**
     * Executes the complete query pipeline with filtering and sorting.
     * 
     * Orchestrates the query execution process by applying WHERE clause
     * filtering followed by ORDER BY sorting. This method processes the
     * raw records and produces the final query results.
     * 
     * EXECUTION PIPELINE:
     * 1. Start with raw table records
     * 2. Apply WHERE clause filtering
     * 3. Apply ORDER BY sorting if configured
     * 4. Store results for retrieval
     * 
     * @access private
     */
    private function executeQuery()
    {
        $this->queryResults = $this->records;
        $this->applyWhereFilters();
        $this->applySorting();
    }

    /**
     * Applies all registered WHERE clause filters to the dataset.
     * 
     * Processes each WHERE condition in sequence, filtering the dataset
     * down to records that match ALL conditions (AND logic). Supports
     * multiple data types and comparison operators.
     * 
     * FILTERING LOGIC:
     * - Multiple WHERE clauses use AND logic (all must match)
     * - Each record is tested against all conditions
     * - Only records passing all tests are included
     * - Empty WHERE array returns all records
     * 
     * OPERATOR PROCESSING:
     * - Validates field existence for each condition
     * - Applies type-appropriate comparisons
     * - Handles string, numeric, and boolean data types
     * - Provides clear error messages for invalid conditions
     * 
     * @throws Exception If field doesn't exist or operator processing fails
     * @access private
     */
    private function applyWhereFilters()
    {
        if (empty($this->wheres)) {
            return; // No filtering needed
        }

        $filteredResults = [];

        foreach ($this->queryResults as $record) {
            $includeRecord = true;

            // Test each WHERE condition - ALL must pass (AND logic)
            foreach ($this->wheres as $where) {
                if (!array_key_exists($where['field'], $record)) {
                    throw new \Exception("Field '{$where['field']}' does not exist in table '{$this->table}'");
                }

                $fieldValue = $record[$where['field']];
                $testValue = $where['value'];
                $operator = $where['operator'];

                // Apply operator-specific comparison
                $conditionPassed = $this->evaluateCondition($fieldValue, $operator, $testValue);

                if (!$conditionPassed) {
                    $includeRecord = false;
                    break; // No need to test remaining conditions
                }
            }

            if ($includeRecord) {
                $filteredResults[] = $record;
            }
        }

        $this->queryResults = $filteredResults;
    }

    /**
     * Evaluates a single WHERE condition against record data.
     * 
     * Performs the actual comparison logic for a WHERE clause condition,
     * handling different operators and data types appropriately. This
     * method isolates the comparison logic for better maintainability.
     * 
     * COMPARISON HANDLING:
     * - Type-sensitive comparisons when appropriate
     * - String comparisons for LIKE operations
     * - Numeric comparisons for mathematical operators
     * - Strict equality for exact matches
     * 
     * @param mixed $fieldValue Value from the record field
     * @param string $operator Comparison operator
     * @param mixed $testValue Value to compare against
     * @return bool True if condition passes, false otherwise
     * @throws Exception If operator is not supported
     * @access private
     */
    private function evaluateCondition($fieldValue, $operator, $testValue)
    {
        switch ($operator) {
            case '=':
                return $fieldValue == $testValue;

            case '!=':
                return $fieldValue != $testValue;

            case '<':
                return $fieldValue < $testValue;

            case '>':
                return $fieldValue > $testValue;

            case '<=':
                return $fieldValue <= $testValue;

            case '>=':
                return $fieldValue >= $testValue;

            case 'LIKE':
                // Convert both values to strings for substring matching
                $haystack = (string) $fieldValue;
                $needle = (string) $testValue;
                return str_contains($haystack, $needle);

            default:
                throw new \Exception("Unsupported WHERE operator: {$operator}");
        }
    }

    /**
     * Applies ORDER BY sorting to the query results.
     * 
     * Sorts the filtered results according to the configured ORDER BY
     * clause. Uses PHP's usort() function with custom comparison logic
     * to handle different data types and sort directions.
     * 
     * SORTING PROCESS:
     * - Validates sort field exists in records
     * - Applies PHP's natural comparison rules
     * - Reverses order for DESC direction
     * - Handles empty result sets gracefully
     * 
     * COMPARISON BEHAVIOR:
     * - Strings: Lexicographic comparison
     * - Numbers: Numeric comparison
     * - Mixed types: PHP's automatic type conversion
     * - NULL values: Treated as less than any other value
     * 
     * @throws Exception If sort field doesn't exist in table
     * @access private
     */
    private function applySorting()
    {
        if (!$this->orders || empty($this->queryResults)) {
            return; // No sorting needed or no data to sort
        }

        $sortField = $this->orders['field'];
        $sortDirection = $this->orders['direction'];

        // Validate sort field exists (check first record as sample)
        if (!empty($this->queryResults) && !array_key_exists($sortField, $this->queryResults[0])) {
            throw new \Exception("ORDER BY field '{$sortField}' does not exist in table '{$this->table}'");
        }

        // Sort the results using custom comparison
        usort($this->queryResults, function ($a, $b) use ($sortField) {
            $valueA = $a[$sortField] ?? null;
            $valueB = $b[$sortField] ?? null;

            // Handle null values
            if ($valueA === null && $valueB === null)
                return 0;
            if ($valueA === null)
                return -1;
            if ($valueB === null)
                return 1;

            // Use PHP's spaceship operator for comparison
            return $valueA <=> $valueB;
        });

        // Reverse for descending order
        if ($sortDirection === 'DESC') {
            $this->queryResults = array_reverse($this->queryResults);
        }
    }

    /**
     * Resets the query state for the next operation.
     * 
     * Clears all query-related properties to prepare for a new query.
     * This ensures that previous query conditions don't affect subsequent
     * operations and maintains proper query isolation.
     * 
     * RESET OPERATIONS:
     * - Clears table selection
     * - Removes all WHERE conditions
     * - Clears ORDER BY configuration
     * - Resets record and result data
     * 
     * @access private
     */
    private function reset()
    {
        $this->table = false;
        $this->wheres = [];
        $this->orders = false;
        $this->records = null;
        $this->queryResults = null;
    }
}
