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
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-clock mr-1"></i>
                        <?= $stats['loginTime'] ?> on <?= $stats['loginDate'] ?>
                    </span>
                    <a href="/user/logout"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if ($session->getFlashData('success')): ?>
        <div class="container mx-auto px-4 py-4">
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <div>
                        <?php foreach ($session->getFlashData('success') as $message): ?>
                            <p class="text-sm"><?= htmlspecialchars($message) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <!-- User Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Welcome Card -->
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Welcome</h3>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($user['name']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Role Card -->
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-shield-alt text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Role</h3>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($stats['role']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Status</h3>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($stats['status']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Last Login Card -->
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-clock text-indigo-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Session</h3>
                        <p class="text-sm text-gray-600"><?= $stats['loginTime'] ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- User Actions -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6 space-y-4">
                    <a href="/user/profile"
                        class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200 group">
                        <div class="flex-shrink-0">
                            <div
                                class="w-10 h-10 bg-blue-100 group-hover:bg-blue-200 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-edit text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-md font-medium text-gray-900">View Profile</h4>
                            <p class="text-sm text-gray-600">View and manage your account details</p>
                        </div>
                        <div class="ml-auto">
                            <i class="fas fa-chevron-right text-gray-400 group-hover:text-gray-600"></i>
                        </div>
                    </a>

                    <?php if ($user['user_group_id'] === 'admin'): ?>
                        <a href="/user/admin"
                            class="flex items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors duration-200 group">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-10 h-10 bg-purple-100 group-hover:bg-purple-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-cog text-purple-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-md font-medium text-gray-900">Admin Panel</h4>
                                <p class="text-sm text-gray-600">Manage users and system settings</p>
                            </div>
                            <div class="ml-auto">
                                <i class="fas fa-chevron-right text-gray-400 group-hover:text-gray-600"></i>
                            </div>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Framework Features -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Framework Demonstration</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center mt-1">
                                <i class="fas fa-check text-blue-600 text-xs"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">Enhanced Validation</h4>
                                <p class="text-sm text-gray-600">Login form uses comprehensive validation with custom
                                    messages</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-5 h-5 bg-green-100 rounded-full flex items-center justify-center mt-1">
                                <i class="fas fa-check text-green-600 text-xs"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">Security Cleaning</h4>
                                <p class="text-sm text-gray-600">All input is cleaned for XSS and injection prevention
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-5 h-5 bg-purple-100 rounded-full flex items-center justify-center mt-1">
                                <i class="fas fa-check text-purple-600 text-xs"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">View Templates</h4>
                                <p class="text-sm text-gray-600">Complete MVC with Tailwind CSS styled views</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-5 h-5 bg-yellow-100 rounded-full flex items-center justify-center mt-1">
                                <i class="fas fa-check text-yellow-600 text-xs"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">Authentication System</h4>
                                <p class="text-sm text-gray-600">FileDatabase-based auth with role management</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="mt-8 text-center">
            <a href="/"
                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                <i class="fas fa-home mr-2"></i>
                Back to Home
            </a>
        </div>
    </div>
</main>

<?= $view->render('includes/footer'); ?>

