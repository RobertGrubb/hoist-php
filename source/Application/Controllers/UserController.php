<?php

/**
 * ===================================================================
 * USER CONTROLLER - COMPLETE MVC AUTHENTICATION DEMONSTRATION
 * ===================================================================
 * 
 * This controller demonstrates the complete MVC flow in Hoist PHP Framework
 * with proper authentication, validation, security, and view rendering.
 * It serves as a comprehensive example for developers learning the framework.
 * 
 * Features Demonstrated:
 * - MVC Architecture: Controllers, Views, Models working together
 * - Authentication System: Login, logout, session management
 * - Enhanced Validation: Using the new validation system
 * - Security: Input cleaning and XSS prevention
 * - View Rendering: Tailwind CSS styled templates
 * - Flash Messages: Success and error message handling
 * - Role-Based Access: Admin and user permissions
 * - Database Operations: User CRUD with FileDatabase
 * 
 * Routes:
 * /user         - Login page or dashboard if logged in
 * /user/login   - Process login form
 * /user/logout  - Logout and redirect
 * /user/profile - User profile page (auth required)
 * /user/admin   - Admin panel (admin role required)
 * /user/register - User registration (if enabled)
 * 
 * @package HoistPHP\Application\Controllers
 * @author  Hoist PHP Framework Team
 * @version 1.0.0
 */
class UserController extends Controller
{
    // ===============================================================
    // AUTHENTICATION PAGES
    // ===============================================================

    /**
     * Main user page - login form or dashboard based on auth status.
     * 
     * Demonstrates:
     * - Conditional view rendering based on authentication
     * - Flash message display
     * - Clean separation of authenticated vs non-authenticated content
     */
    public function index()
    {
        // Check if user is already logged in
        if ($this->instance->auth->user) {
            // User is logged in - show dashboard
            $this->renderDashboard();
        } else {
            // User not logged in - show login form
            $this->renderLogin();
        }
    }

    /**
     * Process login form submission.
     * 
     * Demonstrates:
     * - Enhanced validation with custom messages
     * - Input cleaning for security
     * - Flash message handling for errors
     * - Proper redirect flow
     */
    public function login()
    {
        // Only allow POST requests
        if (strtoupper($this->request->method()) !== 'POST') {
            $this->instance->redirect('/user');
            return;
        }

        // Clean input data first for security
        $cleanData = $this->instance->cleaner->cleanBatch([
            'email' => 'trim|lowercase|clean_email|remove_xss',
            'password' => 'trim|remove_xss'
        ], $this->request->all());

        // Validate the cleaned data
        $validation = $this->instance->validation->validateBatch([
            'email' => 'required|email',
            'password' => 'required|min:3'
        ], $cleanData, [
            'email.required' => 'Please enter your email address',
            'email.email' => 'Please enter a valid email address',
            'password.required' => 'Please enter your password',
            'password.min' => 'Password must be at least 3 characters'
        ]);

        if (!$validation['valid']) {
            $this->session->setFlashData('error', $validation['errors']);
            $this->instance->redirect('/user');
            return;
        }

        // Attempt authentication
        if ($this->instance->auth->login($cleanData['email'], $cleanData['password'])) {
            $this->session->setFlashData('success', [
                'Welcome back, ' . $this->instance->auth->user['name'] . '!'
            ]);
            $this->instance->redirect('/user');
        } else {
            $this->session->setFlashData('error', ['Invalid email or password. Please try again.']);
            $this->instance->redirect('/user');
        }
    }

    /**
     * Logout user and redirect to login page.
     * 
     * Demonstrates:
     * - Proper logout flow
     * - Flash message for user feedback
     * - Clean session handling
     */
    public function logout()
    {
        if ($this->instance->auth->user) {
            $userName = $this->instance->auth->user['name'];
            $this->instance->auth->logout();
            $this->session->setFlashData('success', ["Goodbye, {$userName}! You have been logged out."]);
        }

        $this->instance->redirect('/user');
    }

    // ===============================================================
    // AUTHENTICATED USER PAGES
    // ===============================================================

    /**
     * User profile page (requires authentication).
     * 
     * Demonstrates:
     * - Authentication middleware
     * - User data display
     * - Secure data handling
     */
    public function profile()
    {
        // Require authentication
        $this->instance->auth->required();

        $user = $this->instance->auth->user;

        $this->instance->view->render('user/profile', [
            'title' => 'User Profile',
            'user' => $user,
            'pageTitle' => 'Your Profile'
        ]);
    }

    /**
     * Admin panel (requires admin role).
     * 
     * Demonstrates:
     * - Role-based access control
     * - Database queries for admin data
     * - Bulk data display
     */
    public function admin()
    {
        // Require authentication and admin role
        $this->instance->auth->required();
        $this->instance->auth->requireGroup('admin');

        // Get all users for admin view
        $users = $this->models->user->all();

        // Get some basic statistics
        $totalUsers = count($users);
        $activeUsers = count(array_filter($users, function ($user) {
            return $user['status'] === 'active';
        }));
        $adminUsers = count(array_filter($users, function ($user) {
            return $user['user_group_id'] === 'admin';
        }));

        $this->instance->view->render('user/admin', [
            'title' => 'Admin Panel',
            'users' => $users,
            'currentUser' => $this->instance->auth->user,
            'stats' => [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'admins' => $adminUsers
            ],
            'pageTitle' => 'Administration Panel'
        ]);
    }

    // ===============================================================
    // USER REGISTRATION (OPTIONAL)
    // ===============================================================

    /**
     * User registration page.
     * 
     * Demonstrates:
     * - Complex form validation
     * - Password strength requirements
     * - User creation with security
     */
    public function register()
    {
        // Check if registration is enabled (you can add this to constants)
        $registrationEnabled = true; // Set this based on your app configuration

        if (!$registrationEnabled) {
            $this->session->setFlashData('error', ['Registration is currently disabled.']);
            $this->instance->redirect('/user');
            return;
        }

        if (strtoupper($this->request->method()) === 'POST') {
            $this->processRegistration();
        } else {
            $this->renderRegistration();
        }
    }

    /**
     * Process user registration.
     */
    private function processRegistration()
    {
        // Clean input data
        $cleanData = $this->instance->cleaner->cleanBatch([
            'name' => 'trim|clean_html:basic|remove_xss',
            'email' => 'trim|lowercase|clean_email|remove_xss',
            'password' => 'trim|remove_xss',
            'password_confirmation' => 'trim|remove_xss'
        ], $this->request->all());

        // Validate registration data
        $validation = $this->instance->validation->validateBatch([
            'name' => 'required|min:2|max:50|alpha_spaces',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|password_strength:medium',
            'password_confirmation' => 'required|same:password'
        ], $cleanData, [
            'name.required' => 'Please enter your full name',
            'name.alpha_spaces' => 'Name can only contain letters and spaces',
            'email.required' => 'Please enter your email address',
            'email.unique' => 'This email address is already registered',
            'password.password_strength' => 'Password must contain uppercase, lowercase, and numbers',
            'password_confirmation.same' => 'Password confirmation does not match'
        ]);

        if (!$validation['valid']) {
            $this->session->setFlashData('error', $validation['errors']);
            $this->session->setFlashData('form_data', $cleanData);
            $this->instance->redirect('/user/register');
            return;
        }

        // Create new user
        try {
            $userId = $this->models->user->create([
                'name' => $cleanData['name'],
                'email' => $cleanData['email'],
                'password' => $cleanData['password'], // UserModel will hash this
                'user_group_id' => 'user', // Default role
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if ($userId) {
                $this->session->setFlashData('success', [
                    'Registration successful! You can now log in with your credentials.'
                ]);
                $this->instance->redirect('/user');
            } else {
                throw new Exception('Failed to create user account');
            }
        } catch (Exception $e) {
            $this->session->setFlashData('error', [
                'Registration failed: ' . $e->getMessage()
            ]);
            $this->instance->redirect('/user/register');
        }
    }

    // ===============================================================
    // PRIVATE VIEW RENDERING METHODS
    // ===============================================================

    /**
     * Render login page with form.
     */
    private function renderLogin()
    {
        $this->instance->view->render('user/login', [
            'title' => 'User Login',
            'pageTitle' => 'Sign In to Your Account'
        ]);
    }

    /**
     * Render user dashboard for authenticated users.
     */
    private function renderDashboard()
    {
        $user = $this->instance->auth->user;

        // Get some user-specific data for the dashboard
        $userStats = [
            'loginTime' => date('g:i A'),
            'loginDate' => date('F j, Y'),
            'role' => ucfirst($user['user_group_id']),
            'status' => ucfirst($user['status'])
        ];

        $this->instance->view->render('user/dashboard', [
            'title' => 'User Dashboard',
            'user' => $user,
            'stats' => $userStats,
            'pageTitle' => 'Welcome, ' . $user['name']
        ]);
    }

    /**
     * Render registration page.
     */
    private function renderRegistration()
    {
        $this->instance->view->render('user/register', [
            'title' => 'User Registration',
            'pageTitle' => 'Create Your Account'
        ]);
    }
}
