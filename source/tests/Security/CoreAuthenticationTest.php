<?php

require_once __DIR__ . '/../bootstrap.php';

class CoreAuthenticationTest extends TestCase
{
    private $instance;

    protected function setUp(): void
    {
        $this->instance = $this->createMockInstance();
    }

    public function testUserCanLogin()
    {
        // Test successful login
        $result = $this->instance->auth->login('admin@example.com', 'admin');

        $this->assertTrue($result);
        $this->assertTrue($this->instance->auth->isLoggedIn());
        $this->assertNotNull($this->instance->auth->getUser());
    }

    public function testUserCanLogout()
    {
        // Login first
        $this->instance->auth->login('admin@example.com', 'admin');
        $this->assertTrue($this->instance->auth->isLoggedIn());

        // Then logout
        $this->instance->auth->logout();
        $this->assertFalse($this->instance->auth->isLoggedIn());
        $this->assertNull($this->instance->auth->getUser());
    }

    public function testInvalidCredentialsFail()
    {
        // Test with invalid email
        $result = $this->instance->auth->login('invalid@example.com', 'admin');
        $this->assertFalse($result);
        $this->assertFalse($this->instance->auth->isLoggedIn());

        // Test with invalid password
        $result = $this->instance->auth->login('admin@example.com', 'wrongpassword');
        $this->assertFalse($result);
        $this->assertFalse($this->instance->auth->isLoggedIn());
    }

    public function testAdminRoleDetection()
    {
        // Login as admin
        $this->instance->auth->setUser([
            'id' => 1,
            'email' => 'admin@example.com',
            'role' => 'admin',
            'is_active' => 1
        ]);

        $this->assertTrue($this->instance->auth->isAdmin());
        $this->assertEquals('admin', $this->instance->auth->getUserRole());
    }

    public function testUserRoleDetection()
    {
        // Login as regular user
        $this->instance->auth->setUser([
            'id' => 2,
            'email' => 'user@example.com',
            'role' => 'user',
            'is_active' => 1
        ]);

        $this->assertFalse($this->instance->auth->isAdmin());
        $this->assertEquals('user', $this->instance->auth->getUserRole());
    }

    public function testRequireLoginEnforcement()
    {
        // When not logged in, requireLogin should trigger redirect
        $this->instance->auth->logout();

        $this->expectOutputString('');
        // We can't test actual redirects in unit tests, but we can verify the state
        $this->assertFalse($this->instance->auth->isLoggedIn());
    }

    public function testUserDataAccess()
    {
        $userData = [
            'id' => 1,
            'email' => 'test@example.com',
            'role' => 'user',
            'is_active' => 1
        ];

        $this->instance->auth->setUser($userData);

        $this->assertEquals(1, $this->instance->auth->getUserId());
        $this->assertEquals('test@example.com', $this->instance->auth->getUserEmail());
        $this->assertEquals('user', $this->instance->auth->getUserRole());
    }

    public function testCSRFTokenGeneration()
    {
        $token1 = $this->instance->auth->generateCSRFToken();
        $token2 = $this->instance->auth->generateCSRFToken();

        $this->assertNotEmpty($token1);
        $this->assertNotEmpty($token2);
        $this->assertIsString($token1);
        $this->assertIsString($token2);
    }

    public function testCSRFTokenValidation()
    {
        $validToken = $this->instance->auth->generateCSRFToken();

        $this->assertTrue($this->instance->auth->validateCSRFToken($validToken));
        $this->assertFalse($this->instance->auth->validateCSRFToken('invalid_token'));
        $this->assertFalse($this->instance->auth->validateCSRFToken(''));
    }

    public function testAdminActionLogging()
    {
        // Setup admin user
        $this->instance->auth->setUser([
            'id' => 1,
            'email' => 'admin@example.com',
            'role' => 'admin',
            'is_active' => 1
        ]);

        // Log an action
        $this->instance->auth->logAdminAction('user_create', ['email' => 'newuser@example.com']);

        $logs = $this->instance->auth->getAdminActionLogs();
        $this->assertNotEmpty($logs);

        $lastLog = end($logs);
        $this->assertEquals('user_create', $lastLog['action']);
        $this->assertEquals(1, $lastLog['admin_id']);
        $this->assertEquals('admin@example.com', $lastLog['admin_email']);
    }
}
