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
                        <?= $components->render('UI.Alert', ['type' => 'error', 'messages' => $session->getFlashData('error'), 'title' => 'Please fix the following errors:']) ?>
                    <?php endif; ?>

                    <?php if ($session->getFlashData('success')): ?>
                        <?= $components->render('UI.Alert', ['type' => 'success', 'messages' => $session->getFlashData('success')]) ?>
                    <?php endif; ?>

                    <!-- Registration Form -->
                    <form method="POST" action="/user/register" class="space-y-6">
                        <!-- Full Name -->
                        <?= $components->render('Form.Input', [
                            'type' => 'text',
                            'name' => 'name',
                            'label' => 'Full Name',
                            'icon' => 'fas fa-user',
                            'placeholder' => 'Enter your full name',
                            'required' => true,
                            'value' => $session->getFlashData('form_data')['name'] ?? ''
                        ]) ?>

                        <!-- Email Address -->
                        <?= $components->render('Form.Input', [
                            'type' => 'email',
                            'name' => 'email',
                            'label' => 'Email Address',
                            'icon' => 'fas fa-envelope',
                            'placeholder' => 'Enter your email address',
                            'required' => true,
                            'value' => $session->getFlashData('form_data')['email'] ?? ''
                        ]) ?>

                        <!-- Password -->
                        <?= $components->render('Form.Input', [
                            'type' => 'password',
                            'name' => 'password',
                            'label' => 'Password',
                            'icon' => 'fas fa-lock',
                            'placeholder' => 'Create a secure password',
                            'required' => true,
                            'help' => 'Password must be at least 8 characters with letters and numbers',
                            'showToggle' => true
                        ]) ?>

                        <!-- Confirm Password -->
                        <?= $components->render('Form.Input', [
                            'type' => 'password',
                            'name' => 'password_confirm',
                            'label' => 'Confirm Password',
                            'icon' => 'fas fa-lock',
                            'placeholder' => 'Confirm your password',
                            'required' => true,
                            'showToggle' => true
                        ]) ?>

                        <!-- Terms and Conditions -->
                        <?= $components->render('Form.Checkbox', [
                            'name' => 'terms',
                            'label' => 'I agree to the <a href="#" class="text-green-600 hover:text-green-500 font-medium">Terms and Conditions</a> and <a href="#" class="text-green-600 hover:text-green-500 font-medium">Privacy Policy</a>',
                            'required' => true
                        ]) ?>

                        <!-- Register Button -->
                        <?= $components->render('Form.Button', [
                            'type' => 'submit',
                            'text' => 'Create Account',
                            'icon' => 'fas fa-user-plus',
                            'variant' => 'primary',
                            'size' => 'lg',
                            'fullWidth' => true,
                            'classes' => 'transform hover:scale-105'
                        ]) ?>
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

