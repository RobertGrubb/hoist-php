<?php

/**
 * ===============================================================
 * HOIST FRAMEWORK - DATABASE ADAPTER EXAMPLE
 * ===============================================================
 * 
 * This example demonstrates the revolutionary DatabaseAdapter that
 * provides seamless migration from FileDatabase to MySQL without
 * any code changes.
 * 
 * The same code works in both development (FileDatabase) and
 * production (MySQL) environments!
 */

class ExampleController extends Controller
{
    /**
     * Demonstrates unified database operations.
     * 
     * This exact same code will work whether you're using:
     * - FileDatabase (development)
     * - MySQL (production)
     * 
     * No code changes needed during migration!
     */
    public function databaseExample()
    {
        // ✨ REVOLUTIONARY: Same syntax for both backends!

        // 1. Create a new user
        $userId = $this->instance->db->table('users')->insert([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'status' => 'active',
            'preferences' => ['email', 'sms'], // Arrays work seamlessly!
            'metadata' => [                    // Objects work seamlessly!
                'theme' => 'dark',
                'timezone' => 'UTC'
            ],
            'email_verified' => true,           // Booleans work seamlessly!
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // 2. Find users with complex conditions
        $activeUsers = $this->instance->db->table('users')
            ->where('status', '=', 'active')
            ->where('email_verified', '=', true)
            ->order('created_at', 'DESC')
            ->all(10);

        // 3. Get a specific user
        $user = $this->instance->db->table('users')
            ->where('email', '=', 'jane@example.com')
            ->first();

        // 4. Update user data
        $affected = $this->instance->db->table('users')
            ->where('id', '=', $userId)
            ->update([
                'last_login' => date('Y-m-d H:i:s'),
                'login_count' => 1
            ]);

        // 5. Search users
        $searchResults = $this->instance->db->table('users')
            ->where('name', 'LIKE', 'Jane')
            ->where('status', '=', 'active')
            ->all();

        // Check which backend we're using
        $backend = $this->instance->db->getBackend();

        // Return results
        $this->instance->response->sendJson([
            'message' => 'Database operations completed successfully!',
            'backend' => $backend,
            'user_id' => $userId,
            'active_users_count' => count($activeUsers),
            'search_results_count' => count($searchResults),
            'affected_rows' => $affected,
            'demo_note' => 'This exact same code works with both FileDatabase and MySQL!'
        ]);
    }

    /**
     * Demonstrates migration workflow.
     */
    public function migrationDemo()
    {
        $steps = [
            '1. Development Phase' => [
                'description' => 'Start building with FileDatabase (zero setup)',
                'code' => '$users = $this->instance->db->table("users")->all();',
                'backend' => 'FileDatabase (JSON files)',
                'setup_time' => '0 seconds'
            ],

            '2. Migration Phase' => [
                'description' => 'One command migration to production MySQL',
                'command' => 'php hoist migrate:to-mysql --database=myapp',
                'result' => 'All data migrated with proper schemas'
            ],

            '3. Production Phase' => [
                'description' => 'Same code, now using MySQL automatically',
                'code' => '$users = $this->instance->db->table("users")->all();',
                'backend' => 'MySQL (production database)',
                'code_changes' => '0 lines changed!'
            ]
        ];

        $this->instance->response->sendJson([
            'title' => 'HOIST Framework: Seamless Migration Workflow',
            'current_backend' => $this->instance->db->getBackend(),
            'workflow' => $steps,
            'benefits' => [
                'Zero configuration development',
                'One command migration',
                'No code changes needed',
                'Automatic data type handling',
                'Performance optimization per backend'
            ]
        ]);
    }

    /**
     * Shows data type handling examples.
     */
    public function dataTypesExample()
    {
        // The adapter automatically handles different data types
        $exampleData = [
            'string_field' => 'Hello World',
            'integer_field' => 42,
            'float_field' => 3.14159,
            'boolean_field' => true,
            'array_field' => ['option1', 'option2', 'option3'],
            'object_field' => [
                'setting1' => 'value1',
                'setting2' => 'value2',
                'nested' => ['deep' => 'value']
            ],
            'null_field' => null,
            'date_field' => date('Y-m-d H:i:s')
        ];

        // Insert the data - adapter handles all conversions!
        $recordId = $this->instance->db->table('data_types_demo')->insert($exampleData);

        // Retrieve the data back
        $retrieved = $this->instance->db->table('data_types_demo')
            ->where('id', '=', $recordId)
            ->first();

        $this->instance->response->sendJson([
            'message' => 'Data types handled automatically!',
            'backend' => $this->instance->db->getBackend(),
            'original_data' => $exampleData,
            'retrieved_data' => $retrieved,
            'conversions' => [
                'FileDatabase' => 'All types stored as-is in JSON',
                'MySQL' => 'Arrays/objects → JSON, booleans → 1/0, etc.',
                'Developer_effort' => 'Zero! Adapter handles everything.'
            ]
        ]);
    }
}
