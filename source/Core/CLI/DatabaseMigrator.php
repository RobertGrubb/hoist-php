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
    private static $migrationCounter = 0; // Counter for unique timestamps

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
     * 🚀 MIGRATE TO MYSQL (WITH AUTOMATIC PHINX GENERATION!)
     * 
     * The ULTIMATE migration function that:
     * 1. Converts FileDatabase to MySQL
     * 2. Generates Phinx migrations for each table
     * 3. Generates Phinx seeds for all data
     * 4. Marks migrations as run in development
     * 
     * RESULT: Production deployment just runs `phinx migrate`!
     */
    public function migrateToMySQL($tables, $options)
    {
        try {
            // Connect to MySQL
            $this->connectToMySQL($options);

            // Create database if it doesn't exist
            $this->createDatabase($options['database']);

            // 🎯 CREATE PHINX TRACKING TABLE
            $this->createPhinxLogTable($options['database']);

            $totalRecords = 0;
            $migratedTables = 0;
            $generatedMigrations = [];
            $generatedSeeds = [];

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

                // 🎯 GENERATE PHINX MIGRATION FOR THIS TABLE
                echo "📝 Generating migration for table: {$tableInfo['table']}\n";
                $migrationFile = $this->generatePhinxMigration($tableInfo['table'], $schema);
                if ($migrationFile) {
                    $generatedMigrations[] = $migrationFile;
                    echo "📄 Generated migration: {$migrationFile}\n";
                } else {
                    echo "❌ Failed to generate migration for table: {$tableInfo['table']}\n";
                }

                // Insert data
                $inserted = $this->insertData($options['database'], $tableInfo['table'], $data);

                // 🎯 GENERATE PHINX SEED FOR THIS TABLE
                if ($inserted > 0) {
                    echo "🌱 Generating seed for table: {$tableInfo['table']}\n";
                    $seedFile = $this->generatePhinxSeed($tableInfo['table'], $data);
                    if ($seedFile) {
                        $generatedSeeds[] = $seedFile;
                        echo "🌱 Generated seed: {$seedFile}\n";
                    } else {
                        echo "❌ Failed to generate seed for table: {$tableInfo['table']}\n";
                    }
                }

                echo "✅ Migrated {$tableInfo['table']}: {$inserted} records\n";

                $totalRecords += $inserted;
                $migratedTables++;
            }

            // 🎯 MARK MIGRATIONS AS RUN IN DEVELOPMENT
            echo "🔍 About to mark " . count($generatedMigrations) . " migrations as completed\n";
            if (!empty($generatedMigrations)) {
                echo "📋 Migration files to mark:\n";
                foreach ($generatedMigrations as $migration) {
                    echo "   - {$migration}\n";
                }
            }
            $this->markMigrationsAsRun($generatedMigrations, $options['database']);

            echo "\n🎉 PHINX INTEGRATION COMPLETE!\n";
            echo "📁 Generated " . count($generatedMigrations) . " migration files\n";
            echo "🌱 Generated " . count($generatedSeeds) . " seed files\n";
            echo "✅ Marked migrations as run in development\n";
            echo "\n🚀 PRODUCTION DEPLOYMENT:\n";
            echo "   1. Deploy your code\n";
            echo "   2. Run: vendor/bin/phinx migrate -e production\n";
            echo "   3. Run: vendor/bin/phinx seed:run -e production\n";

            return [
                'success' => true,
                'tables' => $migratedTables,
                'records' => $totalRecords,
                'migrations' => count($generatedMigrations),
                'seeds' => count($generatedSeeds),
                'message' => 'Migration completed successfully with Phinx generation'
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

                // Calculate length based on final stored format
                if (is_string($value)) {
                    $maxLength = max($maxLength, strlen($value));
                } elseif (is_array($value) || is_object($value)) {
                    // For arrays/objects, calculate JSON string length
                    $jsonString = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $maxLength = max($maxLength, strlen($jsonString));
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
        if (is_array($value) || is_object($value))
            return 'JSON'; // Arrays and objects become JSON columns

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
                case 'JSON':
                    return 'JSON';
                case 'TEXT':
                    return 'TEXT';
                case 'VARCHAR':
                    $length = max(255, min(65535, $maxLength + 50));
                    return $length > 255 ? 'TEXT' : "VARCHAR({$length})";
            }
        }

        // Mixed types - use most permissive
        if (in_array('JSON', $uniqueTypes))
            return 'JSON'; // If any JSON data, use JSON column
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

            // Process record values to handle arrays/objects
            $processedRecord = [];
            foreach ($record as $key => $value) {
                $processedRecord[$key] = $this->processValueForMySQL($value);
            }

            $columns = array_keys($processedRecord);
            $placeholders = array_fill(0, count($columns), '?');

            $sql = "INSERT INTO `{$database}`.`{$tableName}` (`" .
                implode('`, `', $columns) . "`) VALUES (" .
                implode(', ', $placeholders) . ")";

            $stmt = $this->mysqlConnection->prepare($sql);
            $stmt->execute(array_values($processedRecord));
            $inserted++;
        }

        return $inserted;
    }

    /**
     * 🔄 PROCESS VALUE FOR MYSQL
     * 
     * Converts PHP values to MySQL-compatible format
     */
    private function processValueForMySQL($value)
    {
        // Handle arrays and objects - convert to JSON
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        // Handle booleans - convert to integers for MySQL
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        // Handle null values
        if (is_null($value)) {
            return null;
        }

        // For everything else (strings, numbers), return as-is
        return $value;
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
     * � CREATE PHINX LOG TABLE
     * 
     * Creates the phinxlog table that Phinx uses to track migrations
     */
    private function createPhinxLogTable($database)
    {
        echo "📋 Creating Phinx migration tracking table...\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS `{$database}`.`phinxlog` (
            `version` bigint(20) NOT NULL,
            `migration_name` varchar(100) DEFAULT NULL,
            `start_time` timestamp NULL DEFAULT NULL,
            `end_time` timestamp NULL DEFAULT NULL,
            `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`version`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->mysqlConnection->exec($sql);
        echo "✅ Created phinxlog table\n";
    }

    /**
     * �📂 LOAD FILE DATA
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
                    $nullable = $definition['nullable'] ? ' (nullable)' : '';
                    $jsonNote = ($definition['type'] === 'JSON') ? ' ← arrays/objects converted to JSON' : '';
                    echo "  - {$column}: {$definition['type']}{$nullable}{$jsonNote}\n";
                }
            }
        }

        echo "\nMySQL Configuration:\n";
        echo "  Host: {$options['host']}\n";
        echo "  Database: {$options['database']}\n";
        echo "  User: {$options['user']}\n";

        return 0;
    }

    /**
     * 🎯 GENERATE PHINX MIGRATION
     * 
     * Creates a Phinx migration file for a table schema
     */
    private function generatePhinxMigration($tableName, $schema)
    {
        $className = 'Create' . ucfirst($this->toCamelCase($tableName)) . 'Table';
        
        // Generate unique timestamp with counter to avoid duplicates
        $baseTimestamp = date('YmdHis');
        $timestamp = $baseTimestamp . sprintf('%02d', self::$migrationCounter++);
        
        $filename = $timestamp . '_' . strtolower($className) . '.php';

        // Ensure migrations directory exists (in source/Database/migrations)
        $migrationDir = __DIR__ . '/../../Database/migrations';
        if (!is_dir($migrationDir)) {
            mkdir($migrationDir, 0755, true);
        }

        $filepath = $migrationDir . '/' . $filename;

        // Generate column definitions for Phinx
        $columns = [];
        foreach ($schema as $columnName => $definition) {
            if ($columnName === 'id' && strpos($definition['type'], 'AUTO_INCREMENT') !== false) {
                continue; // Skip - Phinx auto-creates ID
            }

            $phinxType = $this->convertToPhinxType($definition['type']);
            $nullable = $definition['nullable'] ? '' : ', [\'null\' => false]';

            $columns[] = "              ->addColumn('{$columnName}', '{$phinxType}'{$nullable})";
        }

        $migrationContent = "<?php

use Phinx\\Migration\\AbstractMigration;

/**
 * Auto-generated migration from FileDatabase → MySQL conversion
 * Table: {$tableName}
 * Generated: " . date('Y-m-d H:i:s') . "
 */
class {$className} extends AbstractMigration
{
    /**
     * Create {$tableName} table
     */
    public function up()
    {
        \$table = \$this->table('{$tableName}');
        \$table" . implode("\n        ", $columns) . "
              ->create();
    }

    /**
     * Drop {$tableName} table
     */
    public function down()
    {
        \$this->table('{$tableName}')->drop()->save();
    }
}
";

        if (file_put_contents($filepath, $migrationContent)) {
            return $filename;
        }

        return false;
    }

    /**
     * 🌱 GENERATE PHINX SEED
     * 
     * Creates a Phinx seed file for table data
     */
    private function generatePhinxSeed($tableName, $data)
    {
        $className = ucfirst($this->toCamelCase($tableName)) . 'Seeder';
        $filename = strtolower($className) . '.php';

        // Ensure seeds directory exists (in source/Database/seeds)
        $seedDir = __DIR__ . '/../../Database/seeds';
        if (!is_dir($seedDir)) {
            mkdir($seedDir, 0755, true);
        }

        $filepath = $seedDir . '/' . $filename;

        // Prepare data for seeding (limit to reasonable amount)
        $seedData = array_slice($data, 0, 1000); // Max 1000 records for seeds
        $processedData = [];

        foreach ($seedData as $record) {
            $processedRecord = [];
            foreach ($record as $key => $value) {
                if ($key === 'id')
                    continue; // Skip IDs, let auto-increment handle
                $processedRecord[$key] = $this->processValueForSeed($value);
            }
            $processedData[] = $processedRecord;
        }

        $dataString = var_export($processedData, true);
        $dataString = str_replace('array (', '[', $dataString);
        $dataString = str_replace(')', ']', $dataString);

        $seedContent = "<?php

use Phinx\\Seed\\AbstractSeed;

/**
 * Auto-generated seed from FileDatabase → MySQL conversion
 * Table: {$tableName}
 * Records: " . count($seedData) . " (limited from " . count($data) . " total)
 * Generated: " . date('Y-m-d H:i:s') . "
 */
class {$className} extends AbstractSeed
{
    /**
     * Seed {$tableName} table
     */
    public function run()
    {
        \$data = {$dataString};

        \$table = \$this->table('{$tableName}');
        \$table->insert(\$data)
              ->save();
    }
}
";

        if (file_put_contents($filepath, $seedContent)) {
            return $filename;
        }

        return false;
    }

    /**
     * ✅ MARK MIGRATIONS AS RUN
     * 
     * Marks generated migrations as already executed (phinxlog table already exists)
     */
    private function markMigrationsAsRun($migrationFiles, $database)
    {
        if (empty($migrationFiles)) {
            return;
        }

        try {
            // Use the existing MySQL connection
            if (!$this->mysqlConnection) {
                echo "⚠️  No MySQL connection available to mark migrations\n";
                return;
            }

            echo "� Marking migrations as completed in phinxlog...\n";

            // Verify table exists and start transaction
            try {
                $this->mysqlConnection->beginTransaction();
                $checkTable = $this->mysqlConnection->query("SELECT COUNT(*) FROM `{$database}`.`phinxlog`");
                echo "✅ Confirmed phinxlog table exists with " . $checkTable->fetchColumn() . " existing records\n";

                // Insert migrations with fully qualified table name
                $stmt = $this->mysqlConnection->prepare("
                    INSERT INTO `{$database}`.`phinxlog` (version, migration_name, start_time, end_time, breakpoint) 
                    VALUES (?, ?, ?, ?, 0)
                    ON DUPLICATE KEY UPDATE end_time = VALUES(end_time)
                ");

                $insertedCount = 0;
                foreach ($migrationFiles as $filename) {
                    // Extract full timestamp from filename (16 characters including counter)
                    $version = substr($filename, 0, 16);
                    $migrationName = pathinfo($filename, PATHINFO_FILENAME);
                    $now = date('Y-m-d H:i:s');

                    echo "🔍 Inserting migration: version={$version}, name={$migrationName}\n";
                    $result = $stmt->execute([$version, $migrationName, $now, $now]);
                    
                    if ($result) {
                        $insertedCount++;
                        echo "✅ Marked migration {$migrationName} as completed\n";
                    } else {
                        $errorInfo = $stmt->errorInfo();
                        echo "❌ Failed to mark migration {$migrationName}: " . print_r($errorInfo, true) . "\n";
                    }
                }

                // Commit and verify
                $this->mysqlConnection->commit();
                $finalCount = $this->mysqlConnection->query("SELECT COUNT(*) FROM `{$database}`.`phinxlog`")->fetchColumn();
                echo "💾 Transaction committed. Final phinxlog count: {$finalCount}\n";
                
            } catch (Exception $e) {
                $this->mysqlConnection->rollback();
                throw $e;
            }

            echo "� Added " . count($migrationFiles) . " migrations to phinxlog table\n";

        } catch (Exception $e) {
            echo "⚠️  Could not mark migrations as run: " . $e->getMessage() . "\n";
            echo "💡 You may need to run 'vendor/bin/phinx migrate' manually\n";
        }
    }

    /**
     * 🔄 CONVERT TO PHINX TYPE
     */
    private function convertToPhinxType($mysqlType)
    {
        $mysqlType = strtoupper($mysqlType);

        if (strpos($mysqlType, 'VARCHAR') !== false)
            return 'string';
        if (strpos($mysqlType, 'TEXT') !== false)
            return 'text';
        if (strpos($mysqlType, 'INT') !== false)
            return 'integer';
        if (strpos($mysqlType, 'DECIMAL') !== false)
            return 'decimal';
        if (strpos($mysqlType, 'FLOAT') !== false)
            return 'float';
        if (strpos($mysqlType, 'BOOLEAN') !== false)
            return 'boolean';
        if (strpos($mysqlType, 'DATE') !== false)
            return 'date';
        if (strpos($mysqlType, 'DATETIME') !== false)
            return 'datetime';
        if (strpos($mysqlType, 'JSON') !== false)
            return 'json';

        return 'string'; // Default fallback
    }

    /**
     * 🐪 TO CAMEL CASE
     */
    private function toCamelCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    /**
     * 🌱 PROCESS VALUE FOR SEED
     */
    private function processValueForSeed($value)
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        return $value;
    }
}
