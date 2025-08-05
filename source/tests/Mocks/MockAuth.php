<?php

class MockAuth
{
    private $currentUser = null;
    private $isLoggedIn = false;
    private $sessionInitialized = false;
    private $adminActionLogs = [];

    public function __construct($instance = null)
    {
        // Mock constructor
    }

    public function login($email, $password)
    {
        // Mock login with realistic validation
        if (
            ($email === 'admin@example.com' && $password === 'admin') ||
            ($email === 'user@example.com' && $password === 'user')
        ) {
            $this->isLoggedIn = true;
            $this->currentUser = [
                'id' => $email === 'admin@example.com' ? 1 : 2,
                'email' => $email,
                'role' => $email === 'admin@example.com' ? 'admin' : 'user',
                'is_active' => 1
            ];
            return true;
        } else {
            $this->isLoggedIn = false;
            $this->currentUser = null;
            return false;
        }
    }

    public function logout()
    {
        $this->isLoggedIn = false;
        $this->currentUser = null;
    }

    public function isLoggedIn()
    {
        return $this->isLoggedIn;
    }

    public function isAdmin()
    {
        return $this->currentUser && $this->currentUser['role'] === 'admin';
    }

    public function getUser()
    {
        return $this->currentUser;
    }

    public function setUser($userData)
    {
        $this->currentUser = $userData;
        $this->isLoggedIn = !empty($userData);
    }

    public function getUserId()
    {
        return $this->currentUser ? $this->currentUser['id'] : null;
    }

    public function getUserEmail()
    {
        return $this->currentUser ? $this->currentUser['email'] : null;
    }

    public function getUserRole()
    {
        return $this->currentUser ? $this->currentUser['role'] : null;
    }

    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public function requireAdmin()
    {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }
    }

    public function initializeSession()
    {
        $this->sessionInitialized = true;
    }

    public function generateCSRFToken()
    {
        return 'mock_csrf_token_' . time();
    }

    public function validateCSRFToken($token)
    {
        // For testing, accept any token starting with 'mock_csrf_token_'
        return strpos($token, 'mock_csrf_token_') === 0;
    }

    public function logAdminAction($action, $details = [])
    {
        $this->adminActionLogs[] = [
            'action' => $action,
            'admin_id' => $this->getUserId(),
            'admin_email' => $this->getUserEmail(),
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    public function getAdminActionLogs()
    {
        return $this->adminActionLogs;
    }

    public function clearAdminActionLogs()
    {
        $this->adminActionLogs = [];
    }
}
