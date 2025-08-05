<?php

require_once __DIR__ . '/../bootstrap.php';

class CoreDatabaseTest extends TestCase
{
    private $instance;

    protected function setUp(): void
    {
        $this->instance = $this->createMockInstance();
    }

    public function testUserModelOperations()
    {
        // Test creating a user
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
            'is_active' => 1
        ];

        $user = $this->instance->models->user->createUser($userData);
        $this->assertNotNull($user);
        $this->assertEquals('Test User', $user['name']);
        $this->assertEquals('test@example.com', $user['email']);
        $this->assertArrayHasKey('id', $user);
    }

    public function testUserRetrieval()
    {
        // Create a user first
        $userData = [
            'name' => 'Retrieve Test',
            'email' => 'retrieve@example.com',
            'role' => 'user',
            'is_active' => 1
        ];

        $user = $this->instance->models->user->createUser($userData);
        $userId = $user['id'];

        // Test retrieval by ID
        $retrievedUser = $this->instance->models->user->getUserById($userId);
        $this->assertNotNull($retrievedUser);
        $this->assertEquals($userId, $retrievedUser['id']);
        $this->assertEquals('retrieve@example.com', $retrievedUser['email']);

        // Test retrieval by email
        $retrievedByEmail = $this->instance->models->user->getUserByEmail('retrieve@example.com');
        $this->assertNotNull($retrievedByEmail);
        $this->assertEquals($userId, $retrievedByEmail['id']);
    }

    public function testUserUpdate()
    {
        // Create a user first
        $user = $this->instance->models->user->createUser([
            'name' => 'Update Test',
            'email' => 'update@example.com',
            'role' => 'user',
            'is_active' => 1
        ]);

        $userId = $user['id'];

        // Update the user
        $updateData = [
            'name' => 'Updated Name',
            'role' => 'admin'
        ];

        $updatedUser = $this->instance->models->user->updateUser($userId, $updateData);
        $this->assertNotFalse($updatedUser);
        $this->assertEquals('Updated Name', $updatedUser['name']);
        $this->assertEquals('admin', $updatedUser['role']);
        $this->assertEquals('update@example.com', $updatedUser['email']); // Should remain unchanged
    }

    public function testUserDeletion()
    {
        // Create a user first
        $user = $this->instance->models->user->createUser([
            'name' => 'Delete Test',
            'email' => 'delete@example.com',
            'role' => 'user',
            'is_active' => 1
        ]);

        $userId = $user['id'];

        // Verify user exists
        $this->assertNotNull($this->instance->models->user->getUserById($userId));

        // Delete the user
        $deleteResult = $this->instance->models->user->deleteUser($userId);
        $this->assertTrue($deleteResult);

        // Verify user no longer exists
        $this->assertNull($this->instance->models->user->getUserById($userId));
    }

    public function testGetAllUsers()
    {
        // Clear any existing users and add test users
        $this->instance->models->user->setMockUsers([
            [
                'id' => 1,
                'name' => 'User One',
                'email' => 'user1@example.com',
                'role' => 'user',
                'is_active' => 1,
                'created_at' => '2024-01-01 10:00:00'
            ],
            [
                'id' => 2,
                'name' => 'User Two',
                'email' => 'user2@example.com',
                'role' => 'admin',
                'is_active' => 1,
                'created_at' => '2024-01-02 10:00:00'
            ]
        ]);

        $allUsers = $this->instance->models->user->getAllUsers();
        $this->assertCount(2, $allUsers);
        $this->assertEquals('user1@example.com', $allUsers[0]['email']);
        $this->assertEquals('user2@example.com', $allUsers[1]['email']);
    }

    public function testUserValidation()
    {
        // Test valid user validation
        $validUser = $this->instance->models->user->validateUser('admin@site.com', 'admin');
        $this->assertNotFalse($validUser);
        $this->assertEquals('admin@site.com', $validUser['email']);

        // Test invalid email
        $invalidEmail = $this->instance->models->user->validateUser('nonexistent@example.com', 'admin');
        $this->assertFalse($invalidEmail);

        // Test invalid password (in a real system this would check password hashes)
        $invalidPassword = $this->instance->models->user->validateUser('admin@site.com', 'wrongpassword');
        $this->assertFalse($invalidPassword);
    }

    public function testUserCreationWithDefaults()
    {
        $user = $this->instance->models->user->createUser([
            'name' => 'Default Test',
            'email' => 'default@example.com'
        ]);

        $this->assertNotNull($user);
        $this->assertArrayHasKey('created_at', $user);
        $this->assertNotEmpty($user['created_at']);
    }

    public function testNonexistentUserRetrieval()
    {
        // Test retrieving user that doesn't exist
        $nonexistent = $this->instance->models->user->getUserById(99999);
        $this->assertNull($nonexistent);

        $nonexistentEmail = $this->instance->models->user->getUserByEmail('nonexistent@example.com');
        $this->assertNull($nonexistentEmail);
    }

    public function testUpdateNonexistentUser()
    {
        // Try to update a user that doesn't exist
        $result = $this->instance->models->user->updateUser(99999, ['name' => 'Updated']);
        $this->assertFalse($result);
    }

    public function testDeleteNonexistentUser()
    {
        // Try to delete a user that doesn't exist
        $result = $this->instance->models->user->deleteUser(99999);
        $this->assertFalse($result);
    }
}
