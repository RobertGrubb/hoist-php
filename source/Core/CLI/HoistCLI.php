<?php

/**
 * ===============================================================
 * HOIST FRAMEWORK - COMMAND LINE INTERFACE
 * ===============================================================
 * 
 * CLI tool for framework operations, database migrations, and development utilities.
 * 
 * This CLI system provides powerful command-line tools for:
 * - Database migrations (FileDatabase to MySQL)
 * - Code generation and scaffolding
 * - Development server management
 * - Production deployment helpers
 * 
 * USAGE:
 * php hoist [command] [options]
 * 
 * COMMANDS:
 * migrate:to-mysql     Convert FileDatabase to MySQL
 * migrate:to-file      Convert MySQL to FileDatabase  
 * generate:controller  Create new controller
 * generate:model       Create new model
 * generate:component   Create new UI component
 * serve                Start development server
 * cache:clear          Clear application cache
 * 
 * @package HoistPHP\CLI
 * @author  Hoist PHP Framework Team
 * @version 1.0.0
 */

require_once __DIR__ . '/../Bootstrap.php';

class HoistCLI
{
    private $instance;
    private $commands = [];

    public function __construct()
    {
        // Initialize framework instance for CLI
        $this->instance = new Instance();

        // Register built-in commands
        $this->registerCommands();
    }

    /**
     * Register all available CLI commands
     */
    private function registerCommands()
    {
        $this->commands = [
            'migrate:to-mysql' => 'migrateToMySQL',
            'migrate:to-file' => 'migrateToFileDatabase',
            'generate:controller' => 'generateController',
            'generate:model' => 'generateModel',
            'generate:component' => 'generateComponent',
            'serve' => 'startDevServer',
            'cache:clear' => 'clearCache',
            'help' => 'showHelp',
            '--help' => 'showHelp',
            '-h' => 'showHelp'
        ];
    }

    /**
     * Execute CLI command
     */
    public function run($argv)
    {
        $command = $argv[1] ?? 'help';
        $args = array_slice($argv, 2);

        if (!isset($this->commands[$command])) {
            $this->error("Unknown command: {$command}");
            $this->showHelp();
            return 1;
        }

        $method = $this->commands[$command];
        return $this->$method($args);
    }

    /**
     * 🚀 MIGRATE FILEDATABASE TO MYSQL
     * 
     * This is the GAME-CHANGING feature that allows seamless transition
     * from development (FileDatabase) to production (MySQL).
     */
    public function migrateToMySQL($args)
    {
        $this->info("🚀 Hoist Database Migration: FileDatabase → MySQL");
        $this->info("============================================");

        // Parse command line options
        $options = $this->parseOptions($args, [
            'host' => 'localhost',
            'port' => '3306',
            'database' => null,
            'user' => 'root',
            'password' => '',
            'preserve-files' => false,
            'dry-run' => false,
            'force' => false
        ]);

        // Validate required options
        if (!$options['database']) {
            $this->error("Database name is required. Use --database=myapp");
            return 1;
        }

        try {
            $migrator = new DatabaseMigrator($this->instance);

            // Show migration plan
            $this->info("\n📋 Migration Plan:");
            $this->info("Source: FileDatabase (Application/Database/)");
            $this->info("Target: MySQL ({$options['host']}:{$options['port']}/{$options['database']})");

            // Discover tables
            $tables = $migrator->discoverFileDatabaseTables();
            $tableNames = array_map(function ($table) {
                return $table['database'] . '.' . $table['table'] . ' (' . $table['records'] . ' records)';
            }, $tables);
            $this->info("Tables to migrate: " . implode(', ', $tableNames));

            if ($options['dry-run']) {
                $this->info("\n🔍 DRY RUN - No actual changes will be made");
                return $migrator->showMigrationPlan($tables, $options);
            }

            // Confirm migration
            if (!$options['force']) {
                $confirm = $this->confirm("🤔 Continue with migration?");
                if (!$confirm) {
                    $this->info("Migration cancelled.");
                    return 0;
                }
            }

            // Execute migration
            $this->info("\n🔄 Starting migration...");
            $result = $migrator->migrateToMySQL($tables, $options);

            if ($result['success']) {
                $this->success("✅ Migration completed successfully!");
                $this->info("📊 Migrated {$result['tables']} tables with {$result['records']} total records");

                if (isset($result['migrations']) && isset($result['seeds'])) {
                    $this->info("🎯 PHINX INTEGRATION:");
                    $this->info("   📁 Generated {$result['migrations']} migration files");
                    $this->info("   🌱 Generated {$result['seeds']} seed files");
                    $this->info("   ✅ Marked as run in development");
                    $this->info("");
                    $this->info("🚀 FOR PRODUCTION DEPLOYMENT:");
                    $this->info("   1. Deploy your code to production");
                    $this->info("   2. Run: vendor/bin/phinx migrate -e production");
                    $this->info("   3. Run: vendor/bin/phinx seed:run -e production");
                    $this->info("");
                }

                $this->info("🚀 Your application is now running on MySQL!");
                $this->info("💡 Update your .env file with the database credentials");

            } else {
                $this->error("❌ Migration failed: " . $result['error']);
                return 1;
            }

        } catch (Exception $e) {
            $this->error("Migration error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * 📁 MIGRATE MYSQL TO FILEDATABASE
     * 
     * Reverse migration for development or testing
     */
    public function migrateToFileDatabase($args)
    {
        $this->info("📁 Hoist Database Migration: MySQL → FileDatabase");
        $this->info("============================================");

        // Implementation for reverse migration
        $this->warning("⚠️  This feature is coming soon!");
        $this->info("Use case: Convert production data back to FileDatabase for local development");

        return 0;
    }

    /**
     * Generate new controller
     */
    public function generateController($args)
    {
        $name = $args[0] ?? null;

        if (!$name) {
            $this->error("Controller name is required. Usage: php hoist generate:controller UserController");
            return 1;
        }

        // TODO: Implement CodeGenerator class
        $this->warning("⚠️  Code generation feature is coming soon!");
        $this->info("Controller generation will create: Application/Controllers/{$name}.php");

        return 0;
    }

    /**
     * Generate new model
     */
    public function generateModel($args)
    {
        $name = $args[0] ?? null;

        if (!$name) {
            $this->error("Model name is required. Usage: php hoist generate:model User");
            return 1;
        }

        // TODO: Implement CodeGenerator class
        $this->warning("⚠️  Code generation feature is coming soon!");
        $this->info("Model generation will create: Application/Models/{$name}Model.php");

        return 0;
    }

    /**
     * Generate new UI component
     */
    public function generateComponent($args)
    {
        $name = $args[0] ?? null;

        if (!$name) {
            $this->error("Component name is required. Usage: php hoist generate:component Form.CustomInput");
            return 1;
        }

        // TODO: Implement CodeGenerator class
        $this->warning("⚠️  Code generation feature is coming soon!");
        $this->info("Component generation will create: Application/Components/{$name}.php");

        return 0;
    }

    /**
     * Start development server
     */
    public function startDevServer($args)
    {
        $port = $this->getOption($args, 'port', '8080');
        $host = $this->getOption($args, 'host', 'localhost');

        $this->info("🚀 Starting Hoist development server...");
        $this->info("Server: http://{$host}:{$port}");
        $this->info("Document root: " . ROOT_DIRECTORY . '/public');
        $this->info("Press Ctrl+C to stop");

        // Start PHP built-in server
        $command = "php -S {$host}:{$port} -t " . ROOT_DIRECTORY . '/public';
        passthru($command);

        return 0;
    }

    /**
     * Clear application cache
     */
    public function clearCache($args)
    {
        $this->info("🧹 Clearing application cache...");

        $cacheDir = APPLICATION_DIRECTORY . '/Cache';
        $cleared = 0;

        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/{data,meta,tags}/*', GLOB_BRACE);
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $cleared++;
                }
            }
        }

        $this->success("✅ Cleared {$cleared} cache files");
        return 0;
    }

    /**
     * Show help information
     */
    public function showHelp($args = [])
    {
        $this->info("🚀 Hoist PHP Framework CLI Tool");
        $this->info("==============================");
        $this->info("");
        $this->info("USAGE:");
        $this->info("  php hoist [command] [options]");
        $this->info("");
        $this->info("MIGRATION COMMANDS:");
        $this->info("  migrate:to-mysql       Convert FileDatabase to MySQL");
        $this->info("  migrate:to-file        Convert MySQL to FileDatabase");
        $this->info("");
        $this->info("GENERATION COMMANDS:");
        $this->info("  generate:controller    Create new controller");
        $this->info("  generate:model         Create new model");
        $this->info("  generate:component     Create new UI component");
        $this->info("");
        $this->info("DEVELOPMENT COMMANDS:");
        $this->info("  serve                  Start development server");
        $this->info("  cache:clear            Clear application cache");
        $this->info("");
        $this->info("EXAMPLES:");
        $this->info("  php hoist migrate:to-mysql --database=myapp --user=root --password=secret");
        $this->info("  php hoist generate:controller UserController");
        $this->info("  php hoist generate:component Form.CustomInput");
        $this->info("  php hoist serve --port=8080");
        $this->info("");
        $this->info("📝 NOTE: Migration commands require you to configure your .env file with");
        $this->info("   proper database connection settings before running the migration.");

        return 0;
    }

    // Helper methods for CLI output and option parsing

    private function parseOptions($args, $defaults = [])
    {
        $options = $defaults;

        foreach ($args as $arg) {
            if (strpos($arg, '--') === 0) {
                $parts = explode('=', substr($arg, 2), 2);
                $key = $parts[0];
                $value = $parts[1] ?? true;
                $options[$key] = $value;
            }
        }

        return $options;
    }

    private function getOption($args, $key, $default = null)
    {
        foreach ($args as $arg) {
            if (strpos($arg, "--{$key}=") === 0) {
                return substr($arg, strlen("--{$key}="));
            }
        }
        return $default;
    }

    private function confirm($message)
    {
        echo $message . " (y/N): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        return trim(strtolower($line)) === 'y';
    }

    private function info($message)
    {
        echo $message . "\n";
    }

    private function success($message)
    {
        echo "\033[32m" . $message . "\033[0m\n";
    }

    private function warning($message)
    {
        echo "\033[33m" . $message . "\033[0m\n";
    }

    private function error($message)
    {
        echo "\033[31m" . $message . "\033[0m\n";
    }
}

// CLI entry point
if (php_sapi_name() === 'cli') {
    $cli = new HoistCLI();
    exit($cli->run($argv));
} else {
    echo "This tool must be run from the command line.\n";
    exit(1);
}
