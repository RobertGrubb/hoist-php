<?php

use Medoo\Medoo;

/**
 * ===============================================================
 * HOIST FRAMEWORK - DATABASE ADAPTER
 * ===============================================================
 * 
 * 🚀 THE SEAMLESS MIGRATION SOLUTION!
 * 
 * This adapter provides a unified interface that maintains FileDatabase's
 * beloved chainable syntax while automatically routing queries to the
 * appropriate backend (FileDatabase for development, MySQL for production).
 * 
 * This enables TRUE seamless migration:
 * - Write code once with FileDatabase syntax
 * - Same code works with MySQL after migration
 * - Zero code changes needed during scaling
 * - Automatic backend detection and routing
 * 
 * CORE CAPABILITIES:
 * - Unified chainable query interface
 * - Automatic backend detection (FileDatabase vs MySQL)
 * - Query translation from FileDatabase syntax to Medoo
 * - Transparent fallback handling
 * - Performance optimization for both backends
 * 
 * USAGE PATTERNS:
 * 
 * Development (FileDatabase):
 * ```php
 * $users = $this->instance->db->table('users')
 *                            ->where('status', '=', 'active')
 *                            ->order('name', 'ASC')
 *                            ->all(10);
 * ```
 * 
 * Production (MySQL) - SAME CODE:
 * ```php
 * $users = $this->instance->db->table('users')
 *                            ->where('status', '=', 'active')
 *                            ->order('name', 'ASC')
 *                            ->all(10);
 * ```
 * 
 * DEVELOPER BENEFITS:
 * - No syntax learning curve when scaling
 * - Consistent API across environments
 * - Automatic optimization based on backend
 * - Gradual migration support
 * - Framework-native experience
 * 
 * @package HoistPHP\Core\Libraries
 * @author  Hoist PHP Framework Team
 * @version 1.0.0
 */

class DatabaseAdapter
{
    // ===============================================================
    // CLASS PROPERTIES
    // ===============================================================

    /**
     * Framework instance for service access.
     * 
     * @var Instance Framework service container
     */
    private $instance;

    /**
     * Backend detection flag.
     * 
     * @var bool True if MySQL is available, false for FileDatabase only
     */
    private $useMySQL;

    // ===============================================================
    // CONSTRUCTOR
    // ===============================================================

    /**
     * Initializes the database adapter with backend detection.
     * 
     * @param Instance $instance Framework service container
     */
    public function __construct($instance)
    {
        $this->instance = $instance;
        $this->useMySQL = $this->instance->database->hasMySQL();
    }

    // ===============================================================
    // UNIFIED TABLE INTERFACE
    // ===============================================================

    /**
     * Creates a query builder for the specified table.
     * 
     * Returns either a MySQL query builder or FileDatabase query builder
     * based on backend availability, maintaining identical API.
     * 
     * @param string $tableName Name of the table to query
     * @return MySQLQueryBuilder|FileDatabase Query builder instance
     */
    public function table($tableName)
    {
        if ($this->useMySQL) {
            return new MySQLQueryBuilder($this->instance->database->client, $tableName);
        }

        return $this->instance->fileDatabase->table($tableName);
    }

    // ===============================================================
    // BACKEND INFORMATION
    // ===============================================================

    /**
     * Returns the current backend type.
     * 
     * @return string 'mysql' or 'filedb'
     */
    public function getBackend()
    {
        return $this->useMySQL ? 'mysql' : 'filedb';
    }

    /**
     * Checks if MySQL backend is active.
     * 
     * @return bool True if using MySQL, false if using FileDatabase
     */
    public function isMySQL()
    {
        return $this->useMySQL;
    }

    /**
     * Checks if FileDatabase backend is active.
     * 
     * @return bool True if using FileDatabase, false if using MySQL
     */
    public function isFileDatabase()
    {
        return !$this->useMySQL;
    }
}

/**
 * ===============================================================
 * MYSQL QUERY BUILDER
 * ===============================================================
 * 
 * Translates FileDatabase chainable syntax to Medoo operations.
 * Maintains identical API while leveraging MySQL's performance
 * and relational capabilities.
 */

class MySQLQueryBuilder
{
    // ===============================================================
    // CLASS PROPERTIES
    // ===============================================================

    /**
     * Medoo database client.
     * 
     * @var Medoo Active MySQL connection
     */
    private $medoo;

    /**
     * Target table name.
     * 
     * @var string Table to operate on
     */
    private $tableName;

    /**
     * WHERE conditions array.
     * 
     * @var array Accumulated WHERE conditions
     */
    private $whereConditions = [];

    /**
     * ORDER BY configuration.
     * 
     * @var array|null Order configuration
     */
    private $orderConfig = null;

    // ===============================================================
    // CONSTRUCTOR
    // ===============================================================

    /**
     * Initializes MySQL query builder.
     * 
     * @param Medoo $medoo Active Medoo connection
     * @param string $tableName Target table name
     */
    public function __construct($medoo, $tableName)
    {
        $this->medoo = $medoo;
        $this->tableName = $tableName;
    }

    // ===============================================================
    // QUERY BUILDING METHODS
    // ===============================================================

    /**
     * Adds WHERE clause condition.
     * 
     * Translates FileDatabase WHERE syntax to Medoo format.
     * 
     * @param string $field Field name
     * @param string $operator Comparison operator
     * @param mixed $value Comparison value
     * @return MySQLQueryBuilder Self for chaining
     */
    public function where($field, $operator, $value)
    {
        // Convert FileDatabase operators to Medoo format
        switch ($operator) {
            case '=':
                $this->whereConditions[$field] = $value;
                break;
            case '!=':
                $this->whereConditions[$field . '[!]'] = $value;
                break;
            case '>':
                $this->whereConditions[$field . '[>]'] = $value;
                break;
            case '>=':
                $this->whereConditions[$field . '[>=]'] = $value;
                break;
            case '<':
                $this->whereConditions[$field . '[<]'] = $value;
                break;
            case '<=':
                $this->whereConditions[$field . '[<=]'] = $value;
                break;
            case 'LIKE':
                $this->whereConditions[$field . '[~]'] = $value;
                break;
            default:
                // Fallback to equality for unknown operators
                $this->whereConditions[$field] = $value;
        }

        return $this;
    }

    /**
     * Sets ORDER BY clause.
     * 
     * @param string $field Field to order by
     * @param string $direction Order direction (ASC/DESC)
     * @return MySQLQueryBuilder Self for chaining
     */
    public function order($field, $direction = 'ASC')
    {
        $this->orderConfig = [$field => strtoupper($direction)];
        return $this;
    }

    // ===============================================================
    // QUERY EXECUTION METHODS
    // ===============================================================

    /**
     * Executes query and returns all results.
     * 
     * @param int|null $limit Maximum number of results
     * @return array Query results
     */
    public function all($limit = null)
    {
        $conditions = $this->buildConditions();

        // Add LIMIT if specified
        if ($limit !== null && is_numeric($limit) && $limit > 0) {
            $conditions['LIMIT'] = (int) $limit;
        }

        return $this->medoo->select($this->tableName, '*', $conditions);
    }

    /**
     * Executes query and returns first result.
     * 
     * @return array|false First result or false if not found
     */
    public function get()
    {
        $conditions = $this->buildConditions();
        $result = $this->medoo->get($this->tableName, '*', $conditions);
        return $result ?: false;
    }

    /**
     * Alias for get() method.
     * 
     * @return array|false First result or false if not found
     */
    public function first()
    {
        return $this->get();
    }

    /**
     * Executes query and returns last result.
     * 
     * @return array|false Last result or false if not found
     */
    public function last()
    {
        $conditions = $this->buildConditions();

        // Reverse the order for last() operation
        if ($this->orderConfig) {
            $field = array_keys($this->orderConfig)[0];
            $direction = $this->orderConfig[$field] === 'ASC' ? 'DESC' : 'ASC';
            $conditions['ORDER'] = [$field => $direction];
        }

        $result = $this->medoo->get($this->tableName, '*', $conditions);
        return $result ?: false;
    }

    /**
     * Inserts new record.
     * 
     * @param array $data Data to insert
     * @return int|false Insert ID or false on failure
     */
    public function insert(array $data)
    {
        // Process values for MySQL compatibility
        $processedData = $this->processValuesForMySQL($data);

        try {
            $this->medoo->insert($this->tableName, $processedData);
            $id = $this->medoo->id();
            return $id ? (int) $id : false;
        } catch (Exception $e) {
            error_log("MySQL insert error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates existing records.
     * 
     * @param array $data Data to update
     * @return int Number of affected rows
     */
    public function update(array $data)
    {
        if (empty($this->whereConditions)) {
            throw new Exception('UPDATE requires at least one WHERE clause to prevent accidental mass updates.');
        }

        // Process values for MySQL compatibility
        $processedData = $this->processValuesForMySQL($data);
        $conditions = $this->buildConditions();

        try {
            $statement = $this->medoo->update($this->tableName, $processedData, $conditions);
            return $statement->rowCount();
        } catch (Exception $e) {
            error_log("MySQL update error: " . $e->getMessage());
            return 0;
        }
    }

    // ===============================================================
    // PRIVATE HELPER METHODS
    // ===============================================================

    /**
     * Builds Medoo-compatible conditions array.
     * 
     * @return array Medoo conditions
     */
    private function buildConditions()
    {
        $conditions = [];

        // Add WHERE conditions
        if (!empty($this->whereConditions)) {
            if (count($this->whereConditions) > 1) {
                $conditions['AND'] = $this->whereConditions;
            } else {
                $conditions = array_merge($conditions, $this->whereConditions);
            }
        }

        // Add ORDER BY
        if ($this->orderConfig) {
            $conditions['ORDER'] = $this->orderConfig;
        }

        return $conditions;
    }

    /**
     * Processes values for MySQL compatibility.
     * 
     * Handles arrays, objects, and other data types that need
     * special processing for MySQL storage.
     * 
     * @param array $data Raw data array
     * @return array Processed data array
     */
    private function processValuesForMySQL(array $data)
    {
        $processed = [];

        foreach ($data as $key => $value) {
            // Handle arrays and objects - convert to JSON
            if (is_array($value) || is_object($value)) {
                $processed[$key] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            // Handle booleans - convert to integers
            elseif (is_bool($value)) {
                $processed[$key] = $value ? 1 : 0;
            }
            // Handle null values
            elseif (is_null($value)) {
                $processed[$key] = null;
            }
            // Everything else as-is
            else {
                $processed[$key] = $value;
            }
        }

        return $processed;
    }
}
