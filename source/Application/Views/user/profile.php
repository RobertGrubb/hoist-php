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
                <div class="bg-white rounded-lg shadow-md border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Account Details</h3>
                    </div>
                    <div class="p-6">
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['name']) ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['email']) ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">User Role</dt>
                                <dd class="mt-1">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $user['user_group_id'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                                        <i
                                            class="fas <?= $user['user_group_id'] === 'admin' ? 'fa-crown' : 'fa-user' ?> mr-1"></i>
                                        <?= htmlspecialchars(ucfirst($user['user_group_id'])) ?>
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Account Status</dt>
                                <dd class="mt-1">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <i
                                            class="fas <?= $user['status'] === 'active' ? 'fa-check-circle' : 'fa-times-circle' ?> mr-1"></i>
                                        <?= htmlspecialchars(ucfirst($user['status'])) ?>
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <?= date('F j, Y', strtotime($user['created_at'])) ?>
                                    <span
                                        class="text-gray-500 text-xs">(<?= date('g:i A', strtotime($user['created_at'])) ?>)</span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Security Information -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Security & Access</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- Password Security -->
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-lock text-green-600 text-sm"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">Password Protection</h4>
                                    <p class="text-sm text-gray-600">Your password is securely hashed using modern
                                        encryption</p>
                                </div>
                            </div>

                            <!-- Session Security -->
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-shield-alt text-blue-600 text-sm"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">Session Management</h4>
                                    <p class="text-sm text-gray-600">Secure session handling with automatic timeout</p>
                                </div>
                            </div>

                            <!-- Data Validation -->
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check-double text-purple-600 text-sm"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">Input Validation</h4>
                                    <p class="text-sm text-gray-600">All user input is validated and sanitized for
                                        security</p>
                                </div>
                            </div>

                            <!-- Permissions -->
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-key text-yellow-600 text-sm"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">Access Control</h4>
                                    <p class="text-sm text-gray-600">
                                        <?php if ($user['user_group_id'] === 'admin'): ?>
                                            Full administrative access to all system features
                                        <?php else: ?>
                                            Standard user access with role-based permissions
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Actions -->
            <div class="mt-8 bg-white rounded-lg shadow-md border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Account Actions</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <?php if ($user['user_group_id'] === 'admin'): ?>
                            <a href="/user/admin"
                                class="flex items-center justify-center px-4 py-3 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg transition-colors duration-200 group">
                                <i class="fas fa-cog text-purple-600 mr-2"></i>
                                <span class="font-medium text-purple-700 group-hover:text-purple-800">Admin Panel</span>
                            </a>
                        <?php endif; ?>

                        <a href="/user"
                            class="flex items-center justify-center px-4 py-3 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors duration-200 group">
                            <i class="fas fa-tachometer-alt text-blue-600 mr-2"></i>
                            <span class="font-medium text-blue-700 group-hover:text-blue-800">Dashboard</span>
                        </a>

                        <a href="/user/logout"
                            class="flex items-center justify-center px-4 py-3 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition-colors duration-200 group">
                            <i class="fas fa-sign-out-alt text-red-600 mr-2"></i>
                            <span class="font-medium text-red-700 group-hover:text-red-800">Logout</span>
                        </a>
                    </div>
                </div>
            </div>

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

