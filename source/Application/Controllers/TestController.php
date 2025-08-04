<?php

/**
 * Test Controller for Authentication System
 * 
 * Simple controller to test the FileDatabase-based authentication
 * system and UserModel functionality.
 */
class TestController extends Controller
{
    /**
     * Test login page - shows login form
     */
    public function index()
    {
        echo '<html><head><title>Test Login</title></head><body>';
        echo '<h1>Test Authentication System</h1>';

        if ($this->instance->auth->user) {
            echo '<p>Logged in as: ' . $this->instance->auth->user['name'] . ' (' . $this->instance->auth->user['email'] . ')</p>';
            echo '<p>User Group: ' . $this->instance->auth->userGroup . '</p>';
            echo '<a href="/test/logout">Logout</a><br>';
            echo '<a href="/test/profile">View Profile</a><br>';
            echo '<a href="/test/admin">Admin Panel</a>';
        } else {
            echo '<form method="post" action="/test/login">';
            echo '<label>Email: <input type="email" name="email" value="admin@site.com" required></label><br><br>';
            echo '<label>Password: <input type="password" name="password" value="admin" required></label><br><br>';
            echo '<button type="submit">Login</button>';
            echo '</form>';
            echo '<p><small>Use: admin@site.com / admin</small></p>';
        }

        echo '</body></html>';
    }

    /**
     * Process login attempt
     */
    public function login()
    {
        $email = $this->request->post('email');
        $password = $this->request->post('password');

        if ($this->instance->auth->login($email, $password)) {
            $this->instance->redirect('/test');
        } else {
            $this->session->setFlashData('error', 'Invalid credentials');
            $this->instance->redirect('/test');
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        $this->instance->auth->logout();
        $this->instance->redirect('/test');
    }

    /**
     * User profile page (requires authentication)
     */
    public function profile()
    {
        $this->instance->auth->required();

        echo '<html><head><title>User Profile</title></head><body>';
        echo '<h1>User Profile</h1>';
        echo '<p><strong>Name:</strong> ' . $this->instance->auth->user['name'] . '</p>';
        echo '<p><strong>Email:</strong> ' . $this->instance->auth->user['email'] . '</p>';
        echo '<p><strong>Group:</strong> ' . $this->instance->auth->user['user_group_id'] . '</p>';
        echo '<p><strong>Status:</strong> ' . $this->instance->auth->user['status'] . '</p>';
        echo '<p><strong>Created:</strong> ' . $this->instance->auth->user['created_at'] . '</p>';
        echo '<a href="/test">Back to Main</a>';
        echo '</body></html>';
    }

    /**
     * Admin only page (requires admin group)
     */
    public function admin()
    {
        $this->instance->auth->required();
        $this->instance->auth->requireGroup('admin');

        echo '<html><head><title>Admin Panel</title></head><body>';
        echo '<h1>Admin Panel</h1>';
        echo '<p>This page is only accessible to admin users.</p>';
        echo '<p>All users:</p>';
        echo '<ul>';

        $users = $this->models->user->all();
        foreach ($users as $user) {
            echo '<li>' . $user['name'] . ' (' . $user['email'] . ') - ' . $user['user_group_id'] . '</li>';
        }

        echo '</ul>';
        echo '<a href="/test">Back to Main</a>';
        echo '</body></html>';
    }
}
