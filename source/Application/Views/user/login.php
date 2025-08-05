<?= $view->render('includes/header'); ?>

<main
    class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div
                class="mx-auto h-16 w-16 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center shadow-lg">
                <i class="fas fa-user text-white text-xl"></i>
            </div>
            <h2 class="mt-6 text-3xl font-bold text-gray-900">
                <?= htmlspecialchars($pageTitle) ?>
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Welcome back! Please sign in to your account
            </p>
        </div>

        <!-- Flash Messages -->
        <?php if ($session->getFlashData('error')): ?>
            <?= $components->render('UI.Alert', ['type' => 'error', 'messages' => $session->getFlashData('error')]) ?>
        <?php endif; ?>

        <?php if ($session->getFlashData('success')): ?>
            <?= $components->render('UI.Alert', ['type' => 'success', 'messages' => $session->getFlashData('success')]) ?>
        <?php endif; ?>

        <!-- Login Form -->
        <form class="mt-8 space-y-6" method="POST" action="/user/login">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="space-y-4">
                    <!-- Email Field -->
                    <?= $components->render('Form.Input', [
                        'type' => 'email',
                        'name' => 'email',
                        'label' => 'Email Address',
                        'value' => 'admin@site.com',
                        'required' => true,
                        'placeholder' => 'Enter your email address',
                        'icon' => 'fas fa-envelope'
                    ]) ?>

                    <!-- Password Field -->
                    <?= $components->render('Form.Input', [
                        'type' => 'password',
                        'name' => 'password',
                        'label' => 'Password',
                        'value' => 'admin',
                        'required' => true,
                        'placeholder' => 'Enter your password',
                        'icon' => 'fas fa-lock',
                        'showToggle' => true
                    ]) ?>
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <?= $components->render('Form.Button', [
                        'type' => 'submit',
                        'text' => 'Sign In',
                        'variant' => 'primary',
                        'size' => 'lg',
                        'fullWidth' => true,
                        'icon' => 'fas fa-sign-in-alt'
                    ]) ?>
                </div>
            </div>
        </form>

        <!-- Demo Credentials -->
        <?= $components->render('UI.Alert', [
            'type' => 'info',
            'messages' => ['<strong>Email:</strong> admin@site.com<br><strong>Password:</strong> admin<br><span class="text-xs mt-1 block">These credentials are pre-filled for testing purposes.</span>'],
            'title' => 'Demo Credentials',
            'dismissible' => false,
            'allowHtml' => true
        ]) ?>

        <!-- Additional Links -->
        <div class="text-center space-y-2">
            <div>
                <a href="/user/register"
                    class="text-blue-600 hover:text-blue-500 text-sm font-medium transition-colors duration-200">
                    Don't have an account? Sign up
                </a>
            </div>
            <div>
                <a href="/" class="text-gray-500 hover:text-gray-400 text-sm transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</main>

<?= $view->render('includes/footer'); ?>

