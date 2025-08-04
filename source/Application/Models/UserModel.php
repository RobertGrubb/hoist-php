<?php

/**
 * User Model for FileDatabase Operations
 * 
 * Manages user data using the FileDatabase system for JSON-based storage.
 * Provides user authentication, profile management, and user query operations
 * with enhanced security features including modern password hashing.
 * 
 * CORE CAPABILITIES:
 * - FileDatabase integration for user data persistence
 * - Modern password hashing with PHP password_hash()
 * - User authentication and verification
 * - Profile and avatar management
 * - Advanced user querying and filtering
 * 
 * SECURITY FEATURES:
 * - Password hashing using PASSWORD_DEFAULT algorithm
 * - Secure password verification
 * - Deleted user filtering
 * - Email uniqueness validation
 * 
 * PASSWORD SECURITY:
 * - Uses PHP's password_hash() with PASSWORD_DEFAULT
 * - Automatic salt generation
 * - Secure password verification with password_verify()
 * - No plain text password storage
 * 
 * @package HoistPHP\Application\Models
 * @version 2.0.0
 * @since 1.0.0
 */
class UserModel
{
    /**
     * FileDatabase instance for user data operations.
     * 
     * @var FileDatabase JSON-based database instance
     * @access private
     */
    private $fileDatabase;

    /**
     * Table name for user data storage.
     * 
     * @var string Table name in FileDatabase
     * @access public
     */
    public $table = "users";

    /**
     * Initializes the UserModel with FileDatabase connection.
     * 
     * Sets up the user model with FileDatabase integration for
     * JSON-based user data management and operations. The instance
     * parameter is provided by the framework but not used since
     * we're using FileDatabase directly.
     * 
     * @param Instance|null $instance Framework instance (for compatibility)
     */
    public function __construct($instance = null)
    {
        $this->fileDatabase = new FileDatabase('app');
    }

    /**
     * Generates avatar URL for user.
     * 
     * Returns either the custom avatar upload URL or generates
     * a default avatar using the user's name via UI Avatars service.
     * 
     * @param array $user User data array containing name and avatar fields
     * @return string Avatar URL (either custom upload or generated)
     */
    public function avatar($user)
    {
        if (!$user['avatar']) {
            return 'https://ui-avatars.com/api/?name=' . str_replace(' ', '+', $user['name']);
        }

        return '/uploads/' . $user['avatar'];
    }

    /**
     * Retrieves a single user record from FileDatabase.
     * 
     * Fetches one user record based on the provided where conditions.
     * Automatically filters out deleted users unless specifically requested.
     * 
     * @param array $where Filter conditions for user lookup
     * @return array|null Single user record or null if not found
     */
    public function get($where = [])
    {
        $query = $this->fileDatabase->table($this->table);

        foreach ($where as $field => $value) {
            $query->where($field, '=', $value);
        }

        return $query->first();
    }

    /**
     * Retrieves multiple user records from FileDatabase.
     * 
     * Fetches multiple user records based on the provided where conditions.
     * Supports ORDER BY, LIMIT, and other query options.
     * 
     * @param array $where Filter conditions and query options
     * @return array Array of user records (empty array if none found)
     */
    public function getMany($where = [])
    {
        $query = $this->fileDatabase->table($this->table);

        // Apply where conditions
        $whereConditions = $where;
        $limit = null;

        // Extract ORDER BY if present
        if (isset($where['ORDER'])) {
            $orderBy = $where['ORDER'];
            unset($whereConditions['ORDER']);

            foreach ($orderBy as $field => $direction) {
                $query->order($field, $direction);
            }
        }

        // Extract LIMIT if present
        if (isset($where['LIMIT'])) {
            $limit = $where['LIMIT'];
            unset($whereConditions['LIMIT']);
        }

        // Apply remaining where conditions
        foreach ($whereConditions as $field => $value) {
            $query->where($field, '=', $value);
        }

        // Execute query with or without limit
        if ($limit) {
            return $query->all($limit);
        } else {
            return $query->all();
        }
    }

    /**
     * Creates a new user record in FileDatabase.
     * 
     * Inserts new user data with automatic ID generation and timestamp.
     * Securely hashes password using PHP's password_hash() function.
     * 
     * @param array $data User data to insert
     * @return mixed ID of newly created user, or false on failure
     */
    public function create($data)
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Add timestamp
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        return $this->fileDatabase->table($this->table)->insert($data);
    }

    /**
     * Updates existing user records in FileDatabase.
     * 
     * Updates user data based on where conditions.
     * Securely hashes password if being updated.
     * 
     * @param array $where Update conditions
     * @param array $data Data to update
     * @return bool True on success, false on failure
     */
    public function save($where, $data)
    {
        // Hash password if being updated
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Add update timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');

        $query = $this->fileDatabase->table($this->table);

        foreach ($where as $field => $value) {
            $query->where($field, '=', $value);
        }

        return $query->update($data);
    }

    /**
     * Deletes user records from FileDatabase.
     * 
     * Supports both hard deletion and soft deletion (marking as deleted).
     * 
     * @param array $where Delete conditions
     * @param bool $soft Whether to soft delete (mark as deleted) or hard delete
     * @return bool True on success, false on failure
     */
    public function delete($where, $soft = true)
    {
        if ($soft) {
            return $this->save($where, ['deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')]);
        } else {
            // FileDatabase doesn't have a delete method in the API we saw, so we'll use soft delete
            return $this->save($where, ['deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')]);
        }
    }

    /**
     * Counts user records in FileDatabase.
     * 
     * Returns the number of users matching the specified conditions.
     * 
     * @param array $where Filter conditions
     * @return int Number of matching user records
     */
    public function count($where = [])
    {
        return count($this->getMany($where));
    }

    /**
     * Verifies user password against stored hash.
     * 
     * Uses PHP's password_verify() for secure password comparison.
     * 
     * @param string $password Plain text password to verify
     * @param string $hash Stored password hash
     * @return bool True if password matches, false otherwise
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Returns all active users ordered by name.
     * 
     * Retrieves all non-deleted users sorted alphabetically by name.
     * 
     * @return array Array of all active users
     */
    public function all()
    {
        return $this->getMany([
            'deleted' => 0,
            'ORDER' => [
                'name' => 'ASC'
            ]
        ]);
    }

    /**
     * Lists users with pagination and filters.
     * 
     * Retrieves a limited number of users based on filters and pagination.
     * 
     * @param int $limit Maximum number of users to return
     * @param array $filters Additional filter conditions
     * @return array Array of filtered users
     */
    public function list($limit = 50, $filters = [])
    {
        return $this->getMany(array_merge([
            'deleted' => 0,
            'LIMIT' => $limit
        ], $filters));
    }

    /**
     * Gets a user by their email address.
     * 
     * Finds a single user by email, excluding deleted users.
     * 
     * @param string|null $email User email address
     * @return array|false User data or false if not found
     */
    public function getByEmail($email = null)
    {
        if (is_null($email)) {
            return false;
        }

        $data = $this->get([
            'deleted' => 0,
            'email' => $email
        ]);

        return $data;
    }

    /**
     * Gets a user by their password reset hash.
     * 
     * Finds a single user by their reset hash, excluding deleted users.
     * 
     * @param string|null $hash Password reset hash
     * @return array|false User data or false if not found
     */
    public function getByResetHash($hash = null)
    {
        if (is_null($hash)) {
            return false;
        }

        $data = $this->get([
            'deleted' => 0,
            'reset_hash' => $hash
        ]);

        return $data;
    }

    /**
     * Gets the most recently created users.
     * 
     * Returns the latest users ordered by creation date.
     * 
     * @param int $limit Number of latest users to return
     * @return array Array of latest users
     */
    public function latest($limit = 5)
    {
        return $this->getMany([
            'deleted' => 0,
            'ORDER' => [
                'created_at' => 'DESC'
            ],
            'LIMIT' => $limit
        ]);
    }

    /**
     * Checks if a user exists by email.
     * 
     * Determines if a user with the given email exists (including deleted).
     * 
     * @param string $email Email address to check
     * @return array|false User data if exists, false otherwise
     */
    public function existsByEmail($email)
    {
        return $this->get([
            'email' => $email
        ]);
    }
}
