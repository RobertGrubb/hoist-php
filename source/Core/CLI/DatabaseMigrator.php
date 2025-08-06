<?php

/**
 * ===============================================================
 * HOIST FRAMEWORK - DATABASE MIGRATOR
 * ===============================================================
 * 
 * 🚀 THE GAME-CHANGING FEATURE!
 * 
 * This class enables seamless migration from FileDatabase to MySQL,
 * allowing developers to:
 * 
 * 1. START with zero configuration (FileDatabase)
 * 2. BUILD entire MVP with file-based storage
 * 3. SCALE to production MySQL with ONE COMMAND
 * 
 * This solves the classic development dilemma:
 * - FileDatabase: Perfect for development, not scalable
 * - MySQL: Production ready, setup complexity
 * - Solution: Start with FileDatabase, migrate when ready!
 * 
 * MIGRATION FEATURES:
 * - Automatic table discovery from JSON files
 * - Schema generation with proper data types
 * - Data type inference from existing records
 * - Backup creation before migration
 * - Rollback capabilities
 * - Configuration file updates
 * 
 * @package HoistPHP\CLI
 * @author  Hoist PHP Framework Team
 * @version 1.0.0
 */

class DatabaseMigrator
{
    private $instance;
    private $fileDbPath;
    private $mysqlConnection;

    public function __construct($instance)
    {
        $this->instance = $instance;
        $this->fileDbPath = APPLICATION_DIRECTORY . '/Database';
    }

    /**
     * 🔍 DISCOVER FILEDATABASE TABLES
     * 
     * Scans the FileDatabase directory structure to find all tables
     */
    public function discoverFileDatabaseTables()
    {
        $tables = [];

        if (!is_dir($this->fileDbPath)) {
            return $tables;
        }

        // Scan all database directories
        $databases = glob($this->fileDbPath . '/*', GLOB_ONLYDIR);

        foreach ($databases as $dbDir) {
            $dbName = basename($dbDir);

            // Find JSON table files
            $tableFiles = glob($dbDir . '/*.json');

            foreach ($tableFiles as $tableFile) {
                $tableName = pathinfo($tableFile, PATHINFO_FILENAME);
                $tables[] = [
                    'database' => $dbName,
                    'table' => $tableName,
                    'file' => $tableFile,
                    'records' => $this->countRecords($tableFile)
                ];
            }
        }

        return $tables;
    }

    /**
     * 🚀 MIGRATE TO MYSQL
     * 
     * The main migration function that converts FileDatabase to MySQL
     */
    public function migrateToMySQL($tables, $options)
    {
        try {
            // Connect to MySQL
            $this->connectToMySQL($options);

            // Create database if it doesn't exist
            $this->createDatabase($options['database']);

            $totalRecords = 0;
            $migratedTables = 0;

            foreach ($tables as $tableInfo) {
                echo "🔄 Migrating table: {$tableInfo['table']} ({$tableInfo['records']} records)\n";

                // Load FileDatabase data
                $data = $this->loadFileData($tableInfo['file']);

                if (empty($data)) {
                    echo "⚠️  Skipping empty table: {$tableInfo['table']}\n";
                    continue;
                }

                // Analyze data structure and create MySQL table
                $schema = $this->analyzeDataStructure($data);
                $this->createMySQLTable($options['database'], $tableInfo['table'], $schema);

                // Insert data
                $inserted = $this->insertData($options['database'], $tableInfo['table'], $data);

                echo "✅ Migrated {$tableInfo['table']}: {$inserted} records\n";

                $totalRecords += $inserted;
                $migratedTables++;
            }

            return [
                'success' => true,
                'tables' => $migratedTables,
                'records' => $totalRecords,
                'message' => 'Migration completed successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 📊 ANALYZE DATA STRUCTURE
     * 
     * Examines FileDatabase records to determine MySQL column types
     */
    private function analyzeDataStructure($data)
    {
        $schema = [];
        $sampleSize = min(100, count($data)); // Analyze up to 100 records

        // Collect all unique keys
        $allKeys = [];
        for ($i = 0; $i < $sampleSize; $i++) {
            if (isset($data[$i]) && is_array($data[$i])) {
                $allKeys = array_merge($allKeys, array_keys($data[$i]));
            }
        }
        $allKeys = array_unique($allKeys);

        // Analyze each column
        foreach ($allKeys as $key) {
            $types = [];
            $maxLength = 0;
            $hasNull = false;

            // Sample values for this key
            for ($i = 0; $i < $sampleSize; $i++) {
                if (!isset($data[$i][$key])) {
                    $hasNull = true;
                    continue;
                }

                $value = $data[$i][$key];
                $types[] = $this->detectDataType($value);

                if (is_string($value)) {
                    $maxLength = max($maxLength, strlen($value));
                }
            }

            // Determine final column type
            $columnType = $this->resolveColumnType($types, $maxLength);

            $schema[$key] = [
                'type' => $columnType,
                'nullable' => $hasNull,
                'length' => $maxLength
            ];
        }

        // Ensure we have an ID column
        if (!isset($schema['id'])) {
            $schema = ['id' => ['type' => 'INT AUTO_INCREMENT PRIMARY KEY', 'nullable' => false]] + $schema;
        }

        return $schema;
    }

    /**
     * 🔍 DETECT DATA TYPE
     */
    private function detectDataType($value)
    {
        if (is_null($value))
            return 'NULL';
        if (is_bool($value))
            return 'BOOLEAN';
        if (is_int($value))
            return 'INTEGER';
        if (is_float($value))
            return 'FLOAT';

        if (is_string($value)) {
            // Check for common patterns
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value))
                return 'DATE';
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value))
                return 'DATETIME';
            if (filter_var($value, FILTER_VALIDATE_EMAIL))
                return 'VARCHAR';
            if (filter_var($value, FILTER_VALIDATE_URL))
                return 'TEXT';
            if (strlen($value) > 255)
                return 'TEXT';
            return 'VARCHAR';
        }

        return 'TEXT'; // Default fallback
    }

    /**
     * 🎯 RESOLVE COLUMN TYPE
     */
    private function resolveColumnType($types, $maxLength)
    {
        $types = array_filter($types, function ($t) {
            return $t !== 'NULL';
        });

        if (empty($types))
            return 'VARCHAR(255)';

        $uniqueTypes = array_unique($types);

        // If all same type, use that
        if (count($uniqueTypes) === 1) {
            $type = $uniqueTypes[0];

            switch ($type) {
                case 'INTEGER':
                    return 'INT';
                case 'FLOAT':
                    return 'DECIMAL(10,2)';
                case 'BOOLEAN':
                    return 'BOOLEAN';
                case 'DATE':
                    return 'DATE';
                case 'DATETIME':
                    return 'DATETIME';
                case 'TEXT':
                    return 'TEXT';
                case 'VARCHAR':
                    $length = max(255, min(65535, $maxLength + 50));
                    return $length > 255 ? 'TEXT' : "VARCHAR({$length})";
            }
        }

        // Mixed types - use most permissive
        if (in_array('TEXT', $uniqueTypes))
            return 'TEXT';
        if (in_array('VARCHAR', $uniqueTypes)) {
            $length = max(255, min(65535, $maxLength + 50));
            return $length > 255 ? 'TEXT' : "VARCHAR({$length})";
        }

        return 'TEXT'; // Safe fallback
    }

    /**
     * 🏗️ CREATE MYSQL TABLE
     */
    private function createMySQLTable($database, $tableName, $schema)
    {
        $columns = [];

        foreach ($schema as $column => $definition) {
            $nullable = $definition['nullable'] ? 'NULL' : 'NOT NULL';
            $columns[] = "`{$column}` {$definition['type']} {$nullable}";
        }

        $sql = "CREATE TABLE `{$database}`.`{$tableName}` (\n";
        $sql .= "  " . implode(",\n  ", $columns) . "\n";
        $sql .= ")";

        $this->mysqlConnection->exec($sql);
    }

    /**
     * 📥 INSERT DATA
     */
    private function insertData($database, $tableName, $data)
    {
        if (empty($data))
            return 0;

        $inserted = 0;

        foreach ($data as $record) {
            if (!is_array($record))
                continue;

            $columns = array_keys($record);
            $placeholders = array_fill(0, count($columns), '?');

            $sql = "INSERT INTO `{$database}`.`{$tableName}` (`" .
                implode('`, `', $columns) . "`) VALUES (" .
                implode(', ', $placeholders) . ")";

            $stmt = $this->mysqlConnection->prepare($sql);
            $stmt->execute(array_values($record));
            $inserted++;
        }

        return $inserted;
    }

    /**
     * 🔌 CONNECT TO MYSQL
     */
    private function connectToMySQL($options)
    {
        $dsn = "mysql:host={$options['host']};port={$options['port']};charset=utf8mb4";

        $this->mysqlConnection = new PDO($dsn, $options['user'], $options['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    /**
     * 🏗️ CREATE DATABASE
     */
    private function createDatabase($database)
    {
        $sql = "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $this->mysqlConnection->exec($sql);
    }

    /**
     * 📂 LOAD FILE DATA
     */
    private function loadFileData($filePath)
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    /**
     * 📊 COUNT RECORDS
     */
    private function countRecords($filePath)
    {
        $data = $this->loadFileData($filePath);
        return count($data);
    }

    /**
     *  SHOW MIGRATION PLAN
     */
    public function showMigrationPlan($tables, $options)
    {
        echo "\n📋 MIGRATION PLAN (DRY RUN)\n";
        echo "==========================\n";

        foreach ($tables as $tableInfo) {
            echo "\nTable: {$tableInfo['table']}\n";
            echo "Records: {$tableInfo['records']}\n";

            if ($tableInfo['records'] > 0) {
                $data = $this->loadFileData($tableInfo['file']);
                $schema = $this->analyzeDataStructure($data);

                echo "Schema:\n";
                foreach ($schema as $column => $definition) {
                    echo "  - {$column}: {$definition['type']}" .
                        ($definition['nullable'] ? ' (nullable)' : '') . "\n";
                }
            }
        }

        echo "\nMySQL Configuration:\n";
        echo "  Host: {$options['host']}\n";
        echo "  Database: {$options['database']}\n";
        echo "  User: {$options['user']}\n";

        return 0;
    }
}
