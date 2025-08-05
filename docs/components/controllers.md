# 🎮 Controllers

Controllers are the command center of your Hoist PHP application, handling incoming requests, processing business logic, and orchestrating responses.

## Controller Basics

### **Creating Controllers**

Controllers are PHP classes that extend the base `Controller` class and are stored in `source/Application/Controllers/`:

```php
<?php

class UserController extends Controller
{
    public function index()
    {
        // Display all users
        $users = $this->instance->models->user->getAll();
        $this->instance->view->render('users/index', ['users' => $users]);
    }

    public function show($id)
    {
        // Display specific user
        $user = $this->instance->models->user->find($id);
        if (!$user) {
            return $this->instance->response->sendError('User not found', 404);
        }

        $this->instance->view->render('users/show', ['user' => $user]);
    }

    public function create()
    {
        // Handle user creation
        if ($this->request->isPost()) {
            $validated = $this->request->validate([
                'name' => 'required|min:2|max:100',
                'email' => 'required|email|unique:users'
            ]);

            $user = $this->instance->models->user->create($validated);
            return $this->instance->response->redirect('/users/' . $user['id']);
        }

        $this->instance->view->render('users/create');
    }
}
```

### **Controller Lifecycle**

Controllers support before/after hooks for common operations:

```php
class AdminController extends Controller
{
    // Runs before any method
    public function before()
    {
        // Authentication check
        if (!$this->instance->auth->isLoggedIn()) {
            return $this->instance->response->redirect('/login');
        }

        // Authorization check
        if (!$this->instance->auth->hasRole('admin')) {
            return $this->instance->response->sendError('Access denied', 403);
        }

        // Log admin actions
        $this->instance->models->audit->log([
            'user_id' => $this->instance->auth->id(),
            'action' => 'admin_access',
            'controller' => get_class($this),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public function dashboard()
    {
        // Admin dashboard logic
    }

    // Runs after any method
    public function after()
    {
        // Cleanup, additional logging, etc.
    }
}
```

## Request Handling

### **Accessing Request Data**

Controllers have access to the enhanced Request object:

```php
class ContactController extends Controller
{
    public function submit()
    {
        // Get individual inputs
        $name = $this->request->input('name');
        $email = $this->request->input('email');
        $message = $this->request->input('message');

        // Get all inputs
        $formData = $this->request->all();

        // Get specific fields only
        $contactInfo = $this->request->only(['name', 'email']);

        // Check if field exists and is filled
        if ($this->request->filled('company')) {
            $company = $this->request->input('company');
        }

        // Validate input
        $validated = $this->request->validate([
            'name' => 'required|min:2',
            'email' => 'required|email',
            'message' => 'required|min:10'
        ]);

        // Process the contact form...
    }
}
```

### **File Upload Handling**

```php
class MediaController extends Controller
{
    public function uploadAvatar()
    {
        if (!$this->request->hasFile('avatar')) {
            return $this->instance->response->sendError('No file uploaded');
        }

        $file = $this->request->file('avatar');

        // Validate file
        $validated = $this->request->validate([
            'avatar' => 'required|image|max:2048'  // 2MB max
        ]);

        // Process upload
        if ($file->isValid()) {
            $filename = 'avatar_' . $this->instance->auth->id() . '_' . time() . '.' . $file->getExtension();
            $path = $file->move('uploads/avatars/', $filename);

            // Update user avatar
            $this->instance->models->user->update($this->instance->auth->id(), [
                'avatar' => $path
            ]);

            return $this->instance->response->sendSuccess('Avatar updated successfully');
        }

        return $this->instance->response->sendError('File upload failed');
    }
}
```

## Response Management

### **View Responses**

```php
class BlogController extends Controller
{
    public function article($slug)
    {
        $article = $this->instance->models->article->findBySlug($slug);

        if (!$article) {
            return $this->instance->response->sendError('Article not found', 404);
        }

        // Render view with data
        $this->instance->view->render('blog/article', [
            'article' => $article,
            'title' => $article['title'],
            'meta_description' => substr(strip_tags($article['content']), 0, 160)
        ]);
    }
}
```

### **JSON API Responses**

```php
class ApiController extends Controller
{
    public function getUsers()
    {
        // Pagination
        $page = (int)$this->request->input('page', 1);
        $limit = (int)$this->request->input('limit', 20);
        $offset = ($page - 1) * $limit;

        // Get users with pagination
        $users = $this->instance->models->user->paginate($limit, $offset);
        $total = $this->instance->models->user->count();

        return $this->instance->response->json([
            'success' => true,
            'data' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    public function createUser()
    {
        try {
            $validated = $this->request->validate([
                'name' => 'required|min:2|max:100',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8'
            ]);

            $user = $this->instance->models->user->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => password_hash($validated['password'], PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return $this->instance->response->json([
                'success' => true,
                'data' => $user,
                'message' => 'User created successfully'
            ], 201);

        } catch (ValidationException $e) {
            return $this->instance->response->sendError($e->getErrors(), 422);
        } catch (Exception $e) {
            return $this->instance->response->sendError('Server error', 500);
        }
    }
}
```

### **Redirects and Flash Messages**

```php
class AuthController extends Controller
{
    public function login()
    {
        if ($this->request->isPost()) {
            $email = $this->request->input('email');
            $password = $this->request->input('password');

            if ($this->instance->auth->login($email, $password)) {
                return $this->instance->response->redirect('/dashboard')
                    ->with('success', 'Welcome back!');
            } else {
                return $this->instance->response->back()
                    ->withErrors(['login' => 'Invalid credentials'])
                    ->withInput();
            }
        }

        $this->instance->view->render('auth/login');
    }
}
```

## Nested Controllers

### **Organizing Controllers in Subdirectories**

```php
// Controllers/Admin/UserController.php
class UserController extends Controller
{
    public function index()
    {
        // URL: /admin/user
        $users = $this->instance->models->user->getAll();
        $this->instance->view->render('admin/users/index', ['users' => $users]);
    }

    public function edit($id)
    {
        // URL: /admin/user/edit/123
        $user = $this->instance->models->user->find($id);
        $this->instance->view->render('admin/users/edit', ['user' => $user]);
    }
}

// Controllers/Api/V1/UserController.php
class UserController extends Controller
{
    public function index()
    {
        // URL: /api/v1/user
        return $this->instance->response->json(
            $this->instance->models->user->getAll()
        );
    }
}
```

## Error Handling

### **Graceful Error Management**

```php
class OrderController extends Controller
{
    public function process()
    {
        try {
            // Validate order data
            $validated = $this->request->validate([
                'product_id' => 'required|integer',
                'quantity' => 'required|integer|min:1'
            ]);

            // Check product availability
            $product = $this->instance->models->product->find($validated['product_id']);
            if (!$product) {
                throw new NotFoundException('Product not found');
            }

            if ($product['stock'] < $validated['quantity']) {
                throw new ValidationException(['quantity' => 'Insufficient stock']);
            }

            // Process order
            $order = $this->instance->models->order->create([
                'user_id' => $this->instance->auth->id(),
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'total' => $product['price'] * $validated['quantity'],
                'status' => 'pending'
            ]);

            return $this->instance->response->redirect('/orders/' . $order['id'])
                ->with('success', 'Order placed successfully!');

        } catch (ValidationException $e) {
            return $this->instance->response->back()
                ->withErrors($e->getErrors())
                ->withInput();

        } catch (NotFoundException $e) {
            return $this->instance->response->sendError('Product not found', 404);

        } catch (Exception $e) {
            // Log error
            error_log('Order processing error: ' . $e->getMessage());

            return $this->instance->response->back()
                ->withErrors(['general' => 'An error occurred processing your order'])
                ->withInput();
        }
    }
}
```

## Middleware Pattern

### **Before/After Hooks as Middleware**

```php
class ApiBaseController extends Controller
{
    public function before()
    {
        // CORS headers
        $this->instance->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->instance->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
        $this->instance->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        // Handle preflight requests
        if ($this->request->method() === 'OPTIONS') {
            $this->instance->response->send();
            exit;
        }

        // API authentication
        $token = $this->request->header('Authorization');
        if (!$this->validateApiToken($token)) {
            return $this->instance->response->sendError('Unauthorized', 401);
        }

        // Rate limiting
        $clientIp = $this->request->getClientIp();
        if ($this->isRateLimited($clientIp)) {
            return $this->instance->response->sendError('Rate limit exceeded', 429);
        }
    }

    private function validateApiToken($token)
    {
        // Token validation logic
        return !empty($token) && str_starts_with($token, 'Bearer ');
    }

    private function isRateLimited($ip)
    {
        // Rate limiting logic using cache
        $key = 'rate_limit:' . $ip;
        $requests = $this->instance->cache->get($key, 0);

        if ($requests >= 100) { // 100 requests per hour
            return true;
        }

        $this->instance->cache->set($key, $requests + 1, 3600);
        return false;
    }
}

// Extend the base API controller
class UserApiController extends ApiBaseController
{
    public function getUsers()
    {
        // This method automatically has CORS, auth, and rate limiting
        return $this->instance->response->json(
            $this->instance->models->user->getAll()
        );
    }
}
```

## Best Practices

### **1. Single Responsibility**

```php
// ❌ Bad: Controller doing too much
class UserController extends Controller
{
    public function create()
    {
        // Validation, business logic, email sending, logging all in one method
    }
}

// ✅ Good: Focused responsibilities
class UserController extends Controller
{
    public function create()
    {
        $validated = $this->validateUserCreation();
        $user = $this->createUser($validated);
        $this->sendWelcomeEmail($user);
        $this->logUserCreation($user);

        return $this->instance->response->redirect('/users/' . $user['id']);
    }

    private function validateUserCreation()
    {
        return $this->request->validate([
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users'
        ]);
    }

    private function createUser($data)
    {
        return $this->instance->models->user->create($data);
    }

    private function sendWelcomeEmail($user)
    {
        // Email logic
    }

    private function logUserCreation($user)
    {
        // Logging logic
    }
}
```

### **2. Consistent Response Format**

```php
class ApiController extends Controller
{
    protected function successResponse($data = null, $message = null, $code = 200)
    {
        return $this->instance->response->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'timestamp' => time()
        ], $code);
    }

    protected function errorResponse($message, $code = 400, $errors = null)
    {
        return $this->instance->response->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => time()
        ], $code);
    }
}
```

### **3. Dependency Injection**

```php
class OrderController extends Controller
{
    private $paymentService;
    private $emailService;

    public function __construct()
    {
        parent::__construct();

        // Inject dependencies
        $this->paymentService = new PaymentService($this->instance);
        $this->emailService = new EmailService($this->instance);
    }

    public function process()
    {
        // Use injected services
        $payment = $this->paymentService->charge($amount);
        $this->emailService->sendOrderConfirmation($order);
    }
}
```

---

Controllers are the heart of your application's request handling. Keep them focused, testable, and follow consistent patterns for maintainable code.

**Next:** [Models](models.md) - Learn about data management and database interactions.
