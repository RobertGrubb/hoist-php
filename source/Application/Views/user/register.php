<?= $view->render('includes/header'); ?>

<main class="min-h-screen bg-gradient-to-br from-green-50 to-blue-100">
    <!-- Registration Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-center">
                <div class="text-center">
                    <div
                        class="w-16 h-16 bg-gradient-to-r from-green-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-plus text-white text-2xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($pageTitle) ?></h1>
                    <p class="mt-2 text-gray-600">Join the Hoist PHP Framework community</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Form -->
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-md mx-auto">
            <!-- Registration Card -->
            <div class="bg-white rounded-lg shadow-lg border border-gray-200">
                <div class="px-6 py-8">
                    <!-- Flash Messages -->
                    <?php if ($session->getFlashData('error')): ?>
                        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            <?php foreach ($session->getFlashData('error') as $error): ?>
                                                <li><?= htmlspecialchars($error) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($session->getFlashData('success')): ?>
                        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-400"></i>
                                </div>
                                <div class="ml-3">
                                    <?php foreach ($session->getFlashData('success') as $message): ?>
                                        <p class="text-sm text-green-800"><?= htmlspecialchars($message) ?></p>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Registration Form -->
                    <form method="POST" action="/user/register" class="space-y-6">
                        <!-- Full Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user text-gray-400 mr-2"></i>
                                Full Name
                            </label>
                            <input type="text" id="name" name="name" required
                                value="<?= htmlspecialchars($session->getFlashData('form_data')['name'] ?? '') ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                                placeholder="Enter your full name">
                        </div>

                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                Email Address
                            </label>
                            <input type="email" id="email" name="email" required
                                value="<?= htmlspecialchars($session->getFlashData('form_data')['email'] ?? '') ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                                placeholder="Enter your email address">
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock text-gray-400 mr-2"></i>
                                Password
                            </label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 pr-10"
                                    placeholder="Create a secure password">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    onclick="togglePassword('password')">
                                    <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password-icon"></i>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Password must be at least 8 characters with letters and numbers
                            </p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock text-gray-400 mr-2"></i>
                                Confirm Password
                            </label>
                            <div class="relative">
                                <input type="password" id="password_confirm" name="password_confirm" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 pr-10"
                                    placeholder="Confirm your password">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    onclick="togglePassword('password_confirm')">
                                    <i class="fas fa-eye text-gray-400 hover:text-gray-600"
                                        id="password_confirm-icon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="terms" name="terms" type="checkbox" required
                                    class="focus:ring-green-500 h-4 w-4 text-green-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="terms" class="text-gray-700">
                                    I agree to the
                                    <a href="#" class="text-green-600 hover:text-green-500 font-medium">Terms and
                                        Conditions</a>
                                    and
                                    <a href="#" class="text-green-600 hover:text-green-500 font-medium">Privacy
                                        Policy</a>
                                </label>
                            </div>
                        </div>

                        <!-- Register Button -->
                        <div>
                            <button type="submit"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-green-500 to-blue-600 hover:from-green-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 transform hover:scale-105">
                                <i class="fas fa-user-plus mr-2"></i>
                                Create Account
                            </button>
                        </div>
                    </form>

                    <!-- Login Link -->
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600">
                            Already have an account?
                            <a href="/user/login"
                                class="font-medium text-green-600 hover:text-green-500 transition-colors duration-200">
                                Sign in here
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Features Preview -->
            <div class="mt-8 bg-white rounded-lg shadow-md border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">What you'll get</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-shield-alt text-green-600 text-sm"></i>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Secure Account</h4>
                                <p class="text-sm text-gray-600">Enterprise-grade security with encrypted data storage
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-tachometer-alt text-blue-600 text-sm"></i>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Personal Dashboard</h4>
                                <p class="text-sm text-gray-600">Access to your personalized control panel</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-code text-purple-600 text-sm"></i>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Framework Access</h4>
                                <p class="text-sm text-gray-600">Full demonstration of Hoist PHP capabilities</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Framework Demo Note -->
            <div class="mt-8 bg-gradient-to-r from-green-500 to-blue-600 rounded-lg shadow-lg">
                <div class="p-6 text-white">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-rocket text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Hoist PHP Registration Demo</h3>
                            <p class="text-green-100 text-sm leading-relaxed">
                                This registration form demonstrates the complete user registration flow using Hoist PHP
                                Framework.
                                It showcases enhanced validation, security cleaning, password hashing, and modern UI
                                design with Tailwind CSS.
                                All user input is validated and sanitized before being stored in the FileDatabase
                                system.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-icon');

        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

<?= $view->render('includes/footer'); ?>

