<?= $view->render('includes/header'); ?>

<main class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div
                        class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <h1 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($pageTitle) ?></h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/user" class="text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Back to Dashboard
                    </a>
                    <a href="/user/logout"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Profile Header -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center space-x-4">
                        <div
                            class="w-16 h-16 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($user['name']) ?></h2>
                            <p class="text-gray-600"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Account Details -->
                <?= $components->render('UI.Card', [
                    'content' => $components->render('Layout.DefinitionList', [
                        'items' => [
                            ['label' => 'Full Name', 'value' => htmlspecialchars($user['name'])],
                            ['label' => 'Email Address', 'value' => htmlspecialchars($user['email'])],
                            ['label' => 'User Role', 'value' => $components->render('UI.Badge', ['text' => ucfirst($user['user_group_id']), 'icon' => $user['user_group_id'] === 'admin' ? 'fas fa-crown' : 'fas fa-user', 'color' => $user['user_group_id'] === 'admin' ? 'purple' : 'blue'])],
                            ['label' => 'Account Status', 'value' => $components->render('UI.Badge', ['text' => ucfirst($user['status']), 'icon' => $user['status'] === 'active' ? 'fas fa-check-circle' : 'fas fa-times-circle', 'color' => $user['status'] === 'active' ? 'green' : 'red'])],
                            ['label' => 'Member Since', 'value' => date('F j, Y', strtotime($user['created_at'])) . ' <span class="text-gray-500 text-xs">(' . date('g:i A', strtotime($user['created_at'])) . ')</span>']
                        ]
                    ]),
                    'title' => 'Account Details'
                ]) ?>

                <!-- Security Information -->
                <?= $components->render('UI.Card', [
                    'content' => $components->render('Layout.FeatureList', [
                        'features' => [
                            ['title' => 'Password Protection', 'description' => 'Your password is securely hashed using modern encryption'],
                            ['title' => 'Session Management', 'description' => 'Secure session handling with automatic timeout'],
                            ['title' => 'Input Validation', 'description' => 'All user input is validated and sanitized for security'],
                            ['title' => 'Access Control', 'description' => $user['user_group_id'] === 'admin' ? 'Full administrative access to all system features' : 'Standard user access with role-based permissions']
                        ]
                    ]),
                    'title' => 'Security & Access'
                ]) ?>
            </div>

            <!-- Account Actions -->
            <?= $components->render('UI.Card', [
                'content' => '
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    ' . ($user['user_group_id'] === 'admin' ? $components->render('UI.ActionButton', ['href' => '/user/admin', 'text' => 'Admin Panel', 'icon' => 'fas fa-cog', 'color' => 'purple']) : '') . '
                    ' . $components->render('UI.ActionButton', ['href' => '/user', 'text' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'color' => 'blue']) . '
                    ' . $components->render('UI.ActionButton', ['href' => '/user/logout', 'text' => 'Logout', 'icon' => 'fas fa-sign-out-alt', 'color' => 'red']) . '
                </div>',
                'title' => 'Account Actions'
            ]) ?>

            <!-- Framework Demo Note -->
            <div class="mt-8 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg">
                <div class="p-6 text-white">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-lightbulb text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Hoist PHP Framework Demo</h3>
                            <p class="text-blue-100 text-sm leading-relaxed">
                                This profile page demonstrates the complete MVC architecture of Hoist PHP Framework.
                                It showcases view templates with Tailwind CSS, authentication management, role-based
                                access control,
                                and secure data handling. The controller uses enhanced validation and security cleaning
                                for all user input.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?= $view->render('includes/footer'); ?>

