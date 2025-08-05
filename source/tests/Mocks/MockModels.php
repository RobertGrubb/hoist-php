<?php

class MockModels
{
    public $user;

    public function __construct($instance)
    {
        $this->user = new MockUserModel($instance);
    }
}

class MockUserModel
{
    private $instance;
    private $mockUsers = [];

    public function __construct($instance)
    {
        $this->instance = $instance;

        // Default test users
        $this->mockUsers = [
            [
                'id' => 1,
                'email' => 'admin@example.com',
                'role' => 'admin',
                'is_active' => 1,
                'created_at' => '2024-01-01 10:00:00'
            ],
            [
                'id' => 2,
                'email' => 'admin@site.com',
                'role' => 'admin',
                'is_active' => 1,
                'created_at' => '2024-01-01 10:00:00'
            ]
        ];
    }

    public function setMockUsers($users)
    {
        $this->mockUsers = $users;
    }

    public function getAllUsers()
    {
        return $this->mockUsers;
    }

    public function getUserById($id)
    {
        foreach ($this->mockUsers as $user) {
            if ($user['id'] == $id) {
                return $user;
            }
        }
        return null;
    }

    public function getUserByEmail($email)
    {
        foreach ($this->mockUsers as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }

    public function createUser($data)
    {
        $newId = count($this->mockUsers) + 1;
        $newUser = array_merge($data, [
            'id' => $newId,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->mockUsers[] = $newUser;
        return $newUser;
    }

    public function updateUser($id, $data)
    {
        foreach ($this->mockUsers as &$user) {
            if ($user['id'] == $id) {
                $user = array_merge($user, $data);
                return $user;
            }
        }
        return false;
    }

    public function deleteUser($id)
    {
        foreach ($this->mockUsers as $index => $user) {
            if ($user['id'] == $id) {
                unset($this->mockUsers[$index]);
                $this->mockUsers = array_values($this->mockUsers); // Re-index
                return true;
            }
        }
        return false;
    }

    public function validateUser($email, $password)
    {
        $user = $this->getUserByEmail($email);
        if ($user && $user['is_active']) {
            // For testing, check specific valid passwords
            if (
                ($email === 'admin@example.com' && $password === 'admin') ||
                ($email === 'admin@site.com' && $password === 'admin') ||
                ($email === 'user@example.com' && $password === 'user')
            ) {
                return $user;
            }
        }
        return false;
    }
}
